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

class MonitorEventEvaluator {

	const DEFAULT_DECODER = "match";

	private $tEvaluator;
	private $tDecoder;

	public function __construct($evaluator, $decoder) {
		$this->tEvaluator = $evaluator;
		$this->tDecoder = $decoder;
	}

	public function __toString() {
		return "{$this->tDecoder}:{$this->tEvaluator}";
	}

	public function getEvaluaotr() {
		return $this->tEvaluator;
	}

	public function getDecoder() {
		return $this->tDecoder;
	}

}

?>
