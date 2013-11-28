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

$status = -1;
$elapsed = microtime(true);
try {
	$config = dirname(__FILE__)."/logmon.conf.php";
	$requiredConfigs = array($config);
	CheckConfig::configs($requiredConfigs);
	require_once($config);

	$requiredExtensions = array("mbstring", "pcre", "PDO");
	CheckConfig::extensions($requiredExtensions);

	mb_internal_encoding("UTF-8");

	Options::setDebug(DEBUG || array_search("--debug", $argv));
	Options::setPretend(array_search("--pretend", $argv));
	Options::setVerbose(Options::debug() || Options::pretend() || array_search("--verbose", $argv));
	Log::open(__FILE__, true, Options::verbose(), Options::debug());

	Log::notice(sprintf("Running '%s'...", implode(" ", $argv)));
	$monitor = Monitor::create(dirname(__FILE__)."/monitor");
	if($monitor !== false) {
		$sources = $monitor->getEnabledSources();
		$dbh = new DBH(DBDSN, DBUSER, DBPASS);
		$processor = new Processor($dbh);
		foreach($sources as $source) {
			$sourceEvents = $monitor->getSourceEvents($source);
			$processor->process($source, $sourceEvents);
		}
		$processor->discard(EVENT_DISCARD_THRESHOLD);
		$status = 0;
	} else {
		$status = 1;
	}
} catch(Exception $e) {
	Log::err(sprintf("Log file processing failed with exception: %s\nDetails: %s", $e->getMessage(), $e));
	$status = 1;
}
if(isset($dbh)) {
	$dbh->close();
}
$elapsed = round(microtime(true) - $elapsed, 3);
Log::notice("Log file processing finished with status '{$status}' (Total processing time: {$elapsed} s)");
Log::close();
exit($status);

?>
