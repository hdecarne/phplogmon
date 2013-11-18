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

class Files {

	public static function path($path, $name) {
		return $path."/".$name;
	}

	public static function safeOpendir($path) {
		$dir = opendir($path);
		if($dir === false) {
			throw new Exception(Log::err("Cannot open directory '{$dir}'"));
		}
		return $dir;
	}

	public static function readdirMatch($dir, $pattern) {
		$file = readdir($dir);
		while($file !== false && !fnmatch($pattern, $file)) {
			$file = readdir($dir);
		}
		return $file;
	}

	public static function safeFilemtime($file) {
		$mtime = filemtime($file);
		if($mtime === false) {
			throw new Exception(Log::err("Cannot get mtime of file '{$file}'"));
		}
		return $mtime;
	}

}

?>
