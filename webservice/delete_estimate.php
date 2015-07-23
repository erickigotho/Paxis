<?php
if(!isset($_SESSION)){
	session_start();
}

if(trim($_SESSION['user_id']) == '')
	exit;

define('__ROOT__', dirname(dirname(__FILE__)));
define('__BASENAME__', basename(__ROOT__));
$result['success'] = '';
require_once(__ROOT__ . '/modules/Dynamo.class.php');

if(trim($_REQUEST["unit_id"]) != '')
{
	$estimate_room_items_units_obj = new Dynamo("estimate_room_items_units");
	$query = "DELETE FROM estimate_room_items_units WHERE id = ".$_REQUEST["unit_id"];
	if($estimate_room_items_units_obj->customExecuteQuery($query))
		$result['success'] = true;
}

if(trim($result['success']) == '')
	$result['success'] = false;

header('Content-type: application/json');
echo json_encode($result);
?>