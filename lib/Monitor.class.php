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

class Monitor {

	private $_name;
	private $_source;
	private $_buflen;
	private $_tspattern;
	private $_tsformat;
	private $_filters = array();

	public function __construct($name, $source, $buflen, $tspattern, $tsformat) {
		$this->_name = $name;
		$this->_source = $source;
		$this->_buflen = $buflen;
		$this->_tspattern = $tspattern;
		$this->_tsformat = $tsformat;
	}

	public function __toString() {
		return "Monitor:{$this->_name}:'{$this->_source}'";
	}

	public function addFilter($filter) {
		$this->_filters[] = $filter;
	}

	public function process($dbh) {
		$files = $this->collectSourceFiles();
		$dbh->beginTransaction();
		$fileStates = DBHSourceState::queryAll($dbh, $this->_name);
		$fileStatesLast = 0;
		foreach($fileStates as $fileState) {
			$fileStateLast = $fileState->last();
			if($fileStateLast > $fileStatesLast) {
				$fileStatesLast = $fileStateLast;
			}
		}
		foreach($files as $file) {
			$mtime = filemtime($file);
			if($mtime === false) {
				throw new Exception("Cannot stat file '{$file}'.");
			}
			if(array_key_exists($file, $fileStates)) {
				$fileState = $fileStates[$file];
			} else {
				$fileState = DBHSourceState::addNew($fileStates, $dbh, $this->_name, $file);
			}
			if($fileState->touch($mtime)) {
				Log::notice("Processing modified source file '{$file}'...");
				$lineCount = 0;
				$eventCount = 0;
				$reader = LineReader::open($file, $this->_buflen);
				$lastTS = 0;
				$lineAndTS = $this->readLineAndTS($reader);
				while($lineAndTS !== false) {
					$lastTS = $lineAndTS["ts"];
					if($lastTS > $fileStatesLast) {
						$line = $lineAndTS["line"];
						$lineCount++;
						$eventCount += $this->processLine($dbh, $lastTS, $line);
					}
					$lineAndTS = $this->readLineAndTS($reader);
				}
				$reader->close();
				$fileState->update($lastTS);
				Log::notice("${lineCount} line(s) processed.");
				Log::notice("${eventCount} event(s) recorded.");
			}
		}
		foreach($fileStates as $fileState) {
			$fileState->deleteIfUntouched();
		}
		$dbh->commit();
	}

	private function collectSourceFiles() {
		$files = array();
		Log::debug("Collecting source files for '{$this->_source}'...");
		$pathinfo = pathinfo($this->_source);
		$dirname = $pathinfo['dirname'];
		$basename = $pathinfo['basename'];
		$dir = opendir($dirname);
		if($dir !== false) {
			while(($dirFile = readdir($dir)) !== false) {
				if(fnmatch($basename, $dirFile)) {
					$file = "{$dirname}/{$dirFile}";
					Log::debug("Considering source file '{$file}'...");
					$files[] = $file;
				}
			}
		} else {
			Log::warning("Cannot open source file directory '{$dirname}'.");
		}
		return $files;
	}

	private function readLineAndTS($reader) {
		$lineAndTS = false;
		$line0 = $reader->nextLine();
		while($line0 !== false && preg_match($this->_tspattern, $line0, $match) !== 1) {
			Log::warning("Skipping unexpected line '{$line0}'.");
			$line0 = $reader->nextLine();
		}
		if($line0 !== false) {
			$tsObject = DateTime::createFromFormat($this->_tsformat, $match[1]);
			if($tsObject === false) {
				throw new Exception("Cannot parse timestamp '{$match[1]}' with format '{$this->_tsformat}'.");
			}
			$ts = $tsObject->getTimestamp();
			$line = $line0;
			$line1 = $reader->peekLine();
			while($line1 !== false && preg_match($this->_tspattern, $line1, $match) !== 1) {
				$line .= $line1;
				$reader->skipLine();
				$line1 = $reader->peekLine();
			}
			$lineAndTS = array("line" => $line, "ts" => $ts);
		}
		return $lineAndTS;
	}

	private function processLine($dbh, $ts, $line) {
		$eventCount = 0;
		foreach($this->_filters as $filter) {
			if($filter->process($dbh, $ts, $line)) {
				$eventCount++;
				break;
			}
		}
		return $eventCount;
	}

}

?>
