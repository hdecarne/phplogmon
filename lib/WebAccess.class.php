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

class WebAccess {

	const STATUS_SERVICE_UNAVAILABLE = 503;

	const SESSION_NAME = "Logmon";

	const SESSION_LANG = "lang";
	const SESSION_MOBILE = "mobile";

	const SESSION_TYPE = "type";
	const SESSION_LOGHOST = "loghost";
	const SESSION_SERVICE = "service";

	const REQUEST_CMD = "cmd";
	const REQUEST_TYPE = "type";
	const REQUEST_LOGHOST = "loghost";
	const REQUEST_SERVICE = "service";
	const REQUEST_HOSTIP = "hostip";
	const REQUEST_HOSTMAC = "hostmac";
	const REQUEST_USER = "user";

	private $tDbh;

	protected function __construct($dbh) {
		$this->tDbh = $dbh;
		session_name(self::SESSION_NAME);
		if(!session_start()) {
			throw new Exception("Cannot start session");
		}
		if(!isset($_SESSION[self::SESSION_LANG])) {
			$_SESSION[self::SESSION_LANG] = self::getDefaultLang();
		}
		self::mergeSession(self::SESSION_LANG);
		if(!isset($_SESSION[self::SESSION_MOBILE])) {
			$_SESSION[self::SESSION_MOBILE] = self::getDefaultMobile();
		}
		self::mergeSession(self::SESSION_MOBILE);
	}

	protected function dbh() {
		return $this->tDbh;
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

	public static function sendStatusAndExit($status) {
		http_response_code($status);
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

	public static function getSession($key, $defaultValue) {
		return (isset($_SESSION[$key]) ? $_SESSION[$key] : $defaultValue);
	}

	protected function getSessionLang() {
		return self::getSession(self::SESSION_LANG, "en");
	}

	protected function getSessionMobile() {
		return self::getSession(self::SESSION_MOBILE, false);
	}

	protected function getSessionType() {
		return self::getSession(self::SESSION_TYPE, "*");
	}

	protected function getSessionLoghost() {
		return self::getSession(self::SESSION_LOGHOST, "*");
	}

	protected function getSessionService() {
		return self::getSession(self::SESSION_SERVICE, "*");
	}

	public static function getRequest($key, $defaultValue) {
		return (isset($_REQUEST[$key]) ? $_REQUEST[$key] : $defaultValue);
	}

	protected function getRequestCmd() {
		return self::getRequest(self::REQUEST_CMD, "*");
	}

	protected function getRequestHostip() {
		return self::getRequest(self::REQUEST_HOSTIP, "*");
	}

	protected function getRequestHostmac() {
		return self::getRequest(self::REQUEST_HOSTMAC, "*");
	}

	protected function getRequestUser() {
		return self::getRequest(self::REQUEST_USER, "*");
	}

}

?>
