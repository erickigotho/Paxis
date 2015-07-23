<?php
 
if(!isset($_SESSION)){
	session_start();
}

define('__ROOT__', dirname(dirname(__FILE__)));
define('__BASENAME__', basename(__ROOT__));

require_once(__ROOT__ . '/modules/Dynamo.class.php');

$result['success'] = false;
$result['message'] = '';
		
$id = !isset($_POST['id']) ? "": $_POST['id'];

if(trim($id) != '')
{
	$estimate_rooms = new Dynamo("estimate_rooms");
	$estimate_room_items_units = new Dynamo("estimate_room_items_units");
	
	if($estimate_rooms->deleteCustom("WHERE id = ".$id))
	{
		$query = "DELETE FROM estimate_room_items_units WHERE estimate_room_items_id IN (SELECT id FROM estimate_room_items WHERE room_id = ".$id.")";
		$estimate_room_items_units->customExecuteQuery($query);
		
		$estimate_room_items = new Dynamo("estimate_room_items");
		if($estimate_room_items->deleteCustom("WHERE room_id = ".$id))
		{
			$result['success'] = true;
			$result['message'] = 'Room successfully deleted.';
		}
	}
}

header('Content-type: application/json');
echo json_encode($result);
?>