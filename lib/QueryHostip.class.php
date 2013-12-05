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

class QueryHostip {

	private static $sIpv4Networks = null;
	private static $sIpv6Networks = null;

	private function __construct() {
	}

	public static function normalizeHostip($hostip) {
		$normalized = false;
		if($hostip == "") {
			$normalized = $hostip;
		} elseif(($pton = @inet_pton($hostip)) !== false) {
			$normalized = strtolower(inet_ntop($pton));
		}
		return $normalized;
	}

	public static function getHostipId($dbh, $hostip) {
		$cache =& $dbh->getCache(get_class());
		return (isset($cache[$hostip]) ? $cache[$hostip] : $cache[$hostip] = self::getDbHostipId($dbh, $hostip));
	}

	private static function getDbHostipId($dbh, $hostip) {
		if($hostip != "") {
			Log::debug("Retrieving info for ip '{$hostip}'...");
		}
		$network = self::determineNetwork($hostip);
		$host = self::safeGethostbyaddr($hostip);
		$select = $dbh->prepare("SELECT a.id FROM hostip a WHERE hostip = ? AND network = ? AND host = ?");
		$select->bindValue(1, $hostip, PDO::PARAM_STR);
		$select->bindValue(2, $network, PDO::PARAM_STR);
		$select->bindValue(3, $host, PDO::PARAM_STR);
		$select->execute();
		$select->bindColumn(1, $id, PDO::PARAM_STR);
		if($select->fetch(PDO::FETCH_BOUND) === false) {
			$geoipRecord = self::safeGeoiprecordbyname($hostip);
			if(!Options::pretend()) {
				$insert = $dbh->prepare("INSERT INTO hostip (hostip, network, host, continentcode, countrycode, countryname, region, city, postalcode, latitude, longitude) VALUES(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
				$insert->bindValue(1, $hostip, PDO::PARAM_STR);
				$insert->bindValue(2, $network, PDO::PARAM_STR);
				$insert->bindValue(3, $host, PDO::PARAM_STR);
				$insert->bindValue(4, $geoipRecord["continent_code"], PDO::PARAM_STR);
				$insert->bindValue(5, $geoipRecord["country_code"], PDO::PARAM_STR);
				$insert->bindValue(6, $geoipRecord["country_name"], PDO::PARAM_STR);
				$insert->bindValue(7, $geoipRecord["region"], PDO::PARAM_STR);
				$insert->bindValue(8, $geoipRecord["city"], PDO::PARAM_STR);
				$insert->bindValue(9, $geoipRecord["postal_code"], PDO::PARAM_STR);
				$insert->bindValue(10, $geoipRecord["latitude"], PDO::PARAM_STR);
				$insert->bindValue(11, $geoipRecord["longitude"], PDO::PARAM_STR);
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
			$delete = $dbh->prepare("DELETE FROM hostip WHERE id NOT IN (SELECT hostipid FROM event)");
			$delete->execute();
		}
	}

	private static function determineNetwork($hostip) {
		$matchingNetwork = false;
		if($hostip != "") {
			if(is_null(self::$sIpv4Networks) || is_null(self::$sIpv6Networks)) {
				self::initNetworks();
			}
			$addr = inet_pton($hostip);
			if(strlen($addr) == 16) {
				foreach(self::$sIpv6Networks as $network) {
					if(self::matchIpv6Network($addr, $network[0], $network[1])) {
						$matchingNetwork = $network[2];
						break;
					}
				}
			} else {
				$addr = ip2long($hostip);
				foreach(self::$sIpv4Networks as $network) {
					if(self::matchIpv4Network($addr, $network[0], $network[1])) {
						$matchingNetwork = $network[2];
						break;
					}
				}
			}
			if($matchingNetwork === false) {
				$matchingNetwork = $hostip;
			}
		} else {
			$matchingNetwork = $hostip;
		}
		return $matchingNetwork;
	}

	private static function matchIpv4Network($addr, $networkAddr, $networkScope) {
		$mask = (0xffffffff << (32 - $networkScope)) & 0xffffffff;
		return ($addr & $mask) == $networkAddr;
	}

	private static function matchIpv6Network($addr, $networkAddr, $networkScope) {
		$match = false;
		$bitOffset = 0;
		$byteOffset = 0;
		while($bitOffset < $networkScope) {
			$addrByte = ord(substr($addr, $byteOffset, 1));
			$networkAddrByte = ord(substr($networkAddr, $byteOffset, 1));
			if($bitOffset + 8 < $networkScope) {
				if($addrByte == $networkAddrByte) {
					$bitOffset += 8;
					$byteOffset++;
				} else {
					$bitOffset = $networkScope;
				}
			} elseif($bitOffset + 8 == $networkScope) {
				$match = $addrByte == $networkAddrByte;
				$bitOffset = $networkScope;
			} else {
				$mask = (0xff << (8 - ($networkScope - $bitOffset))) & 0xff;
				$addrByte &= $mask;
				$match = $addrByte == $networkAddrByte;
				$bitOffset = $networkScope;
			}
		}
		return $match;
	}

	private static function initNetworks() {
		self::$sIpv4Networks = array();
		self::$sIpv6Networks = array();
		$output = array();
		$status = -1;
		exec("ifconfig", $output, $status);
		if($status == 0) {
			self::initNetworksIfconfig($output);
		}
	}

	private static function initNetworksIfconfig($output) {
		Log::debug("Getting local networks from ifconfig output...");
		foreach($output as $line) {
			if((preg_match("/\s.*inet\s*([0-9\.]+)\s*netmask\s*([0-9\.]+)$/", $line, $matches) === 1
				|| preg_match("/\s*inet\s*([0-9\.]+)\s*netmask\s*([0-9\.]+).*/", $line, $matches) === 1)
				&& ($addr = ip2long($matches[1])) !== false && ($mask = ip2long($matches[2])) !== false) {
				$addr &= $mask;
				$ip = self::normalizeHostip(long2ip($addr));
				$scope = self::long2scope($mask);
				$network = $ip."/".$scope;
				self::$sIpv4Networks[] = array($addr, $scope, $network);
				Log::debug(" {$network}");
			} elseif(preg_match("/\s*inet6\s*([0-9a-fA-F:]+)\s*prefixlen\s*([0-9a-fA-F:]+)\s*scopeid.*/", $line, $matches) === 1
				&& ($addr = @inet_pton($matches[1])) !== false && ($scope = intval($matches[2])) !== false) {
				$byteOffset = floor($scope/8);
				$tmpAddr = substr($addr, 0, $byteOffset);
				$mergeBits = $scope % 8;
				if($mergeBits != 0) {
					$mergeMask = (0xff << (8 - $mergeBits)) & 0xff;
					$tmpAddr .= substr($addr, $byteOffset, 1) & $mergeMask;
				}
				$addr = str_pad($tmpAddr, 16, chr(0));
				$ip = self::normalizeHostip(inet_ntop($addr));
				$network = $ip."/".$scope;
				self::$sIpv6Networks[] = array($addr, $scope, $network);
				Log::debug(" {$network}");
			}
		}
	}

	private static function long2scope($mask) {
		$scope = 0;
		$bit = 0x80000000;
		while(($mask & $bit) == $bit) {
			$scope++;
			$bit >>= 1;
		}
		return $scope;
	}

	private static function safeGethostbyaddr($hostip) {
		$host = ($hostip != "" ? gethostbyaddr($hostip) : $hostip);
		return ($host !== false ? $host : $hostip);
	}

	private static function safeGeoiprecordbyname($hostip) {
		$record = false;
		if(function_exists("geoip_record_by_name")) {
			$record = @geoip_record_by_name($hostip);
		}
		if($record === false) {
			$record = array(
				"continent_code" => "",
				"country_code" => "",
				"country_code3" => "",
				"country_name" => "",
				"region" => "",
				"city" => "",
				"postal_code" => "",
				"latitude" => 0.0,
				"longitude" => 0.0,
				"dma_code" => "",
				"area_code" => ""
			);
		}
		return $record;
	}

}

?>
