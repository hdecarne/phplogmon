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

class DBH {

	private $_dbh = null;
	private $_stmts = array();

	public function __construct($dsn, $user, $pass) {
		Log::debug("Establishing database connection '{$dsn}' with user '{$user}'...");
		$this->_dbh = new PDO($dsn, $user, $pass);
		$this->_dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	}

	public function close() {
		Log::debug("Closing database connection...");
		if($this->_dbh !== null) {
			if($this->_dbh->inTransaction()) {
				Log::warning("Pending transaction detected, performing rollback.");
			}
			foreach($this->_stmts as $stmt) {
				$stmt->closeCursor();
			}
			$this->_stmts = array();
			$this->_dbh = null;
		}
	}

	public function beginTransaction() {
		$this->_dbh->beginTransaction();
	}

	public function commit() {
		$this->_dbh->commit();
	}

	public function rollback() {
		$this->_dbh->rollback();
	}

	public function prepare($sql) {
		if(array_key_exists($sql, $this->_stmts)) {
			$stmt = $this->_stmts[$sql];
		} else {
			$stmt = $this->_dbh->prepare($sql);
			$this->_stmts[$sql] = $stmt;
		}
		return $stmt;
	}

	public function lastInsertId() {
		return $this->_dbh->lastInsertId();
	}

}

?>
