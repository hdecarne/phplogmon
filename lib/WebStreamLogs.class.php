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

class WebStreamLogs extends WebStream {

	public function __construct($dbh) {
		parent::__construct($dbh);
	}

	public function sendData() {
		$this->sendContentType(self::CONTENT_TYPE_TEXT_PLAIN);
		$download = $this->getRequestDownload() != 0;
		if($download) {
			$this->sendContentDisposition("logs.txt");
		}
		$this->sendLogs();
	}

	private function sendLogs() {
		$dbh = $this->dbh();
		$typeId = $this->getRequestType();
		$loghostId = $this->getRequestLoghost();
		$networkId = $this->getRequestNetwork();
		$serviceId = $this->getRequestService();
		$hostipId = $this->getRequestHostip();
		$hostmacId = $this->getRequestHostmac();
		$userId = $this->getRequestUser();
		$select = $dbh->prepare(
			"SELECT b.line FROM event a, log b ".
			"WHERE ('*' = ? OR a.typeid = ?) AND ('*' = ? OR a.loghostid = ?) AND ('*' = ? OR a.networkid = ?) AND ('*' = ? OR a.serviceid = ?) AND ".
				"('*' = ? OR a.hostipid = ?) AND ('*' = ? OR a.hostmacid = ?) AND ('*' = ? OR a.userid = ?) AND a.id = b.eventid ".
			"ORDER BY b.time ASC");
		$select->bindParam(1, $typeId, PDO::PARAM_STR);
		$select->bindParam(2, $typeId, PDO::PARAM_STR);
		$select->bindParam(3, $loghostId, PDO::PARAM_STR);
		$select->bindParam(4, $loghostId, PDO::PARAM_STR);
		$select->bindParam(5, $networkId, PDO::PARAM_STR);
		$select->bindParam(6, $networkId, PDO::PARAM_STR);
		$select->bindParam(7, $serviceId, PDO::PARAM_STR);
		$select->bindParam(8, $serviceId, PDO::PARAM_STR);
		$select->bindParam(9, $hostipId, PDO::PARAM_STR);
		$select->bindParam(10, $hostipId, PDO::PARAM_STR);
		$select->bindParam(11, $hostmacId, PDO::PARAM_STR);
		$select->bindParam(12, $hostmacId, PDO::PARAM_STR);
		$select->bindParam(13, $userId, PDO::PARAM_STR);
		$select->bindParam(14, $userId, PDO::PARAM_STR);
		$select->execute();
		$select->bindColumn(1, $line, PDO::PARAM_STR);
		while($select->fetch(PDO::FETCH_BOUND) !== false) {
			print($line);
			print("\n");
		}
	}

}

?>
