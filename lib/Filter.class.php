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

abstract class Filter {

	const STATUS_INFO = 0;
	const STATUS_GRANTED = 1;
	const STATUS_DENIED = 2;
	const STATUS_ERROR = 3;

	private static $_ipinfoUpdated = array();
	private $_name;

	protected function __construct($name) {
		$this->_name = $name;
	}

	public function __toString() {
		return "Filter:{$this->_name}";
	}

	protected function getFilterConfigParam($param) {
		$key = "FILTER_{$this->_name}_{$param}";
		if(!defined($key)) {
			throw new Exception("Required config parameter '{$key}' is not defined.");
		}
		return constant($key);
	}

	protected function recordIPEvent($dbh, $status, $service, $ip, $user, $ts, $line) {
		DBHIPEvent::insertOrUpdate($dbh, $status, $service, $ip, $user, $ts, $line);
		if(!array_key_exists($ip, self::$_ipinfoUpdated)) {
			$host = gethostbyaddr($ip);
			if($host === false) {
				$host = $ip;
			}
			$record = (extension_loaded("geoip") ?  @geoip_record_by_name($ip) : false);
			if($record !== false) {
				$continentcode = $record["continent_code"];
				$countrycode = $record["country_code"];
				$countryname = $record["country_name"];
				$region = $record["region"];
				$city = $record["city"];
				$postalcode = $record["postal_code"];
				$latitude = $record["latitude"];
				$longitude = $record["longitude"];
			} else {
				$continentcode = null;
				$countrycode = null;
				$countryname = null;
				$region = null;
				$city = null;
				$postalcode = null;
				$latitude = null;
				$longitude = null;
			}
			DBHIPInfo::insertOrUpdate($dbh, $ip, $host, $continentcode, $countrycode, $countryname, $region, $city, $postalcode, $latitude, $longitude);
			self::$_ipinfoUpdated[$ip] = $ip;
		}
	}

	protected function recordMacEvent($dbh, $status, $service, $mac, $ip, $ts, $line) {
		DBHMacEvent::insertOrUpdate($dbh, $status, $service, $mac, $ip, $ts, $line);
	}

	public abstract function process($dbh, $ts, $line);

}

?>
