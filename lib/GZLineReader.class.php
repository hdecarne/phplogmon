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

class GZLineReader extends LineReader {

	private $_file;
	private $_buflen;
	private $_line;
	private $_resource;

	public function __construct($file, $buflen) {
		$this->_file = $file;
		$this->_buflen = $buflen;
		$this->_line = false;
		$this->_resource = gzopen($file, 'r');
		if($this->_resource == false) {
			throw new Exception("Cannot open file '{$file}' for reading.");
		}
	}

	public function nextLine() {
		$line = $this->peekLine();
		$this->skipLine();
		return $line;
	}

	public function peekLine() {
		if($this->_line === false && !gzeof($this->_resource)) {
			$this->_line = gzgets($this->_resource, $this->_buflen);
			if($this->_line === false) {
				throw new Exception("Cannot read line from file '{$this->_file}'.");
			}
		}
		return $this->_line;
	}

	public function skipLine() {
		$this->_line = false;
	}

	public function close() {
		$this->_line = false;
		gzclose($this->_resource);
		$this->_resource = false;
	}

}

?>
