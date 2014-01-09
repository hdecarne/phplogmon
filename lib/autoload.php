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

function __autoload($name) {
	if(substr_compare($name, "GeoIp2\\", 0, strlen("GeoIp2\\")) == 0) {
		$includeFile = dirname(__FILE__);
		$includeFile .= DIRECTORY_SEPARATOR."..".DIRECTORY_SEPARATOR."ext-lib".DIRECTORY_SEPARATOR;
		if("\\" != DIRECTORY_SEPARATOR) {
			$includeFile .= str_replace("\\", DIRECTORY_SEPARATOR, $name);
		} else {
			$includeFile .= $name;
		}
		$includeFile .= ".php";
	} elseif(substr_compare($name, "MaxMind\\", 0, strlen("MaxMind\\")) == 0) {
		$includeFile = dirname(__FILE__);
		$includeFile .= DIRECTORY_SEPARATOR."..".DIRECTORY_SEPARATOR."ext-lib".DIRECTORY_SEPARATOR;
		if("\\" != DIRECTORY_SEPARATOR) {
			$includeFile .= str_replace("\\", DIRECTORY_SEPARATOR, $name);
		} else {
			$includeFile .= $name;
		}
		$includeFile .= ".php";
	} else {
		$includeFile = $name;
		$includeFile .= ".class.php";
	}
	include($includeFile);
}

?>
