<?php
/**
 * phplogmon
 *
 * Copyright (c) 2012-2014 Holger de Carne and contributors, All Rights Reserved.
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

class Processor {

	private $tDbh;

	public function __construct($dbh) {
		$this->tDbh = $dbh;
	}

	public function process($monitor, $source) {
		Log::notice("Evaluating source {$source}...");
		$processedLineCount = 0;
		$recordedEventCount = 0;
		$this->tDbh->beginTransaction();
		$sourcestates = ProcessorSourcestate::query($this->tDbh, $source);
		foreach($source->getFiles() as $file) {
			$logfiles = self::scanLogFiles($file->getFile());
			if(count($logfiles) > 0) {
				$matchstates = ProcessorEventMatchstate::create($this->tDbh, $monitor, $source, $file);
				foreach($logfiles as $logfile) {
					if(isset($sourcestates[$logfile])) {
						$sourcestate = $sourcestates[$logfile];
					} else {
						$sourcestate = ProcessorSourcestate::add($sourcestates, $this->tDbh, $source, $logfile);
					}
					if($sourcestate->touch()) {
						Log::info("Processing modified file '{$logfile}'");
						$decoder = $file->getDecoder($logfile);
						while(($line = $this->fetchLine($decoder, $source)) !== false) {
							$lineTimestamp = $this->parseLineTimestamp($line, $source);
							if($lineTimestamp !== false && $sourcestate->updateLast($lineTimestamp)) {
								$recordedEventCount += ProcessorEventMatchstate::matchAndUpdateAll($matchstates, $lineTimestamp, $line);
								$processedLineCount++;
							}
						}
					} else {
						Log::info("Ignoring unmodified file '{$logfile}'");
					}
				}
			} else {
				Log::warning("No log files found for source {$source}");
			}
		}
		ProcessorSourcestate::updateAll($sourcestates);
		$this->tDbh->commit();
		Log::notice(sprintf("%u line(s) processed %u event(s) recorded",$processedLineCount, $recordedEventCount));
	}

	public function discard($days) {
		$discardedEventCount = ProcessorEventMatchstate::discardOld($this->tDbh, $days);
		Log::notice(sprintf("%u old event(s) discarded", $discardedEventCount));
		QueryLoghost::discardUnused($this->tDbh);
		QueryUser::discardUnused($this->tDbh);
		QueryHostipNetwork::discardUnused($this->tDbh);
		QueryHostip::discardUnused($this->tDbh);
		QueryHostmac::discardUnused($this->tDbh);
		QueryService::discardUnused($this->tDbh);
	}

	private function scanLogFiles($file) {
		$logfiles = array();
		$pathinfo = pathinfo($file);
		$path = $pathinfo["dirname"];
		$pattern = $pathinfo["basename"];
		$dir = Files::safeOpendir($path);
		while(($logfile = Files::readdirMatch($dir, $pattern)) !== false) {
			$logfiles[] = Files::path($path, $logfile);
		}
		closedir($dir);
		asort($logfiles);
		return $logfiles;
	}

	private function fetchLine($decoder, $source) {
		$line = $decoder->peekLine();
		if($line !== false) {
			$decoder->skipLine();
			$tspattern = $source->getTspattern();
			while(($nextLine = $decoder->peekLine()) !== false && preg_match($tspattern, $nextLine) === 0) {
				$line .= $nextLine;
				$decoder->skipLine();
			}
		}
		return $line;
	}

	private function parseLineTimestamp($line, $source) {
		$timestamp = false;
		if(preg_match($source->getTspattern(), $line, $matches) === 1 && count($matches) == 2) {
			$dateTime = DateTime::createFromFormat($source->getTsformat(), $matches[1]);
			if($dateTime !== false) {
				$timestamp = $dateTime->getTimestamp();
			} else {
				Log::warning("Cannot parse timestamp format for line '{$line}'");
			}
		} else {
			Log::warning("Cannot parse timestamp pattern for line '{$line}'");
		}
		return $timestamp;
	}

}

?>
