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

class SHOREWALLFilter extends Filter {

	const SERVICE_ICMPV6 = "icmpv6";

	const DENIED1 = "/^.* Shorewall:.*:DROP:.* SRC=(.*) DST=.* PROTO=ICMPv6 TYPE=128 .*$/U";

	public function __construct() {
		parent::__construct("SHOREWALL");
	}

	public function process($dbh, $ts, $line) {
		if(preg_match(self::DENIED1, $line, $match) === 1) {
			$this->recordIPEvent($dbh, Filter::STATUS_INFO, self::SERVICE_ICMPV6, $match[1], "", $ts, $line);
			$processed = true;
		} else {
			$processed = false;
		}
		return $processed;
	}

}

?>
