#!/usr/bin/php
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
require_once("lib/autoload.php");
require_once("logmon.conf.php");

mb_internal_encoding("UTF-8");

$logName = $argv[0];
$logDebug = array_search("--debug", $argv);
$logConsole = $logDebug || array_search("--verbose", $argv);
Log::open($logName, true, $logConsole, $logDebug);

$cmdLine = implode(" ", $argv);
Log::notice("Running '{$cmdLine}'...");

$status = -1;
$dbh = null;
try {
	Log::debug("Preparing log file processing...");
	$monitors = evalMonitorConfig();
	if(count($monitors) == 0) {
		throw new Exception(Log::err("No monitors defined. Please check your configuration."));
	}
	Log::debug("Starting log file processing...");
	$dbh = new DBH(DBDSN, DBUSER, DBPASS);
	foreach($monitors as $monitor) {
		Log::debug("Processing {$monitor}...");
		$monitor->process($dbh);
	}
	$dbh->beginTransaction();
	DBHIPEvent::deleteIfOld($dbh, EVENT_DISCARD_THRESHOLD);
	DBHIPInfo::deleteIfUnused($dbh);
	DBHMacEvent::deleteIfOld($dbh, EVENT_DISCARD_THRESHOLD);
	$dbh->commit();
	$status = 0;
} catch(Exception $e) {
	$message = $e->getMessage();
	Log::err("Log file processing failed with exception '{$message}'.\nDetails: {$e}");
	$status = 1;
}
if($dbh !== null) {
	$dbh->close();
	$dbh = null;
}
Log::notice("Log file processing finished with status '{$status}'.");
Log::close();
exit($status);

function evalMonitorConfig() {
	$monitors = array();
	$config = get_defined_constants();
	foreach($config as $key => $value) {
		if(preg_match('/FILTER_(\w+)_MONITOR/', $key, $match) === 1 && $value != false) {
			Log::debug("Preparing filter {$match[1]} for monitor {$value}...");
			if(array_key_exists($value, $monitors)) {
				$monitor = $monitors[$value];
			} else {
				$monitor = newMonitor($value);
				$monitors[$value] = $monitor;
			}
			$filterClass = "{$match[1]}Filter";
			include("filter/{$filterClass}.class.php");
			$filter = new $filterClass;
			$monitor->addFilter($filter);
		}
	}
	return $monitors;
}

function newMonitor($name) {
	$source = getMonitorConfigParam($name, "SOURCE");
	$buflen = getMonitorConfigParam($name, "BUFLEN");
	$tspattern = getMonitorConfigParam($name, "TSPATTERN");
	$tsformat = getMonitorConfigParam($name, "TSFORMAT");
	return new Monitor($name, $source, $buflen, $tspattern, $tsformat);
}

function getMonitorConfigParam($name, $param) {
	$key = "MONITOR_{$name}_{$param}";
	if(!defined($key)) {
		throw new Exception("Required config parameter '{$key}' is not defined.");
	}
	return constant($key);
}

?>
