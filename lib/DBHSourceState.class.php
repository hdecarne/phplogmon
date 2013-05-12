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

class DBHSourceState {

	private $_dbh;
	private $_id;
	private $_monitor;
	private $_file;
	private $_mtime;
	private $_last;
	private $_touched;

	private function __construct($dbh, $id, $monitor, $file, $mtime, $last) {
		$this->_dbh = $dbh;
		$this->_id = $id;
		$this->_monitor = $monitor;
		$this->_file = $file;
		$this->_mtime = $mtime;
		$this->_last = $last;
		$this->_touched = false;
	}

	public static function queryAll($dbh, $monitorName) {
		$states = array();
		$select = $dbh->prepare("SELECT id, monitor, file, mtime, last FROM sourcestate WHERE monitor = :monitor");
		$select->bindValue(":monitor", $monitorName, PDO::PARAM_STR);
		$select->execute();
		$row = $select->fetch(PDO::FETCH_NUM);
		while($row !== false) {
			$id = $row[0];
			$monitor = $row[1];
			$file = $row[2];
			$mtime = $row[3];
			$last = $row[4];
			$states[$file] = new DBHSourceState($dbh, $id, $monitor, $file, $mtime, $last);
			$row = $select->fetch(PDO::FETCH_NUM);
		}
		return $states;
	}

	public static function addNew($files, $dbh, $monitorName, $file) {
		$insert = $dbh->prepare("INSERT INTO sourcestate (monitor, file, mtime, last) VALUES(:monitor, :file, :mtime, :last)");
		$insert->bindValue(":monitor", $monitorName, PDO::PARAM_STR);
		$insert->bindValue(":file", $file, PDO::PARAM_STR);
		$insert->bindValue(":mtime", 0, PDO::PARAM_INT);
		$insert->bindValue(":last", 0, PDO::PARAM_INT);
		$insert->execute();
		return $files[$file] = new DBHSourceState($dbh, $dbh->lastInsertId(), $monitorName, $file, 0, 0);
	}

	public function touch($mtime) {
		$this->_touched = true;
		$updated = $this->_mtime != $mtime;
		$this->_mtime = $mtime;
		return $updated;
	}

	public function last() {
		return $this->_last;
	}

	public function update($last) {
		$update = $this->_dbh->prepare("UPDATE sourcestate SET mtime = :mtime, last = :last WHERE id = :id");
		$update->bindValue(":mtime", $this->_mtime, PDO::PARAM_INT); 
		$update->bindValue(":last", $last, PDO::PARAM_INT); 
		$update->bindValue(":id", $this->_id, PDO::PARAM_INT); 
		$update->execute();
	}

	public function deleteIfUntouched() {
		if(!$this->_touched) {
			$delete = $this->_dbh->prepare("DELETE FROM sourcestate WHERE id = :id");
			$delete->bindValue(":id", $this->_id, PDO::PARAM_INT); 
			$delete->execute();
		}
	}

}

?>
