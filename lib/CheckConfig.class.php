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

class CheckConfig {

	public static function configs($files) {
		$missing = array();
		foreach($files as $file) {
			if(!is_readable($file)) {
				$missing[] = $file;
			}
		}
		if(count($missing) > 0) {
			throw new Exception(Log::err(sprintf("Cannot read required config(s) file: %s", Strings::format($missing))));
		}
	}

	public static function extensions($names) {
		$missing = array();
		foreach($names as $name) {
			if(!extension_loaded($name)) {
				$missing[] = $name;
			}
		}
		if(count($missing) > 0) {
			throw new Exception(Log::err(sprintf("Missing required extension(s): %s", Strings::format($missing))));
		}
	}

}

?>
