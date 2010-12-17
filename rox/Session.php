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
				// update current session id and delete the old session
				session_regenerate_id(true);
				throw new InvalidSessionException();
			}
		} else {
			$this->_writeConfig();
		}

		// spear the session object onto the request
		$args['request']->session = $this;
	}

	/**
	 * Initialize session.
	 *
	 * @return void
	 */
	protected function _init() {
		// if using HTTPS, tell clients to send cookies over the secure connection only
		if (!empty($_SERVER['HTTPS'])) {
			ini_set('session.cookie_secure', 1);
		}

		// session.use_trans_sid        - transparently handle sid passing using URLs if the client rejects cookies. Disabled to prevent session fixation.
		// session.auto_start           - start sessions automatically (no need to call session_start()). Disabled to be able to configure sessions.
		// session.use_cookies          - use cookies for passing sids.
		// session.use_only_cookies     - don't use PHPSESSID from URLs. Enabled to prevent session fixation.
		// url_rewriter.tags            - specifies which HTML tags are rewritten to include session id if transparent sid support is enabled.
		// session.cookie_lifetime      - set to 24 hours by default.
		// session.name                 - name of the session cookie.
		ini_set('session.use_trans_sid', 0);
		ini_set('session.auto_start', 0);
		ini_set('session.use_cookies', 1);
		ini_set('session.use_only_cookies', 1);
		ini_set('url_rewriter.tags', '');
		ini_set('session.cookie_lifetime', Rox_Config::read('Session.cookie_lifetime', 86400));
		ini_set('session.name', Rox_Config::read('Session.cookie', 'ROXAPP'));

		// it's possible to support multiple session backends, but only the db
		// backend has been implemented so far.
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
		if (Rox_Config::read('Session.check_user_agent') && md5($_SERVER['HTTP_USER_AGENT']) !== $config['userAgent']) {
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
	public function delete($key, $warn=false) {
		if ($warn && !isset($_SESSION[$key])) {
			throw new Rox_Exception("Key ${key} is not in session.");
		} elseif (isset($_SESSION[$key])) {
			unset($_SESSION[$key]);
		}
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

	/**
	 * Callback executed when the session is being opened.
	 *
	 * @return bool
	 */
	public static function open($save_path, $session_name) {
		return true;
	}

	/**
	 * Callback executed when the session operation is done.
	 *
	 * @return bool
	 */
	public static function close() {
		return true;
	}

	/**
	 * Callback executed when the session garbage collector is executed.
	 *
	 * @return bool
	 */
	public static function gc($max_lifetime=null) {
		self::model()->deleteAll(array(
			'timestamp + ' . Rox_Config::read('Session.lifetime', 84600) . ' < ' . time(),
		));
		return true;
	}

	/**
	 * Callback executed when the session is read.
	 *
	 * @return string
	 */
	public static function read($sid) {
		// Since PHP 5.0.5 write and close session handlers are called after
		// destructing objects. We need to make PHP call session_write_close()
		// before object destructors, otherwise our handlers won't work.
		register_shutdown_function('session_write_close');

		$session = self::model()->findFirst(array(
			'conditions' => array('sid' => $sid),
		));
		return $session ? $session->session : '';
	}

	/**
	 * Callback executed when the session data is to be saved.
	 *
	 * @return bool
	 */
	public static function write($sid, $data) {
		// Do not write to the sessions table if the client does not have
		// a cookie and new session isn't being created. This should
		// keep crawlers out of the table.
		if (empty($_COOKIE[session_name()]) && empty($data)) {
			return true;
		}
		$session = self::model()->findFirst(array(
			'conditions' => array('sid' => $sid),
		));
		if (!$session) {
			$session = new DBSessionBackend(array('sid' => $sid));
		}
		$session->setData(array(
			'session' => $data,
			'hostname' => $_SERVER['REMOTE_ADDR'],
			'timestamp' => time(),
		));
		$session->save();
		return true;
	}

	/**
	 * Callback executed when the session destroyed using session_destroy().
	 *
	 * @return bool
	 */
	public static function destroy($sid) {
		self::model()->deleteAll(array('sid' => $sid));
		return true;
	}
}
