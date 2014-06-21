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

abstract class MatchesDecoder {

	const DECODER_MATCH = "match";
	const DECODER_SRVBYPORT = "srvbyport";
	const DECODER_MACFROMIP = "macfromip";
	const DECODER_IPFROMMAC = "ipfrommac";

	private static $sDecoders = array(
		self::DECODER_MATCH => "MatchesDecoderMatch",
		self::DECODER_SRVBYPORT => "MatchesDecoderSrvbyport",
		self::DECODER_MACFROMIP => "MatchesDecoderMacfromip",
		self::DECODER_IPFROMMAC => "MatchesDecoderIpfrommac"
	);

	public static function validDecoders() {
		return array_keys(self::$sDecoders);
	}

	public static function create($decoder) {
		if(!isset(self::$sDecoders[$decoder])) {
			throw new Exception(Log::err("Unknown decoder '{$decoder}'"));
		}
		$decoderClass = self::$sDecoders[$decoder];
		return new $decoderClass();
	}

	protected function bindParams($matches, $term, $count) {
		$params = array();
		$paramsValid = false;
		$indexes = explode("|", $term);
		if(count($indexes) == $count) {
			$paramsValid = true;
			foreach($indexes as $index) {
				$param = $this->bindParam($matches, $index);
				$params[] = $param;
				$paramsValid = $paramsValid && ($param !== false);
			}
		} else {
			Log::warning("Unexpected number of parameters in term '{$term}'");
		}
		return ($paramsValid ? $params : false);
	}

	protected function bindParam($matches, $index) {
		$param = false;
		if(is_numeric($index)) {
			$indexVal = intval($index);
			$indexLimit = count($matches) - 1;
			if(0 <= $index && $index <= $indexLimit) {
				$param = $matches[$indexVal];
			} else {
				Log::warning("Param index {$indexVal} out of range [0,{$indexLimit}]");
			}
		} else {
			Log::warning("Invalid param index '{$index}'");
		}
		return $param;
	}

	abstract public function apply($dbh, $matches, $term);

}

?>
