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

	private static $sSyslog = false;
	private static $sConsole = false;
	private static $sDebug = false;

	public static function open($name, $syslog, $console, $debug) {
		self::$sSyslog = ($syslog ? openlog($name, LOG_CONS|LOG_PID, LOG_USER) : false);
		self::$sConsole = $console;
		self::$sDebug = $debug;
	}

	public static function close() {
		self::$sDebug = false;
		self::$sConsole = true;
		if(self::$sSyslog) {
			closelog();
			self::$sSyslog = false;
		}
	}

	public static function debug($message) {
		if(is_array($message)) {
			foreach($message as $message0) {
				self::debug($message0);
			}
		} else {
			if(self::$sDebug && self::$sConsole) {
				print "DEBUG:   {$message}\n";
			}
		}
		return $message;
	}

	public static function info($message) {
		if(is_array($message)) {
			foreach($message as $message0) {
				self::info($message0);
			}
		} else {
			if(self::$sConsole) {
				print "INFO:    {$message}\n";
			}
		}
		return $message;
	}

	public static function notice($message) {
		if(is_array($message)) {
			foreach($message as $message0) {
				self::debug($message0);
			}
		} else {
			if(self::$sConsole) {
				print "NOTICE:  {$message}\n";
			}
			if(self::$sSyslog) {
				syslog(LOG_NOTICE, $message);
			}
		}
		return $message;
	}

	public static function warning($message) {
		if(is_array($message)) {
			foreach($message as $message0) {
				self::debug($message0);
			}
		} else {
			if(self::$sConsole) {
				print "WARNING: {$message}\n";
			}
			if(self::$sSyslog) {
				syslog(LOG_WARNING, $message);
			}
			if(!self::$sConsole && !self::$sSyslog) {
				error_log("WARNING: {$message}");
			}
		}
		return $message;
	}

	public static function err($message) {
		if(is_array($message)) {
			foreach($message as $message0) {
				self::debug($message0);
			}
		} else {
			if(self::$sConsole) {
				print "ERR:     {$message}\n";
			}
			if(self::$sSyslog) {
				syslog(LOG_ERR, $message);
			}
			if(!self::$sConsole && !self::$sSyslog) {
				error_log("ERR:     {$message}");
			}
		}
		return $message;
	}

}

?>
