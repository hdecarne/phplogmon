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

class WebViewHostmacs extends WebView {

	public function __construct($dbh) {
		parent::__construct($dbh);
	}

	public function sendHtml() {
		$l12n = $this->l12n();
		$this->beginHtml();
		$title = $l12n->t("LogMon - MAC access");
		$this->beginHeader($title);
		$this->endHeader();
		$this->beginBody();
		$this->printNavBar();
		$this->printFilter();
		$this->printEventData();
		$this->endBody();
		$this->endHtml();
	}

	private function printEventData() {
		$dbh = $this->dbh();
		$typeId = $this->getSessionType();
		$loghostId = $this->getSessionLoghost();
		$serviceId = $this->getSessionService();
		$networkId = $this->getSessionNetwork();
		$select = $dbh->prepare(
			"SELECT a.typeid, b.id, b.loghost, c.id, c.service, d.id, d.network, e.id, e.hostmac, e.vendor, ".
				"SUM(a.count), MIN(a.first), MAX(a.last) ".
			"FROM event a, loghost b, service c, network d, hostmac e ".
			"WHERE a.loghostid = b.id AND a.serviceid = c.id AND a.networkid = d.id AND a.hostmacid = e.id AND e.hostmac <> '' ".
				"AND ('*' = ? OR a.typeid = ?) AND ('*' = ? OR b.id = ?) AND ('*' = ? OR c.id = ?) AND ('*' = ? OR d.id = ?) ".
			"GROUP BY a.typeid, b.id, c.id, d.id, e.id ".
			"ORDER BY MAX(a.last) DESC");
		$select->bindParam(1, $typeId, PDO::PARAM_STR);
		$select->bindParam(2, $typeId, PDO::PARAM_STR);
		$select->bindParam(3, $loghostId, PDO::PARAM_STR);
		$select->bindParam(4, $loghostId, PDO::PARAM_STR);
		$select->bindParam(5, $serviceId, PDO::PARAM_STR);
		$select->bindParam(6, $serviceId, PDO::PARAM_STR);
		$select->bindParam(7, $networkId, PDO::PARAM_STR);
		$select->bindParam(8, $networkId, PDO::PARAM_STR);
		$select->execute();
		$select->bindColumn(1, $typeId, PDO::PARAM_STR);
		$select->bindColumn(2, $loghostId, PDO::PARAM_STR);
		$select->bindColumn(3, $loghost, PDO::PARAM_STR);
		$select->bindColumn(4, $serviceId, PDO::PARAM_STR);
		$select->bindColumn(5, $service, PDO::PARAM_STR);
		$select->bindColumn(6, $networkId, PDO::PARAM_STR);
		$select->bindColumn(7, $network, PDO::PARAM_STR);
		$select->bindColumn(8, $hostmacId, PDO::PARAM_STR);
		$select->bindColumn(9, $hostmac, PDO::PARAM_STR);
		$select->bindColumn(10, $vendor, PDO::PARAM_STR);
		$select->bindColumn(11, $count, PDO::PARAM_INT);
		$select->bindColumn(12, $first, PDO::PARAM_INT);
		$select->bindColumn(13, $last, PDO::PARAM_INT);
		$l12n = $this->l12n();
		$this->beginEventTable(array(
			$l12n->t("Nr"),
			$l12n->t("Status"),
			$l12n->t("Log"),
			$l12n->t("Service"),
			$l12n->t("Network"),
			$l12n->t("MAC"),
			$l12n->t("Count"),
			$l12n->t("When"),
			$l12n->t("Logs")
		));
		$rowNr = 1;
		$now = time();
		while($select->fetch(PDO::FETCH_BOUND) !== false) {
			$this->beginEventRow();
			$this->printEventRowNr($rowNr);
			$this->printEventType($typeId);
			$this->printEventLoghost($loghost);
			$this->printEventService($service);
			$this->printEventNetwork($network);
			$this->printEventHostmac($hostmacId, $hostmac, $vendor);
			$this->printEventCount($count);
			$this->printEventTimerange($now, $first, $last);
			$this->printEventLogLinks($typeId, $loghostId, $serviceId, $networkId, "*", $hostmacId, "*");
			$this->endEventRow();
			$rowNr++;
		}
		$this->endEventTable();
	}

}

?>
