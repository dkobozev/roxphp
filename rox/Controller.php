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
 * Controller
 *
 * @package Rox
 */
class Rox_Controller {

	/**
	 * Page title
	 *
	 * @var string
	 */
	public $pageTitle = 'RoxPHP';

	/**
	 * Layout name
	 *
	 * @var string
	 */
	public $layout = 'default';

	/**
	 * List of enabled middleware
	 *
	 * @var array
	 */
	public $middleware = array();

	/**
	 * List of helpers to be automatically loaded when rendering
	 *
	 * @var array
	 */
	public $helpers = array();

	/**
	 * Request object
	 *
	 * @var Rox_Request
	 */
	public $request;

	/**
	 * Response object
	 *
	 * @var Rox_Response
	 */
	public $response;

	/**
	 * Request params
	 *
	 * @var array
	 */
	public $params;

	/**
	 * View variables
	 *
	 * @var array  
	 */
	protected $_viewVars = array();

	/**
	 * Constructor
	 *
	 * @param array $config
	 * @return void
	 */
	public function __construct($config = array()) {
		if (isset($config['request'])) {
			$this->request = $config['request'];
		}

		$vars = get_class_vars('ApplicationController');
		$this->helpers = array_merge($vars['helpers'], $this->helpers);
	}

	/**
	 * Renders the current action
	 */
	public function render() {
		$this->set('rox_page_title', $this->pageTitle);

		foreach ($this->helpers as $helper) {
			$helperName = Rox_Inflector::lowerCamelize($helper);
			$this->set($helperName, Rox::getHelper($helper));
		}

		$viewPath = $this->params['controller'];
		if (!empty($this->params['namespace'])) {
			$simpleControllerName = substr($this->params['controller'], strlen($this->params['namespace']) + 1);
			$viewPath = $this->params['namespace'] . '/' . $simpleControllerName;
		}

		$viewName = $this->params['action'];

		$view = new Rox_View($this->_viewVars);
		$view->params = $this->params;

		echo $view->render($viewPath, $viewName, $this->layout);
	}

	/**
	 * Sets a view variable
	 *
	 * @param string|array $varName
	 * @param mixed $value
	 */
	public function set($varName, $value = null) {
		if (is_array($varName)) {
			$this->_viewVars += $varName;
			return;
		}

		$this->_viewVars[$varName] = $value;
	}

	/**
	 * undocumented function
	 *
	 * @param string $type
	 * @param string $message 
	 */
	public function flash($type, $message) {
		if (!isset($_SESSION['flash'])) {
			$_SESSION['flash'] = array();
		}
		$_SESSION['flash'][$type] = $message;
	}

	/**
	 * Sends redirect headers and exit
	 *
	 * @param string $url
	 */
	protected function redirect($url) {
		header('HTTP/1.1 301');
		header('Location: ' . Rox_Router::url($url));
		exit;
	}

	/**
	 * Redirects to referer
	 *
	 * @param string $default
	 */
	protected function redirectToReferer($default = '/') {
		if (isset($_SERVER['HTTP_REFERER']) && !empty($_SERVER['HTTP_REFERER'])) {
			$referer = $_SERVER['HTTP_REFERER'];
		} else {
			$referer = Rox_Router::url($default);
		}

		header('HTTP/1.1 301');
		header('Location: ' . $referer);
		exit;
	}

	function _invokeMiddleware($method, $args) {
		foreach ($this->middleware as $m) {
			if (method_exists($m, $method)) {
				$m->$method($args);
			}
		}
	}

	// ------------------------------------------------
	//  Callbacks
	// ------------------------------------------------

	/**
	 * Before-filter callback
	 *
	 * @return void
	 */
	public function beforeFilter() {
		$this->_invokeMiddleware('processRequest', array(
			'request' => &$this->request,
		));
	}

	/**
	 * After-filter callback
	 *
	 * @return void
	 */
	public function afterFilter() {
		$this->_invokeMiddleware('processResponse', array(
			'request' => &$this->request,
			'response' => &$this->response,
		));
	}
}
