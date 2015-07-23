<?php
if(!isset($_SESSION)){
	session_start();
}

define('__ROOT__', dirname(dirname(__FILE__)));
define('__BASENAME__', basename(__ROOT__));

require_once(__ROOT__ . '/modules/Room.class.php');

$userId = !isset($_POST['userId']) ? "": $_POST['userId'];
$propertyId = !isset($_POST['propertyId']) ? "": $_POST['propertyId'];
$statusId = !isset($_POST['statusId']) ? "": $_POST['statusId'];
$data = !isset($_POST['data']) ? "": $_POST['data'];
$save = !isset($_POST['save']) ? "": $_POST['save'];
$submit = !isset($_POST['submit']) ? "": $_POST['submit'];
$reportId = !isset($_POST['reportId']) ? "": $_POST['reportId'];
$propertyName = !isset($_POST['propertyName']) ? "": $_POST['propertyName'];
$propertyAddress = !isset($_POST['propertyAddress']) ? "": $_POST['propertyAddress'];
$propertyEmails = !isset($_POST['propertyEmails']) ? "": $_POST['propertyEmails'];
$propertyStatus = !isset($_POST['propertyStatus']) ? "": $_POST['propertyStatus'];

$roomObj = new Room();
$roomObj->addReport($userId, $propertyId, $statusId, $data, $save, $submit, $reportId, $propertyName, $propertyAddress, $propertyEmails, $propertyStatus);
?>