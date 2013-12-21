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

class WebViewEvents extends WebView {

	public function __construct($dbh) {
		parent::__construct($dbh);
	}

	public function sendHtml() {
		$l12n = $this->l12n();
		$this->beginHtml();
		$title = $l12n->t("LogMon - Events");
		$this->beginHeader($title);
		$this->endHeader();
		$this->beginBody();
		$this->printNavBar();
		$this->printFilter();
		if($this->getRequestHostip() != "*") {
			$this->printHostipDetails();
			$this->printHostipEventData();
		} elseif($this->getRequestHostmac() != "*") {
			$this->printHostmacEventData();
		} elseif($this->getRequestUser() != "*") {
			$this->printUserEventData();
		}
		$this->endBody();
		$this->endHtml();
	}

	private function printHostipDetails() {
		$dbh = $this->dbh();
		$typeId = $this->getSessionType();
		$loghostId = $this->getSessionLoghost();
		$serviceId = $this->getSessionService();
		$networkId = $this->getSessionNetwork();
		$hostipId = $this->getRequestHostip();
		$select = $dbh->prepare(
			"SELECT a.id, a.hostip, a.host, a.continentcode, a.countrycode, a.countryname, a.region, a.city, a.postalcode, a.latitude, a.longitude ".
			"FROM hostip a ".
			"WHERE a.id = ?");
		$select->bindParam(1, $hostipId, PDO::PARAM_STR);
		$select->execute();
		$select->bindColumn(1, $hostipId, PDO::PARAM_STR);
		$select->bindColumn(2, $hostip, PDO::PARAM_STR);
		$select->bindColumn(3, $host, PDO::PARAM_STR);
		$select->bindColumn(4, $continentcode, PDO::PARAM_STR);
		$select->bindColumn(5, $countrycode, PDO::PARAM_STR);
		$select->bindColumn(6, $countryname, PDO::PARAM_STR);
		$select->bindColumn(7, $region, PDO::PARAM_STR);
		$select->bindColumn(8, $city, PDO::PARAM_STR);
		$select->bindColumn(9, $postalcode, PDO::PARAM_STR);
		$select->bindColumn(10, $latitude, PDO::PARAM_STR);
		$select->bindColumn(11, $longitude, PDO::PARAM_STR);
		if($select->fetch(PDO::FETCH_BOUND) !== false) {
			$l12n = $this->l12n();
			$this->beginDetailsSection();
			$this->beginDetails1();
			$this->printImgCountry("icon128", $countrycode, $countryname);
			$this->endDetails1();
			$this->beginDetails2();
			$this->beginDetailsTable();
			$this->beginDetailsTableElement($l12n->t("IP address"));
			$this->printHostipWhoisLink("icon16", $hostip);
			$this->endDetailsTableElement();
			if($hostip != $host) {
				$this->beginDetailsTableElement($l12n->t("DNS name"));
				$this->printHostWhoisLink("icon16", $host);
				$this->endDetailsTableElement();
			}
			if($latitude != 0 || $longitude != 0) {
				$this->beginDetailsTableElement($l12n->t("Location"));
				$location = "";
				if($city != "") {
					if($postalcode != "") {
						$location .= "{$postalcode} ";
					}
					$location .= "{$city}, ";
				}
				if($region != "") {
					$location .= "{$region}, ";
				}
				$location .= "{$countryname} ({$countrycode}), {$continentcode}";
				$this->printMapLink("icon16", $host, $latitude, $longitude, $location);
				$this->endDetailsTableElement();
			}
			$this->beginDetailsTableElement($l12n->t("Logs"));
			$typeId = $this->getSessionType();
			$loghostId = $this->getSessionLoghost();
			$serviceId = $this->getSessionService();
			$networkId = $this->getSessionNetwork();
			$this->printLogLinks("icon16", $typeId, $loghostId, $serviceId, $networkId, $hostipId, "*", "*");
			$this->endDetailsTableElement();
			$this->endDetailsTable();
			$this->endDetails2();
			$this->endDetailsSection();
		}
	}

