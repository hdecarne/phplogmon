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

class MatchesDecoderSrvbyport extends MatchesDecoder {

	public function apply($dbh, $matches, $term) {
		$result = false;
		$params = $this->bindParams($matches, $term, 2);
		if($params !== false) {
			$port = $params[0];
			$proto = strtolower($params[1]);
			$service = getservbyport($port, $proto);
			$result = ($service != false ? $service : "{$port}/{$proto}");
		}
		return $result;
	}

}

?>
