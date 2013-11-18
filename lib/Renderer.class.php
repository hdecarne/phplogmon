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

abstract class Renderer {

	public static function match() {
		$userAgent = (isset($_SERVER["HTTP_USER_AGENT"]) ? $_SERVER["HTTP_USER_AGENT"] : "");
		if(self::isMobileUA($userAgent)) {
			$renderer = new RendererMobile();
		} else {
			$renderer = new RendererStandard();
		}
		return $renderer;
	}

	private static function isMobileUA($userAgent) {
		return strpos($userAgent, "iPhone") !== false;
	}

	abstract public function stylesheet();

	public function beginErrorMessage() {
		print "<div class=\"msg_error\">\n";
	}

	public function beginWarningMessage() {
		print "<div class=\"msg_warning\">\n";
	}

	public function beginInfoMessage() {
		print "<div class=\"msg_info\">\n";
	}

	public function beginSuccessMessage() {
		print "<div class=\"msg_success\">\n";
	}

	public function endMessage() {
		print "</div>\n";
	}

}

?>
