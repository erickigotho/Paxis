<?php
 
if(!isset($_SESSION)){
	session_start();
}

define('__ROOT__', dirname(dirname(__FILE__)));
define('__BASENAME__', basename(__ROOT__));

require_once(__ROOT__ . '/modules/Dynamo.class.php');

if(trim($_REQUEST['id']) != '')
{
	$unitsObj = new Dynamo("units");
	$unitsObj->delete($id);
	
	if($unitsObj->delete())
	{
		$result['success'] = true;
		$result['message'] = 'Estimate Unit successfully deleted!';
	}
	else
	{
		$result['success'] = false;
		$result['message'] = 'Sorry, there has been a problem processing your request.';
	}
}
else
{
	$result['success'] = false;
	$result['message'] = 'Sorry, there has been a problem processing your request.';
}

header('Content-type: application/json');
echo json_encode($result);
?>