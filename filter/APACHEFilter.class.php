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

class APACHEFilter extends Filter {

	const SERVICE_HTTP = "http";

	const HTTP_ACCESS = "/^(.*) - (.*) \[.*\] \".*\" (\d\d\d) .*$/U";

	public function __construct() {
		parent::__construct("POSTFIX");
	}

	public function process($dbh, $ts, $line) {
		if(preg_match(self::HTTP_ACCESS, $line, $match) === 1) {
			$user = $match[2];
			if($user === "-") {
				$user = '';
			}
			$httpStatus = $match[3];
			if((100 <= $httpStatus && $httpStatus < 200) || (300 <= $httpStatus && $httpStatus < 400)) {
				$eventStatus = Filter::STATUS_INFO;
			} elseif(200 <= $httpStatus && $httpStatus < 300) {
				$eventStatus = Filter::STATUS_GRANTED;
			} elseif($httpStatus == 401) {
				$eventStatus = ($user !== "" ? Filter::STATUS_DENIED : Filter::STATUS_INFO);
			} else {
				$eventStatus = Filter::STATUS_ERROR;
			}
			$this->recordIPEvent($dbh, $eventStatus, self::SERVICE_HTTP, $match[1], $user, $ts, $line);
			$processed = true;
		} else {
			$processed = false;
		}
		return $processed;
	}

}

?>
