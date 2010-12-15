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
 * Thrown when session hijacking attempts are detected.
 *
 * @package Rox
 */
class InvalidSessionException extends Exception {}

/**
 * Session middleware.
 *
 * @package Rox
 */
class Session {
	/**
	 * Handle request.
	 *
	 * @return void
	 */
	public function processRequest($args) {
		$this->_init();
		session_start();

		// check session
		if ($this->_hasConfig()) {
			if (!$this->isValid()) {
				// update current session id and delete the old session file
				session_regenerate_id(true);
				throw new InvalidSessionException();
			}
		} else {
			$this->_writeConfig();
		}

		// staple the session object onto the request
		$args['request']->session = $this;
	}

	/**
	 * Initialize session.
	 *
	 * @return void
	 */
	protected function _init() {
		$this->cookieLifeTime = 7 * 86400; // a week

		// tell clients to only send cookies over secure connections
		if (!empty($_SERVER['HTTPS'])) {
			ini_set('session.cookie_secure', 1);
		}

		ini_set('session.use_trans_sid', 0);
		ini_set('session.auto_start', 0);
		ini_set('session.use_only_cookies', 1);
		ini_set('session.cookie_lifetime', $this->cookieLifeTime);
		ini_set('session.name', Rox_Config::read('Session.cookie', 'ROXAPP'));

		switch (Rox_Config::read('Session.save')) {
			case 'db':
				ini_set('session.save_handler', 'user');
				ini_set('session.serialize_handler', 'php');
				session_set_save_handler(
					array('DBSessionBackend', 'open'),
					array('DBSessionBackend', 'close'),
					array('DBSessionBackend', 'read'),
					array('DBSessionBackend', 'write'),
					array('DBSessionBackend', 'destroy'),
					array('DBSessionBackend', 'gc'));
			break;
		}
	}

	/**
	 * Check if the session config has already been written.
	 *
	 * @return bool
	 */
	protected function _hasConfig() {
		$config = $this->read('Config');
		return !empty($config);
	}

	/**
	 * Write session config.
	 *
	 * @return void
	 */
	protected function _writeConfig() {
		$this->write('Config', array(
			'userAgent' => md5($_SERVER['HTTP_USER_AGENT']),
		));
	}

	/**
	 * Detect hijacking attempts.
	 *
	 * @return bool
	 */
	public function isValid() {
		$config = $this->read('Config');
		if (md5($_SERVER['HTTP_USER_AGENT']) !== $config['userAgent']) {
			return false;
		}
		return true;
	}

	/**
	 * Read a value from session.
	 *
	 * @return mixed
	 */
	public function read($key, $default=null) {
		return isset($_SESSION[$key]) ? $_SESSION[$key] : $default;
	}

	/**
	 * Write a value to session.
	 *
	 * @return void
	 */
	public function write($key, $value) {
		$_SESSION[$key] = $value;
	}

	/**
	 * Delete a value from session.
	 *
	 * @return void
	 */
	public function delete($key) {
		unset($_SESSION[$key]);
	}
}

/**
 * Backend for database session storage mechanism.
 *
 * @package Rox
 */
class DBSessionBackend extends Rox_ActiveRecord {
	protected $_table = 'sessions';

	public static function model($class = __CLASS__) {
		return parent::model($class);
	}

	public static function open() {
		return true;
	}

	public static function close() {
		return true;
	}

	public static function gc($max_lifetime=null) {
		self::model()->deleteAll(array('expire < ' . time()));
		return true;
	}

	public static function read($sid) {
		// Write and Close handlers are called after destructing objects since PHP 5.0.5
		// Thus destructors can use sessions but session handler can't use objects.
		// So we are moving session closure before destructing objects. (Thanks, Drupal!)
		register_shutdown_function('session_write_close');

		$session = self::model()->findFirst(array(
			'conditions' => array('sid' => $sid),
		));
		return $session ? $session->data : '';
	}

	public static function write($sid, $data) {
		$session = self::model()->findFirst(array(
			'conditions' => array('sid' => $sid),
		));
		if (!$session) {
			$session = new DBSessionBackend(array('sid' => $sid));
		}
		$session->setData(array(
			'data' => $data,
			'expires' => time() + Rox_Config::read('Session.timeout', 86400),
		));
		return $session->save();
	}

	public static function destroy($sid) {
		self::model()->deleteAll(array('sid' => $sid));
		return true;
	}
}
