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

class Log {

	private static $_syslog = false;
	private static $_console = false;
	private static $_debug = false;

	public static function open($name, $syslog, $console, $debug) {
		self::$_syslog = ($syslog ? openlog($name, LOG_CONS|LOG_PID, LOG_USER) : false);
		self::$_console = $console;
		self::$_debug = $debug;
	}

	public static function close() {
		self::$_debug = false;
		self::$_console = false;
		if(self::$_syslog) {
			closelog();
			self::$_syslog = false;
		}
	}

	public static function debug($message) {
		if(self::$_debug) {
			if(self::$_console) {
				print "DEBUG:   {$message}\n";
			}
		}
		return $message;
	}

	public static function notice($message) {
		if(self::$_console) {
			print "NOTICE:  {$message}\n";
		}
		if(self::$_syslog) {
			syslog(LOG_NOTICE, $message);
		}
		return $message;
	}

	public static function warning($message) {
		if(self::$_console) {
			print "WARNING: {$message}\n";
		}
		if(self::$_syslog) {
			syslog(LOG_WARNING, $message);
		}
		return $message;
	}

	public static function err($message) {
		if(self::$_console) {
			print "ERR:     {$message}\n";
		}
		if(self::$_syslog) {
			syslog(LOG_ERR, $message);
		}
		return $message;
	}

}

?>
