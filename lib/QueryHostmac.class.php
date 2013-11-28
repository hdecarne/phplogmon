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

class QueryHostmac {

	private static $sVendorCache = array();

	private function __construct() {
	}

	private static function getDbHostmacId($dbh, $hostmac) {
		$select = $dbh->prepare("SELECT a.id FROM hostmac a WHERE hostmac = ?");
		$select->bindValue(1, $hostmac, PDO::PARAM_STR);
		$select->execute();
		$select->bindColumn(1, $id, PDO::PARAM_STR);
		if($select->fetch(PDO::FETCH_BOUND) === false) {
			$vendor = self::getVendor($hostmac);
			if(!Options::pretend()) {
				$insert = $dbh->prepare("INSERT INTO hostmac (hostmac, vendor) VALUES(?, ?)");
				$insert->bindValue(1, $hostmac, PDO::PARAM_STR);
				$insert->bindValue(2, $vendor, PDO::PARAM_STR);
				$insert->execute();
				$id = $dbh->lastInsertId();
			} else {
				$id = false;
			}
		}
		return $id;
	}

	public static function getHostmacId($dbh, $hostmac) {
		$cache =& $dbh->getCache(get_class());
		return (isset($cache[$hostmac]) ? $cache[$hostmac] : $cache[$hostmac] = self::getDbHostmacId($dbh, $hostmac));
	}

	public static function discardUnused($dbh) {
		if(!Options::pretend()) {
			$dbh->clearCache(get_class());
			$delete = $dbh->prepare("DELETE FROM hostmac WHERE id NOT IN (SELECT hostmacid FROM event)");
			$delete->execute();
		}
	}

	private static function getVendor($hostmac) {
		if(count(self::$sVendorCache) == 0) {
			$fh = Files::safeFopen(dirname(__FILE__)."/oui.txt", "r");
			while(($line = fgets($fh)) !== false) {
				if(preg_match("/^(.*)\\(hex\\)(.*)$/U", $line, $matches) === 1) {
					$vendorPrefix = strtoupper(str_replace("-", ":", trim($matches[1])));
					$vendor = trim($matches[2]);
					self::$sVendorCache[$vendorPrefix] = $vendor;
				}
			}
			fclose($fh);
		}
		$hostmacPrefix = substr($hostmac, 0, 8);
		return (isset(self::$sVendorCache[$hostmacPrefix]) ? self::$sVendorCache[$hostmacPrefix] : $hostmacPrefix);
	}

}

?>
