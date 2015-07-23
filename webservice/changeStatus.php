<?php
if(!isset($_SESSION)){
	session_start();
}

define('__ROOT__', dirname(dirname(__FILE__)));
define('__BASENAME__', basename(__ROOT__));

require_once(__ROOT__ . '/modules/Dynamo.class.php');

$result['success'] = false;
$result['message'] = '';

$report_room_items_object = new Dynamo("report_room_items");
$report_room_item_comments_object = new Dynamo("report_room_item_comments");

if(trim($_REQUEST['itemId']) != '' && trim($_REQUEST['status']) != '')
{
	$query = "SELECT is_estimate,report_id,room_id,room_template_item_id FROM report_room_items WHERE id = ".$_REQUEST['itemId'];
	$arrayRoomItems = $report_room_items_object->customFetchQuery($query);
	$arrayRoomItems = $arrayRoomItems[0];
	
	if($arrayRoomItems['is_estimate'] > 0)
	{
		$query2 = "UPDATE report_room_items SET status_id = ".$_REQUEST['status']." WHERE report_id  = ".$arrayRoomItems['report_id']." AND room_id = ".$arrayRoomItems['room_id']." AND room_template_item_id = ".$arrayRoomItems['room_template_item_id']." AND is_estimate = 0";		
		$report_room_items_object->customExecuteQuery($query2);
	}
	
	$query = "UPDATE report_room_items SET status_id = ".$_REQUEST['status']." WHERE id = ".$_REQUEST['itemId'];
	if($report_room_items_object->customExecuteQuery($query))
	{
		$result['success'] = true;
	}
	else
	{
		$result['success'] = false;
	}
}

if(trim($_REQUEST['comment']) != '' && $_REQUEST['reportId'] > 0 && trim($_REQUEST['itemId']) != '')
{
	$query = "SELECT * FROM report_room_item_comments WHERE report_id = ".$_REQUEST['reportId']." AND room_item_id = ".$_REQUEST['itemId']." AND 	`order` = ".$_REQUEST['order'];
	
	if(count($report_room_item_comments_object->customFetchQuery($query)) > 0)
	{
		$query = "UPDATE report_room_item_comments SET comment = '".addslashes(stripslashes($_REQUEST['comment']))."' WHERE report_id = ".$_REQUEST['reportId']." AND room_item_id = ".$_REQUEST['itemId']." AND `order` = ".$_REQUEST['order'];
	}
	else
	{
		$query = "INSERT INTO report_room_item_comments(`comment`,`user_id`,`room_item_id`,`report_id`,`order`,`date`) VALUE('".addslashes(stripslashes($_REQUEST['comment']))."',".$_SESSION['user_id'].",".$_REQUEST['itemId'].",".$_REQUEST['reportId'].",".$_REQUEST['order'].",NOW())";
	}
	
	/*if(trim($_REQUEST['addAnotherComment']) == 1)
	{
		$query = "INSERT INTO report_room_item_comments(`comment`,`user_id`,`room_item_id`,`report_id`,`date`) VALUE('".addslashes(stripslashes($_REQUEST['comment']))."',".$_SESSION['user_id'].",".$_REQUEST['itemId'].",".$_REQUEST['reportId'].",NOW())";
	}
	else
	{
		$query = "SELECT * FROM report_room_item_comments WHERE report_id = ".$_REQUEST['reportId']." AND room_item_id = ".$_REQUEST['itemId'];
		
		if(count($report_room_item_comments_object->customFetchQuery($query)) > 0)
		{
			$query = "UPDATE report_room_item_comments SET comment = '".addslashes(stripslashes($_REQUEST['comment']))."' WHERE report_id = ".$_REQUEST['reportId']." AND room_item_id = ".$_REQUEST['itemId'];
		}
		else
		{
			$query = "INSERT INTO report_room_item_comments(`comment`,`user_id`,`room_item_id`,`report_id`,`date`) VALUE('".addslashes(stripslashes($_REQUEST['comment']))."',".$_SESSION['user_id'].",".$_REQUEST['itemId'].",".$_REQUEST['reportId'].",NOW())";
		}
	}*/
	
	if($report_room_item_comments_object->customExecuteQuery($query))
	{
		$result['success'] = true;
	}
	else
	{
		$result['success'] = false;
	}
	
}
header('Content-type: application/json');
echo json_encode($result);
?>