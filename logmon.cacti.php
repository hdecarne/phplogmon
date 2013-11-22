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
	Options::setVerbose(Options::debug() || array_search("--verbose", $argv));
	Log::open(__FILE__, true, Options::verbose(), Options::debug());

	Log::notice(sprintf("Running '%s'...", implode(" ", $argv)));
	$grantedCount = 0;
	$deniedCount = 0;
	$errorCount = 0;
	$dbh = new DBH(DBDSN, DBUSER, DBPASS);
	$select = $dbh->prepare("SELECT typeid, count(typeid) FROM event WHERE last >= ? GROUP BY typeid");
	$select->bindValue(1, time() - 3600, PDO::PARAM_INT);
	$select->execute();
	$select->bindColumn(1, $typeid, PDO::PARAM_STR);
	$select->bindColumn(2, $count, PDO::PARAM_INT);
	while($select->fetch(PDO::FETCH_BOUND) !== false) {
		switch($typeid) {
			case MonitorEvent::TYPEID_GRANTED:
				$grantedCount = $count;
				break;
			case MonitorEvent::TYPEID_DENIED:
				$deniedCount = $count;
				break;
			case MonitorEvent::TYPEID_ERROR:
				$errorCount = $count;
				break;
			default:
				Log::warning("Unknown typeid {$typeid} encountered");
		}
	}
	print "GRANTED:{$grantedCount} DENIED:{$deniedCount} ERROR:{$errorCount}";
	$status = 0;
} catch(Exception $e) {
	Log::err(sprintf("Cacti data generation failed with exception: %s\nDetails: %s", $e->getMessage(), $e));
	$status = 1;
}
if(isset($dbh)) {
	$dbh->close();
}
$elapsed = round(microtime(true) - $elapsed, 3);
Log::notice("Cacti data generation finished with status '{$status}' (Total processing time: {$elapsed} s)");
Log::close();
exit($status);

?>
