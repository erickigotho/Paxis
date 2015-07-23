<?php 
if(!isset($_SESSION)){
	session_start();
}

define('__ROOT__', dirname(dirname(__FILE__)));
define('__BASENAME__', basename(__ROOT__));

require_once(__ROOT__ . '/modules/Authenticate.class.php');

$email = !isset($_POST['email']) ? "": $_POST['email'];
$password = !isset($_POST['password']) ? "": $_POST['password'];

$authObj = new Authenticate();
$authObj->login($email, $password);
?>