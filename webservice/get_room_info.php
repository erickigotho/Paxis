<?php
require_once(dirname(dirname(__FILE__)) . '/modules/Report.class.php');
$reportId = isset($_REQUEST['reportId'])?$_REQUEST['reportId']:0;

if($reportId != 0)
{
	$reportObj = new Report();
	$reportInfo = $reportObj->getReportDetails($reportId, false);
	
	header('Content-type: application/json');	
	echo json_encode($reportInfo['rooms']); 
}
?>