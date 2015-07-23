<?php
 
if(!isset($_SESSION)){
	session_start();
}

define('__ROOT__', dirname(dirname(__FILE__)));
define('__BASENAME__', basename(__ROOT__));

require_once(__ROOT__ . '/modules/Property.class.php');

$propertyId = !isset($_POST['propertyId']) ? "": $_POST['propertyId'];
$status = !isset($_POST['status']) ? 0: $_POST['status'];
$userId = !isset($_POST['userId']) ? '': $_POST['userId'];
$reportId = !isset($_POST['reportId']) ? '': $_POST['reportId'];

$propertyObj = new Property();
$propertyObj->archiveProperty($propertyId, $status, $userId, $reportId);
?>