	private function printHostipEventData() {
		$dbh = $this->dbh();
		$typeId = $this->getSessionType();
		$loghostId = $this->getSessionLoghost();
		$serviceId = $this->getSessionService();
		$networkId = $this->getSessionNetwork();
		$hostipId = $this->getRequestHostip();
		$select = $dbh->prepare(
			"SELECT a.typeid, b.id, b.loghost, c.id, c.service, d.id, d.network, e.id, e.user, f.id, f.hostmac, f.vendor, ".
				"a.count, a.first, a.last ".
			"FROM event a, loghost b, service c, network d, user e, hostmac f ".
			"WHERE a.loghostid = b.id AND a.serviceid = c.id AND a.networkid = d.id AND a.userid = e.id AND a.hostmacid = f.id AND ".
				"('*' = ? OR a.typeid = ?) AND ('*' = ? OR b.id = ?) AND ('*' = ? OR c.id = ?) AND ('*' = ? OR d.id = ?) AND a.hostipid = ? ".
			"ORDER BY a.last DESC");
		$select->bindParam(1, $typeId, PDO::PARAM_STR);
		$select->bindParam(2, $typeId, PDO::PARAM_STR);
		$select->bindParam(3, $loghostId, PDO::PARAM_STR);
		$select->bindParam(4, $loghostId, PDO::PARAM_STR);
		$select->bindParam(5, $serviceId, PDO::PARAM_STR);
		$select->bindParam(6, $serviceId, PDO::PARAM_STR);
		$select->bindParam(7, $networkId, PDO::PARAM_STR);
		$select->bindParam(8, $networkId, PDO::PARAM_STR);
		$select->bindParam(9, $hostipId, PDO::PARAM_STR);
		$select->execute();
		$select->bindColumn(1, $typeId, PDO::PARAM_STR);
		$select->bindColumn(2, $loghostId, PDO::PARAM_STR);
		$select->bindColumn(3, $loghost, PDO::PARAM_STR);
		$select->bindColumn(4, $serviceId, PDO::PARAM_STR);
		$select->bindColumn(5, $service, PDO::PARAM_STR);
		$select->bindColumn(6, $networkId, PDO::PARAM_STR);
		$select->bindColumn(7, $network, PDO::PARAM_STR);
		$select->bindColumn(8, $userId, PDO::PARAM_STR);
		$select->bindColumn(9, $user, PDO::PARAM_STR);
		$select->bindColumn(10, $hostmacId, PDO::PARAM_STR);
		$select->bindColumn(11, $hostmac, PDO::PARAM_STR);
		$select->bindColumn(12, $vendor, PDO::PARAM_STR);
		$select->bindColumn(13, $count, PDO::PARAM_INT);
		$select->bindColumn(14, $first, PDO::PARAM_INT);
		$select->bindColumn(15, $last, PDO::PARAM_INT);
		$l12n = $this->l12n();
        $this->beginEventTable(array(
			$l12n->t("Nr"),
			$l12n->t("Status"),
			$l12n->t("Log"),
			$l12n->t("Service"),
			$l12n->t("Network"),
			$l12n->t("User"),
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
			$this->printEventUser($userId, $user);
			$this->printEventHostmac($hostmacId, $hostmac, $vendor);
			$this->printEventCount($count);
			$this->printEventTimerange($now, $first, $last);
			$this->printEventLogLinks($typeId, $loghostId, $serviceId, $networkId, $hostipId, $hostmacId, $userId);
			$this->endEventRow();
			$rowNr++;
		}
		$this->endEventTable();
	}

	private function printHostmacEventData() {
		$dbh = $this->dbh();
		$typeId = $this->getSessionType();
		$loghostId = $this->getSessionLoghost();
		$serviceId = $this->getSessionService();
		$networkId = $this->getSessionNetwork();
		$hostmacId = $this->getRequestHostmac();
		$select = $dbh->prepare(
			"SELECT a.typeid, b.id, b.loghost, c.id, c.service, d.id, d.network, e.id, e.hostip, e.host, e.countrycode, e.countryname, f.id, f.user, ".
				"a.count, a.first, a.last ".
			"FROM event a, loghost b, service c, network d, hostip e, user f ".
			"WHERE a.loghostid = b.id AND a.serviceid = c.id AND a.networkid = d.id AND a.hostipid = e.id AND a.userid = f.id AND ".
				"('*' = ? OR a.typeid = ?) AND ('*' = ? OR b.id = ?) AND ('*' = ? OR c.id = ?) AND ('*' = ? OR d.id = ?) AND a.hostmacid = ? ".
			"ORDER BY a.last DESC");
		$select->bindParam(1, $typeId, PDO::PARAM_STR);
		$select->bindParam(2, $typeId, PDO::PARAM_STR);
		$select->bindParam(3, $loghostId, PDO::PARAM_STR);
		$select->bindParam(4, $loghostId, PDO::PARAM_STR);
		$select->bindParam(5, $serviceId, PDO::PARAM_STR);
		$select->bindParam(6, $serviceId, PDO::PARAM_STR);
		$select->bindParam(7, $networkId, PDO::PARAM_STR);
		$select->bindParam(8, $networkId, PDO::PARAM_STR);
		$select->bindParam(9, $hostmacId, PDO::PARAM_STR);
		$select->execute();
		$select->bindColumn(1, $typeId, PDO::PARAM_STR);
		$select->bindColumn(2, $loghostId, PDO::PARAM_STR);
		$select->bindColumn(3, $loghost, PDO::PARAM_STR);
		$select->bindColumn(4, $serviceId, PDO::PARAM_STR);
		$select->bindColumn(5, $service, PDO::PARAM_STR);
		$select->bindColumn(6, $networkId, PDO::PARAM_STR);
		$select->bindColumn(7, $network, PDO::PARAM_STR);
		$select->bindColumn(8, $hostipId, PDO::PARAM_STR);
		$select->bindColumn(9, $hostip, PDO::PARAM_STR);
		$select->bindColumn(10, $host, PDO::PARAM_STR);
		$select->bindColumn(11, $countrycode, PDO::PARAM_STR);
		$select->bindColumn(12, $countryname, PDO::PARAM_STR);
		$select->bindColumn(13, $userId, PDO::PARAM_STR);
		$select->bindColumn(14, $user, PDO::PARAM_STR);
		$select->bindColumn(15, $count, PDO::PARAM_INT);
		$select->bindColumn(16, $first, PDO::PARAM_INT);
		$select->bindColumn(17, $last, PDO::PARAM_INT);
		$l12n = $this->l12n();
        $this->beginEventTable(array(
			$l12n->t("Nr"),
			$l12n->t("Status"),
			$l12n->t("Log"),
			$l12n->t("Service"),
			$l12n->t("Network"),
			$l12n->t("Host"),
			$l12n->t("User"),
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
			$this->printEventHostip($hostipId, $hostip, $host, $countrycode, $countryname);
			$this->printEventUser($userId, $user);
			$this->printEventCount($count);
			$this->printEventTimerange($now, $first, $last);
			$this->printEventLogLinks($typeId, $loghostId, $serviceId, $networkId, $hostipId, $hostmacId, $userId);
			$this->endEventRow();
			$rowNr++;
		}
		$this->endEventTable();
	}

	private function printUserEventData() {
		$dbh = $this->dbh();
		$typeId = $this->getSessionType();
		$loghostId = $this->getSessionLoghost();
		$serviceId = $this->getSessionService();
		$networkId = $this->getSessionNetwork();
		$userId = $this->getRequestUser();
		$select = $dbh->prepare(
			"SELECT a.typeid, b.id, b.loghost, c.id, c.service, d.id, d.network, e.id, e.hostip, e.host, e.countrycode, e.countryname, f.id, f.hostmac, f.vendor, ".
				"a.count, a.first, a.last ".
			"FROM event a, loghost b, service c, network d, hostip e, hostmac f ".
			"WHERE a.loghostid = b.id AND a.serviceid = c.id AND a.networkid = d.id AND a.hostipid = e.id AND a.hostmacid = f.id AND ".
				"('*' = ? OR a.typeid = ?) AND ('*' = ? OR b.id = ?) AND ('*' = ? OR c.id = ?) AND ('*' = ? OR d.id = ?) AND a.userid = ? ".
			"ORDER BY a.last DESC");
		$select->bindParam(1, $typeId, PDO::PARAM_STR);
		$select->bindParam(2, $typeId, PDO::PARAM_STR);
		$select->bindParam(3, $loghostId, PDO::PARAM_STR);
		$select->bindParam(4, $loghostId, PDO::PARAM_STR);
		$select->bindParam(5, $serviceId, PDO::PARAM_STR);
		$select->bindParam(6, $serviceId, PDO::PARAM_STR);
		$select->bindParam(7, $networkId, PDO::PARAM_STR);
		$select->bindParam(8, $networkId, PDO::PARAM_STR);
		$select->bindParam(9, $userId, PDO::PARAM_STR);
		$select->execute();
		$select->bindColumn(1, $typeId, PDO::PARAM_STR);
		$select->bindColumn(2, $loghostId, PDO::PARAM_STR);
		$select->bindColumn(3, $loghost, PDO::PARAM_STR);
		$select->bindColumn(4, $serviceId, PDO::PARAM_STR);
		$select->bindColumn(5, $service, PDO::PARAM_STR);
		$select->bindColumn(6, $networkId, PDO::PARAM_STR);
		$select->bindColumn(7, $network, PDO::PARAM_STR);
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
			$l12n->t("Service"),
			$l12n->t("Network"),
			$l12n->t("Host"),
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
			$this->printEventHostip($hostipId, $hostip, $host, $countrycode, $countryname);
			$this->printEventHostmac($hostmacId, $hostmac, $vendor);
			$this->printEventCount($count);
			$this->printEventTimerange($now, $first, $last);
			$this->printEventLogLinks($typeId, $loghostId, $serviceId, $networkId, $hostipId, $hostmacId, $userId);
			$this->endEventRow();
			$rowNr++;
		}
		$this->endEventTable();
	}

}

?>
