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

class QueryService {

	private function __construct() {
	}

	private static function getDbServiceId($dbh, $service) {
		$select = $dbh->prepare("SELECT a.id FROM service a WHERE service = ?");
		$select->bindValue(1, $service, PDO::PARAM_STR);
		$select->execute();
		$select->bindColumn(1, $id, PDO::PARAM_STR);
		if($select->fetch(PDO::FETCH_BOUND) === false) {
			if(!Options::pretend()) {
				$insert = $dbh->prepare("INSERT INTO service (service) VALUES(?)");
				$insert->bindValue(1, $service, PDO::PARAM_STR);
				$insert->execute();
				$id = $dbh->lastInsertId();
			} else {
				$id = false;
			}
		}
		return $id;
	}

	public static function getServiceId($dbh, $service) {
		$cache =& $dbh->getCache(get_class());
		return (isset($cache[$service]) ? $cache[$service] : $cache[$service] = self::getDbServiceId($dbh, $service));
	}

	public static function discardUnused($dbh) {
		if(!Options::pretend()) {
			$dbh->clearCache(get_class());
			$delete = $dbh->prepare("DELETE FROM service WHERE id NOT IN (SELECT serviceid FROM event)");
			$delete->execute();
		}
	}

}

?>
