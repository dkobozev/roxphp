<?php
/**
 * RoxPHP  
 *  
 * This Software is released under the MIT License.
 * See license.txt for more details.
 *
 * @package	rox
 * @author Ramon Torres
 * @copyright Copyright (c) 2008 Ramon Torres
 * @license http://roxphp.com/static/license.html
 * @link http://roxphp.com 
 * @access public
 */

define('DS', DIRECTORY_SEPARATOR);

// define paths
define('WEBROOT', dirname(__FILE__) . DS);
define('APP', dirname(WEBROOT) . DS);
 
define('ROX', dirname(APP).DS.'rox' . DS);

define('CONFIG', APP . 'config' . DS);
define('MODELS', APP . 'models' . DS);
define('VIEWS', APP . 'views' . DS);
define('LAYOUTS', VIEWS . 'layouts' . DS);
define('CONTROLLERS', APP . 'controllers' . DS);

define('WWW', dirname(dirname(dirname($_SERVER['PHP_SELF']))));

// include configuration files
include(CONFIG . 'main.php');
include(CONFIG . 'database.php');

// set error reporting level
error_reporting(ROX_DEBUG ? E_ALL : 0);

// include core files
include(ROX . 'exception.php');
include(ROX . 'object.php');
include(ROX . 'rox.php');
include(ROX . 'validator.php');
include(ROX . 'registry.php');
include(ROX . 'dispatcher.php');
include(ROX . 'datasource.php');
include(ROX . 'router.php');
include(ROX . 'model.php');			// M
include(ROX . 'view.php');			// V
include(ROX . 'controller.php');	// C 

$DataSource = new DataSource;
$DataSource->connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
Registry::addObject('DataSource', $DataSource);

$Dispatcher = new Dispatcher;
$Dispatcher->dispatch(isset($_GET['route']) ? $_GET['route'] : ROX_DEFAULT_ROUTE);