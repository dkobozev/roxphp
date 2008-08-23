<?php
/**
 * RoxPHP
 *
 * Copyright (C) 2008 Ramon Torres
 *
 * This Software is released under the MIT License.
 * See license.txt for more details.
 *
 * @package Rox
 * @author Ramon Torres
 * @copyright Copyright (c) 2008 Ramon Torres (http://roxphp.com)
 * @license http://roxphp.com/static/license.html
 * @version $Id$
 */

define('DS', DIRECTORY_SEPARATOR);

// define paths
define('WEBROOT', dirname(__FILE__) . DS);
define('APP', dirname(WEBROOT) . DS);
define('ROX', dirname(APP) . DS . 'rox' . DS);

define('CONFIG', APP . 'config' . DS);
define('MODELS', APP . 'models' . DS);
define('VIEWS', APP . 'views' . DS);
define('LAYOUTS', VIEWS . 'layouts' . DS);
define('CONTROLLERS', APP . 'controllers' . DS);
define('HELPERS', APP . 'helpers' . DS);

define('WWW', dirname(dirname(dirname($_SERVER['PHP_SELF']))));

// include configuration files
require CONFIG . 'main.php';
require CONFIG . 'database.php';

// set error reporting level
error_reporting(ROX_DEBUG ? E_ALL | E_STRICT : 0);

// include core files
require ROX . 'Exception' . DS . 'Handler.php';
set_exception_handler(array('Exception_Handler', 'handle'));

require ROX . 'Object.php';
require ROX . 'Rox.php';
require ROX . 'Validator.php';
require ROX . 'Registry.php';
require ROX . 'Dispatcher.php';
require ROX . 'DataSource.php';
require ROX . 'Router.php';
require ROX . 'Model.php';      // M
require ROX . 'View.php';       // V
require ROX . 'Controller.php'; // C
require ROX . 'Constants.php';

require APP . 'base' . DS . 'app_model.php';
require APP . 'base' . DS . 'app_controller.php';
require CONFIG . 'init.php';

$DataSource = DataSource::getInstance();
$DataSource->connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

$Dispatcher = new Dispatcher;
$Dispatcher->dispatch(isset($_GET['route']) ? $_GET['route'] : ROX_DEFAULT_ROUTE);