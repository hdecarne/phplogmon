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

class ViewError extends View {

	private $tMessage;

	public function __construct($e) {
		$this->tMessage = $e->getMessage();
		parent::__construct(null);
	}

	public function renderTitle() {
		print htmlentities("Error");
	}

	public function renderBody() {
		$this->renderer()->beginErrorMessage();
		print "<h1>";
		print htmlentities("An error occured while processing the request");
		print "</h1>\n";
		print htmlentities("Request processing failed with message: \"");
		print htmlentities($this->tMessage);
		print "\"<br/>\n";
		print "<a href=\".\">";
		print htmlentities("Return to start page");
		print "</a>\n";
		$this->renderer()->endMessage();
	}

}

?>
