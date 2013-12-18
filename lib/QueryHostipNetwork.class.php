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

class QueryHostipNetwork {

	private static $sNetworkmapIndex = array();

	private static $sNetmaskMap = array(
		"0.0.0.0" => 32,
		"128.0.0.0" => 31,
		"192.0.0.0" => 30,
		"224.0.0.0" => 29,
		"240.0.0.0" => 28,
		"248.0.0.0" => 27,
		"252.0.0.0" => 26,
		"254.0.0.0" => 25,
		"255.0.0.0" => 24,
		"255.128.0.0" => 23,
		"255.192.0.0" => 22,
		"255.224.0.0" => 21,
		"255.240.0.0" => 20,
		"255.248.0.0" => 19,
		"255.252.0.0" => 18,
		"255.254.0.0" => 17,
		"255.255.0.0" => 16,
		"255.255.128.0" => 15,
		"255.255.192.0" => 14,
		"255.255.224.0" => 13,
		"255.255.240.0" => 12,
		"255.255.248.0" => 11,
		"255.255.252.0" => 10,
		"255.255.254.0" => 9,
		"255.255.255.0" => 8,
		"255.255.255.128" => 7,
		"255.255.255.192" => 6,
		"255.255.255.224" => 5,
		"255.255.255.240" => 4,
		"255.255.255.248" => 3,
		"255.255.255.252" => 2,
		"255.255.255.254" => 1,
		"255.255.255.255" => 0
	);

	private function __construct() {
	}

	public static function getNetworkId($dbh, $networkmap, $hostip) {
		$cache =& $dbh->getCache(get_class());
		return (isset($cache[$hostip]) ? $cache[$hostip] : $cache[$hostip] = self::getDbNetworkId($dbh, $networkmap, $hostip));
	}

	private static function getDbNetworkId($dbh, $networkmap, $hostip) {
		Log::debug("Retrieving network for host ip '{$hostip}'...");
		$network = self::mapNetwork($networkmap, $hostip);
		$select = $dbh->prepare("SELECT a.id FROM network a WHERE network = ?");
		$select->bindValue(1, $network, PDO::PARAM_STR);
		$select->execute();
		$select->bindColumn(1, $id, PDO::PARAM_STR);
		if($select->fetch(PDO::FETCH_BOUND) === false) {
			if(!Options::pretend()) {
				$insert = $dbh->prepare("INSERT INTO network (network) VALUES(?)");
				$insert->bindValue(1, $network, PDO::PARAM_STR);
				$insert->execute();
				$id = $dbh->lastInsertId();
			} else {
				$id = false;
			}
		}
		return $id;
	}

	public static function discardUnused($dbh) {
		if(!Options::pretend()) {
			$dbh->clearCache(get_class());
			$delete = $dbh->prepare("DELETE FROM network WHERE id NOT IN (SELECT networkid FROM event)");
			$delete->execute();
		}
	}

	private static function mapNetwork($networkmap, $hostip) {
		if($hostip === "") {
			$network = $networkmap->getInternal();
		} elseif(self::isLocalhost($hostip)) {
			$network = $networkmap->getInternal();
		} else {
			$network = self::matchNetworkmapEntry($networkmap, $hostip);
		}
		return $network;
	}

	private static function isLocalhost($hostip) {
		$normalized = ($hostip !== "" ? inet_ntop(inet_pton($hostip)) : $hostip);
		return $normalized === "127.0.0.1" || $normalized === "::1";
	}

	private static function matchNetworkmapEntry($networkmap, $hostip) {
		self::prepareNetworkmap($networkmap);
		$sourceKey = Strings::format($networkmap->getSourceNames());
		$hostipValue = ($hostip !== "" ? inet_pton($hostip) : false);
		$network = null;
		if($hostipValue !== false) {
			foreach(self::$sNetworkmapIndex[$sourceKey] as $prefix => $prefixIndex) {
				$hostipKey = self::address2Key($hostipValue, $prefix);
				if(isset($prefixIndex[$hostipKey])) {
					$network = $prefixIndex[$hostipKey];
					break;
				}
			}
		}
		return (!is_null($network) ? $network : $networkmap->getExternal());
	}

