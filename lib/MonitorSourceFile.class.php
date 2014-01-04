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

class MonitorSourceFile {

	const DECODER_DEFAULT = FileDecoder::DECODER_DIRECT;

	private $tFile;
	private $tDefaultService;
	private $tDecoder;

	public function __construct($file, $defaultService, $decoder) {
		$this->tFile = $file;
		$this->tDefaultService = $defaultService;
		$this->tDecoder = $decoder;
	}

	public function __toString() {
		return "{$this->tDecoder}:{$this->tFile}";
	}

	public function getFile() {
		return $this->tFile;
	}

	public function getDefaultService() {
		return $this->tDefaultService;
	}

	public function getDecoder($logfile) {
		return FileDecoder::create($logfile, $this->tDecoder);
	}

}

?>
