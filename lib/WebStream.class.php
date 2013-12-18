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

abstract class WebStream extends WebAccess {

	const CONTENT_TYPE_TEXT_PLAIN = "text/plan";

	protected function __construct($dbh) {
		parent::__construct($dbh);
	}

	public function send() {
		$this->sendData();
	}

	abstract public function sendData();

	protected function sendContentType($type) {
		header("Content-type: {$type}");
	}

	protected function sendContentDisposition($file) {
		header("Content-Disposition: attachment; filename=\"{$file}\"");
	}

}

?>
