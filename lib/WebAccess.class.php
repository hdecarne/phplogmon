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

abstract class WebAccess {

	const STATUS_FORBIDDEN = 403;
	const STATUS_SERVICE_UNAVAILABLE = 503;

	const SESSION_NAME = "Logmon";

	const SESSION_LANG = "lang";
	const SESSION_MOBILE = "mobile";

	const DEFAULT_LANG = "en";
	const DEFAULT_MOBILE = "0";

	const SESSION_TYPEFILTER = "typefilter";
	const SESSION_LOGHOSTFILTER = "loghostfilter";
	const SESSION_NETWORKFILTER = "networkfilter";
	const SESSION_SERVICEFILTER = "servicefilter";
	const SESSION_COUNTFILTER = "countfilter";
	const SESSION_LIMITFILTER = "limitfilter";

	const REQUEST_CMD = "cmd";
	const REQUEST_TYPEFILTER = "typefilter";
	const REQUEST_LOGHOSTFILTER = "loghostfilter";
	const REQUEST_NETWORKFILTER = "networkfilter";
	const REQUEST_SERVICEFILTER = "servicefilter";
	const REQUEST_COUNTFILTER = "countfilter";
	const REQUEST_LIMITFILTER = "limitfilter";
	const REQUEST_TYPE = "type";
	const REQUEST_LOGHOST = "loghost";
	const REQUEST_NETWORK = "network";
	const REQUEST_SERVICE = "service";
	const REQUEST_HOSTIP = "hostip";
	const REQUEST_HOSTMAC = "hostmac";
	const REQUEST_USER = "user";
	const REQUEST_DOWNLOAD = "download";

	const DEFAULT_CMD = "*";
	const DEFAULT_TYPEFILTER = "*";
	const DEFAULT_LOGHOSTFILTER = "*";
	const DEFAULT_NETWORKFILTER = "*";
	const DEFAULT_SERVICEFILTER = "*";
	const DEFAULT_COUNTFILTER = 1;
	const DEFAULT_LIMITFILTER = 100;
	const DEFAULT_TYPE = "*";
	const DEFAULT_LOGHOST = "*";
	const DEFAULT_NETWORK = "*";
	const DEFAULT_SERVICE = "*";
	const DEFAULT_HOSTIP = "*";
	const DEFAULT_HOSTMAC = "*";
	const DEFAULT_USER = "*";
	const DEFAULT_DOWNLOAD = "0";

	private $tDbh;
	private $tL12n;

	protected function __construct($dbh) {
		$this->tDbh = $dbh;
		session_name(self::SESSION_NAME);
		if(!session_start()) {
			throw new Exception(Log::err("Cannot start session"));
		}
		if(!isset($_SESSION[self::SESSION_LANG])) {
			$_SESSION[self::SESSION_LANG] = self::getDefaultLang();
		}
		self::mergeSession(self::SESSION_LANG);
		$this->tL12n = L12n::match($this->getSessionLang());
		if(!isset($_SESSION[self::SESSION_MOBILE])) {
			$_SESSION[self::SESSION_MOBILE] = self::getDefaultMobile();
		}
		self::mergeSession(self::SESSION_MOBILE);
	}

	abstract public function sendResponse();

	protected function dbh() {
		return $this->tDbh;
	}

	protected function l12n() {
		return $this->tL12n;
	}

	public static function sendStatusAndExit($status) {
		http_response_code($status);
		flush();
		exit;
	}

	public static function reportExceptionAndExit($e) {
		print "<!DOCTYPE HTML>\n";
		print "<html>\n";
		print "<head>\n";
		print "<title>Error</title>\n";
		print "</head>\n";
		print "<body>\n";
		print "<h1>";
		print htmlentities("An exception occured");
		print "</h1>\n";
		print htmlentities($e->getMessage());
		print "\n<h1>";
		print htmlentities("Exception details:");
		print "</h1>\n";
		print "<pre>\n";
		print htmlentities($e);
		print "\n</pre>\n";
		print "<hr>\n";
		print "<address>";
		print htmlentities(Version::signature());
		print "</address>\n";
		print "</body>\n";
		print "</html>\n";
		flush();
		exit;
	}

	private static function getDefaultLang() {
		return "en";
	}

