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

abstract class WebView extends WebAccess {

	private $tTypeFilterEnabled;
	private $tLoghostFilterEnabled;
	private $tNetworkFilterEnabled;
	private $tServiceFilterEnabled;
	private $tCountFilterEnabled;
	private $tLimitFilterEnabled;

	protected function __construct($dbh, $typeFilter, $loghostFilter, $networkFilter, $serviceFilter, $countFilter, $limitFilter) {
		parent::__construct($dbh);
		$this->tTypeFilterEnabled = $typeFilter;
		$this->tLoghostFilterEnabled = $loghostFilter;
		$this->tNetworkFilterEnabled = $networkFilter;
		$this->tServiceFilterEnabled = $serviceFilter;
		$this->tCountFilterEnabled = $countFilter;
		$this->tLimitFilterEnabled = $limitFilter;
		self::initSession(self::SESSION_TYPEFILTER, $this->tTypeFilterEnabled);
		self::initSession(self::SESSION_LOGHOSTFILTER, $this->tLoghostFilterEnabled);
		self::initSession(self::SESSION_NETWORKFILTER, $this->tNetworkFilterEnabled);
		self::initSession(self::SESSION_SERVICEFILTER, $this->tServiceFilterEnabled);
		self::initSession(self::SESSION_COUNTFILTER, $this->tCountFilterEnabled);
		self::initSession(self::SESSION_LIMITFILTER, $this->tLimitFilterEnabled);
	}

	private static function initSession($key, $enabled) {
		if($enabled) {
			self::mergeSession($key);
		} else {
			self::clearSession($key);
		}
	}

	public function sendResponse() {
		$this->sendHtml();
	}

	abstract public function sendHtml();

	protected function beginHtml() {
		print("<!DOCTYPE HTML>\n");
		print("<html>\n");
	}

	protected function endHtml() {
		print("</html>\n");
	}

	protected function beginHeader($title) {
		print("<head>");
		print("<meta charset=\"utf-8\" />");
		print("<meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\" />");
		print("<meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0\" />");
		$stylesheet = ($this->getSessionMobile() ? "css/mobile.css" : "css/desktop.css");
		print("<link rel=\"stylesheet\" type=\"text/css\" href=\"{$stylesheet}\">");
		print("<script src=\"js/logmon.js\" type=\"text/javascript\"></script>");
		print("<title>{$title}</title>");
	}

	protected function endHeader() {
		print("</head>");
	}

	protected function beginBody() {
		print("<body>");
		print("<form name=\"request\" action=\".\" method=\"get\">");
		$cmd = $this->getRequestCmd();
		print("<input name=\"cmd\" type=\"hidden\" value=\"{$cmd}\" />");
		$typefilter = $this->getSessionTypeFilter();
		print("<input name=\"typefilter\" type=\"hidden\" value=\"{$typefilter}\" />");
		$loghostfilter = $this->getSessionLoghostFilter();
		print("<input name=\"loghostfilter\" type=\"hidden\" value=\"{$loghostfilter}\" />");
		$networkfilter = $this->getSessionNetworkFilter();
		print("<input name=\"networkfilter\" type=\"hidden\" value=\"{$networkfilter}\" />");
		$servicefilter = $this->getSessionServiceFilter();
		print("<input name=\"servicefilter\" type=\"hidden\" value=\"{$servicefilter}\" />");
		$countfilter = $this->getSessionCountFilter();
		print("<input name=\"countfilter\" type=\"hidden\" value=\"{$countfilter}\" />");
		$limitfilter = $this->getSessionLimitFilter();
		print("<input name=\"limitfilter\" type=\"hidden\" value=\"{$limitfilter}\" />");
		$service = $this->getRequestService();
		print("<input name=\"service\" type=\"hidden\" value=\"{$service}\" />");
		$hostip = $this->getRequestHostip();
		print("<input name=\"hostip\" type=\"hidden\" value=\"{$hostip}\" />");
		$hostmac = $this->getRequestHostmac();
		print("<input name=\"hostmac\" type=\"hidden\" value=\"{$hostmac}\" />");
		$user = $this->getRequestUser();
		print("<input name=\"user\" type=\"hidden\" value=\"{$user}\" />");
		print("</form>");
	}

