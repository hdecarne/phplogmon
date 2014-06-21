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

class QueryHostip {

	private static $geoip2Reader = null;

	private function __construct() {
	}

	public static function normalizeHostip($hostip) {
		$normalized = false;
		if($hostip === false || $hostip === "") {
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
			$geoipRecord = self::safeGetGeoipRecord($hostip);
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

	private static function safeGetGeoipRecord($hostip) {
		$record = false;
		if($hostip != "") {
			if(GEOIP2_CITY_DATABASE_FILE != false) {
				if(is_null(self::$geoip2Reader)) {
					if(!extension_loaded("bcmath")) {
						Log::warning("Missing required extension 'bcmath' for GeoIP2");
					}
					self::$geoip2Reader = new GeoIp2\Database\Reader(GEOIP2_CITY_DATABASE_FILE);
				}
				try {
					$found = self::$geoip2Reader->city($hostip);
					$record = array(
						"continent_code" => utf8_encode($found->continent->code),
						"country_code" => utf8_encode($found->country->isoCode),
						"country_name" => utf8_encode($found->country->name),
						"region" => utf8_encode($found->mostSpecificSubdivision->isoCode),
						"city" => utf8_encode($found->city->name),
						"postal_code" => utf8_encode($found->postal->code),
						"latitude" => $found->location->latitude,
						"longitude" => $found->location->longitude
					);
				} catch(GeoIp2\Exception\AddressNotFoundException $e) {
				}
			} elseif(function_exists("geoip_record_by_name")) {
				$found = @geoip_record_by_name($hostip);
				if($found !== false) {
					$record = array(
						"continent_code" => utf8_encode($found["continent_code"]),
						"country_code" => utf8_encode($found["country_code"]),
						"country_name" => utf8_encode($found["country_name"]),
						"region" => utf8_encode($found["region"]),
						"city" => utf8_encode($found["city"]),
						"postal_code" => utf8_encode($found["postal_code"]),
						"latitude" => $found["latitude"],
						"longitude" => $found["longitude"]
					);
				}
			}
		}
		if($record === false) {
			$record = array(
				"continent_code" => "",
				"country_code" => "",
				"country_name" => "",
				"region" => "",
				"city" => "",
				"postal_code" => "",
				"latitude" => 0.0,
				"longitude" => 0.0
			);
		}
		return $record;
	}

}

?>
