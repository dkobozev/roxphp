<?php
/**
 * RoxPHP
 *
 * Copyright (C) 2008 - 2010 Ramon Torres
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Copyright (c) 2008 - 2010 Ramon Torres
 * @package Rox
 * @license The MIT License (http://www.opensource.org/licenses/mit-license.php)
 */

/**
 * Exception thrown on missing configuration variable
 *
 * @package Rox
 */
class Rox_KeyException extends Rox_Exception {}

/**
 * Singleton config class
 *
 * @package Rox
 */
class Rox_Config {

	protected $_values = array();

	/**
	 * Return a single instance of the config class
	 *
	 * @return object
	 */
	public static function &getInstance() {
		static $instance = null;
		if (!$instance) {
			$instance = new Rox_Config();
		}
		return $instance;
	}

	/**
	 * Write a configuration variable
	 *
	 * @return void
	 */
	public static function write($key, $value) {
		$conf =& self::getInstance();
		$conf->_values[$key] = $value;
	}

	/**
	 * Read and returns a configuration variable
	 *
	 * @return mixed
	 */
	public static function read($key, $default=null) {
		$conf =& self::getInstance();
		return isset($conf->_values[$key]) ? $conf->_values[$key] : $default;
	}

	/**
	 * Delete a configuration variable
	 *
	 * @return void
	 */
	public static function delete($key) {
		$conf =& self::getInstance();
		if (!isset($this->_values[$key])) {
			throw new Rox_KeyException();
		}
		unset($conf->_values[$key]);
	}
}
