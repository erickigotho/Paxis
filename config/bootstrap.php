<?php
if(!isset($_SESSION)){
	session_start();
}

if (!defined('DS')) {
	define('DS', DIRECTORY_SEPARATOR);
}

if (!defined('ROOT')) {
	define('ROOTDIR', dirname(dirname(__FILE__)));
}

require_once(ROOTDIR . '/helpers/Template.class.php');
require_once(ROOTDIR . '/modules/Authenticate.class.php');
?>