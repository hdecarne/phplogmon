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
		$this->tL12n = L12n::match(self::getSession(self::SESSION_LANG));
	}

	protected function l12n() {
		return $this->tL12n;
	}

	abstract public function printHtml();

	protected function beginHtml() {
		print("<!DOCTYPE HTML>\n");
		print("<html>\n");
	}

	protected function endHtml() {
		print("</html>\n");
	}

	protected function beginHeader($title) {
		print("<header>\n");
		print("<meta charset=\"utf-8\" />\n");
		print("<meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\" />\n");
		print("<meta http-equiv=\"cache-control\" content=\"no-cache\" />\n");
		print("<meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0\" />\n");
		$stylesheet = (self::getSession(self::SESSION_MOBILE) ? "css/mobile.css" : "css/desktop.css");
		print("<link rel=\"stylesheet\" type=\"text/css\" href=\"{$stylesheet}\">\n");
		print("<script src=\"js/logmon.js\" type=\"text/javascript\"></script>\n");
		print("<title>{$title}</title>\n");
	}

	protected function endHeader() {
		print("</header>\n");
	}

	protected function beginBody() {
		print("<body>\n");
		print("<form name=\"request\" action=\".\" method=\"get\">\n");
		print("<input name=\"cmd\" type=\"hidden\" />\n");
		$type = self::getRequest("type", "*");
		print("<input name=\"type\" type=\"hidden\" value=\"{$type}\" />\n");
		$loghost = self::getRequest("loghost", "*");
		print("<input name=\"loghost\" type=\"hidden\" value=\"{$loghost}\" />\n");
		$service = self::getRequest("service", "*");
		print("<input name=\"service\" type=\"hidden\" value=\"{$service}\" />\n");
		print("</form>\n");
	}

	protected function endBody() {
		print("</body>\n");
	}

	protected function getSelectType() {
		return self::getRequest("type", "*");
	}

	protected function printSelectType() {
		$value = $this->getSelectType();
		$l12n = $this->l12n();
		print("<label for=\"typefilter\">");
		Html::out($l12n->t("Status:"));
		print("</label>");
		print("<select size=\"1\" onchange=\"applyOption('hostip', 'type', this.value)\">\n");
		print("<option value=\"*\"");
		print($value == "*" ? " selected>" : ">");
		Html::out("*");
		print("</option>\n");
		print("<option value=\"1\"");
		print($value == "1" ? " selected>" : ">");
		Html::out($l12n->t("Granted"));
		print("</option>\n");
		print("<option value=\"2\"");
		print($value == "2" ? " selected>" : ">");
		Html::out($l12n->t("Denied"));
		print("</option>\n");
		print("<option value=\"3\"");
		print($value == "3" ? " selected>" : ">");
		Html::out($l12n->t("Error"));
		print("</option>\n");
		print("</select>\n");
	}

	protected function getSelectLoghost() {
		return self::getRequest("loghost", "*");
	}

	protected function printSelectLoghost() {
		$value = $this->getSelectLoghost();
		$l12n = $this->l12n();
		print("<label for=\"loghostfilter\">");
		Html::out($l12n->t("Log:"));
		print("</label>");
		$dbh = $this->dbh();
		$select = $dbh->prepare("SELECT a.id, a.loghost FROM loghost a ORDER BY a.loghost");
		$select->execute();
		$select->bindColumn(1, $loghostId, PDO::PARAM_STR);
		$select->bindColumn(2, $loghost, PDO::PARAM_STR);
		print("<select size=\"1\" onchange=\"applyOption('hostip', 'loghost', this.value)\">\n");
		print("<option");
		print($value == "*" ? " selected>" : ">");
		Html::out("*");
		print("</option>\n");
		while($select->fetch(PDO::FETCH_BOUND) !== false) {
			print("<option value=\"{$loghostId}\"");
			print($value == $loghostId ? " selected>" : ">");
			Html::out($loghost);
			print("</option>\n");
		}
		print("</select>\n");
	}

	protected function getSelectService() {
		return self::getRequest("service", "*");
	}

	protected function printSelectService() {
		$value = $this->getSelectService();
		$l12n = $this->l12n();
		print("<label for=\"servicefilter\">");
		Html::out($l12n->t("Service:"));
		print("</label>");
		$dbh = $this->dbh();
		$select = $dbh->prepare("SELECT a.id, a.service FROM service a ORDER BY a.service");
		$select->execute();
		$select->bindColumn(1, $serviceId, PDO::PARAM_STR);
		$select->bindColumn(2, $service, PDO::PARAM_STR);
		print("<select size=\"1\" onchange=\"applyOption('hostip', 'service', this.value)\">\n");
		print("<option");
		print($value == "*" ? " selected>" : ">");
		Html::out("*");
		print("</option>\n");
		while($select->fetch(PDO::FETCH_BOUND) !== false) {
			print("<option value=\"{$serviceId}\"");
			print($value == $serviceId ? " selected>" : ">");
			Html::out($service);
			print("</option>\n");
		}
		print("</select>\n");
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

}

?>