	protected function endBody($attribution = true) {
		$l12n = $this->l12n();
		print("<address>");
		Html::out(Version::signature());
		if(isset($_SERVER["REQUEST_TIME_FLOAT"])) {
			$elapsed = microtime(true) - $_SERVER["REQUEST_TIME_FLOAT"];
			Html::out(sprintf($l12n->t(" - %f s"), $elapsed));
		}
		print("</address>");
		if($attribution) {
			print("<address class=\"attribution\">");
			print("<a href=\"http://phplogmon.carne.de\">phpLogMon</a> sources are Copyright (c) 2012-2014 Holger de Carne and contributors und subject to the GPL version 3 or later.<br/>");
			print("The accompanied image resources are subject to different copyrights by: ");
			print("<a href=\"http://vathanx.deviantart.com/art/World-Flag-Icons-PNG-108083900\">Vathanx</a> from <a href=\"http://vathanx.deviantart.com\">vathanx.deviantart.com</a> | ");
			print("<a href=\"http://www.oxygen-icons.org\">Oxygen Team</a> from <a href=\"http://www.oxygen-icons.org\">www.oxygen-icons.org</a> | ");
			print("<a href=\"http://www.flaticon.com/packs/batch\">Adam Whitcroft</a> from <a href=\"http://www.flaticon.com\">www.flaticon.com</a> | ");
			print("<a href=\"http://www.mouserunner.com/Spheres_ColoCons1_Free_Icons.html\">Ken Saunders</a> from <a href=\"http://www.mouserunner.com\">www.mouserunner.com</a></br>");
			print("See <a href=\"?cmd=viewabout\">License</a> for full details.");
			print("</address>");
		}
		print("</body>");
	}

	protected function printNavBar() {
		$l12n = $this->l12n();
		print("<div class=\"navbar\">");
		print("<a class=\"navbar\" href=\"?cmd=viewevents&amp;typefilter=*&amp;loghostfilter=*&amp;networkfilter=*&amp;servicefilter=*\">");
		Html::out($l12n->t("Browse all"));
		print("</a>");
		print(" | ");
		print("<a class=\"navbar\" href=\"?cmd=viewservices&amp;typefilter=*&amp;loghostfilter=*&amp;networkfilter=*&amp;servicefilter=*\">");
		Html::out($l12n->t("Service access"));
		print("</a>");
		print(" | ");
		print("<a class=\"navbar\" href=\"?cmd=viewhostips&amp;typefilter=*&amp;loghostfilter=*&amp;networkfilter=*&amp;servicefilter=*\">");
		Html::out($l12n->t("Host access"));
		print("</a>");
		print(" | ");
		print("<a class=\"navbar\" href=\"?cmd=viewusers&amp;typefilter=*&amp;loghostfilter=*&amp;networkfilter=*&amp;servicefilter=*\">");
		Html::out($l12n->t("User access"));
		print("</a>");
		print(" | ");
		print("<a class=\"navbar\" href=\"?cmd=viewhostmacs&amp;typefilter=*&amp;loghostfilter=*&amp;networkfilter=*&amp;servicefilter=*\">");
		Html::out($l12n->t("MAC access"));
		print("</a>");
		print(" | ");
		print("<a class=\"navbar\" href=\"?cmd=viewabout\">");
		Html::out($l12n->t("About"));
		print("</a>");
		print("</div>");
	}

	protected function printFilter() {
		$l12n = $this->l12n();
		print("<div class=\"filter\">");
		if($this->tCountFilterEnabled) {
			$this->printSelectCountFilter();
		}
		if($this->tLimitFilterEnabled) {
			$this->printSelectLimitFilter();
		}
		if($this->tTypeFilterEnabled) {
			$this->printSelectTypeFilter();
		}
		if($this->tLoghostFilterEnabled) {
			$this->printSelectLoghostFilter();
		}
		if($this->tNetworkFilterEnabled) {
			$this->printSelectNetworkFilter();
		}
		if($this->tServiceFilterEnabled) {
			$this->printSelectServiceFilter();
		}
		if($this->getSessionTypeFilter() != "*" || $this->getSessionLoghostFilter() != "*" ||
			$this->getSessionNetworkFilter() != "*" || $this->getSessionServiceFilter() != "*") {
			print(" <input type=\"button\" value=\"");
			Html::out($l12n->t("Clear"));
			print("\" onclick=\"clearOptions()\" />");
		}
		print("</div>");
	}

