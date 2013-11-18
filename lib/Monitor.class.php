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

class Monitor {

	private $tReadSources = array();
	private $tEnabledSources = array();
	private $tReadEvents = array();

	private function __construct() {
	}

	public static function create($path) {
		Log::debug("Reading monitor configuration from directory '{$path}'...");
		$xmls = self::scanXmls($path);
		$reader = new MonitorXmlReader();
		$monitor = new self();
		foreach($xmls as $xml) {
			if($reader->read(Files::path($path, $xml))) {
				foreach($reader->getSources() as $source) {
					$monitor->tReadSources[] = $source;
					Log::debug("Read source '{$source}'");
				}
				foreach($reader->getEvents() as $event) {
					$monitor->tReadEvents[] = $event;
					Log::debug("Read event '{$event}'");
				}
			}
		}
		$sourceRefs = array();
		$sourceEnabled = array();
		foreach($monitor->tReadSources as $source) {
			$sourceid = $source->getId();
			if(!isset($sourceRefs[$sourceid])) {
				$sourceRefs[$sourceid] = 0;
				$sourceEnabled[$sourceid] = true;
			} else {
				$sourceEnabled[$sourceid] = false;
			}
		}
		foreach($monitor->tReadEvents as $event) {
			$eventSourceid = $event->getSourceid();
			if(isset($sourceRefs[$eventSourceid])) {
				$sourceRefs[$eventSourceid]++;
			} else {
				Log::warning("Ignoring event '{$event}' due to undefined source '{$eventSourceid}'");
			}
		}
		foreach($monitor->tReadSources as $source) {
			$sourceid = $source->getId();
			if($sourceEnabled[$sourceid] == false) {
				Log::warning("Ignoring duplicate source '{$source}'");
			} elseif($sourceRefs[$source->getId()] == 0) {
				Log::warning("Ignoring unused source '{$source}'");
			} else {
				$monitor->tEnabledSources[] = $source;
			}
		}
		if(count($monitor->tEnabledSources) == 0) {
			Log::err("Found no active source/events definitions while reading monitor configuration from directory '{$path}'");
			$monitor = false;
		}
		return $monitor;
	}

	private static function scanXmls($path) {
		$xmls = array();
		$dir = Files::safeOpendir($path);
		while(($file = Files::readdirMatch($dir, "*.xml")) !== false) {
			$xmls[] = $file;
		}
		closedir($dir);
		return $xmls;
	}

	public function getEnabledSources() {
		return $this->tEnabledSources;
	}

	public function getSourceEvents($source) {
		$events = array();
		$sourceid = $source->getId();
		foreach($this->tReadEvents as $event) {
			if($event->getSourceid() == $sourceid) {
				$events[] = $event;
			}
		}
		return $events;
	}

}

?>
