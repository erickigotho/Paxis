<?php
if(!isset($_SESSION)){
	session_start();
}

define('__ROOT__', dirname(dirname(__FILE__)));
define('__BASENAME__', basename(__ROOT__));

require_once(__ROOT__ . '/modules/Dynamo.class.php');
require_once(__ROOT__ . '/modules/Room.class.php');

$roomName = !isset($_POST['roomName']) ? "": $_POST['roomName'];
$items = !isset($_POST['items']) ? "": $_POST['items'];
$workcategories = !isset($_POST['workcategories']) ? "": $_POST['workcategories'];

$roomObj = new Room();
$roomObj->addRoomTemplate($roomName, $items, $workcategories);
?>