	protected function printSelectTypeFilter() {
		$l12n = $this->l12n();
		$value = $this->getSessionTypeFilter();
		print("<label for=\"typefilter\"> ");
		Html::out($l12n->t("Status:"));
		print("</label>");
		print("<select id=\"typefilter\" size=\"1\" onchange=\"applyOption('*', 'typefilter', this.value)\">");
		print("<option value=\"*\"");
		print($value == "*" ? " selected>" : ">");
		Html::out("*");
		print("</option>");
		print("<option value=\"1\"");
		print($value == "1" ? " selected>" : ">");
		Html::out($l12n->t("Granted"));
		print("</option>");
		print("<option value=\"2\"");
		print($value == "2" ? " selected>" : ">");
		Html::out($l12n->t("Denied"));
		print("</option>");
		print("<option value=\"3\"");
		print($value == "3" ? " selected>" : ">");
		Html::out($l12n->t("Error"));
		print("</option>");
		print("</select>");
	}

	protected function printSelectLoghostFilter() {
		$l12n = $this->l12n();
		$value = $this->getSessionLoghostFilter();
		print("<label for=\"loghostfilter\"> ");
		Html::out($l12n->t("Log:"));
		print("</label>");
		$dbh = $this->dbh();
		$select = $dbh->prepare("SELECT a.id, a.loghost FROM loghost a ORDER BY a.loghost");
		$select->execute();
		$select->bindColumn(1, $loghostId, PDO::PARAM_STR);
		$select->bindColumn(2, $loghost, PDO::PARAM_STR);
		print("<select id=\"loghostfilter\" size=\"1\" onchange=\"applyOption('*', 'loghostfilter', this.value)\">");
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

	protected function printSelectNetworkFilter() {
		$l12n = $this->l12n();
		$value = $this->getSessionNetworkFilter();
		print("<label for=\"networkfilter\"> ");
		Html::out($l12n->t("Network:"));
		print("</label>");
		$dbh = $this->dbh();
		$select = $dbh->prepare("SELECT a.id, a.network FROM network a ORDER BY a.network");
		$select->execute();
		$select->bindColumn(1, $networkId, PDO::PARAM_STR);
		$select->bindColumn(2, $network, PDO::PARAM_STR);
		print("<select id=\"networkfilter\" size=\"1\" onchange=\"applyOption('*', 'networkfilter', this.value)\">");
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

	protected function printSelectServiceFilter() {
		$l12n = $this->l12n();
		$value = $this->getSessionServiceFilter();
		print("<label for=\"servicefilter\"> ");
		Html::out($l12n->t("Service:"));
		print("</label>");
		$dbh = $this->dbh();
		$select = $dbh->prepare("SELECT a.id, a.service FROM service a ORDER BY a.service");
		$select->execute();
		$select->bindColumn(1, $serviceId, PDO::PARAM_STR);
		$select->bindColumn(2, $service, PDO::PARAM_STR);
		print("<select id=\"servicefilter\" size=\"1\" onchange=\"applyOption('*', 'servicefilter', this.value)\">");
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

	protected function printSelectCountFilter() {
		$l12n = $this->l12n();
		$value = $this->getSessionCountFilter();
		print("<label for=\"countfilter\"> ");
		Html::out($l12n->t("Min count:"));
		print("</label>");
		print("<select id=\"countfilter\" size=\"1\" onchange=\"applyOption('*', 'countfilter', this.value)\">");
		for($option = 1; $option <= 1000; $option *= 10) {
			print("<option value=\"{$option}\"");
			print($value == $option ? " selected>" : ">");
			Html::out($option);
			print("</option>");
		}
		print("</select>");
	}

	protected function printSelectLimitFilter() {
		$l12n = $this->l12n();
		$value = $this->getSessionLimitFilter();
		print("<label for=\"limitfilter\"> ");
		Html::out($l12n->t("Max lines:"));
		print("</label>");
		print("<select id=\"limitfilter\" size=\"1\" onchange=\"applyOption('*', 'limitfilter', this.value)\">");
		print("<option value=\"0\"");
		print($value == 0 ? " selected>" : ">");
		Html::out("*");
		print("</option>");
		for($option = 10; $option < 100; $option += 10) {
			print("<option value=\"{$option}\"");
			print($value == $option ? " selected>" : ">");
			Html::out($option);
			print("</option>");
		}
		for($option = 100; $option <= 1000; $option += 100) {
			print("<option value=\"{$option}\"");
			print($value == $option ? " selected>" : ">");
			Html::out($option);
			print("</option>");
		}
		print("</select>");
	}

	protected function beginEventTable($headers) {
		print("<section class=\"events\">");
		print("<table>");
		print("<thead>");
		print("<tr>");
		foreach($headers as $header) {
			print("<th>");
			Html::out($header);
			print("</th>");
		}
		print("</tr>");
		print("</thead>");
		print("<tbody>");
	}

	protected function endEventTable() {
		print("</tbody>");
		print("</table>");
		print("</section>");
	}

	protected function beginEventRow() {
		print("<tr>");
	}

	protected function endEventRow() {
		print("</tr>");
	}

	protected function printEventRowNr($rowNr) {
		print("<td class=\"right\">");
		Html::out($rowNr);
		print("</td>");
	}

	protected function printEventType($typeId) {
		print("<td class=\"center\">");
		$this->printImgEventType("icon16", $typeId);
		print("</td>");
	}

	protected function printEventLoghost($loghost) {
		print("<td>");
		Html::out($loghost);
		print("</td>");
	}

	protected function printEventNetwork($network) {
		print("<td>");
		Html::out($network);
		print("</td>");
	}

	protected function printEventService($serviceId, $service, $typeId, $loghostId, $networkId) {
		print("<td><a href=\"?cmd=viewservice&amp;service={$serviceId}&amp;typefilter={$typeId}&amp;loghostfilter={$loghostId}&amp;networkfilter={$networkId}\">");
		Html::out($service);
		print("</a></td>");
	}

	protected function printEventHostip($hostipId, $hostip, $host, $countrycode, $countryname, $typeId, $loghostId, $networkId, $serviceId) {
		if($hostip != "") {
			print("<td><a href=\"?cmd=viewhostip&amp;hostip={$hostipId}&amp;typefilter={$typeId}&amp;loghostfilter={$loghostId}&amp;networkfilter={$networkId}&amp;servicefilter={$serviceId}\">");
			$this->printImgCountry("icon16", $countrycode, $countryname);
			if($host != $hostip) {
				print("<span title=\"{$hostip}\">");
				Html::out(" {$host}");
				print("</span>");
			} else {
				Html::out(" {$host}");
			}
			print("</a></td>");
		} else {
			print("<td class=\"center\">");
			Html::out("-");
			print("</td>");
		}
	}

	protected function printEventHostmac($hostmacId, $hostmac, $vendor, $typeId, $loghostId, $networkId, $serviceId) {
		if($hostmac != "") {
			print("<td><a href=\"?cmd=viewhostmac&amp;hostmac={$hostmacId}&amp;typefilter={$typeId}&amp;loghostfilter={$loghostId}&amp;networkfilter={$networkId}&amp;servicefilter={$serviceId}\">");
			$this->printImgVendor("icon16", $hostmac, $vendor);
			Html::out(" {$hostmac}");
			if($vendor != "") {
				Html::out(" ({$vendor})");
			}
			print("</a></td>");
		} else {
			print("<td class=\"center\">");
			Html::out("-");
			print("</td>");
		}
	}

	protected function printEventUser($userId, $user, $statusId, $typeId, $loghostId, $networkId, $serviceId) {
		if($user != "") {
			print("<td><a href=\"?cmd=viewuser&amp;user={$userId}&amp;typefilter={$typeId}&amp;loghostfilter={$loghostId}&amp;networkfilter={$networkId}&amp;servicefilter={$serviceId}\">");
			$this->printImgUserStatus("icon16", $statusId);
			if(!Options::kioskMode()) {
				Html::out(" {$user}");
			} else {
				Html::out(" ********");
			}
			print("</a></td>");
		} else {
			print("<td class=\"center\">");
			Html::out("-");
			print("</td>");
		}
	}

	protected function printEventCount($count) {
		print("<td class=\"right\">");
		Html::out($count);
		print("</td>");
	}

	protected function printEventTimerange($now, $first, $last) {
		$l12n = $this->l12n();
		$elapsed = $now - $last;
		print("<td>");
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
		print("</td>");
	}

	protected function printEventLogLinks($typeId, $loghostId, $networkId, $serviceId, $hostipId, $hostmacId, $userId) {
		$l12n = $this->l12n();
		print("<td class=\"center\">");
		print("<a href=\"?cmd=streamlogs&amp;type={$typeId}&amp;loghost={$loghostId}&amp;network={$networkId}&amp;service={$serviceId}&amp;hostip={$hostipId}&amp;hostmac={$hostmacId}&amp;user={$userId}\">");
		$alt = $title = Html::format($l12n->t("View"));
		print("<img class=\"icon16\" src=\"img/log_view.png\" alt=\"{$alt}\" title=\"{$title}\" />");
		print("</a> <a href=\"?cmd=streamlogs&amp;type={$typeId}&amp;loghost={$loghostId}&amp;network={$networkId}&amp;service={$serviceId}&amp;hostip={$hostipId}&amp;hostmac={$hostmacId}&amp;user={$userId}&amp;download=1\">");
		$alt = $title = Html::format($l12n->t("Download"));
		print("<img class=\"icon16\" src=\"img/log_download.png\" alt=\"{$alt}\" title=\"{$title}\" />");
		print("</a></td>");
	}

	protected function printImgService($imgClass, $service) {
		$l12n = $this->l12n();
		$src = "img/service_generic.png";
		$alt = Html::format($l12n->t("Service"));
		$title = Html::format("$service");
		print("<img class=\"{$imgClass}\" src=\"{$src}\" alt=\"{$alt}\" title=\"{$title}\" />");
	}

	protected function printImgEventType($imgClass, $typeId) {
		$l12n = $this->l12n();
		$alt = Html::format($l12n->t("Status"));
		switch($typeId) {
			case MonitorEvent::TYPEID_GRANTED:
				$src = "img/type_granted.png";
				$title = Html::format($l12n->t("Granted"));
				break;
			case MonitorEvent::TYPEID_DENIED:
				$src = "img/type_denied.png";
				$title = Html::format($l12n->t("Denied"));
				break;
			case MonitorEvent::TYPEID_ERROR:
				$src = "img/type_error.png";
				$title = Html::format($l12n->t("Error"));
				break;
			default:
				$src = "img/type_unknown.png";
				$title = Html::format($l12n->t("Unknown"));
		}
		print("<img class=\"{$imgClass}\" src=\"{$src}\" alt=\"{$alt}\" title=\"{$title}\" />");
	}

	protected function printImgCountry($imgClass, $countrycode, $countryname) {
		$l12n = $this->l12n();
		$imgName = strtoupper($countrycode);
		$imgSrc = "img/country/{$imgName}.png";
		$imgFile = dirname(__FILE__)."/../".$imgSrc;
		if(preg_match("/[A-Z]{2}/", $countrycode) == 1 && is_file($imgFile)) {
			$src = $imgSrc;
		} else {
			$src = "img/country_generic.png";
		}
		$alt = Html::format($countrycode);
		$title = Html::format($countryname);
		print("<img class=\"{$imgClass}\" src=\"{$src}\" alt=\"{$alt}\" title=\"{$title}\" />");
	}

	protected function printImgVendor($imgClass, $hostmac, $vendor) {
		$l12n = $this->l12n();
		$src = "img/vendor_generic.png";
		$alt = Html::format($l12n->t("Vendor"));
		$title = Html::format("$vendor");
		print("<img class=\"{$imgClass}\" src=\"{$src}\" alt=\"{$alt}\" title=\"{$title}\" />");
	}

	protected function printImgUserStatus($imgClass, $statusId) {
		$l12n = $this->l12n();
		$alt = Html::format($l12n->t("User"));
		switch($statusId) {
			case Userdb::STATUS_INVALID:
				$src = "img/user_invalid.png";
				$title = Html::format($l12n->t("Invalid"));
				break;
			case Userdb::STATUS_VALID:
				$src = "img/user_valid.png";
				$title = Html::format($l12n->t("Valid"));
				break;
			default:
				$src = "img/user_generic.png";
				$title = Html::format($l12n->t("Unknown"));
		}
		print("<img class=\"{$imgClass}\" src=\"{$src}\" alt=\"{$alt}\" title=\"{$title}\" />");
	}

	protected function beginDetailsSection() {
		print("<section class=\"details\">");
	}

	protected function endDetailsSection() {
		print("</section>");
	}

	protected function beginDetails1() {
		print("<div class=\"details1\">");
	}

	protected function endDetails1() {
		print("</div>");
	}

	protected function beginDetails2() {
		print("<div class=\"details2\">");
	}

	protected function endDetails2() {
		print("</div>");
	}

	protected function beginDetailsTable() {
		print("<table>");
		print("<tbody>");
	}

	protected function endDetailsTable() {
		print("</tbody>");
		print("</table>");
	}

	protected function beginDetailsTableElement($title) {
		print("<tr>");
		print("<td class=\"clear right bold\">");
		Html::out($title);
		print("</td><td class=\"clear\">");
	}

	protected function endDetailsTableElement() {
		print("</td>");
		print("</tr>");
	}

	protected function printLogLinks($imgClass, $typeId, $loghostId, $networkId, $serviceId, $hostipId, $hostmacId, $userId) {
		$l12n = $this->l12n();
		print("<a href=\"?cmd=streamlogs&amp;type={$typeId}&amp;loghost={$loghostId}&amp;network={$networkId}&amp;service={$serviceId}&amp;&amp;hostip={$hostipId}&amp;hostmac={$hostmacId}&amp;user={$userId}\">");
		$alt = $title = Html::format($l12n->t("View"));
		print("<img class=\"icon16\" src=\"img/log_view.png\" alt=\"{$alt}\" title=\"{$title}\" />");
		print("</a> <a href=\"?cmd=streamlogs&amp;type={$typeId}&amp;loghost={$loghostId}&amp;network={$networkId}&amp;service={$serviceId}&amp;hostip={$hostipId}&amp;hostmac={$hostmacId}&amp;user={$userId}&amp;download=1\">");
		$alt = $title = Html::format($l12n->t("Download"));
		print("<img class=\"icon16\" src=\"img/log_download.png\" alt=\"{$alt}\" title=\"{$title}\" />");
		print("</a>");
	}

	protected function printMapLink($imgClass, $host, $latitude, $longitude, $location) {
		$l12n = $this->l12n();
		$alt = Html::format($l12n->t("Map"));
		$title = Html::format(sprintf($l12n->t("Location %f x %f"), $latitude, $longitude));
		$url = sprintf(MAP_URI_FORMAT, $host, $latitude, $longitude);
		print("<a href=\"{$url}\">");
		print("<img class=\"{$imgClass}\" src=\"img/map_link.png\" alt=\"{$alt}\" title=\"{$title}\" /> ");
		Html::out($location);
		print("</a>");
	}

	protected function printHostWhoisLink($imgClass, $host) {
		$l12n = $this->l12n();
		$alt = Html::format($l12n->t("Whois"));
		$title = Html::format(sprintf($l12n->t("Whois '%s'"), $host));
		$url = sprintf(WHOISHOST_URI_FORMAT, $host);
		print("<a href=\"{$url}\">");
		print("<img class=\"{$imgClass}\" src=\"img/whois_link.png\" alt=\"{$alt}\" title=\"{$title}\" /> ");
		Html::out($host);
		print("</a>");
	}

	protected function printHostipWhoisLink($imgClass, $hostip) {
		$l12n = $this->l12n();
		$alt = Html::format($l12n->t("Whois"));
		$title = Html::format(sprintf($l12n->t("Whois '%s'"), $hostip));
		$url = sprintf(WHOISIP_URI_FORMAT, $hostip);
		print("<a href=\"{$url}\">");
		print("<img class=\"{$imgClass}\" src=\"img/whois_link.png\" alt=\"{$alt}\" title=\"{$title}\" /> ");
		Html::out($hostip);
		print("</a>");
	}

}

?>
