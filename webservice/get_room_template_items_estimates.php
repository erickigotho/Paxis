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
$property_obj = new Dynamo("properties");
$estimates_obj = new Dynamo("estimates");
$room_templates_obj = new Dynamo("room_templates");
$estimate_rooms_obj = new Dynamo("estimate_rooms");
$estimate_room_items_obj = new Dynamo("estimate_room_items");


$data = $roomObj->getRoomTemplateItems($id,false);

$array_room_templates = $room_templates_obj->getOne();
$maxIdEstimateRooms = $estimate_rooms_obj->getMaxId();
ob_start();
if(trim($_REQUEST['estimatesId']) == '' && trim($_REQUEST['propertyId']) != '')
{		
	$_REQUEST['estimatesId'] = $estimates_obj->getMaxId();
	$query = "INSERT INTO estimates (`id`,`property_id`,`date_created`,`reported_by`,`is_submitted`,`is_saved`,`is_closed`) 
	VALUES(".$_REQUEST['estimatesId'].",".$_REQUEST['propertyId'].",NOW(),".$_SESSION['user_id'].",0,1,0)";
	
	$estimates_obj->customExecuteQuery($query);
	
	$query = "UPDATE properties SET in_estimates = 1 WHERE id = ".$_REQUEST['propertyId'];
	$property_obj->customExecuteQuery($query);
}


if(count($data) > 0)
{	
	$query = "INSERT INTO estimate_rooms VALUES({$maxIdEstimateRooms},".$_REQUEST['estimatesId'].",{$id},'".htmlentities(html_entity_decode($_REQUEST['roomName']),ENT_QUOTES)."',NOW(),".$_SESSION['user_id'].")";
	$room_templates_obj->customExecuteQuery($query);
	$result['roomId'] = $maxIdEstimateRooms;
	
	$query = "INSERT INTO estimate_room_items (`id`,`estimate_id`,`room_id`,`room_template_item_id`,`name`,`date_created`) VALUES";
	
	$reportRoomMaxId = $estimate_room_items_obj->getMaxId();
	for($i=0;$i<count($data);$i++)
	{
		$query .= "(".$reportRoomMaxId.",".$_REQUEST['estimatesId'].",{$maxIdEstimateRooms},".$data[$i]['roomTemplateItemId'].",'".addslashes(stripslashes($data[$i]['name']))."',NOW()),";
		
		$data[$i]['roomId'] = $maxIdEstimateRooms;
		$data[$i]['itemId'] = $reportRoomMaxId;
		$data[$i]['estimatesId'] = $_REQUEST['estimatesId'];
		$reportRoomMaxId += 1;
	}
	
	$query = substr($query,0,-1);	
	$estimate_room_items_obj->customExecuteQuery($query);
	
	$result['success'] = true;
	$result['data'] = $data;
}

header('Content-type: application/json');
echo json_encode($result);
?>