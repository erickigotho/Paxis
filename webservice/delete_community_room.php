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
$roomObj->deleteCommunityRoom($id);

if(trim($_POST['reportId']) != '')
{
	$array_report_rooms = array();
	$community_report_rooms_obj = new Dynamo("community_report_rooms");
	$array_report_rooms = $community_report_rooms_obj->getAll("WHERE report_id = ".$_POST['reportId']);
	
	if(count($array_report_rooms) <= 0)
	{
		$query = "DELETE FROM community_reports WHERE id = ".$_POST['reportId'];
		$community_report_rooms_obj->customExecuteQuery($query);
	}
}
?>