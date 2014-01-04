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

class FileDecoderGzip extends FileDecoder {

	private $tResource;
	private $tLine = false;

	public function __construct($file) {
		parent::__construct($file, FileDecoder::DECODER_GZIP);
		if(!extension_loaded("zlib")) {
			throw new Exception(Log::err("Missing extension 'zlib' for gzip decoding"));
		}
		$this->tResource = gzopen($file, "r");
		if($this->tResource === false) {
			throw new Exception(Log::err("Cannot open file '{$file}' for reading"));
		}
	}

	public function peekLine() {
		$line = false;
		if($this->tResource !== false) {
			if($this->tLine === false) {
				$this->tLine = self::safeTrim(gzgets($this->tResource, FileDecoder::BUFLEN));
			}
			if($this->tLine !== false){
				$line = $this->tLine;
			} else {
				gzclose($this->tResource);
				$this->tResource = false;
			}
		}
		return $line;
	}

	public function skipLine() {
		$this->tLine = false;
	}

}

?>
