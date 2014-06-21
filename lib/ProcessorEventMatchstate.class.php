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

class ProcessorEventMatchstate {

	private $tDbh;
	private $tSource;
	private $tFile;
	private $tNetworkmap;
	private $tUserdb;
	private $tEvent;
	private $tNextPatternIndex;
	private $tMatches;
	private $tMatchedTimestamp;
	private $tMatchedLines;
	private $tMatchedLoghost;
	private $tMatchedService;
	private $tMatchedHostip;
	private $tMatchedHostmac;
	private $tMatchedUser;

	private function __construct($dbh, $source, $file, $networkmap, $userdb, $event) {
		$this->tDbh = $dbh;
		$this->tSource = $source;
		$this->tFile = $file;
		$this->tNetworkmap = $networkmap;
		$this->tUserdb = $userdb;
		$this->tEvent = $event;
		$this->reset();
	}

	private function reset() {
		$this->tNextPatternIndex = 0;
		$this->tMatches = array();
		$this->tMatchedTimestamp = 0;
		$this->tMatchedLines = array();
		$this->tMatchedLoghost = "";
		$this->tMatchedService = "";
		$this->tMatchedHostip = "";
		$this->tMatchedHostmac = "";
		$this->tMatchedUser = "";
	}

	public function __toString() {
		return "loghost='{$this->tMatchedLoghost}';service='{$this->tMatchedService}';hostip='{$this->tMatchedHostip}';hostmac='{$this->tMatchedHostmac}';user='{$this->tMatchedUser}';{$this->tEvent}";
	}

	private function isEmpty() {
		return $this->tMatchedLoghost === "" && $this->tMatchedService === "" && $this->tMatchedHostip === "" && $this->tMatchedHostmac === "" && $this->tMatchedUser === "";
	}

	private function isErroneous() {
		return $this->tMatchedLoghost === false || $this->tMatchedService === false || $this->tMatchedHostip === false || $this->tMatchedHostmac === false || $this->tMatchedUser === false;
	}

	public static function create($dbh, $monitor, $source, $file) {
		$networkmap = $monitor->getSourceNetworkmap($source);
		$userdb = $monitor->getSourceUserdb($source);
		$events = $monitor->getSourceEvents($source);
		$states = array();
		foreach($events as $event) {
			$states[] = new self($dbh, $source, $file, $networkmap, $userdb, $event);
		}
		return $states;
	}

	public static function matchAndUpdateAll($states, $lineTimestamp, $line) {
		$matchCount = 0;
		foreach($states as $state) {
			$matchCount += $state->matchAndUpdate($lineTimestamp, $line);
		}
		return $matchCount;
	}

	public static function discardOld($dbh, $days) {
		$discardCount = 0;
		if(!Options::pretend()) {
			$threshold = time() - $days * 24 * 60 * 60;
			$delete = $dbh->prepare("DELETE FROM log WHERE time <= ? OR eventid IN (SELECT id FROM event WHERE last <= ?)");
			$delete->bindValue(1, $threshold, PDO::PARAM_INT);
			$delete->bindValue(2, $threshold, PDO::PARAM_INT);
			$delete->execute();
			$delete = $dbh->prepare("DELETE FROM event WHERE last <= ?");
			$delete->bindValue(1, $threshold, PDO::PARAM_INT);
			$delete->execute();
			$discardCount = $delete->rowCount();
		}
		return $discardCount;
	}

	private function matchAndUpdate($lineTimestamp, $line) {
		$patterns = $this->tEvent->getPatterns();
		$match = false;
		if(preg_match($patterns[$this->tNextPatternIndex], $line, $matches) === 1) {
			$this->tNextPatternIndex++;
			$match = true;
		} elseif($this->tNextPatternIndex > 0 && preg_match($patterns[0], $line, $matches) === 1) {
			$this->reset();
			$this->tNextPatternIndex = 1;
			$match = true;
		}
		$matchCount = 0;
		if($match) {
			if(count($matches) > 0) {
				$this->tMatches = array_merge($this->tMatches, array_slice($matches, 1));
			}
			$this->tMatchedLines[] = $line;
			if($this->tNextPatternIndex == 1) {
				$this->tMatchedTimestamp = $lineTimestamp;
			}
			if($this->tNextPatternIndex == count($patterns)) {
				$this->tMatchedLoghost = $this->applyLoghostEvaluator();
				$this->tMatchedService = $this->applyServiceEvaluator();
				$this->tMatchedHostip = $this->applyHostipEvaluator();
				$this->tMatchedHostmac = $this->applyHostmacEvaluator();
				$this->tMatchedUser = $this->applyUserEvaluator();
				if(!$this->isEmpty()) {
					if(!$this->isErroneous()) {
						if(Options::pretend() || Options::debug()) {
							Log::info("Found event '{$this}'");
							$matchedLineIndex = 0;
							foreach($this->tMatchedLines as $matchedLine) {
								Log::debug("line[{$matchedLineIndex}] '{$matchedLine}'");
								$matchedLineIndex++;
							}
						}
						$this->update();
						$matchCount = 1;
					} else {
						Log::debug("Ignoring line '{$line}' due to erroneous event '{$this}'");
					}
				} else {
					Log::debug("Ignoring line '{$line}' due to empty event '{$this}'");
				}
				$this->reset();
			}
		}
		return $matchCount;
	}

	private function applyLoghostEvaluator() {
		$evaluator = $this->tEvent->getLoghostEvaluator();
		if(!is_null($evaluator)) {
			$loghost = $this->applyEvaluator($evaluator);
		} else {
			$loghost = $this->tSource->getLoghost();
		}
		return $loghost;
	}

