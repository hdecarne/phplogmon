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

class ProcessorSourcestate {

	private $tDbh;
	private $tTouched;
	private $tId;
	private $tSourceid;
	private $tLoghost;
	private $tFile;
	private $tMtime;
	private $tLast;
	private $tUpdatedLast;

	private function __construct($dbh) {
		$this->tDbh = $dbh;
		$this->tTouched = false;
	}

	public static function query($dbh, $source) {
		$sourcestates = array();
		$select = $dbh->prepare("SELECT a.id, a.sourceid, a.loghost, a.file, a.mtime, a.last FROM sourcestate a WHERE a.sourceid = ?");
		$select->bindValue(1, $source->getId(), PDO::PARAM_STR);
		$select->execute();
		$select->bindColumn(1, $id, PDO::PARAM_STR);
		$select->bindColumn(2, $sourceid, PDO::PARAM_STR);
		$select->bindColumn(3, $loghost, PDO::PARAM_STR);
		$select->bindColumn(4, $file, PDO::PARAM_STR);
		$select->bindColumn(5, $mtime, PDO::PARAM_INT);
		$select->bindColumn(6, $last, PDO::PARAM_INT);
		while($select->fetch(PDO::FETCH_BOUND) !== false) {
			$sourcestate = new self($dbh);
			$sourcestate->tId = $id;
			$sourcestate->tSourceid = $sourceid;
			$sourcestate->tLoghost = $loghost;
			$sourcestate->tFile = $file;
			$sourcestate->tMtime = $mtime;
			$sourcestate->tLast = $last;
			$sourcestate->tUpdatedLast = $last;
			$sourcestates[$sourcestate->tFile] = $sourcestate;
		}
		return $sourcestates;
	}

	public static function add(&$sourcestates, $dbh, $source, $file) {
		$sourcestate = new self($dbh);
		$sourcestate->tId = null;
		$sourcestate->tSourceid = $source->getId();
		$sourcestate->tLoghost = $source->getLoghost();
		$sourcestate->tFile = $file;
		$sourcestate->tMtime = 0;
		$sourcestate->tLast = 0;
		foreach($sourcestates as $sourcestate2) {
			if($sourcestate2->tLast > $sourcestate->tLast) {
				$sourcestate->tLast = $sourcestate2->tLast;
			}
		}
		$sourcestate->tUpdatedLast = $sourcestate->tLast;
		$sourcestates[$sourcestate->tFile] = $sourcestate;
		return $sourcestate;
	}

	public static function updateAll($sourcestates) {
		clearstatcache();
		foreach($sourcestates as $sourcestate) {
			$sourcestate->update();
		}
	}

	public function touch() {
		$mtime = Files::safeFilemtime($this->tFile);
		$modified = $mtime > $this->tMtime;
		$this->tTouched = true;
		return $modified;
	}

	public function updateLast($timestamp) {
		if($timestamp > $this->tLast) {
			$this->tUpdatedLast = $timestamp;
			$updated = true;
		} else {
			$updated = false;
		}
		return $updated;
	}

	private function update() {
		$this->tLast = $this->tUpdatedLast;
		if(!Options::pretend()) {
			if($this->tTouched) {
				$this->tMtime = Files::safeFilemtime($this->tFile);
				if(is_null($this->tId)) {
					$insert = $this->tDbh->prepare("INSERT INTO sourcestate (sourceid, loghost, file, mtime, last) VALUES(?, ?, ?, ?, ?)");
					$insert->bindValue(1, $this->tSourceid, PDO::PARAM_STR);
					$insert->bindValue(2, $this->tLoghost, PDO::PARAM_STR);
					$insert->bindValue(3, $this->tFile, PDO::PARAM_STR);
					$insert->bindValue(4, $this->tMtime, PDO::PARAM_INT);
					$insert->bindValue(5, $this->tLast, PDO::PARAM_INT);
					$insert->execute();
					$this->tId = $this->tDbh->lastInsertId();
				} else {
					$update = $this->tDbh->prepare("UPDATE sourcestate a SET a.mtime = ?, a.last = ? WHERE a.id = ? ");
					$update->bindValue(1, $this->tMtime, PDO::PARAM_INT);
					$update->bindValue(2, $this->tLast, PDO::PARAM_INT);
					$update->bindValue(3, $this->tId, PDO::PARAM_STR);
					$update->execute();
				}
			} elseif(!is_null($this->tId)) {
				$delete = $this->tDbh->prepare("DELETE FROM sourcestate WHERE id = ?");
				$delete->bindValue(1, $this->tId, PDO::PARAM_STR);
				$delete->execute();
			}
		}
	}

}

?>
