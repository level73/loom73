<?php
// Set Filesystem paths as constants
const APP_DIR = __DIR__;
define( 'ROOT_DIR', dirname(APP_DIR, 2));

// Load Config files
require_once ROOT_DIR . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';
require_once ROOT_DIR . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'system.php';
require_once ROOT_DIR . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'labels.php';

$dotenv = Dotenv\Dotenv::createImmutable(ROOT_DIR . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR);
$dotenv->load();

session_name($_SERVER['APPNAME']);
// Initialize Session
session_start();
// Set Timezone
date_default_timezone_set($_SERVER['TMZ']);

// Set header and $url var for mvc init
header('Content-Type: text/html; charset=utf-8');
$url = (isset($_GET['url']) ? $_GET['url'] : "");

// Load base classes
require_once ROOT_DIR . DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR . 'Woodframe' . DIRECTORY_SEPARATOR . 'Errata.php';
// Load base functions lib
require_once ROOT_DIR . DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR . 'functions.php';
// Load Helpers
require_once ROOT_DIR . DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR . 'helpers.php';
// Load Main Init function
require_once ROOT_DIR . DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR . 'loom73.php';

// Launch Application
Loom73();
