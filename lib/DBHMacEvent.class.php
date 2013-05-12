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

class DBHMacEvent {

	private $_status;
	private $_service;
	private $_mac;
	private $_ip;
	private $_count;
	private $_first;
	private $_last;
	private $_line;

	private function __construct($status, $service, $mac, $ip, $count, $first, $last, $line) {
		$this->_status = $status;
		$this->_service = $service;
		$this->_mac = $mac;
		$this->_ip = $ip;
		$this->_count = $count;
		$this->_last = $last;
		$this->_line = $line;
	}

	public static function deleteIfOld($dbh, $threshold) {
		$delete = $dbh->prepare("DELETE FROM macevent WHERE last < :limit");
		$delete->bindValue(":limit", time() - $threshold);
		$delete->execute();
	}

	public static function insertOrUpdate($dbh, $status, $service, $mac, $ip, $ts, $line) {
		$select = $dbh->prepare("SELECT count, first, last, line FROM macevent WHERE status = :status AND service = :service AND mac = :mac AND ip = :ip");
		$select->bindValue(":status", $status, PDO::PARAM_INT);
		$select->bindValue(":service", $service, PDO::PARAM_STR);
		$select->bindValue(":mac", $mac, PDO::PARAM_STR);
		$select->bindValue(":ip", $ip, PDO::PARAM_STR);
		$select->execute();
		$row = $select->fetch(PDO::FETCH_NUM);
		if($row !== false) {
			$count = $row[0];
			$first = $row[1];
			$last = $row[2];
			$countUpdate = $count + 1;
			$firstUpdate = ($ts < $first ? $ts : $first);
			$lastUpdate = ($ts > $last ? $ts : $last);
			$update = $dbh->prepare("UPDATE macevent SET count = :count, first = :first, last = :last, line = :line WHERE status = :status AND service = :service AND mac = :mac AND ip = :ip");
			$update->bindValue(":count", $countUpdate, PDO::PARAM_INT);
			$update->bindValue(":first", $firstUpdate, PDO::PARAM_INT);
			$update->bindValue(":last", $lastUpdate, PDO::PARAM_INT);
			$update->bindValue(":line", $line, PDO::PARAM_STR);
			$update->bindValue(":status", $status, PDO::PARAM_INT);
			$update->bindValue(":service", $service, PDO::PARAM_STR);
			$update->bindValue(":mac", $mac, PDO::PARAM_STR);
			$update->bindValue(":ip", $ip, PDO::PARAM_STR);
			$update->execute();
			$ipevent = new DBHMacEvent($status, $service, $mac, $ip, $countUpdate, $firstUpdate, $lastUpdate, $line);
		} else {
			$insert = $dbh->prepare("INSERT INTO macevent (status, service, mac, ip, count, first, last, line) VALUES(:status, :service, :mac, :ip, 1, :ts, :ts, :line)");
			$insert->bindValue(":status", $status, PDO::PARAM_INT);
			$insert->bindValue(":service", $service, PDO::PARAM_STR);
			$insert->bindValue(":mac", $mac, PDO::PARAM_STR);
			$insert->bindValue(":ip", $ip, PDO::PARAM_STR);
			$insert->bindValue(":ts", $ts, PDO::PARAM_INT);
			$insert->bindValue(":line", $line, PDO::PARAM_STR);
			$insert->execute();
			$ipevent = new DBHMacEvent($status, $service, $mac, $ip, 1, $ts, $ts, $line);
		}
		return $ipevent;
	}

}

?>
