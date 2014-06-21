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

class QueryHostmacIp {

	private function __construct() {
	}

	public static function getHostip($dbh, $hostmac) {
		$cache =& $dbh->getCache(get_class());
		return (isset($cache[$hostmac]) ? $cache[$hostmac] : $cache[$hostmac] = self::getDbHostip($dbh, $hostmac));
	}

	private static function getDbHostip($dbh, $hostmac) {
		Log::debug("Retrieving ip for host mac '{$hostmac}'...");
		$select = $dbh->prepare(
			"SELECT c.hostip FROM event a, hostmac b, hostip c ".
			"WHERE a.hostmacid = b.id AND a.hostipid = c.id AND b.hostmac = ? AND c.hostip <> ''".
			"ORDER BY a.last DESC");
		$select->bindValue(1, $hostmac, PDO::PARAM_STR);
		$select->execute();
		$select->bindColumn(1, $hostip, PDO::PARAM_STR);
		if($select->fetch(PDO::FETCH_BOUND) === false) {
			$hostip = "";
		}
		return $hostip;
	}

}

?>
