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

class MonitorNetwork {

	const TYPE_INET = "inet";
	const TYPE_INET6 = "inet6";
	const TYPE_IFCONFIG = "ifconfig";

	private static $sTypes = array(
		self::TYPE_INET,
		self::TYPE_INET6,
		self::TYPE_IFCONFIG
	);

	private $tName;
	private $tType;
	private $tNetwork;

	public function __construct($name, $type, $network) {
		$this->tName = $name;
		$this->tType = $type;
		$this->tNetwork = $network;
	}

	public static function validTypes() {
		return self::$sTypes;
	}

	public function getName() {
		return $this->tName;
	}

	public function getType() {
		return $this->tType;
	}

	public function getNetwork() {
		return $this->tNetwork;
	}

}

?>
