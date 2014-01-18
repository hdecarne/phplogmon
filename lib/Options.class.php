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

class Options {

	private static $sOptionDebug = false;
	private static $sOptionVerbose = false;
	private static $sOptionPretend = false;
	private static $sOptionKioskMode = false;

	private function __construct() {
	}

	public static function setDebug($optionDebug) {
		return self::$sOptionDebug = $optionDebug;
	}

	public static function debug() {
		return self::$sOptionDebug;
	}

	public static function setVerbose($optionVerbose) {
		self::$sOptionVerbose = $optionVerbose;
	}

	public static function verbose() {
		return self::$sOptionVerbose;
	}

	public static function setPretend($optionPretend) {
		self::$sOptionPretend = $optionPretend;
	}

	public static function pretend() {
		return self::$sOptionPretend;
	}

	public static function setKioskMode($optionKioskMode) {
		self::$sOptionKioskMode = $optionKioskMode;
	}

	public static function kioskMode() {
		return self::$sOptionKioskMode;
	}

}

?>
