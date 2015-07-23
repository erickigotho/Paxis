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
$reports_obj = new Dynamo("reports");
$room_templates_obj = new Dynamo("room_templates");
$report_rooms_obj = new Dynamo("report_rooms");
$report_room_items_obj = new Dynamo("report_room_items");

$data = $roomObj->getRoomTemplateItems($id,false);

$array_room_templates = $room_templates_obj->getOne();
$maxIdReportRooms = $report_rooms_obj->getMaxId();

if(trim($_REQUEST['reportId']) == '' && trim($_REQUEST['propertyId']) != '')
{
	$_REQUEST['reportId'] = $reports_obj->getMaxId();
	$query = "INSERT INTO reports (`id`,`property_id`,`date_reported`,`status_id`,`reported_by`,`is_submitted`,`is_saved`,`is_closed`) 
	VALUES(".$_REQUEST['reportId'].",".$_REQUEST['propertyId'].",NOW(),0,".$_SESSION['user_id'].",0,1,0)";
	
	$reports_obj->customExecuteQuery($query);
}

if(count($data) > 0)
{
	$query = "INSERT INTO report_rooms VALUES({$maxIdReportRooms},".$_REQUEST['reportId'].",{$id},'".htmlentities(html_entity_decode($_REQUEST['roomName']),ENT_QUOTES)."',NOW(),".$_SESSION['user_id'].")";
	$room_templates_obj->customExecuteQuery($query);
	$result['roomId'] = $maxIdReportRooms;
	
	$query = "INSERT INTO report_room_items (`id`,`report_id`,`room_id`,`room_template_item_id`,`name`,`status_id`,`date_created`) VALUES";
	
	$reportRoomMaxId = $report_room_items_obj->getMaxId();
	for($i=0;$i<count($data);$i++)
	{
		$query .= "(".$reportRoomMaxId.",".$_REQUEST['reportId'].",{$maxIdReportRooms},".$data[$i]['roomTemplateItemId'].",'".addslashes(stripslashes($data[$i]['name']))."',2,NOW()),";
		
		$data[$i]['itemId'] = $reportRoomMaxId;
		$data[$i]['reportId'] = $_REQUEST['reportId'];
		$reportRoomMaxId += 1;
	}
	
	$query = substr($query,0,-1);
	
	$report_room_items_obj->customExecuteQuery($query);
	
	$result['success'] = true;
	$result['data'] = $data;
}

header('Content-type: application/json');
echo json_encode($result);
?>