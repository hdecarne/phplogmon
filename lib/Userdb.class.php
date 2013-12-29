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

abstract class Userdb {

	const TYPE_NONE = "none";
	const TYPE_POSIX = "posix";

	const STATUS_UNKNOWN = 0;
	const STATUS_INVALID = 1;
	const STATUS_VALID = 2;

	private static $sTypes = array(
		self::TYPE_NONE => "UserdbNone",
		self::TYPE_POSIX => "UserdbPosix"
	);

	protected function __construct($properties) {
	}

	public static function validTypes() {
		return array_keys(self::$sTypes);
	}

	public static function create($type, $properties) {
		if(!isset(self::$sTypes[$type])) {
			throw new Exception(Log::err("Unknown user db type '{$type}'"));
		}
		$userdbClass = self::$sTypes[$type];
		return new $userdbClass($properties);
	}

	function splitUserDomains($user) {
		$domainPos = strpos($user, "\\", 0);
		$emailPos = strpos($user, "@", 0);
		if($domainPos === false || $emailPos === false || $domainPos < $emailPos) {
			if($domainPos !== false && 0 < $domainPos && $domainPos + 1 < strlen($user)) {
				$loginDomain = substr($user, 0, $domainPos);
				$domainUser = substr($user, $domainPos + 1);
			} else {
				$loginDomain = "";
				$domainUser = $user;
			}
			if($domainPos === false || $loginDomain != "") {
				if($emailPos !== false && 0 < $emailPos && $emailPos + 1 < strlen($user)) {
					$emailUser = substr($user, 0, $emailPos);
					$emailDomain = substr($user, $emailPos + 1);
				} else {
					$emailUser = $domainUser;
					$emailDomain = "";
				}
			} else {
				$emailUser = $domainUser;
				$emailDomain = "";
			}
		} else {
			$loginDomain = "";
			$emailUser = $user;
			$emailDomain = "";
		}
		return array($loginDomain, $emailUser, $emailDomain);
	}

	abstract public function getStatus($user);

}

?>
