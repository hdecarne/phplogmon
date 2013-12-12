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

	public function printHtml() {
		$this->beginHtml();
		$l12n = $this->l12n();
		$title = $l12n->t("LogMon - Events");
		$this->beginHeader($title);
		$this->endHeader();
		$this->beginBody();
		print("<div class=\"filter\">");
		$this->printFilter();
		if($this->getRequestHostip() != "*") {
			print("</div><div class=\"events\">");
			$this->printHostipEventData();
			print("</div>");
		} elseif($this->getRequestHostmac() != "*") {
			print("</div><div class=\"events\">");
			$this->printHostmacEventData();
			print("</div>");
		} elseif($this->getRequestUser() != "*") {
			print("</div><div class=\"events\">");
			$this->printUserEventData();
			print("</div>");
		}
		$this->endBody();
		$this->endHtml();
	}

	private function printFilter() {
		print("<span class=\"header1\">");
		Html::out("Filter");
		print("</span> ");
		$this->printSelectType();
		$this->printSelectLoghost();
		$this->printSelectService();
	}

	private function printHostipEventData() {
		$dbh = $this->dbh();
		$type = $this->getSessionType();
		$loghost = $this->getSessionLoghost();
		$service = $this->getSessionService();
		$hostip = $this->getRequestHostip();
		$select = $dbh->prepare("SELECT a.typeid, b.loghost, c.service, d.id, d.user, e.id, e.hostmac, e.vendor, SUM(a.count), MIN(a.first), MAX(a.last) FROM event a, loghost b, service c, user d, hostmac e WHERE a.loghostid = b.id AND a.serviceid = c.id AND a.userid = d.id AND a.hostmacid = e.id AND ('*' = ? OR a.typeid = ?) AND ('*' = ? OR b.id = ?) AND ('*' = ? OR c.id = ?) AND ('*' = ? OR a.hostipid = ?) GROUP BY a.typeid, b.id, c.id, d.id, e.id ORDER BY MAX(a.last) DESC");
		$select->bindParam(1, $type, PDO::PARAM_STR);
		$select->bindParam(2, $type, PDO::PARAM_STR);
		$select->bindParam(3, $loghost, PDO::PARAM_STR);
		$select->bindParam(4, $loghost, PDO::PARAM_STR);
		$select->bindParam(5, $service, PDO::PARAM_STR);
		$select->bindParam(6, $service, PDO::PARAM_STR);
		$select->bindParam(7, $hostip, PDO::PARAM_STR);
		$select->bindParam(8, $hostip, PDO::PARAM_STR);
		$select->execute();
		$select->bindColumn(1, $typeId, PDO::PARAM_STR);
		$select->bindColumn(2, $loghost, PDO::PARAM_STR);
		$select->bindColumn(3, $service, PDO::PARAM_STR);
		$select->bindColumn(4, $userId, PDO::PARAM_STR);
		$select->bindColumn(5, $user, PDO::PARAM_STR);
		$select->bindColumn(6, $hostmacId, PDO::PARAM_STR);
		$select->bindColumn(7, $hostmac, PDO::PARAM_STR);
		$select->bindColumn(8, $vendor, PDO::PARAM_STR);
		$select->bindColumn(9, $count, PDO::PARAM_INT);
		$select->bindColumn(10, $first, PDO::PARAM_INT);
		$select->bindColumn(11, $last, PDO::PARAM_INT);
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
		Html::out($l12n->t("User"));
		print("</th><th>");
		Html::out($l12n->t("MAC"));
		print("</th><th>");
		Html::out($l12n->t("Count"));
		print("</th><th>");
		Html::out($l12n->t("Period"));
		print("</th></tr>");
		print("</thead>");
		print("<tbody>");
		$rowNr = 1;
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
			if($user != "") {
				print("<a href=\"?cmd=viewevents&user={$userId}\">");
				$this->printUser($user);
				print("</a>");
			} else {
				Html::out("-");
			}
			print("</td><td>");
			if($hostmac != "") {
				print("<a href=\"?cmd=viewevents&hostmac={$hostmacId}\">");
				$this->printHostmac($hostmac, $vendor);
				print("</a>");
			} else {
				Html::out("-");
			}
			print("</td><td class=\"right\">");
			Html::out($count);
			print("</td><td>");
			Html::out($l12n->formatTimestamp($first));
			Html::out(" - ");
			Html::out($l12n->formatTimestamp($last));
			print("</td></tr>");
			$rowNr++;
		}
		print("</tbody>");
		print("</table>");
	}

	private function printHostmacEventData() {
		$dbh = $this->dbh();
		$type = $this->getSessionType();
		$loghost = $this->getSessionLoghost();
		$service = $this->getSessionService();
		$hostmac = $this->getRequestHostmac();
		$select = $dbh->prepare("SELECT a.typeid, b.loghost, c.service, d.id, d.hostip, d.host, d.countrycode, d.countryname, e.id, e.user, SUM(a.count), MIN(a.first), MAX(a.last) FROM event a, loghost b, service c, hostip d, user e WHERE a.loghostid = b.id AND a.serviceid = c.id AND a.hostipid = d.id AND a.userid = e.id AND ('*' = ? OR a.typeid = ?) AND ('*' = ? OR b.id = ?) AND ('*' = ? OR c.id = ?) AND ('*' = ? OR a.hostmacid = ?) GROUP BY a.typeid, b.id, c.id, d.id, e.id ORDER BY MAX(a.last) DESC");
		$select->bindParam(1, $type, PDO::PARAM_STR);
		$select->bindParam(2, $type, PDO::PARAM_STR);
		$select->bindParam(3, $loghost, PDO::PARAM_STR);
		$select->bindParam(4, $loghost, PDO::PARAM_STR);
		$select->bindParam(5, $service, PDO::PARAM_STR);
		$select->bindParam(6, $service, PDO::PARAM_STR);
		$select->bindParam(7, $hostmac, PDO::PARAM_STR);
		$select->bindParam(8, $hostmac, PDO::PARAM_STR);
		$select->execute();
		$select->bindColumn(1, $typeId, PDO::PARAM_STR);
		$select->bindColumn(2, $loghost, PDO::PARAM_STR);
		$select->bindColumn(3, $service, PDO::PARAM_STR);
		$select->bindColumn(4, $hostipId, PDO::PARAM_STR);
		$select->bindColumn(5, $hostip, PDO::PARAM_STR);
		$select->bindColumn(6, $host, PDO::PARAM_STR);
		$select->bindColumn(7, $countrycode, PDO::PARAM_STR);
		$select->bindColumn(8, $countryname, PDO::PARAM_STR);
		$select->bindColumn(9, $userId, PDO::PARAM_STR);
		$select->bindColumn(10, $user, PDO::PARAM_STR);
		$select->bindColumn(11, $count, PDO::PARAM_INT);
		$select->bindColumn(12, $first, PDO::PARAM_INT);
		$select->bindColumn(13, $last, PDO::PARAM_INT);
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
		Html::out($l12n->t("Host"));
		print("</th><th>");
		Html::out($l12n->t("User"));
		print("</th><th>");
		Html::out($l12n->t("Count"));
		print("</th><th>");
		Html::out($l12n->t("Period"));
		print("</th></tr>");
		print("</thead>");
		print("<tbody>");
		$rowNr = 1;
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
			if($hostip != "") {
				print("<a href=\"?cmd=viewevents&hostip={$hostipId}\">");
				$this->printHostip($hostip, $host, $countrycode, $countryname);
				print("</a>");
			} else {
				Html::out("-");
			}
			print("</td><td>");
			if($user != "") {
				print("<a href=\"?cmd=viewevents&user={$userId}\">");
				$this->printUser($user);
				print("</a>");
			} else {
				Html::out("-");
			}
			print("</td><td class=\"right\">");
			Html::out($count);
			print("</td><td>");
			Html::out($l12n->formatTimestamp($first));
			Html::out(" - ");
			Html::out($l12n->formatTimestamp($last));
			print("</td></tr>");
			$rowNr++;
		}
		print("</tbody>");
		print("</table>");
	}

	private function printUserEventData() {
		$dbh = $this->dbh();
		$type = $this->getSessionType();
		$loghost = $this->getSessionLoghost();
		$service = $this->getSessionService();
		$user = $this->getRequestUser();
		$select = $dbh->prepare("SELECT a.typeid, b.loghost, c.service, d.id, d.hostip, d.host, d.countrycode, d.countryname, e.id, e.hostmac, e.vendor, SUM(a.count), MIN(a.first), MAX(a.last) FROM event a, loghost b, service c, hostip d, hostmac e WHERE a.loghostid = b.id AND a.serviceid = c.id AND a.hostipid = d.id AND a.hostmacid = e.id AND ('*' = ? OR a.typeid = ?) AND ('*' = ? OR b.id = ?) AND ('*' = ? OR c.id = ?) AND ('*' = ? OR a.userid = ?) GROUP BY a.typeid, b.id, c.id, d.id, e.id ORDER BY MAX(a.last) DESC");
		$select->bindParam(1, $type, PDO::PARAM_STR);
		$select->bindParam(2, $type, PDO::PARAM_STR);
		$select->bindParam(3, $loghost, PDO::PARAM_STR);
		$select->bindParam(4, $loghost, PDO::PARAM_STR);
		$select->bindParam(5, $service, PDO::PARAM_STR);
		$select->bindParam(6, $service, PDO::PARAM_STR);
		$select->bindParam(7, $user, PDO::PARAM_STR);
		$select->bindParam(8, $user, PDO::PARAM_STR);
		$select->execute();
		$select->bindColumn(1, $typeId, PDO::PARAM_STR);
		$select->bindColumn(2, $loghost, PDO::PARAM_STR);
		$select->bindColumn(3, $service, PDO::PARAM_STR);
		$select->bindColumn(4, $hostipId, PDO::PARAM_STR);
		$select->bindColumn(5, $hostip, PDO::PARAM_STR);
		$select->bindColumn(6, $host, PDO::PARAM_STR);
		$select->bindColumn(7, $countrycode, PDO::PARAM_STR);
		$select->bindColumn(8, $countryname, PDO::PARAM_STR);
		$select->bindColumn(9, $hostmacId, PDO::PARAM_STR);
		$select->bindColumn(10, $hostmac, PDO::PARAM_STR);
		$select->bindColumn(11, $vendor, PDO::PARAM_STR);
		$select->bindColumn(12, $count, PDO::PARAM_INT);
		$select->bindColumn(13, $first, PDO::PARAM_INT);
		$select->bindColumn(14, $last, PDO::PARAM_INT);
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
		Html::out($l12n->t("Host"));
		print("</th><th>");
		Html::out($l12n->t("MAC"));
		print("</th><th>");
		Html::out($l12n->t("Count"));
		print("</th><th>");
		Html::out($l12n->t("Period"));
		print("</th></tr>");
		print("</thead>");
		print("<tbody>");
		$rowNr = 1;
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
			if($hostip != "") {
				print("<a href=\"?cmd=viewevents&hostip={$hostipId}\">");
				$this->printHostip($hostip, $host, $countrycode, $countryname);
				print("</a>");
			} else {
				Html::out("-");
			}
			print("</td><td>");
			if($hostmac != "") {
				print("<a href=\"?cmd=viewevents&hostmac={$hostmacId}\">");
				$this->printHostmac($hostmac, $vendor);
				print("</a>");
			} else {
				Html::out("-");
			}
			print("</td><td class=\"right\">");
			Html::out($count);
			print("</td><td>");
			Html::out($l12n->formatTimestamp($first));
			Html::out(" - ");
			Html::out($l12n->formatTimestamp($last));
			print("</td></tr>");
			$rowNr++;
		}
		print("</tbody>");
		print("</table>");
	}

}

?>
