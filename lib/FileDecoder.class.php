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

abstract class FileDecoder {

	const BUFLEN = 1024;

	const DECODER_AUTO = "auto";
	const DECODER_DIRECT = "direct";
	const DECODER_GZIP = "gzip";
	const DECODER_BZIP2 = "bzip2";

	private static $sDecoders = array(
		self::DECODER_AUTO => "FileDecoderAuto",
		self::DECODER_DIRECT => "FileDecoderDirect",
		self::DECODER_GZIP => "FileDecoderGzip",
		self::DECODER_BZIP2 => "FileDecoderBzip2"
	);

	protected function __construct($file, $decoder) {
		Log::debug("Opening '{$decoder}:{$file}'...");
	}

	public static function create($file, $decoder) {
		if(!isset(self::$sDecoders[$decoder])) {
			throw new Exception("Unknown decoder '{$decoder}' for file '{$file}'");
		}
		$decoderClass = self::$sDecoders[$decoder];
		return new $decoderClass($file);
	}

	abstract public function peekLine();

	abstract public function skipLine();

}

?>
