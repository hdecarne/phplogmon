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

class WebViewUser extends WebView {

	public function __construct($dbh) {
		parent::__construct($dbh, true, true, true, true, true, true);
	}

	public function sendHtml() {
		$l12n = $this->l12n();
		$this->beginHtml();
		$title = $l12n->t("LogMon - User");
		$this->beginHeader($title);
		$this->endHeader();
		$this->beginBody();
		$this->printNavBar();
		$this->printFilter();
		if($this->getRequestUser() != "*") {
			$this->printUserDetails();
			$this->printUserEventData();
		}
		$this->endBody();
		$this->endHtml();
	}

	private function printUserDetails() {
		$dbh = $this->dbh();
		$typeId = $this->getSessionTypeFilter();
		$loghostId = $this->getSessionLoghostFilter();
		$networkId = $this->getSessionNetworkFilter();
		$serviceId = $this->getSessionServiceFilter();
		$userId = $this->getRequestUser();
		$select = $dbh->prepare(
			"SELECT a.id, a.user, a.statusid ".
			"FROM user a ".
			"WHERE a.id = ?");
		$select->bindParam(1, $userId, PDO::PARAM_STR);
		$select->execute();
		$select->bindColumn(1, $userId, PDO::PARAM_STR);
		$select->bindColumn(2, $user, PDO::PARAM_STR);
		$select->bindColumn(3, $statusId, PDO::PARAM_STR);
		if($select->fetch(PDO::FETCH_BOUND) !== false) {
			$l12n = $this->l12n();
			$this->beginDetailsSection();
			$this->beginDetails1();
			$this->printImgUserStatus("icon128", $statusId);
			$this->endDetails1();
			$this->beginDetails2();
			$this->beginDetailsTable();
			$this->beginDetailsTableElement($l12n->t("User"));
			if(!Options::kioskMode()) {
				Html::out($user);
			} else {
				Html::out("********");
			}
			$this->endDetailsTableElement();
			$this->beginDetailsTableElement($l12n->t("Logs"));
			$this->printLogLinks("icon16", $typeId, $loghostId, $networkId, $serviceId, "*", "*", $userId);
			$this->endDetailsTableElement();
			$this->endDetailsTable();
			$this->endDetails2();
			$this->endDetailsSection();
		}
	}

	private function printUserEventData() {
		$dbh = $this->dbh();
		$typeId = $this->getSessionTypeFilter();
		$loghostId = $this->getSessionLoghostFilter();
		$networkId = $this->getSessionNetworkFilter();
		$serviceId = $this->getSessionServiceFilter();
		$userId = $this->getRequestUser();
		$select = $dbh->prepare(
			"SELECT a.typeid, b.id, b.loghost, c.id, c.network, d.id, d.service, e.id, e.hostip, e.host, e.countrycode, e.countryname, f.id, f.hostmac, f.vendor, ".
				"a.count, a.first, a.last ".
			"FROM event a, loghost b, network c, service d, hostip e, hostmac f ".
			"WHERE a.loghostid = b.id AND a.networkid = c.id AND a.serviceid = d.id AND a.hostipid = e.id AND a.hostmacid = f.id AND ".
				"('*' = ? OR a.typeid = ?) AND ('*' = ? OR b.id = ?) AND ('*' = ? OR c.id = ?) AND ('*' = ? OR d.id = ?) AND a.userid = ? ".
			"ORDER BY a.last DESC");
		$select->bindParam(1, $typeId, PDO::PARAM_STR);
		$select->bindParam(2, $typeId, PDO::PARAM_STR);
		$select->bindParam(3, $loghostId, PDO::PARAM_STR);
		$select->bindParam(4, $loghostId, PDO::PARAM_STR);
		$select->bindParam(5, $networkId, PDO::PARAM_STR);
		$select->bindParam(6, $networkId, PDO::PARAM_STR);
		$select->bindParam(7, $serviceId, PDO::PARAM_STR);
		$select->bindParam(8, $serviceId, PDO::PARAM_STR);
		$select->bindParam(9, $userId, PDO::PARAM_STR);
		$select->execute();
		$select->bindColumn(1, $typeId, PDO::PARAM_STR);
		$select->bindColumn(2, $loghostId, PDO::PARAM_STR);
		$select->bindColumn(3, $loghost, PDO::PARAM_STR);
		$select->bindColumn(4, $networkId, PDO::PARAM_STR);
		$select->bindColumn(5, $network, PDO::PARAM_STR);
		$select->bindColumn(6, $serviceId, PDO::PARAM_STR);
		$select->bindColumn(7, $service, PDO::PARAM_STR);
		$select->bindColumn(8, $hostipId, PDO::PARAM_STR);
		$select->bindColumn(9, $hostip, PDO::PARAM_STR);
		$select->bindColumn(10, $host, PDO::PARAM_STR);
		$select->bindColumn(11, $countrycode, PDO::PARAM_STR);
		$select->bindColumn(12, $countryname, PDO::PARAM_STR);
		$select->bindColumn(13, $hostmacId, PDO::PARAM_STR);
		$select->bindColumn(14, $hostmac, PDO::PARAM_STR);
		$select->bindColumn(15, $vendor, PDO::PARAM_STR);
		$select->bindColumn(16, $count, PDO::PARAM_INT);
		$select->bindColumn(17, $first, PDO::PARAM_INT);
		$select->bindColumn(18, $last, PDO::PARAM_INT);
		$l12n = $this->l12n();
        $this->beginEventTable(array(
			$l12n->t("Nr"),
			$l12n->t("Status"),
			$l12n->t("Log"),
			$l12n->t("Network"),
			$l12n->t("Service"),
			$l12n->t("Host"),
			$l12n->t("MAC"),
			$l12n->t("Count"),
			$l12n->t("Last"),
			$l12n->t("Logs")
		));
		$rowNr = 1;
		$now = time();
		$minCount = $this->getSessionCountFilter();
		$rowLimit = $this->getSessionLimitFilter();
		while($select->fetch(PDO::FETCH_BOUND) !== false && ($rowLimit == 0 || $rowNr <= $rowLimit)) {
			if($count >= $minCount) {
				$this->beginEventRow();
				$this->printEventRowNr($rowNr);
				$this->printEventType($typeId);
				$this->printEventLoghost($loghost);
				$this->printEventNetwork($network);
				$this->printEventService($serviceId, $service, $typeId, $loghostId, $networkId);
				$this->printEventHostip($hostipId, $hostip, $host, $countrycode, $countryname, $typeId, $loghostId, $networkId, $serviceId);
				$this->printEventHostmac($hostmacId, $hostmac, $vendor, $typeId, $loghostId, $networkId, $serviceId);
				$this->printEventCount($count);
				$this->printEventTimerange($now, $first, $last);
				$this->printEventLogLinks($typeId, $loghostId, $networkId, $serviceId, $hostipId, $hostmacId, $userId);
				$this->endEventRow();
			}
			$rowNr++;
		}
		$this->endEventTable();
	}

}

?>
