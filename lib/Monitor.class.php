<?php
/**
 * phplogmon
 *
 * Copyright (c) 2012-2014 Holger de Carne and contributors, All Rights Reserved.
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
	private $tReadNetworkmaps = array();
	private $tReadUserdbs = array();
	private $tReadEvents = array();
	private $tEnabledSources = array();

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
				foreach($reader->getNetworkmaps() as $networkmap) {
					$monitor->tReadNetworkmaps[] = $networkmap;
					Log::debug("Read network map '{$networkmap}'");
				}
				foreach($reader->getUserdbs() as $userdb) {
					$monitor->tReadUserdbs[] = $userdb;
					Log::debug("Read user db '{$userdb}'");
				}
				foreach($reader->getEvents() as $event) {
					$monitor->tReadEvents[] = $event;
					Log::debug("Read event '{$event}'");
				}
			}
		}
		self::validateSourceReferences($monitor->tReadSources, $monitor->tReadNetworkmaps,
			$monitor->tReadUserdbs, $monitor->tReadEvents);
		$monitor->tEnabledSources = self::filterUnreferencedSources(self::filterDuplicateSources($monitor->tReadSources),
			$monitor->tReadNetworkmaps, $monitor->tReadUserdbs, $monitor->tReadEvents);
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

	private static function validateSourceReferences($sources, $networkmaps, $userdbs, $events) {
		$sourceNames = array();
		foreach($sources as $source) {
			$sourceName = $source->getName();
			$sourceNames[$sourceName] = $sourceName;
		}
		foreach($networkmaps as $networkmap) {
			foreach($networkmap->getSourceNames() as $networkmapSourceName) {
				if(!isset($sourceNames[$networkmapSourceName])) {
					Log::warning("Unknown source reference '{$networkmapSourceName}' used by network map {$networkmap}");
				}
			}
		}
		foreach($userdbs as $userdb) {
			foreach($userdb->getSourceNames() as $userdbSourceName) {
				if(!isset($sourceNames[$userdbSourceName])) {
					Log::warning("Unknown source reference '{$userdbSourceName}' used by user db {$userdb}");
				}
			}
		}
		foreach($events as $event) {
			foreach($event->getSourceNames() as $eventSourceName) {
				if(!isset($sourceNames[$eventSourceName])) {
					Log::warning("Unknown source reference '{$eventSourceName}' used by event {$event}");
				}
			}
		}
	}

	private static function filterDuplicateSources($sources) {
		$duplicate = array();
		foreach($sources as $source) {
			if(!isset($duplicate[$source->getName()])) {
				$duplicate[$source->getName()] = 1;
			} else {
				$duplicate[$source->getName()]++;
			}
		}
		$filtered = array();
		foreach($sources as $source) {
			if($duplicate[$source->getName()] == 1) {
				$filtered[] = $source;
			} else {
				Log::warning("Ignoring duplicate source '{$source}'");
			}
		}
		return $filtered;
	}

	private static function filterUnreferencedSources($sources, $networkmaps, $userdbs, $events) {
		$filtered = array();
		foreach($sources as $source) {
			$sourceName = $source->getName();
			$networkmapRefCount = 0;
			foreach($networkmaps as $networkmap) {
				$networkmapSourceNames = $networkmap->getSourceNames();
				foreach($networkmapSourceNames as $networkmapSourceName) {
					if($sourceName == $networkmapSourceName) {
						$networkmapRefCount++;
					}
				}
			}
			$userdbRefCount = 0;
			foreach($userdbs as $userdb) {
				$userdbSourceNames = $userdb->getSourceNames();
				foreach($userdbSourceNames as $userdbSourceName) {
					if($sourceName == $userdbSourceName) {
						$userdbRefCount++;
					}
				}
			}
			$eventRefCount = 0;
			foreach($events as $event) {
				$eventSourceNames = $event->getSourceNames();
				foreach($eventSourceNames as $eventSourceName) {
					if($sourceName == $eventSourceName) {
						$eventRefCount++;
					}
				}
			}
			if($eventRefCount > 0 && $networkmapRefCount <= 1 && $userdbRefCount <= 1) {
				$filtered[] = $source;
			} else {
				if($eventRefCount == 0) {
					Log::warning("Ignoring unused source '{$source}'");
				}
				if($networkmapRefCount > 1) {
					Log::warning("Ignoring source '{$source}' due to non-unique network map");
				}
				if($userdbRefCount > 1) {
					Log::warning("Ignoring source '{$source}' due to non-unique user db");
				}
			}
		}
		return $filtered;
	}

	public function getEnabledSources() {
		return $this->tEnabledSources;
	}

	public function getSourceNetworkmap($source) {
		$networkmaps = array();
		$sourceName = $source->getName();
		foreach($this->tReadNetworkmaps as $networkmap) {
			foreach($networkmap->getSourceNames() as $networkmapSourceName) {
				if($sourceName == $networkmapSourceName) {
					$networkmaps[] = $networkmap;
					break;
				}
			}
		}
		$networkmapCount = count($networkmaps);
		if($networkmapCount == 0) {
			$networkmaps[] = new MonitorNetworkmap("internal", "external");
		} elseif($networkmapCount > 1) {
			throw new Exception(Log::err("Unexpected number ({$networkmapCount}) of network maps references to {$source}"));
		}
		return $networkmaps[0];
	}

	public function getSourceUserdb($source) {
		$userdbs = array();
		$sourceName = $source->getName();
		foreach($this->tReadUserdbs as $userdb) {
			foreach($userdb->getSourceNames() as $userdbSourceName) {
				if($sourceName == $userdbSourceName) {
					$userdbs[] = $userdb;
					break;
				}
			}
		}
		$userdbCount = count($userdbs);
		if($userdbCount == 0) {
			$userdbs[] = new MonitorUserdb(Userdb::TYPE_NONE);
		} elseif($userdbCount > 1) {
			throw new Exception(Log::err("Unexpected number ({$userdbCount}) of user db references to {$source}"));
		}
		$userdb = $userdbs[0];
		return Userdb::create($userdb->getType(), $userdb->getProperties());
	}

	public function getSourceEvents($source) {
		$events = array();
		$sourceName = $source->getName();
		foreach($this->tReadEvents as $event) {
			foreach($event->getSourceNames() as $eventSourceName) {
				if($sourceName == $eventSourceName) {
					$events[] = $event;
					break;
				}
			}
		}
		return $events;
	}

}

?>
