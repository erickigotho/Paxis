<?php
// require_once(dirname(dirname(dirname(__FILE__))) . '/modules/User.class.php');
require_once((dirname(__FILE__)) . '/modules/User.class.php');

$code = isset($_GET['code'])?$_GET['code']:'';

$userObj = new User();
if($userObj->confirmUser($code)) {
	header("Location: registration_successful.html");
	die();
} else {
	header("Location: registration_failed.html");
	die();
}
?>