	private function applyServiceEvaluator() {
		$evaluator = $this->tEvent->getServiceEvaluator();
		if(!is_null($evaluator)) {
			$service = $this->applyEvaluator($evaluator);
		} elseif(($eventDefaultService = $this->tEvent->getDefaultService()) != "") {
			$service = $eventDefaultService;
		} elseif(($fileDefaultService = $this->tFile->getDefaultService()) != "") {
			$service = $fileDefaultService;
		} else {
			$service = "";
		}
		return $service;
	}

	private function applyHostipEvaluator() {
		$evaluator = $this->tEvent->getHostipEvaluator();
		$hostip = (!is_null($evaluator) ? $this->applyEvaluator($evaluator) : "");
		return QueryHostip::normalizeHostip($hostip);
	}

	private function applyHostmacEvaluator() {
		$evaluator = $this->tEvent->getHostmacEvaluator();
		$hostmac = (!is_null($evaluator) ? $this->applyEvaluator($evaluator) : "");
		return QueryHostmac::normalizeHostmac($hostmac);
	}

	private function applyUserEvaluator() {
		$evaluator = $this->tEvent->getUserEvaluator();
		return (!is_null($evaluator) ? $this->applyEvaluator($evaluator) : "");
	}

	private function applyEvaluator($evaluator) {
		$result = false;
		$decoder = $evaluator->getDecoder();
		$result = $decoder->apply($this->tDbh, $this->tMatches, $evaluator->getTerm());
		return $result;
	}

	private function update() {
		$loghostId = QueryLoghost::getLoghostId($this->tDbh, $this->tMatchedLoghost);
		$serviceId = QueryService::getServiceId($this->tDbh, $this->tMatchedService);
		$networkId = QueryHostipNetwork::getNetworkId($this->tDbh, $this->tNetworkmap, $this->tMatchedHostip);
		$hostipId = QueryHostip::getHostipId($this->tDbh, $this->tMatchedHostip);
		$hostmacId = QueryHostmac::getHostmacId($this->tDbh, $this->tMatchedHostmac);
		$userId = QueryUser::getUserId($this->tDbh, $this->tUserdb, $this->tMatchedUser);
		if(!Options::pretend()) {
			$select = $this->tDbh->prepare("SELECT a.id, a.count, a.first, a.last FROM event a WHERE a.loghostid = ? AND a.serviceid = ? AND a.typeid = ? AND a.networkid = ? AND a.hostipid = ? AND a.hostmacid = ? AND a.userid = ?");
			$select->bindValue(1, $loghostId, PDO::PARAM_STR);
			$select->bindValue(2, $serviceId, PDO::PARAM_STR);
			$select->bindValue(3, $this->tEvent->getTypeid(), PDO::PARAM_STR);
			$select->bindValue(4, $networkId, PDO::PARAM_STR);
			$select->bindValue(5, $hostipId, PDO::PARAM_STR);
			$select->bindValue(6, $hostmacId, PDO::PARAM_STR);
			$select->bindValue(7, $userId, PDO::PARAM_STR);
			$select->execute();
			$select->bindColumn(1, $id, PDO::PARAM_STR);
			$select->bindColumn(2, $count, PDO::PARAM_INT);
			$select->bindColumn(3, $first, PDO::PARAM_INT);
			$select->bindColumn(4, $last, PDO::PARAM_INT);
			if($select->fetch(PDO::FETCH_BOUND) !== false) {
				$count++;
				$first = min($first, $this->tMatchedTimestamp);
				$last = max($last, $this->tMatchedTimestamp);
				$update = $this->tDbh->prepare("UPDATE event SET count = ?, first = ?, last = ? WHERE id = ?");
				$update->bindValue(1, $count,  PDO::PARAM_INT);
				$update->bindValue(2, $first,  PDO::PARAM_INT);
				$update->bindValue(3, $last,  PDO::PARAM_INT);
				$update->bindValue(4, $id,  PDO::PARAM_STR);
				$update->execute();
			} else {
				$insert = $this->tDbh->prepare("INSERT INTO event (loghostid, serviceid, typeid, networkid, hostipid, hostmacid, userid, count, first, last) VALUES(?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
				$insert->bindValue(1, $loghostId,  PDO::PARAM_STR);
				$insert->bindValue(2, $serviceId, PDO::PARAM_STR);
				$insert->bindValue(3, $this->tEvent->getTypeid(),  PDO::PARAM_STR);
				$insert->bindValue(4, $networkId, PDO::PARAM_STR);
				$insert->bindValue(5, $hostipId, PDO::PARAM_STR);
				$insert->bindValue(6, $hostmacId, PDO::PARAM_STR);
				$insert->bindValue(7, $userId, PDO::PARAM_STR);
				$insert->bindValue(8, 1, PDO::PARAM_INT);
				$insert->bindValue(9, $this->tMatchedTimestamp, PDO::PARAM_INT);
				$insert->bindValue(10, $this->tMatchedTimestamp, PDO::PARAM_INT);
				$insert->execute();
				$id = $this->tDbh->lastInsertId();
			}
			$insert = $this->tDbh->prepare("INSERT INTO log (eventid, time, line) VALUES(?, ?, ?)");
			foreach($this->tMatchedLines as $line) {
				$insert->bindValue(1, $id,  PDO::PARAM_STR);
				$insert->bindValue(2, $this->tMatchedTimestamp, PDO::PARAM_STR);
				$insert->bindValue(3, $line, PDO::PARAM_STR);
				$insert->execute();
			}
		}
	}

}

?>
