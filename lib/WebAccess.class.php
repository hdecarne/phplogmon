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

	const SESSION_KEY_LANG = "Lang";
	const SESSION_KEY_UI = "UI";

	const UI_DESKTOP = "desktop";
	const UI_MOBILE = "mobile";

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

	public static function redirectRootAndExit() {
		$target = $_SESSION[self::SESSION_KEY_UI];
		$redirect = "Location: ";
		$redirect .= (isset($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] == "on" ? "https://" : "http://");
		$redirect .= $_SERVER["HTTP_HOST"];
		$redirect .= substr($_SERVER["SCRIPT_NAME"], 0, -strlen("index.php"));
		$redirect .= $target;
		$redirect .= "/";
		header($redirect);
		flush();
		exit;
	}

	public static function initSession() {
		session_name(self::SESSION_NAME);
		if(!session_start()) {
			throw new Exception("Cannot start session");
		}
		if(!isset($_SESSION[self::SESSION_KEY_LANG])) {
			$_SESSION[self::SESSION_KEY_LANG] = self::getDefaultLang();
		}
		if(!isset($_SESSION[self::SESSION_KEY_UI])) {
			$_SESSION[self::SESSION_KEY_UI] = self::getDefaultUI();
		}
	}

	private static function getDefaultLang() {
		return "en";
	}

	private static function getDefaultUI() {
		return self::UI_DESKTOP;
	}

	public static function lang() {
		return $_SESSION[self::SESSION_KEY_LANG];
	}

}

?>
