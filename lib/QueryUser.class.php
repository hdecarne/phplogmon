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

class QueryUser {

	private function __construct() {
	}

	private static function getDbUserId($dbh, $user) {
		$select = $dbh->prepare("SELECT a.id FROM user a WHERE user = ?");
		$select->bindValue(1, $user, PDO::PARAM_STR);
		$select->execute();
		$select->bindColumn(1, $id, PDO::PARAM_STR);
		if($select->fetch(PDO::FETCH_BOUND) === false) {
			if(!Options::pretend()) {
				$insert = $dbh->prepare("INSERT INTO user (user) VALUES(?)");
				$insert->bindValue(1, $user, PDO::PARAM_STR);
				$insert->execute();
				$id = $dbh->lastInsertId();
			} else {
				$id = false;
			}
		}
		return $id;
	}

	public static function getUserId($dbh, $user) {
		$cache =& $dbh->getCache(get_class());
		return (isset($cache[$user]) ? $cache[$user] : $cache[$user] = self::getDbUserId($dbh, $user));
	}

	public static function discardUnused($dbh) {
	}

}

?>
