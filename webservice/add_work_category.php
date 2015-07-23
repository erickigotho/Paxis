<?php
if(!isset($_SESSION)){
	session_start();
}

define('__ROOT__', dirname(dirname(__FILE__)));
define('__BASENAME__', basename(__ROOT__));

$result['success'] = false;
$result['message'] = '';

require_once(__ROOT__ . '/modules/Dynamo.class.php');

if(trim($_REQUEST['name']) != '')
{
	$workCategoryObj = new Dynamo("work_categories");
	
	if($workCategoryObj->add())
	{
		$result['success'] = true;
		$result['message'] = 'Work Category successfully added!';
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