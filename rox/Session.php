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
 * Session middleware.
 *
 * @package Rox
 */
class Session {
	public function __construct() {
		$this->time = time();
		$this->cookieLifeTime = 7 * 86400; // a week
	}

	/**
	 * Handle request.
	 *
	 * @return void
	 */
	public function processRequest($args) {
		$this->_initSession();
		session_start();
	}

	/**
	 * Initialize session.
	 *
	 * @return void
	 */
	protected function _initSession() {
		// tell clients to only send cookies over secure connections
		if (!empty($_SERVER['HTTPS'])) {
			ini_set('session.cookie_secure', 1);
		}

		switch (Rox_Config::read('Session.save')) {
			case 'php':
			default:
				ini_set('session.use_trans_sid', 0);
				ini_set('session.name', Rox_Config::read('Session.cookie', 'ROXPHP'));
				ini_set('session.cookie_lifetime', $this->cookieLifeTime);
			break;
		}
	}
}
