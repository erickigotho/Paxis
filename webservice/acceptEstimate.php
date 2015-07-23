<?php
if(!isset($_SESSION)){
	session_start();
}

define('__ROOT__', dirname(dirname(__FILE__)));
define('__BASENAME__', basename(__ROOT__));

require_once(__ROOT__ . '/modules/Property.class.php');
require_once(__ROOT__ . '/modules/Report.class.php');
require_once(__ROOT__ . '/modules/Room.class.php');
require_once(__ROOT__ . '/modules/Estimates.class.php');
require_once(__ROOT__ . '/modules/Dynamo.class.php');

$result['success'] = false;
$result['message'] = '';

if(trim($_POST['propertyId']) != '')
{
	require_once("add_estimate.php");
	
	$propertyId = $_POST['propertyId'];
	
	$properties_obj = new Dynamo("properties");
	$query = "UPDATE properties SET in_estimates = 3 WHERE id = ".$propertyId;
	$properties_obj->customExecuteQuery($query);
	
	/*$query = "UPDATE estimates SET is_closed = 2 WHERE id = ".$estimates_id;
	$properties_obj->customExecuteQuery($query);
	*/
	$query = "UPDATE estimates SET is_closed = 1 WHERE property_id = ".$_POST['propertyId']." AND is_closed = 0";
	$properties_obj->customExecuteQuery($query);
	
	$result['success'] = true;
	$result['message'] = '';
}

header('Content-type: application/json');
echo json_encode($result);
?>