	private static function prepareNetworkmap($networkmap) {
		$sourceKey = Strings::format($networkmap->getSourceNames());
		if(!isset(self::$sNetworkmapIndex[$sourceKey])) {
			foreach($networkmap->getNetworks() as $network) {
				$networkName = $network->getName();
				$networkType = $network->getType();
				switch($networkType) {
					case MonitorNetwork::TYPE_INET:
					case MonitorNetwork::TYPE_INET6:
						self::prepareNetworkmapInet($sourceKey, $network);
						break;
					case MonitorNetwork::TYPE_IFCONFIG:
						self::prepareNetworkmapIfconfig($sourceKey, $network);
						break;
					default:
						throw new Exception(Log::err("Unknown network type '{$networkType}' for network '{$networkName}'"));
				}
			}
		}
	}

	private static function prepareNetworkmapInet($sourceKey, $network) {
		$networkName = $network->getName();
		$cidrAddress = $network->getNetwork();
		Log::debug("Preparing inet/inet6 map entry '{$cidrAddress}' for network '{$networkName}'...");
		$cidrAddressParts = explode("/", $cidrAddress);
		if(count($cidrAddressParts) != 2) {
			throw new Exception(Log::err("Unexpected inet/inet6 map entry '{$cidrAddress}' for network '{$networkName}'"));
		}
		self::prepareNetworkmapEntry($sourceKey, $networkName, $cidrAddressParts[0], $cidrAddressParts[1]);
	}

	private static function prepareNetworkmapIfconfig($sourceKey, $network) {
		$networkName = $network->getName();
		$ifconfigCmd = $network->getNetwork();
		Log::debug("Preparing ifconfig map entry '{$ifconfigCmd}' for network '{$networkName}'...");
		$phandle = popen($ifconfigCmd, "r");
		if($phandle === false) {
			throw new Exception(Log::err("Cannot execute ifconfig map entry '{$ifconfigCmd}' for network '{$networkName}'"));
		}
		$matchCount = 0;
		while(($line = fgets($phandle)) !== false) {
			if(preg_match("/.*inet ([0-9\.]+)  netmask ([0-9\.]+).*/", $line, $matches) === 1) {
				$netmask = $matches[2];
				if(!isset(self::$sNetmaskMap[$netmask])) {
					throw new Exception(Log::err("Unexpected netmask '{$netmask}' in ifconfig line '{$line}'"));
				}
				$prefix = self::$sNetmaskMap[$netmask];
				self::prepareNetworkmapEntry($sourceKey, $networkName, $matches[1], $prefix);
				$matchCount++;
			} elseif(preg_match("/.*inet6 ([0-9a-fA-F:]+)  prefixlen ([0-9]+)  scopeid.*/", $line, $matches) === 1) {
				self::prepareNetworkmapEntry($sourceKey, $networkName, $matches[1], $matches[2]);
				$matchCount++;
			}
		}
		if($matchCount == 0) {
			Log::warning("No network map entries have been created from ifconfig command '{$ifconfigCmd}' output");
		}
		pclose($phandle);
	}

	private static function prepareNetworkmapEntry($sourceKey, $networkName, $address, $prefix) {
		$addressValue = inet_pton($address);
		if($addressValue === false) {
			throw new Exception(Log::err("Unexpected address '{$address}' while preparing network map for network '{$networkName}'"));
		}
		if(!isset(self::$sNetworkmapIndex[$sourceKey])) {
			self::$sNetworkmapIndex[$sourceKey] = array();
		}
		if(!isset(self::$sNetworkmapIndex[$sourceKey][$prefix])) {
			self::$sNetworkmapIndex[$sourceKey][$prefix] = array();
		}
		$addressKey = self::address2Key($addressValue, $prefix);
		self::$sNetworkmapIndex[$sourceKey][$prefix][$addressKey] = $networkName;
	}

	private static function address2Key($addressValue, $prefix) {
		if(strlen($addressValue) == 4) {
			$normalizedValue = inet_pton(long2ip(ip2long(inet_ntop($addressValue)) & ((0xffffffff << (32 - $prefix)) & 0xffffffff)));
		} else {
			$prefixBytes = $prefix / 8;
			$normalizedValue = substr($addressValue, 0, $prefixBytes);
			if($prefixBytes * 8 < $prefix) {
				$normalizedValue .= chr(ord(substr($addressValue, $prefixBytes, 1)) & 0xf0);
			}
			$normalizedValue = str_pad($normalizedValue, 16, chr(0));
		}
		return bin2hex($normalizedValue);
	}

}

?>
