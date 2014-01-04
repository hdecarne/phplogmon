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

class QueryHostipMac {

	private function __construct() {
	}

	public static function getHostmac($dbh, $hostip) {
		$cache =& $dbh->getCache(get_class());
		return (isset($cache[$hostip]) ? $cache[$hostip] : $cache[$hostip] = self::getDbHostmac($dbh, $hostip));
	}

	private static function getDbHostmac($dbh, $hostip) {
		Log::debug("Retrieving mac for host ip '{$hostip}'...");
		$select = $dbh->prepare(
			"SELECT c.hostmac FROM event a, hostip b, hostmac c ".
			"WHERE a.hostipid = b.id AND a.hostmacid = c.id AND b.hostip = ? AND c.hostmac <> ''".
			"ORDER BY a.last DESC");
		$select->bindValue(1, $hostip, PDO::PARAM_STR);
		$select->execute();
		$select->bindColumn(1, $hostmac, PDO::PARAM_STR);
		if($select->fetch(PDO::FETCH_BOUND) === false) {
			$hostmac = "";
		}
		return $hostmac;
	}

}

?>
