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

class L12n {

	private static $sLanguages = array(
			"de" => "Lang-de-DE",
			"en" => "Lang-en-US"
		);

	private $tLang;

	private function __construct($lang) {
		$this->tLang = $lang;
	}

	public static function match($lang)  {
		return new self("en");
	}

	public function lang() {
		return $this->tLang;
	}

	public function t($text) {
		return $text;
	}

	public function formatTimestamp($ts) {
		return date($this->t("Y-m-d H:i:s"), $ts);
	}

}

?>
