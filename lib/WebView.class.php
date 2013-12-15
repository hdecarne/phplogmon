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

abstract class WebView extends WebAccess {

	private $tL12n;

	protected function __construct($dbh) {
		parent::__construct($dbh);
		$this->tL12n = L12n::match($this->getSessionLang());
		self::mergeSession(self::SESSION_TYPE);
		self::mergeSession(self::SESSION_LOGHOST);
		self::mergeSession(self::SESSION_SERVICE);
		self::mergeSession(self::SESSION_NETWORK);
	}

	public function send() {
		$this->sendHtml();
	}

	abstract public function sendHtml();

	protected function l12n() {
		return $this->tL12n;
	}

	protected function beginHtml() {
		print("<!DOCTYPE HTML>\n");
		print("<html>\n");
	}

	protected function endHtml() {
		print("</html>\n");
	}

	protected function beginHeader($title) {
		print("<header>");
		print("<meta charset=\"utf-8\" />");
		print("<meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\" />");
		print("<meta http-equiv=\"cache-control\" content=\"no-cache\" />");
		print("<meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0\" />");
		$stylesheet = ($this->getSessionMobile() ? "css/mobile.css" : "css/desktop.css");
		print("<link rel=\"stylesheet\" type=\"text/css\" href=\"{$stylesheet}\">");
		print("<script src=\"js/logmon.js\" type=\"text/javascript\"></script>");
		print("<title>{$title}</title>");
	}

	protected function endHeader() {
		print("</header>");
	}

	protected function beginBody() {
		print("<body>");
		print("<form name=\"request\" action=\".\" method=\"get\">");
		$cmd = $this->getRequestCmd();
		print("<input name=\"cmd\" type=\"hidden\" value=\"{$cmd}\" />");
		$type = $this->getSessionType();
		print("<input name=\"type\" type=\"hidden\" value=\"{$type}\" />");
		$loghost = $this->getSessionLoghost();
		print("<input name=\"loghost\" type=\"hidden\" value=\"{$loghost}\" />");
		$service = $this->getSessionService();
		print("<input name=\"service\" type=\"hidden\" value=\"{$service}\" />");
		$network = $this->getSessionNetwork();
		print("<input name=\"network\" type=\"hidden\" value=\"{$network}\" />");
		$hostip = $this->getRequestHostip();
		print("<input name=\"hostip\" type=\"hidden\" value=\"{$hostip}\" />");
		$hostmac = $this->getRequestHostmac();
		print("<input name=\"hostmac\" type=\"hidden\" value=\"{$hostmac}\" />");
		$user = $this->getRequestUser();
		print("<input name=\"user\" type=\"hidden\" value=\"{$user}\" />");
		print("</form>");
	}

	protected function endBody() {
		print("<address>");
		Html::out(Version::signature());
		if(isset($_SERVER["REQUEST_TIME_FLOAT"])) {
			$elapsed = microtime(true) - $_SERVER["REQUEST_TIME_FLOAT"];
			Html::out(sprintf($this->tL12n->t(" - %f s"), $elapsed));
		}
		print("</address>");
		print("</body>");
	}

	protected function printNavHostips() {
		print("<a class=\"navbar\"href=\"?cmd=viewhostips\">");
		Html::out($this->tL12n->t("IP access"));
		print("</a>");
	}

	protected function printNavHostmacs() {
		print("<a class=\"navbar\"href=\"?cmd=viewhostmacs\">");
		Html::out($this->tL12n->t("MAC access"));
		print("</a>");
	}

	protected function printNavUsers() {
		print("<a class=\"navbar\"href=\"?cmd=viewusers\">");
		Html::out($this->tL12n->t("User access"));
		print("</a>");
	}

	protected function printSelectType() {
		$value = $this->getSessionType();
		print("<label for=\"typefilter\"> ");
		Html::out($this->tL12n->t("Status:"));
		print("</label>");
		print("<select size=\"1\" onchange=\"applyOption('*', 'type', this.value)\">");
		print("<option value=\"*\"");
		print($value == "*" ? " selected>" : ">");
		Html::out("*");
		print("</option>");
		print("<option value=\"1\"");
		print($value == "1" ? " selected>" : ">");
		Html::out($this->tL12n->t("Granted"));
		print("</option>");
		print("<option value=\"2\"");
		print($value == "2" ? " selected>" : ">");
		Html::out($this->tL12n->t("Denied"));
		print("</option>");
		print("<option value=\"3\"");
		print($value == "3" ? " selected>" : ">");
		Html::out($this->tL12n->t("Error"));
		print("</option>");
		print("</select>");
	}

	protected function printSelectLoghost() {
		$value = $this->getSessionLoghost();
		print("<label for=\"loghostfilter\"> ");
		Html::out($this->tL12n->t("Log:"));
		print("</label>");
		$dbh = $this->dbh();
		$select = $dbh->prepare("SELECT a.id, a.loghost FROM loghost a ORDER BY a.loghost");
		$select->execute();
		$select->bindColumn(1, $loghostId, PDO::PARAM_STR);
		$select->bindColumn(2, $loghost, PDO::PARAM_STR);
		print("<select size=\"1\" onchange=\"applyOption('*', 'loghost', this.value)\">");
		print("<option value=\"*\"");
		print($value == "*" ? " selected>" : ">");
		Html::out("*");
		print("</option>");
		while($select->fetch(PDO::FETCH_BOUND) !== false) {
			print("<option value=\"{$loghostId}\"");
			print($value == $loghostId ? " selected>" : ">");
			Html::out($loghost);
			print("</option>");
		}
		print("</select>");
	}

