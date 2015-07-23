<?php
if(!isset($_SESSION)){
	session_start();
}

define('__ROOT__', dirname(dirname(__FILE__)));
define('__BASENAME__', basename(__ROOT__));

require_once(__ROOT__ . '/modules/Property.class.php');

$name = !isset($_POST['name']) ? "": $_POST['name'];
$address = !isset($_POST['address']) ? "": $_POST['address'];
$city = !isset($_POST['city']) ? "": $_POST['city'];
$zip = !isset($_POST['zip']) ? "": $_POST['zip'];
$link = !isset($_POST['link']) ? "": $_POST['link'];
$estimatesMultiplier = !isset($_POST['estimatesMultiplier']) ? "": $_POST['estimatesMultiplier'];
$status = !isset($_POST['status']) ? 0: $_POST['status'];
$userId = !isset($_POST['userId']) ? '': $_POST['userId'];
$emails = !isset($_POST['emails']) ? '': $_POST['emails'];
$propertyType = !isset($_POST['propertyType']) ? '': $_POST['propertyType'];
$jobType = !isset($_POST['jobType']) ? '': $_POST['jobType'];
$estimatesEmailList = !isset($_POST['estimatesEmailList']) ? '': $_POST['estimatesEmailList'];
$community = !isset($_POST['community']) ? '': $_POST['community'];
$estimates = !isset($_POST['estimates']) ? '': $_POST['estimates'];

$propertyObj = new Property();
$propertyObj->addProperty($name, $address, $city, $zip, $link, $estimatesMultiplier, $status, $userId, $emails, $propertyType, $jobType,$estimatesEmailList, $community, $estimates);
?>