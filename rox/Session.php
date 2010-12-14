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

		switch (Rox_Config::read('Session.save')) {
			case 'php':
			default:
				ini_set('session.use_trans_sid', 0);
				ini_set('session.name', Rox_Config::read('Session.cookie', 'ROXAPP'));
				ini_set('session.cookie_lifetime', $this->cookieLifeTime);

				$this->backend = new PHPSessionBackend();
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
	 * Call underlying backend's read function.
	 *
	 * @return mixed
	 */
	public function read($key, $default=null) {
		return $this->backend->read($key, $default);
	}

	/**
	 * Call underlying backend's write function.
	 *
	 * @return void
	 */
	public function write($key, $value) {
		$this->backend->write($key, $value);
	}
}

/**
 * Backend for PHP session storage mechanism.
 *
 * @package Rox
 */
class PHPSessionBackend {
	public function read($key, $default=null) {
		return isset($_SESSION[$key]) ? $_SESSION[$key] : $default;
	}

	public function write($key, $value) {
		$_SESSION[$key] = $value;
	}
}

/**
 * Backend for database session storage mechanism.
 *
 * @package Rox
 */
class DBSessionBackend {
	public function __construct() {
	}

	public function read() {
	}

	public function write() {
	}
}
