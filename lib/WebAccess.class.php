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

	const REQUEST_CMD = "cmd";
	const REQUEST_LANG = "lang";
	const REQUEST_MOBILE = "mobile";

	private $tDbh;

	protected function __construct($dbh) {
		$this->tDbh = $dbh;
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

	public static function initSession() {
		session_name(self::SESSION_NAME);
		if(!session_start()) {
			throw new Exception("Cannot start session");
		}
		if(!isset($_SESSION[self::SESSION_LANG])) {
			$_SESSION[self::SESSION_LANG] = self::getDefaultLang();
		}
		if(!isset($_SESSION[self::SESSION_MOBILE])) {
			$_SESSION[self::SESSION_MOBILE] = self::getDefaultMobile();
		}
	}

	private static function getDefaultLang() {
		return "en";
	}

	private static function getDefaultMobile() {
		$browser = get_browser();
		return is_object($browser) && $browser->ismobiledevice != false;
	}

	public static function getSession($key, $defaultValue) {
		return (isset($_SESSION[$key]) ? $_SESSION[$key] : $defaultValue);
	}

	public static function getRequest($key, $defaultValue) {
		return (isset($_REQUEST[$key]) ? $_REQUEST[$key] : $defaultValue);
	}

	protected function getRequestType() {
		return self::getRequest("type", "*");
	}

	protected function getRequestLoghost() {
		return self::getRequest("loghost", "*");
	}

	protected function getRequestService() {
		return self::getRequest("service", "*");
	}

	protected function getRequestHostip() {
		return self::getRequest("hostip", "*");
	}

	protected function getRequestHostmac() {
		return self::getRequest("hostmac", "*");
	}

	protected function getRequestUser() {
		return self::getRequest("user", "*");
	}

}

?>
