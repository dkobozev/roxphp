<?php
/**
 * RoxPHP
 *
 * Copyright (C) 2008 - 2010 Ramon Torres
 *
 * This Software is released under the MIT License.
 * See license.txt for more details.
 *
 * @package App
 * @author Ramon Torres
 * @copyright Copyright (C) 2008 - 2010 Ramon Torres
 * @license http://roxphp.com/static/license.html
 * @version $Id$
 */

class Rox_Console_Command {

	public $stdout;
	public $stderr;

	public function __construct() {
		$this->stdout = fopen('php://stdout', 'w');
		$this->stderr = fopen('php://stderr', 'w');
	}

	public function header() {
		$this->hr();
		$this->out(' RoxPHP Console');
		$this->hr();
	}

	public function run($argc, $argv) {
		$this->hr();
		print_r($argv);
	}

	public function hr() {
		return $this->out(str_repeat('-', 50));
	}

	public function out($data) {
		return fwrite($this->stdout, $data . "\n");
	}

	public function error($data) {
		return fwrite($this->stderr, $data . "\n");
	}
}