	private static function getDefaultMobile() {
		$browser = get_browser();
		return is_object($browser) && $browser->ismobiledevice != false;
	}

	public static function mergeSession($key) {
		if(isset($_REQUEST[$key])) {
			$_SESSION[$key] = $_REQUEST[$key];
		}
	}

	public static function clearSession($key) {
		if(isset($_SESSION[$key])) {
			unset($_SESSION[$key]);
		}
	}

	public static function getSession($key, $defaultValue) {
		return (isset($_SESSION[$key]) ? $_SESSION[$key] : $defaultValue);
	}

	protected function getSessionLang() {
		return self::getSession(self::SESSION_LANG, self::DEFAULT_LANG);
	}

	protected function getSessionMobile() {
		return self::getSession(self::SESSION_MOBILE, self::DEFAULT_MOBILE);
	}

	protected function getSessionTypeFilter() {
		return self::getSession(self::SESSION_TYPEFILTER, self::DEFAULT_TYPEFILTER);
	}

	protected function getSessionLoghostFilter() {
		return self::getSession(self::SESSION_LOGHOSTFILTER, self::DEFAULT_LOGHOSTFILTER);
	}

	protected function getSessionNetworkFilter() {
		return self::getSession(self::SESSION_NETWORKFILTER, self::DEFAULT_NETWORKFILTER);
	}

	protected function getSessionServiceFilter() {
		return self::getSession(self::SESSION_SERVICEFILTER, self::DEFAULT_SERVICEFILTER);
	}

	protected function getSessionCountFilter() {
		return self::getSession(self::SESSION_COUNTFILTER, self::DEFAULT_COUNTFILTER);
	}

	protected function getSessionLimitFilter() {
		return self::getSession(self::SESSION_LIMITFILTER, self::DEFAULT_LIMITFILTER);
	}

	public static function getRequest($key, $defaultValue) {
		return (isset($_REQUEST[$key]) ? $_REQUEST[$key] : $defaultValue);
	}

	protected function getRequestCmd() {
		return self::getRequest(self::REQUEST_CMD, self::DEFAULT_CMD);
	}

	protected function getRequestTypeFilter() {
		return self::getRequest(self::REQUEST_TYPEFILTER, self::DEFAULT_TYPEFILTER);
	}

	protected function getRequestLoghostFilter() {
		return self::getRequest(self::REQUEST_LOGHOSTFILTER, self::DEFAULT_LOGHOSTFILTER);
	}

	protected function getRequestNetworkFilter() {
		return self::getRequest(self::REQUEST_NETWORKFILTER, self::DEFAULT_NETWORKFILTER);
	}

	protected function getRequestServiceFilter() {
		return self::getRequest(self::REQUEST_SERVICEFILTER, self::DEFAULT_SERVICEFILTER);
	}

	protected function getRequestCountFilter() {
		return self::getRequest(self::REQUEST_COUNTFILTER, self::DEFAULT_COUNTFILTER);
	}

	protected function getRequestLimitFilter() {
		return self::getRequest(self::REQUEST_LIMITFILTER, self::DEFAULT_LIMITFILTER);
	}

	protected function getRequestType() {
		return self::getRequest(self::REQUEST_TYPE, self::DEFAULT_TYPE);
	}

	protected function getRequestLoghost() {
		return self::getRequest(self::REQUEST_LOGHOST, self::DEFAULT_LOGHOST);
	}

	protected function getRequestNetwork() {
		return self::getRequest(self::REQUEST_NETWORK, self::DEFAULT_NETWORK);
	}

	protected function getRequestService() {
		return self::getRequest(self::REQUEST_SERVICE, self::DEFAULT_SERVICE);
	}

	protected function getRequestHostip() {
		return self::getRequest(self::REQUEST_HOSTIP, self::DEFAULT_HOSTIP);
	}

	protected function getRequestHostmac() {
		return self::getRequest(self::REQUEST_HOSTMAC, self::DEFAULT_HOSTMAC);
	}

	protected function getRequestUser() {
		return self::getRequest(self::REQUEST_USER, self::DEFAULT_USER);
	}

	protected function getRequestDownload() {
		return self::getRequest(self::REQUEST_DOWNLOAD, self::DEFAULT_DOWNLOAD);
	}

}

?>
