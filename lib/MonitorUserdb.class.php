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

class MonitorUserdb {

	private $tType;
	private $tSourceNames = array();
	private $tProperties = array();

	public function __construct($type) {
		$this->tType = $type;
	}

	public function __toString() {
		$string = "type={$this->tType}";
		$sourceIndex = 0;
		foreach($this->tSourceNames as $sourceName) {
			$string .= ";source[{$sourceIndex}]='{$sourceName}'";
			$sourceIndex++;
		}
		return $string;
	}

	public function getType() {
		return $this->tType;
	}

	public function addSource($sourceName) {
		$this->tSourceNames[$sourceName] = $sourceName;
	}

	public function getSourceNames() {
		return $this->tSourceNames;
	}

	public function addProperty($name, $value) {
		$this->tProperties[$name] = $value;
	}

	public function getProperties() {
		return $this->tProperties;
	}

}

?>
