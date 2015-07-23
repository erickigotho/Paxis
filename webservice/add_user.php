<?php
if(!isset($_SESSION)){
	session_start();
}

define('__ROOT__', dirname(dirname(__FILE__)));
define('__BASENAME__', basename(__ROOT__));

require_once(__ROOT__ . '/modules/User.class.php');

$email = !isset($_POST['email']) ? "": $_POST['email'];
$password = !isset($_POST['password']) ? "": $_POST['password'];
$firstName = !isset($_POST['firstName']) ? "": $_POST['firstName'];
$lastName = !isset($_POST['lastName']) ? "": $_POST['lastName'];
$companyId = !isset($_POST['companyId']) ? "": $_POST['companyId'];
$userType = !isset($_POST['userType']) ? "": $_POST['userType'];
$isUserActive = !isset($_POST['isActive']) ? 0: $_POST['isActive'];
$phone = !isset($_POST['phone']) ? '': $_POST['phone'];
$baseUrl = !isset($_POST['baseUrl']) ? '': $_POST['baseUrl'];

$userObj = new User();
$userObj->addUser($email, $password, $firstName, $lastName, $companyId, $userType, $isUserActive, $phone, $baseUrl);
?>