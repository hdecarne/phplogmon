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

class WebViewHostip extends WebView {

	public function __construct($dbh) {
		parent::__construct($dbh, true, true, true, true, true, true);
	}

	public function sendHtml() {
		$l12n = $this->l12n();
		$this->beginHtml();
		$title = $l12n->t("LogMon - Host");
		$this->beginHeader($title);
		$this->endHeader();
		$this->beginBody();
		$this->printNavBar();
		$this->printFilter();
		if($this->getRequestHostip() != "*") {
			$this->printHostipDetails();
			$this->printHostipEventData();
		}
		$this->endBody();
		$this->endHtml();
	}

	private function printHostipDetails() {
		$dbh = $this->dbh();
		$typeId = $this->getSessionTypeFilter();
		$loghostId = $this->getSessionLoghostFilter();
		$networkId = $this->getSessionNetworkFilter();
		$serviceId = $this->getSessionServiceFilter();
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
				if($countryname != "") {
					$location .= "{$countryname} ({$countrycode}), ";
				}
				if($continentcode) {
					$location .= "{$continentcode}";
				}
				$this->printMapLink("icon16", $host, $latitude, $longitude, $location);
				$this->endDetailsTableElement();
			}
			$this->beginDetailsTableElement($l12n->t("Logs"));
			$this->printLogLinks("icon16", $typeId, $loghostId, $networkId, $serviceId, $hostipId, "*", "*");
			$this->endDetailsTableElement();
			$this->endDetailsTable();
			$this->endDetails2();
			$this->endDetailsSection();
		}
	}

	private function printHostipEventData() {
		$dbh = $this->dbh();
		$typeId = $this->getSessionTypeFilter();
		$loghostId = $this->getSessionLoghostFilter();
		$networkId = $this->getSessionNetworkFilter();
		$serviceId = $this->getSessionServiceFilter();
		$hostipId = $this->getRequestHostip();
		$select = $dbh->prepare(
			"SELECT a.typeid, b.id, b.loghost, c.id, c.network, d.id, d.service, e.id, e.user, e.statusid, f.id, f.hostmac, f.vendor, ".
				"a.count, a.first, a.last ".
			"FROM event a, loghost b, network c, service d, user e, hostmac f ".
			"WHERE a.loghostid = b.id AND a.networkid = c.id AND a.serviceid = d.id AND a.userid = e.id AND a.hostmacid = f.id AND ".
				"('*' = ? OR a.typeid = ?) AND ('*' = ? OR b.id = ?) AND ('*' = ? OR c.id = ?) AND ('*' = ? OR d.id = ?) AND a.hostipid = ? ".
			"ORDER BY a.last DESC");
		$select->bindParam(1, $typeId, PDO::PARAM_STR);
		$select->bindParam(2, $typeId, PDO::PARAM_STR);
		$select->bindParam(3, $loghostId, PDO::PARAM_STR);
		$select->bindParam(4, $loghostId, PDO::PARAM_STR);
		$select->bindParam(5, $networkId, PDO::PARAM_STR);
		$select->bindParam(6, $networkId, PDO::PARAM_STR);
		$select->bindParam(7, $serviceId, PDO::PARAM_STR);
		$select->bindParam(8, $serviceId, PDO::PARAM_STR);
		$select->bindParam(9, $hostipId, PDO::PARAM_STR);
		$select->execute();
		$select->bindColumn(1, $typeId, PDO::PARAM_STR);
		$select->bindColumn(2, $loghostId, PDO::PARAM_STR);
		$select->bindColumn(3, $loghost, PDO::PARAM_STR);
		$select->bindColumn(4, $networkId, PDO::PARAM_STR);
		$select->bindColumn(5, $network, PDO::PARAM_STR);
		$select->bindColumn(6, $serviceId, PDO::PARAM_STR);
		$select->bindColumn(7, $service, PDO::PARAM_STR);
		$select->bindColumn(8, $userId, PDO::PARAM_STR);
		$select->bindColumn(9, $user, PDO::PARAM_STR);
		$select->bindColumn(10, $statusId, PDO::PARAM_STR);
		$select->bindColumn(11, $hostmacId, PDO::PARAM_STR);
		$select->bindColumn(12, $hostmac, PDO::PARAM_STR);
		$select->bindColumn(13, $vendor, PDO::PARAM_STR);
		$select->bindColumn(14, $count, PDO::PARAM_INT);
		$select->bindColumn(15, $first, PDO::PARAM_INT);
		$select->bindColumn(16, $last, PDO::PARAM_INT);
		$l12n = $this->l12n();
        $this->beginEventTable(array(
			$l12n->t("Nr"),
			$l12n->t("Status"),
			$l12n->t("Log"),
			$l12n->t("Network"),
			$l12n->t("Service"),
			$l12n->t("User"),
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
				$this->printEventUser($userId, $user, $statusId, $typeId, $loghostId, $networkId, $serviceId);
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
