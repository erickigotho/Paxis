<?php
if(!isset($_SESSION)){
	session_start();
}

define('__ROOT__', dirname(dirname(__FILE__)));
define('__BASENAME__', basename(__ROOT__));

require_once(__ROOT__ . '/modules/Room.class.php');
require_once(__ROOT__ . '/modules/Dynamo.class.php');

$id = !isset($_POST['id']) ? "": $_POST['id'];
$roomObj = new Room();
$roomObj->getRoomTemplateItems($id,true);
?>