<?php
/**
 * phplogmon
 *
 * Copyright (c) 2012-2013 Holger de Carne and contributors, All Rights Reserved.
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

class MonitorXmlReader {

	const XML_VERSION = "1";

	private $tParser = null;
	private $tParseErrors;
	private $tParsePathStack;
	private $tParseDataStack;
	private $tParseHandlerStack;
	private $tParsedSources;
	private $tParsedEvents;
	private $tCurrentSource;
	private $tCurrentEventsSourceid;
	private $tCurrentEventsService;
	private $tCurrentEvent;

	public function __destruct() {
		if(!is_null($this->tParser)) {
			xml_parser_free($this->tParser);
			$this->tParser = null;
		}
	}

	public static function validVersions() {
		return array(self::XML_VERSION);
	}

	private function resetParser() {
		if(!is_null($this->tParser)) {
			xml_parser_free($this->tParser);
		}
		$this->tParser = xml_parser_create();
		$this->tParseErrors = array();
		$this->tParsePathStack = array();
		$this->tParseDataStack = array();
		$this->tParseHandlerStack = array();
		$this->tParsedSources = array();
		$this->tParsedEvents = array();
		$this->tCurrentSource = null;
		$this->tCurrentEventsSourceid = null;
		$this->tCurrentEventsService = null;
		$this->tCurrentEvent = null;
		xml_parser_set_option($this->tParser, XML_OPTION_CASE_FOLDING, 0);
		xml_set_object($this->tParser, $this);
		xml_set_element_handler($this->tParser, array($this, "beginElement"), array($this, "endElement"));
		xml_set_character_data_handler($this->tParser, array($this, "elementData"));
	}

	public function read($xml) {
		Log::debug("Reading monitor configuration from file '{$xml}'...");
		$this->resetParser();
		$xmlData = file_get_contents($xml);
		if($xmlData === false) {
			throw new Exception(Log::err("Failed to read file '{$xml}'"));
		}
		$readSucceeded = xml_parse($this->tParser, $xmlData, true) == 1 && count($this->tParseErrors) == 0;
		if(!$readSucceeded) {
			Log::err("Failed to parse file '{$xml}'");
			$errorCode = xml_get_error_code($this->tParser);
			if($errorCode != XML_ERROR_NONE) {
				$error = xml_error_string($errorCode);
				Log::err(" {$error}");
			}
			foreach($this->tParseErrors as $error) {
				Log::err(" {$error}");
			}
		}
		return $readSucceeded;
	}

	public function getSources() {
		return $this->tParsedSources;
	}

	public function getEvents() {
		return $this->tParsedEvents;
	}

	private function beginElement($parser, $name, $attribs) {
		$depth = count($this->tParsePathStack);
		$path = ($depth > 0 ? $this->tParsePathStack[$depth - 1]."/".$name : $name);
		switch($path) {
		case "logmon":
			$this->validateAttribs($attribs, array("version"), array());
			$handlers = array("beginLogmon", null, null);
			break;
		case "logmon/source":
			$this->validateAttribs($attribs, array("id", "loghost"), array("service"));
			$handlers = array("beginSource", null, "endSource");
			break;
		case "logmon/source/tspattern":
			$this->validateAttribs($attribs, array(), array());
			$handlers = array(null, "dataSourceTspattern", null);
			break;
		case "logmon/source/tsformat":
			$this->validateAttribs($attribs, array(), array());
			$handlers = array(null, "dataSourceTsformat", null);
			break;
		case "logmon/source/file":
			$this->validateAttribs($attribs, array(), array("service", "decoder"));
			$handlers = array(null, "dataSourceFile", null);
			break;
		case "logmon/events":
			$this->validateAttribs($attribs, array(), array("sourceid", "service"));
			$handlers = array("beginEvents", null, "endEvents");
			break;
		case "logmon/events/event":
			$this->validateAttribs($attribs, array("type"), array("sourceid", "service"));
			$handlers = array("beginEvent", null, "endEvent");
			break;
		case "logmon/events/event/pattern":
			$this->validateAttribs($attribs, array(), array());
			$handlers = array(null, "dataEventPattern", null);
			break;
		case "logmon/events/event/user":
			$this->validateAttribs($attribs, array(), array("decoder"));
			$handlers = array(null, "dataEventUser", null);
			break;
		case "logmon/events/event/hostip":
			$this->validateAttribs($attribs, array(), array("decoder"));
			$handlers = array(null, "dataEventHostip", null);
			break;
		case "logmon/events/event/hostmac":
			$this->validateAttribs($attribs, array(), array("decoder"));
			$handlers = array(null, "dataEventHostmac", null);
			break;
		case "logmon/events/event/service":
			$this->validateAttribs($attribs, array(), array("decoder"));
			$handlers = array(null, "dataEventService", null);
			break;
		default:
			$line = xml_get_current_line_number($this->tParser);
			$column = xml_get_current_column_number($this->tParser);
			$this->tParseErrors[] = "Unexpected element '{$name}' at line:{$line} column:{$column}";
			$handlers = array(null, null, null);
		}
		$beginHandler = $handlers[0];
		if(count($this->tParseErrors) == 0 && !is_null($beginHandler)) {
			call_user_func(array($this, $beginHandler), $attribs);
		}
		array_push($this->tParsePathStack, $path);
		array_push($this->tParseDataStack, (count($this->tParseErrors) == 0 ? array_merge(array(), $attribs) : false));
		array_push($this->tParseHandlerStack, $handlers);
	}

	private function elementData($parser, $rawvalue) {
		$depth = count($this->tParsePathStack);
		$data = $this->tParseDataStack[$depth - 1];
		if($data !== false) {
			$data["value"] = trim($rawvalue);
			$dataHandler = $this->tParseHandlerStack[$depth - 1][1];
			if(!is_null($dataHandler)) {
				call_user_func(array($this, $dataHandler), $data);
			}
		}
	}

	private function endElement($parser, $name) {
		$path = array_pop($this->tParsePathStack);
		$data = array_pop($this->tParseDataStack);
		$handlers = array_pop($this->tParseHandlerStack);
		if($data !== false) {
			$endHandler = $handlers[2];
			if(!is_null($endHandler)) {
				call_user_func(array($this, $endHandler), $data);
			}
		}
	}

	private function validateAttribs($attribs, $required, $optional) {
		$valid = true;
		foreach($attribs as $key => $value) {
			if(array_search($key, $required) === false && array_search($key, $optional) === false) {
				$line = xml_get_current_line_number($this->tParser);
				$column = xml_get_current_column_number($this->tParser);
				$this->tParseErrors[] = "Unknown attribute '{$key}'='{$value}' at line:{$line} column:{$column}";
				$valid = false;
			}
		}
		foreach($required as $key) {
			if(array_key_exists($key, $attribs) == false) {
				$line = xml_get_current_line_number($this->tParser);
				$column = xml_get_current_column_number($this->tParser);
				$this->tParseErrors[] = "Missing attribute '{$key}' at line:{$line} column:{$column}";
				$valid = false;
			}
		}
		return $valid;
	}

	private function validateAttributeValue($name, $value, $validValues = array()) {
		$valid = true;
		if($value == "") {
			$line = xml_get_current_line_number($this->tParser);
			$column = xml_get_current_column_number($this->tParser);
			$this->tParseErrors[] = "Empty '{$name}' attribute at line:{$line} column:{$column}";
			$valid = false;
		}
		if(count($validValues) > 0 && array_search($value, $validValues) === false) {
			$line = xml_get_current_line_number($this->tParser);
			$column = xml_get_current_column_number($this->tParser);
			$this->tParseErrors[] = "Invalid '{$name}' attribute value '{$value}' at line:{$line} column:{$column}";
			$valid = false;
		}
		return $valid;
	}

	private function validateElementValue($name, $value) {
		$valid = true;
		if($value == "") {
			$line = xml_get_current_line_number($this->tParser);
			$column = xml_get_current_column_number($this->tParser);
			$this->tParseErrors[] = "Invalid '{$name}' element value at line:{$line} column:{$column}";
			$valid = false;
		}
		return $valid;
	}

	private function validateElementExists($name, $exists) {
		if(!$exists) {
			$line = xml_get_current_line_number($this->tParser);
			$column = xml_get_current_column_number($this->tParser);
			$this->tParseErrors[] = "Missing required '{$name}' element at line:{$line} column:{$column}";
			$valid = false;
		}
		return $exists;
	}

	private function beginLogmon($data) {
		$version = trim($data["version"]);
		$this->validateAttributeValue("version", $version, self::validVersions());
	}

	private function beginSource($data) {
		$id = trim($data["id"]);
		$idValid = $this->validateAttributeValue("id", $id);
		$loghost = trim($data["loghost"]);
		$loghostValid = $this->validateAttributeValue("loghost", $loghost);
		if(isset($data["service"])) {
			$service = trim($data["service"]);
			$serviceValid = $this->validateAttributeValue("service", $service);
		} else {
			$service = "";
			$serviceValid = true;
		}
		if($idValid && $loghostValid && $serviceValid) {
			$this->tCurrentSource = new MonitorSource($id, $loghost, $service);
		}
	}

	private function endSource($data) {
		$tspatternValid = $this->validateElementExists("tspattern", $this->tCurrentSource->getTspattern() != null);
		$tspatternValid = $this->validateElementExists("tsformat", $this->tCurrentSource->getTsformat() != null);
		$filesValid =  $this->validateElementExists("file", count($this->tCurrentSource->getFiles()) > 0);
		if($tspatternValid && $tspatternValid && $filesValid) {
			$this->tParsedSources[] = $this->tCurrentSource;
		}
		$this->tCurrentSource = null;
	}

	private function dataSourceTspattern($data) {
		$tspattern = $data["value"];
		$this->validateElementValue("tspattern", $tspattern);
		$this->tCurrentSource->setTspattern($tspattern);
	}

	private function dataSourceTsformat($data) {
		$tsformat = $data["value"];
		$this->validateElementValue("tsformat", $tsformat);
		$this->tCurrentSource->setTsformat($tsformat);
	}

	private function dataSourceFile($data) {
		if(isset($data["decoder"])) {
			$decoder = trim($data["decoder"]);
			$decoderValid = $this->validateAttributeValue("decoder", $decoder);
		} else {
			$decoder = MonitorSourceFile::DECODER_DEFAULT;
			$decoderValid = true;
		}
		$file = $data["value"];
		$fileValid = $this->validateElementValue("file", $file);
		if($decoderValid && $fileValid) {
			$this->tCurrentSource->addFile($file, $decoder);
		}
	}

	private function beginEvents($data) {
		if(isset($data["sourceid"])) {
			$sourceid = trim($data["sourceid"]);
			$sourceidValid = $this->validateAttributeValue("sourceid", $sourceid);
		} else {
			$sourceid = "";
			$sourceidValid = true;
		}
		if(isset($data["service"])) {
			$service = trim($data["service"]);
			$serviceValid = $this->validateAttributeValue("service", $service);
		} else {
			$service = "";
			$serviceValid = true;
		}
		$this->tCurrentEventsSourceid = $sourceid;
		$this->tCurrentEventsService = $service;
	}

	private function endEvents($data) {
		$this->tCurrentEventsSourceid = null;
		$this->tCurrentEventsService = null;
	}

	private function beginEvent($data) {
		$type = trim($data["type"]);
		$typeValid = $this->validateAttributeValue("type", $type, MonitorEvent::validTypes());
		if(isset($data["sourceid"])) {
			$sourceid = trim($data["sourceid"]);
			$sourceidValid = $this->validateAttributeValue("sourceid", $sourceid);
		} else {
			$sourceid = $this->tCurrentEventsSourceid;
			$sourceidValid = true;
		}
		if(isset($data["service"])) {
			$service = trim($data["service"]);
			$serviceValid = $this->validateAttributeValue("service", $service);
		} else {
			$service = $this->tCurrentEventsService;
			$serviceValid = true;
		}
		if($typeValid && $sourceidValid && $serviceValid) {
			$this->tCurrentEvent = new MonitorEvent($type, $sourceid, $service);
		}
	}

	private function endEvent($data) {
		$patternsValid = $this->validateElementExists("pattern", count($this->tCurrentEvent->getPatterns()) > 0);
		$userEvaluatorValid = $this->validateElementExists("user", $this->tCurrentEvent->getUserEvaluator() != null);
		if($patternsValid && $userEvaluatorValid) {
			$this->tParsedEvents[] = $this->tCurrentEvent;
		}
		$this->tCurrentEvent = null;
	}

	private function dataEventPattern($data) {
		$pattern = trim($data["value"]);
		$patternValid = $this->validateElementValue("pattern", $pattern);
		if($patternValid) {
			$this->tCurrentEvent->addPattern($pattern);
		}
	}

	private function dataEventUser($data) {
		$this->dataEventEvaluator("user", "setUserEvaluator", $data);
	}

	private function dataEventHostip($data) {
		$this->dataEventEvaluator("hostip", "setHostipEvaluator", $data);
	}

	private function dataEventHostmac($data) {
		$this->dataEventEvaluator("hostmac", "setHostmacEvaluator", $data);
	}

	private function dataEventService($data) {
		$this->dataEventEvaluator("service", "setServiceEvaluator", $data);
	}

	private function dataEventEvaluator($name, $setter, $data) {
		if(isset($data["decoder"])) {
			$decoder = trim($data["decoder"]);
			$decoderValid = $this->validateAttributeValue("decoder", $decoder);
		} else {
			$decoder = MonitorEventEvaluator::DEFAULT_DECODER;
			$decoderValid = true;
		}
		$evaluator = trim($data["value"]);
		$evaluatorValid = $this->validateElementValue($name, $evaluator);
		if($decoderValid && $evaluatorValid) {
			call_user_func(array($this->tCurrentEvent, $setter), $evaluator, $decoder);
		}
	}

}

?>
