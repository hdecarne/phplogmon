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

class UserdbPosix extends Userdb {

	public function __construct($properties) {
		parent::__construct($properties);
	}

	public function getStatus($user) {
		$splitUser = $this->splitUserDomains($user);
		if($user === "") {
			$status = self::STATUS_INVALID;
		} elseif(posix_getpwnam($user) !== false) {
			$status = self::STATUS_VALID;
		} elseif($user != $splitUser[1] && posix_getpwnam($splitUser[1]) !== false) {
			$status = self::STATUS_VALID;
		} else {
			$status = self::STATUS_INVALID;
		}
		return $status;
	}

}

?>
