<?php


// Define path to application directory
defined('APPLICATION_PATH')
    || define('APPLICATION_PATH', realpath(dirname(__FILE__) . '/application'));

// Define application environment
defined('APPLICATION_ENV')
    || define('APPLICATION_ENV', (getenv('APPLICATION_ENV') ? getenv('APPLICATION_ENV') : 'test'));

/*
// Ensure library/ is on include_path
require_once APPLICATION_PATH . '/../includer.php';
*/

// Add 'ban' to include path
set_include_path(implode(PATH_SEPARATOR, array(
    realpath(APPLICATION_PATH . '/../../../../lib/zend-framework/library'),
    realpath(__DIR__ . '/../src'),
    realpath(APPLICATION_PATH . '/../../../library'),
	get_include_path(),
)));

// The generic test case
require_once __dir__ . '/GenericTestCase.php';

require_once 'Zend/Application.php';
require_once 'Zend/Loader/Autoloader.php';

Zend_Loader_Autoloader::getInstance()->registerNamespace('Ban_');

// Create application, bootstrap, and run
$application = new Zend_Application(
    APPLICATION_ENV,
    APPLICATION_PATH . '/configs/application.ini'
);
$application->bootstrap();


Zend_Locale::disableCache(true);