	protected function printSelectService() {
		$value = $this->getSessionService();
		print("<label for=\"servicefilter\"> ");
		Html::out($this->tL12n->t("Service:"));
		print("</label>");
		$dbh = $this->dbh();
		$select = $dbh->prepare("SELECT a.id, a.service FROM service a ORDER BY a.service");
		$select->execute();
		$select->bindColumn(1, $serviceId, PDO::PARAM_STR);
		$select->bindColumn(2, $service, PDO::PARAM_STR);
		print("<select size=\"1\" onchange=\"applyOption('*', 'service', this.value)\">");
		print("<option value=\"*\"");
		print($value == "*" ? " selected>" : ">");
		Html::out("*");
		print("</option>");
		while($select->fetch(PDO::FETCH_BOUND) !== false) {
			print("<option value=\"{$serviceId}\"");
			print($value == $serviceId ? " selected>" : ">");
			Html::out($service);
			print("</option>");
		}
		print("</select>");
	}

	protected function printSelectNetwork() {
		$value = $this->getSessionNetwork();
		print("<label for=\"networkfilter\"> ");
		Html::out($this->tL12n->t("Network:"));
		print("</label>");
		$dbh = $this->dbh();
		$select = $dbh->prepare("SELECT a.id, a.network FROM network a ORDER BY a.network");
		$select->execute();
		$select->bindColumn(1, $networkId, PDO::PARAM_STR);
		$select->bindColumn(2, $network, PDO::PARAM_STR);
		print("<select size=\"1\" onchange=\"applyOption('*', 'network', this.value)\">");
		print("<option value=\"*\"");
		print($value == "*" ? " selected>" : ">");
		Html::out("*");
		print("</option>");
		while($select->fetch(PDO::FETCH_BOUND) !== false) {
			print("<option value=\"{$networkId}\"");
			print($value == $networkId ? " selected>" : ">");
			Html::out($network);
			print("</option>");
		}
		print("</select>");
	}

	protected function printImgDownload($imgClass) {
		print("<img class=\"{$imgClass}\" src=\"img/download_log.png\" />");
	}

	protected function printImgType($imgClass, $typeId) {
		if($typeId == MonitorEvent::TYPEID_GRANTED) {
			$src = "img/type_granted.png";
			$alt = $this->tL12n->t("Granted");
			$title = $alt;
		} elseif($typeId == MonitorEvent::TYPEID_DENIED) {
			$src = "img/type_denied.png";
			$alt = $this->tL12n->t("Denied");
			$title = $alt;
		} elseif($typeId == MonitorEvent::TYPEID_ERROR) {
			$src = "img/type_error.png";
			$alt = $this->tL12n->t("Error");
			$title = $alt;
		} else {
			$src = "img/type_unknown.png";
			$alt = $this->tL12n->t("Unknown");
			$title = $alt;
		}
		print("<img class=\"{$imgClass}\" src=\"{$src}\" alt=\"{$alt}\" title=\"{$title}\" />");
	}

	protected function printTimerange($now, $first, $last) {
		$l12n = $this->l12n();
		$elapsed = $now - $last;
		if($elapsed < 60) {
			$when = sprintf($l12n->t("> %u second(s)"), $elapsed);
		} elseif(($elapsed /= 60) < 60) {
			$when = sprintf($l12n->t("> %u minute(s)"), $elapsed);
		} elseif(($elapsed /= 60) < 24) {
			$when = sprintf($l12n->t("> %u hour(s)"), $elapsed);
		} else {
			$elapsed /= 24;
			$when = sprintf($l12n->t("> %u day(s)"), $elapsed);
		}
		$timerange = Html::format($l12n->formatTimestamp($first)." - ".$l12n->formatTimestamp($last));
		print("<span title=\"{$timerange}\">");
		Html::out($when);
		print("</span>");
	}

	protected function printImgCountry($imgClass, $countrycode, $countryname) {
		$imgSrc = "img/country/{$countrycode}.png";
		$imgFile = dirname(__FILE__)."/../".$imgSrc;
		if(preg_match("/[A-Z]{2}/", $countrycode) == 1 && is_file($imgFile)) {
			$src = $imgSrc;
			$alt = htmlentities($countrycode);
			$title = htmlentities($countryname);
		} else {
			$src = "img/country_unknown.png";
			$alt = $this->tL12n->t("Unknown");
			$title = $alt;
		}
		print("<img class=\"{$imgClass}\" src=\"{$src}\" alt=\"{$alt}\" title=\"{$title}\" />");
	}

	protected function printHostip($hostip, $host, $countrycode, $countryname) {
		if($hostip != "") {
			$this->printImgCountry("tableicon", $countrycode, $countryname);
			if($host != $hostip) {
				print("<span title=\"{$hostip}\">");
				Html::out(" {$host}");
				print("</span>");
			} else {
				Html::out(" {$host}");
			}
		} else {
			Html::out("-");
		}
	}

	protected function printHostmac($hostmac, $vendor) {
		if($hostmac != "") {
			Html::out("{$hostmac}");
			if($vendor != "") {
				Html::out(" ({$vendor})");
			}
		} else {
			Html::out("-");
		}
	}

	protected function printUser($user) {
		if($user != "") {
			Html::out("{$user}");
		} else {
			Html::out("-");
		}
	}

}

?>
