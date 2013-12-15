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

class WebViewHostips extends WebView {

	public function __construct($dbh) {
		parent::__construct($dbh);
	}

	public function sendHtml() {
		$l12n = $this->l12n();
		$this->beginHtml();
		$title = $l12n->t("LogMon - IP access");
		$this->beginHeader($title);
		$this->endHeader();
		$this->beginBody();
		print("<div class=\"navbar\">");
		$this->printNavbar();
		print("</div><div class=\"filter\">");
		$this->printFilter();
		print("</div><div class=\"events\">");
		$this->printEventData();
		print("</div>");
		$this->endBody();
		$this->endHtml();
	}

	private function printNavbar() {
		$this->printNavHostips();
		Html::out(" | ");
		$this->printNavHostmacs();
		Html::out(" | ");
		$this->printNavUsers();
	}

	private function printFilter() {
		$this->printSelectType();
		$this->printSelectLoghost();
		$this->printSelectService();
		$this->printSelectNetwork();
	}

	private function printEventData() {
		$dbh = $this->dbh();
		$typeId = $this->getSessionType();
		$loghostId = $this->getSessionLoghost();
		$serviceId = $this->getSessionService();
		$networkId = $this->getSessionNetwork();
		$select = $dbh->prepare(
			"SELECT a.typeid, b.id, b.loghost, c.id, c.service, d.id, d.network, e.id, e.hostip, e.host, e.countrycode, e.countryname, ".
				"SUM(a.count), MIN(a.first), MAX(a.last) ".
			"FROM event a, loghost b, service c, network d, hostip e ".
			"WHERE a.loghostid = b.id AND a.serviceid = c.id AND a.networkid = d.id AND a.hostipid = e.id AND e.host <> '' ".
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
		$select->bindColumn(8, $hostipId, PDO::PARAM_STR);
		$select->bindColumn(9, $hostip, PDO::PARAM_STR);
		$select->bindColumn(10, $host, PDO::PARAM_STR);
		$select->bindColumn(11, $countrycode, PDO::PARAM_STR);
		$select->bindColumn(12, $countryname, PDO::PARAM_STR);
		$select->bindColumn(13, $count, PDO::PARAM_INT);
		$select->bindColumn(14, $first, PDO::PARAM_INT);
		$select->bindColumn(15, $last, PDO::PARAM_INT);
		$l12n = $this->l12n();
		print("<table>");
		print("<thead>");
		print("<tr><th>");
		Html::out($l12n->t("Nr"));
		print("</th><th>");
		Html::out($l12n->t("Status"));
		print("</th><th>");
		Html::out($l12n->t("Log"));
		print("</th><th>");
		Html::out($l12n->t("Service"));
		print("</th><th>");
		Html::out($l12n->t("Network"));
		print("</th><th>");
		Html::out($l12n->t("Host"));
		print("</th><th>");
		Html::out($l12n->t("Count"));
		print("</th><th>");
		Html::out($l12n->t("When"));
		print("</th><th>");
		Html::out($l12n->t("Logs"));
		print("</th></tr>");
		print("</thead>");
		print("<tbody>");
		$rowNr = 1;
		$now = time();
		while($select->fetch(PDO::FETCH_BOUND) !== false) {
			print("<tr><td class=\"right\">");
			Html::out($rowNr);
			print("</td><td class=\"center\">");
			$this->printImgType("tableicon", $typeId);
			print("</td><td>");
			Html::out("{$loghost}");
			print("</td><td>");
			Html::out($service);
			print("</td><td>");
			Html::out($network);
			print("</td><td><a href=\"?cmd=viewevents&hostip={$hostipId}\">");
			$this->printHostip($hostip, $host, $countrycode, $countryname);
			print("</a></td><td class=\"right\">");
			Html::out($count);
			print("</td><td>");
			$this->printTimerange($now, $first, $last);
			print("</td><td class=\"center\"><a href=\"?cmd=streamlogs&type={$typeId}&loghost={$loghostId}&service={$serviceId}&hostip={$hostipId}\">");
			$this->printImgLogView("tableicon");
			print("</a> <a href=\"?cmd=streamlogs&type={$typeId}&loghost={$loghostId}&service={$serviceId}&hostip={$hostipId}\">");
			$this->printImgLogDownload("tableicon");
			print("</a></td></tr>");
			$rowNr++;
		}
		print("</tbody>");
		print("</table>");
	}

}

?>
