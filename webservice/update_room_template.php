<?php
 if(!isset($_SESSION)){
	session_start();
}

define('__ROOT__', dirname(dirname(__FILE__)));
define('__BASENAME__', basename(__ROOT__));

require_once(__ROOT__ . '/modules/Dynamo.class.php');
require_once(__ROOT__ . '/modules/Room.class.php');

$id = !isset($_POST['id']) ? "": $_POST['id'];
$name = !isset($_POST['name']) ? "": $_POST['name'];
$workcategories = !isset($_POST['workcategories']) ? "": $_POST['workcategories'];
$items = !isset($_POST['items']) ? "": $_POST['items'];
$itemsIdArray = !isset($_POST['roomTemplatesItemsIdArray']) ? "": $_POST['roomTemplatesItemsIdArray'];

$roomObj = new Room();
$roomObj->updateRoomTemplateInfo($id, $name, $items, $itemsIdArray, $workcategories);
?>