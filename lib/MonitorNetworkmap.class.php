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

class MonitorNetworkmap {

	private $tInternal;
	private $tExternal;
	private $tSourceNames = array();
	private $tNetworks = array();

	public function __construct($internal, $external) {
		$this->tInternal = $internal;
		$this->tExternal = $external;
	}

	public function __toString() {
		$sourcesString = "";
		$sourceIndex = 0;
		foreach($this->tSourceNames as $sourceName) {
			if(strlen($sourcesString) > 0) {
				$sourcesString .= ";";
			}
			$sourcesString .= "source[{$sourceIndex}]='{$sourceName}'";
			$sourceIndex++;
		}
		$networks = array();
		$networks[$this->tInternal] = $this->tInternal;
		$networks[$this->tExternal] = $this->tExternal;
		foreach($this->tNetworks as $network) {
			$networkName = $network->getName();
			$networks[$networkName] = $networkName;
		}
		$networksString = "";
		$networkIndex = 0;
		foreach($networks as $network) {
			if(strlen($networksString) > 0) {
				$networksString .= ";";
			}
			$networksString .= "network[{$networkIndex}]='{$network}'";
			$networkIndex++;
		}
		return "{$sourcesString};{$networksString}";
	}

	public function getInternal() {
		return $this->tInternal;
	}

	public function getExternal() {
		return $this->tExternal;
	}

	public function addSource($sourceName) {
		$this->tSourceNames[$sourceName] = $sourceName;
	}

	public function getSourceNames() {
		return $this->tSourceNames;
	}

	public function addNetwork($name, $type, $network) {
		$this->tNetworks[] = new MonitorNetwork($name, $type, $network);
	}

	public function getNetworks() {
		return $this->tNetworks;
	}

}

?>
