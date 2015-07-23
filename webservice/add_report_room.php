<?php
if(!isset($_SESSION)){
	session_start();
}

define('__ROOT__', dirname(dirname(__FILE__)));
define('__BASENAME__', basename(__ROOT__));

require_once(__ROOT__ . '/modules/Room.class.php');

$roomName = !isset($_POST['roomName']) ? "": $_POST['roomName'];
$roomTemplateId = !isset($_POST['roomTemplateId']) ? "": $_POST['roomTemplateId'];
$userId = !isset($_POST['userId']) ? "": $_POST['userId'];

$roomObj = new Room();
$roomObj->addReportRoom($roomName, $roomTemplateId, $userId);
?>