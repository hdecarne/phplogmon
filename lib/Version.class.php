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

class Version {

	private static $sShortName = "LogMon";
	private static $sLongName = "Log Monitor";
	private static $sBuild = "v1.0.0-20140108";

	public static function shortName() {
		return self::$sShortName;
	}

	public static function longName() {
		return self::$sLongName;
	}

	public static function build() {
		return self::$sBuild;
	}

	public static function signature() {
		return self::longName()." ".self::build();
	}

}

?>
