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

class DBH {

	private $tDbh = null;
	private $tStmts = array();
	private $tCaches = array();

	public function __construct($dsn, $user, $pass) {
		Log::debug("Establishing database connection '{$dsn}' with user '{$user}'...");
		$this->tDbh = new PDO($dsn, $user, $pass);
		$this->tDbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	}

	public function close() {
		Log::debug("Closing database connection...");
		if($this->tDbh !== null) {
			try {
				if($this->tDbh->inTransaction()) {
					Log::warning("Pending transaction detected, performing rollback.");
				}
				foreach($this->tStmts as $stmt) {
					$stmt->closeCursor();
				}
			} catch(Exception $e) {
				Log::warning(sprintf("An exception occured while closing database: %s\nDetails: $s", $e->getMessage(), $e));
			}
			$this->tCaches = array();
			$this->tStmts = array();
			$this->tDbh = null;
		}
	}

	public function beginTransaction() {
		$this->tDbh->beginTransaction();
	}

	public function commit() {
		$this->tDbh->commit();
	}

	public function rollback() {
		$this->tDbh->rollback();
	}

	public function prepare($sql) {
		if(array_key_exists($sql, $this->tStmts)) {
			$stmt = $this->tStmts[$sql];
		} else {
			$stmt = $this->tDbh->prepare($sql);
			$this->tStmts[$sql] = $stmt;
		}
		return $stmt;
	}

	public function lastInsertId() {
		return $this->tDbh->lastInsertId();
	}

	public function &getCache($name) {
		if(isset($this->tCaches[$name])) {
			$cache =& $this->tCaches[$name];
		} else {
			$cache = array();
			$this->tCaches[$name] =& $cache;
		}
		return $cache;
	}

	public function clearCache($name) {
		$this->tCaches[$name] = array();
	}

}

?>
