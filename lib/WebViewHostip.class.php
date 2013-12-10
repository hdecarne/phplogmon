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

class WebViewHostip extends WebView {

	public function __construct($dbh) {
		parent::__construct($dbh);
	}

	public function printHtml() {
		$this->beginHtml();
		$l12n = $this->l12n();
		$title = $l12n->t("LogMon - IP access");
		$this->beginHeader($title);
		$this->endHeader();
		$this->beginBody();
		print("<div class=\"filter\">\n");
		$this->printFilter();
		print("</div><div class=\"events\">\n");
		$this->printEventData();
		print("</div>\n");
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

	private function printEventData() {
		$dbh = $this->dbh();
		$type = $this->getRequestType();
		$loghost = $this->getRequestLoghost();
		$service = $this->getRequestService();
		$select = $dbh->prepare("SELECT a.typeid, b.loghost, c.service, d.id, d.hostip, d.host, d.countrycode, d.countryname, SUM(a.count), MIN(a.first), MAX(a.last) FROM event a, loghost b, service c, hostip d WHERE a.loghostid = b.id AND a.serviceid = c.id AND a.hostipid = d.id AND d.host <> '' AND ('*' = ? OR a.typeid = ?) AND ('*' = ? OR b.id = ?) AND ('*' = ? OR c.id = ?) GROUP BY a.typeid, b.id, c.id, d.id ORDER BY MAX(a.last) DESC");
		$select->bindParam(1, $type, PDO::PARAM_STR);
		$select->bindParam(2, $type, PDO::PARAM_STR);
		$select->bindParam(3, $loghost, PDO::PARAM_STR);
		$select->bindParam(4, $loghost, PDO::PARAM_STR);
		$select->bindParam(5, $service, PDO::PARAM_STR);
		$select->bindParam(6, $service, PDO::PARAM_STR);
		$select->execute();
		$select->bindColumn(1, $typeId, PDO::PARAM_STR);
		$select->bindColumn(2, $loghost, PDO::PARAM_STR);
		$select->bindColumn(3, $service, PDO::PARAM_STR);
		$select->bindColumn(4, $hostipId, PDO::PARAM_STR);
		$select->bindColumn(5, $hostip, PDO::PARAM_STR);
		$select->bindColumn(6, $host, PDO::PARAM_STR);
		$select->bindColumn(7, $countrycode, PDO::PARAM_STR);
		$select->bindColumn(8, $countryname, PDO::PARAM_STR);
		$select->bindColumn(9, $count, PDO::PARAM_INT);
		$select->bindColumn(10, $first, PDO::PARAM_INT);
		$select->bindColumn(11, $last, PDO::PARAM_INT);
		$l12n = $this->l12n();
		print("<table>\n");
		print("<thead>\n");
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
		Html::out($l12n->t("Count"));
		print("</th><th>");
		Html::out($l12n->t("Period"));
		print("</th></tr>\n");
		print("</thead>\n");
		print("<tbody>\n");
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
			print("</td><td><a href=\"?cmd=viewevents&hostip={$hostipId}\">");
			$this->printImgCountry("tableicon", $countrycode, $countryname);
			Html::out(" {$host}");
			print("</a></td><td class=\"right\">");
			Html::out($count);
			print("</td><td>");
			Html::out($l12n->formatTimestamp($first));
			Html::out(" - ");
			Html::out($l12n->formatTimestamp($last));
			print("</td></tr>\n");
			$rowNr++;
		}
		print("</tbody>\n");
		print("</table>");
	}

}

?>
