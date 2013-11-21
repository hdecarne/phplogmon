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

class MonitorEvent {

	const SERVICE_UNKNOWN = "unknown";

	const TYPE_GRANTED = "granted";
	const TYPE_DENIED = "denied";
	const TYPE_ERROR = "error";

	const TYPEID_GRANTED = 1;
	const TYPEID_DENIED = 2;
	const TYPEID_ERROR = 3;

	private static $sTypeMap = array(
		self::TYPE_GRANTED => self::TYPEID_GRANTED,
		self::TYPE_DENIED => self::TYPEID_DENIED,
		self::TYPE_ERROR => self::TYPEID_ERROR
	);

	private $tType;
	private $tSourceid;
	private $tDefaultService;
	private $tPatterns = array();
	private $tUserEvaluator = null;
	private $tHostipEvaluator = null;
	private $tHostmacEvaluator = null;
	private $tServiceEvaluator = null;

	public function __construct($type, $sourceid, $defaultService) {
		$this->tType = $type;
		$this->tSourceid = $sourceid;
		$this->tDefaultService = $defaultService;
	}

	public function __toString() {
		$patternString = "";
		$patternIndex = 0;
		foreach($this->tPatterns as $pattern) {
			if(strlen($patternString) > 0) {
				$patternString .= ";";
			}
			$patternString .= "pattern[{$patternIndex}]='{$pattern}'";
			$patternIndex++;
		}
		return "type={$this->tType};sourceid={$this->tSourceid};{$patternString}";
	}

	public static function validTypes() {
		return array_keys(self::$sTypeMap);
	}

	public function getType() {
		return $this->tType;
	}

	public function getTypeid() {
		return self::$sTypeMap[$this->tType];
	}

	public function getSourceid() {
		return $this->tSourceid;
	}

	public function getDefaultService() {
		return $this->tDefaultService;
	}

	public function addPattern($pattern) {
		$this->tPatterns[] = $pattern;
	}

	public function getPatterns() {
		return $this->tPatterns;
	}

	public function setUserEvaluator($evaluator, $decoder) {
		$this->tUserEvaluator = new MonitorEventEvaluator($evaluator, $decoder);
	}

	public function getUserEvaluator() {
		return $this->tUserEvaluator;
	}

	public function setHostipEvaluator($evaluator, $decoder) {
		$this->tHostipEvaluator = new MonitorEventEvaluator($evaluator, $decoder);
	}

	public function getHostipEvaluator() {
		return $this->tHostipEvaluator;
	}

	public function setHostmacEvaluator($evaluator, $decoder) {
		$this->tHostmacEvaluator = new MonitorEventEvaluator($evaluator, $decoder);
	}

	public function getHostmacEvaluator() {
		return $this->tHostmacEvaluator;
	}

	public function setServiceEvaluator($evaluator, $decoder) {
		$this->tServiceEvaluator = new MonitorEventEvaluator($evaluator, $decoder);
	}

	public function getServiceEvaluator() {
		return $this->tServiceEvaluator;
	}

}

?>
