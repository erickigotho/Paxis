<?php
require_once(dirname(dirname(__FILE__)) . '/modules/Dynamo.class.php');

$result['success'] = false;
$result['message'] = '';

$report_room_items = new Dynamo("report_room_items");

if(trim($_REQUEST['roomId']) != '')
{
	$arrayRoomItemsId = $report_room_items->customFetchQuery("SELECT id FROM report_room_items WHERE room_id = ".$_REQUEST['roomId']." AND status_id = 2");
	
	$report_room_items->customExecuteQuery("UPDATE report_room_items SET status_id = 1 WHERE room_id = ".$_REQUEST['roomId']." AND status_id = 2");
	
	if(count($arrayRoomItemsId) > 0)
	{
		$arrayRoomItemsId2 = array();
		for($i=0;$i<count($arrayRoomItemsId);$i++)
		{
			$arrayRoomItemsId2[] = $arrayRoomItemsId[$i]['id'];
		}
		
		$result['success'] = true;
		$result['message'] = $arrayRoomItemsId2;
	}
	else
	{
		$result['success'] = false;
		$result['message'] = "All pending items have already been marked as complete";	
	}
}
else
{
	$result['success'] = false;
	$result['message'] = "Oops, there's a problem with these line items";
}
header('Content-type: application/json');
echo json_encode($result);
?>