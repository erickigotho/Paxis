<?php
require_once(dirname(dirname(__FILE__)) . '/modules/Report.class.php');
$estimatesId = isset($_REQUEST['estimatesId'])?$_REQUEST['estimatesId']:0;

if($estimatesId != 0)
{
	$estimatesObj = new Estimates();
	$estimatesInfo = $estimatesObj->getEstimateDetails($estimatesId, false);

	header('Content-type: application/json');	
	echo json_encode($estimatesInfo['rooms']); 
}
?>