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

class DBHIPInfo {

	private $_ip;
	private $_host;
	private $_continentcode;
	private $_countrycode;
	private $_countryname;
	private $_region;
	private $_city;
	private $_postalcode;
	private $_latitude;
	private $_longitude;

	private function __construct($ip, $host, $continentcode, $countrycode, $countryname, $region, $city, $postalcode, $latitude, $longitude) {
		$this->_ip = $ip;
		$this->_host = $host;
		$this->_continentcode = $continentcode;
		$this->_countrycode = $countrycode;
		$this->_countryname = $countryname;
		$this->_region = $region;
		$this->_city = $city;
		$this->_postalcode = $postalcode;
		$this->_latitude = $latitude;
		$this->_longitude = $longitude;
	}

	public static function deleteIfUnused($dbh) {
		$delete = $dbh->prepare("DELETE FROM ipevent WHERE ip NOT IN (SELECT ip FROM ipevent)");
	}

	public static function insertOrUpdate($dbh, $ip, $host, $continentcode, $countrycode, $countryname, $region, $city, $postalcode, $latitude, $longitude) {
		$select = $dbh->prepare("SELECT ip FROM ipinfo WHERE ip = :ip");
		$select->bindValue(":ip", $ip, PDO::PARAM_STR);
		$select->execute();
		$row = $select->fetch(PDO::FETCH_NUM);
		if($row !== false) {
			$update = $dbh->prepare("UPDATE ipinfo SET host = :host, continentcode = :continentcode, countrycode = :countrycode, countryname = :countryname, region = :region, city = :city, postalcode = :postalcode, latitude = :latitude, longitude = :longitude WHERE ip = :ip");
			$update->bindValue(":host", $host, PDO::PARAM_INT);
			$update->bindValue(":continentcode", $continentcode, PDO::PARAM_STR);
			$update->bindValue(":countrycode", $countrycode, PDO::PARAM_STR);
			$update->bindValue(":countryname", $countryname, PDO::PARAM_STR);
			$update->bindValue(":region", $region, PDO::PARAM_STR);
			$update->bindValue(":city", $city, PDO::PARAM_STR);
			$update->bindValue(":postalcode", $postalcode, PDO::PARAM_STR);
			$update->bindValue(":latitude", $latitude, PDO::PARAM_STR);
			$update->bindValue(":longitude", $longitude, PDO::PARAM_STR);
			$update->bindValue(":ip", $ip, PDO::PARAM_STR);
			$update->execute();
			$ipevent = new DBHIPInfo($ip, $host, $continentcode, $countrycode, $countryname, $region, $city, $postalcode, $latitude, $longitude);
		} else {
			$insert = $dbh->prepare("INSERT INTO ipinfo (ip, host, continentcode, countrycode, countryname, region, city, postalcode, latitude, longitude) VALUES(:ip, :host, :continentcode, :countrycode, :countryname, :region, :city, :postalcode, :latitude, :longitude)");
			$insert->bindValue(":ip", $ip, PDO::PARAM_STR);
			$insert->bindValue(":host", $host, PDO::PARAM_INT);
			$insert->bindValue(":continentcode", $continentcode, PDO::PARAM_STR);
			$insert->bindValue(":countrycode", $countrycode, PDO::PARAM_STR);
			$insert->bindValue(":countryname", $countryname, PDO::PARAM_STR);
			$insert->bindValue(":region", $region, PDO::PARAM_STR);
			$insert->bindValue(":city", $city, PDO::PARAM_STR);
			$insert->bindValue(":postalcode", $postalcode, PDO::PARAM_STR);
			$insert->bindValue(":latitude", $latitude, PDO::PARAM_STR);
			$insert->bindValue(":longitude", $longitude, PDO::PARAM_STR);
			$insert->execute();
			$ipevent = new DBHIPInfo($ip, $host, $continentcode, $countrycode, $countryname, $region, $city, $postalcode, $latitude, $longitude);
		}
		return $ipevent;
	}

}

?>
