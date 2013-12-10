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

class QueryHostip {

	private function __construct() {
	}

	public static function normalizeHostip($hostip) {
		$normalized = false;
		if($hostip == "") {
			$normalized = $hostip;
		} elseif(($pton = @inet_pton($hostip)) !== false) {
			$normalized = strtolower(inet_ntop($pton));
		}
		return $normalized;
	}

	public static function getHostipId($dbh, $hostip) {
		$cache =& $dbh->getCache(get_class());
		return (isset($cache[$hostip]) ? $cache[$hostip] : $cache[$hostip] = self::getDbHostipId($dbh, $hostip));
	}

	private static function getDbHostipId($dbh, $hostip) {
		if($hostip != "") {
			Log::debug("Retrieving info for ip '{$hostip}'...");
		}
		$host = self::safeGethostbyaddr($hostip);
		$select = $dbh->prepare("SELECT a.id FROM hostip a WHERE hostip = ? AND host = ?");
		$select->bindValue(1, $hostip, PDO::PARAM_STR);
		$select->bindValue(2, $host, PDO::PARAM_STR);
		$select->execute();
		$select->bindColumn(1, $id, PDO::PARAM_STR);
		if($select->fetch(PDO::FETCH_BOUND) === false) {
			$geoipRecord = self::safeGeoiprecordbyname($hostip);
			if(!Options::pretend()) {
				$insert = $dbh->prepare("INSERT INTO hostip (hostip, host, continentcode, countrycode, countryname, region, city, postalcode, latitude, longitude) VALUES(?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
				$insert->bindValue(1, $hostip, PDO::PARAM_STR);
				$insert->bindValue(2, $host, PDO::PARAM_STR);
				$insert->bindValue(3, $geoipRecord["continent_code"], PDO::PARAM_STR);
				$insert->bindValue(4, $geoipRecord["country_code"], PDO::PARAM_STR);
				$insert->bindValue(5, $geoipRecord["country_name"], PDO::PARAM_STR);
				$insert->bindValue(6, $geoipRecord["region"], PDO::PARAM_STR);
				$insert->bindValue(7, $geoipRecord["city"], PDO::PARAM_STR);
				$insert->bindValue(8, $geoipRecord["postal_code"], PDO::PARAM_STR);
				$insert->bindValue(9, $geoipRecord["latitude"], PDO::PARAM_STR);
				$insert->bindValue(10, $geoipRecord["longitude"], PDO::PARAM_STR);
				$insert->execute();
				$id = $dbh->lastInsertId();
			} else {
				$id = false;
			}
		}
		return $id;
	}

	public static function discardUnused($dbh) {
		if(!Options::pretend()) {
			$dbh->clearCache(get_class());
			$delete = $dbh->prepare("DELETE FROM hostip WHERE id NOT IN (SELECT hostipid FROM event)");
			$delete->execute();
		}
	}

	private static function safeGethostbyaddr($hostip) {
		$host = ($hostip != "" ? gethostbyaddr($hostip) : $hostip);
		return ($host !== false ? $host : $hostip);
	}

	private static function safeGeoiprecordbyname($hostip) {
		$record = false;
		if(function_exists("geoip_record_by_name")) {
			$record = @geoip_record_by_name($hostip);
		}
		if($record === false) {
			$record = array(
				"continent_code" => "",
				"country_code" => "",
				"country_code3" => "",
				"country_name" => "",
				"region" => "",
				"city" => "",
				"postal_code" => "",
				"latitude" => 0.0,
				"longitude" => 0.0,
				"dma_code" => "",
				"area_code" => ""
			);
		}
		return $record;
	}

}

?>
