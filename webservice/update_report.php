<?php
if(!isset($_SESSION)){
	session_start();
}

define('__ROOT__', dirname(dirname(__FILE__)));
define('__BASENAME__', basename(__ROOT__));

require_once(__ROOT__ . '/modules/Room.class.php');
require_once(__ROOT__ . '/modules/Dynamo.class.php');
require_once(__ROOT__ . '/modules/Tools.class.php');

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
$propertyCommunity = !isset($_POST['propertyCommunity']) ? "": $_POST['propertyCommunity'];
$propertyType = !isset($_POST['propertyType']) ? "": $_POST['propertyType'];
$propertyJobType = !isset($_POST['propertyJobType']) ? "": $_POST['propertyJobType'];
$reportComment = !isset($_POST['reportComment']) ? "": $_POST['reportComment'];

$roomObj = new Room();

$subcontractor_email = false;

if($_SESSION['user_type'] != 5)
{
	$tools = new Tools;
	$array_sub_contractors = $tools->getSubContractorToEmail($reportId,$propertyId,new Dynamo("subcontractors_assign"));
	
	if(count($array_sub_contractors) > 0)
	{
		for($i=0;$i<count($array_sub_contractors);$i++)
		{
			$propertyEmails .= ",".$array_sub_contractors[$i]['email'];
		}
	}
}

if($propertyId > 0)
{
	$properties_obj = new Dynamo("properties");
	$propertyArray = $properties_obj->getAll("WHERE id = ".$propertyId);
	$propertyArray = $propertyArray[0];
	if($propertyArray["in_estimates"] == 3)
	{
		$query = "UPDATE properties SET in_estimates = 0 WHERE id = ".$propertyId;
		$properties_obj->customExecuteQuery($query);
	}
}

$roomObj->updateReport($userId, $propertyId, $statusId, $data, $save, $submit, $reportId, $propertyName, $propertyAddress, $propertyEmails, $propertyStatus, $propertyCommunity, $propertyType, $propertyJobType, $reportComment);
?>