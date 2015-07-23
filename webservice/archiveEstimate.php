<?php
if(!isset($_SESSION)){
	session_start();
}

define('__ROOT__', dirname(dirname(__FILE__)));
define('__BASENAME__', basename(__ROOT__));

require_once(__ROOT__ . '/modules/Dynamo.class.php');

$result['success'] = false;
$result['message'] = '';

if(trim($_POST['propertyId']) != '')
{
	$propertyId = $_POST['propertyId'];
	
	$user_id = $_SESSION['user_id'];
	
	$properties_obj = new Dynamo("properties");
	$query = "UPDATE properties SET status = 0,in_estimates = 0, closed_by = $user_id,date_closed=NOW() WHERE id = ".$propertyId;
	
	$properties_obj->customExecuteQuery($query);
	
	
	$result['success'] = true;
	$result['message'] = '';
}

header('Content-type: application/json');
echo json_encode($result);
?>