<?php
if(!isset($_SESSION)){
	session_start();
}

define('__ROOT__', dirname(dirname(__FILE__)));
define('__BASENAME__', basename(__ROOT__));

require_once(__ROOT__ . '/modules/Dynamo.class.php');

$result['success'] = false;
$result['message'] = '';

$room_template_estimates_obj = new Dynamo("room_template_estimates");

if(trim($_POST['data']) != '')
{	
	$arrayData = json_decode($_POST['data']);
	
	if(count($arrayData) > 0)
	{
		$query = "DELETE FROM room_template_estimates WHERE room_template_items_id = ".$_POST['room_template_items_id'];
		$room_template_estimates_obj->customExecuteQuery($query);
		
		$maxId = $room_template_estimates_obj->getMaxId();
		$query = "INSERT INTO room_template_estimates VALUES";
		for($i=0;$i<count($arrayData);$i++)
		{
			$query .= "({$maxId},".$_POST['room_template_id'].",".$_POST['room_template_items_id'].",\"".addslashes(stripslashes($arrayData[$i]->item_name))."\",\"".$arrayData[$i]->price_per_unit."\",".$arrayData[$i]->unit_of_measure.",NOW()),";
			$maxId += 1;
		}
		
		$query = substr($query,0,-1);
		$room_template_estimates_obj->customExecuteQuery($query);
		
		$result['success'] = true;
		$result['message'] = 'Successfully added estimates';
	}
}

header('Content-type: application/json');
echo json_encode($result);
?>