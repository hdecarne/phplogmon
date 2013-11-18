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

abstract class View {

	private $tDbh;
	private $tL12n;
	private $tRenderer;

	public static function match($dbh) {
		$view = new ViewStart($dbh);
		return $view;
	}

	protected function __construct($dbh) {
		$this->tDbh = $dbh;
		$this->tL12n = L12n::match();
		$this->tRenderer = Renderer::match();
	}

	protected function dbh() {
		return $this->tDbh;
	}

	protected function l12n() {
		return $this->tLang->l12n();
	}

	protected function renderer() {
		return $this->tRenderer;
	}

	public function lang() {
		return $this->tL12n->lang();
	}

	public function stylesheet() {
		return $this->tRenderer->stylesheet();
	}

	abstract public function renderTitle();

	abstract public function renderBody();

}

?>
