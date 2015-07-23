<?php
if(!isset($_SESSION)){
	session_start();
}

define('__ROOT__', dirname(dirname(__FILE__)));
define('__BASENAME__', basename(__ROOT__));

require_once(__ROOT__ . '/modules/Dynamo.class.php');

$result['success'] = false;
$result['message'] = '';

if(trim($_REQUEST['property_id']) != '' && trim($_REQUEST['data']) != '')
{
	$subcontractors_assign = new Dynamo("subcontractors_assign");
	$subcontractors_assign->deleteCustom("WHERE property_id = ".$_REQUEST['property_id']);
	
	$array_sub = json_decode($_REQUEST['data']);
	
	for($i=0;$i<count($array_sub);$i++)
	{
		$_REQUEST['sub_contractor_id'] = $array_sub[$i]->sub_contractor_id;
		$_REQUEST['work_category_id'] = $array_sub[$i]->work_category_id;
		
		$subcontractors_assign->add();
	}
	
	$result['success'] = true;
	$result['message'] = 'Subcontractors successfully edited!';
}
else
{
	$result['success'] = false;
	$result['message'] = '';
}
header('Content-type: application/json');
echo json_encode($result);
?>