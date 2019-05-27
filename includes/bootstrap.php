<?php
/**
 * Created by PhpStorm.
 * User: Exlord
 * Date: 5/15/2019
 * Time: 4:42 PM
 */

use Dominobot\Message;
use Dominobot\Response;
use Zend\Log\Logger;
use Zend\Log\Writer;

error_reporting(E_ALL);
ini_set('display_errors', 0);
//ini_set('max_execution_time',300);
//ini_set('memory_limit',2048);
//ini_set('upload_max_filesize','10M');
//ini_set('post_max_size','10M');

define('ROOT', dirname(__DIR__));
define('DEV_ENV', getenv('APPLICATION_ENV') == 'development');

require_once ROOT . '/vendor/autoload.php';

$logger = new Logger();
$writer = new Writer\Stream(ROOT . '/error.log');
$logger->addWriter($writer);

// Log PHP errors
Logger::registerErrorHandler($logger);
// Log exceptions
Logger::registerExceptionHandler($logger);

require_once 'functions.php';
require_once 'util.php';
require_once 'dominobot.class.php';
require_once 'gravityform.class.php';


