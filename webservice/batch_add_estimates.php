<?php
if(!isset($_SESSION)){
	session_start();
}

define('__ROOT__', dirname(dirname(__FILE__)));
define('__BASENAME__', basename(__ROOT__));

require_once(__ROOT__ . '/modules/Dynamo.class.php');

$result['success'] = false;
$result['message'] = '';

$work_category_estimates_obj = new Dynamo("work_category_estimates");

if(trim($_POST['data']) != '')
{	
	$arrayData = json_decode($_POST['data']);
	
	if(count($arrayData) > 0)
	{
		$query = "DELETE FROM work_category_estimates WHERE work_category_id = ".$_POST['work_category_id'];
		$work_category_estimates_obj->customExecuteQuery($query);
		
		$maxId = $work_category_estimates_obj->getMaxId();
		$query = "INSERT INTO work_category_estimates VALUES";
		for($i=0;$i<count($arrayData);$i++)
		{
			$query .= "({$maxId},".$_POST['work_category_id'].",\"".addslashes(stripslashes($arrayData[$i]->item_name))."\",\"".$arrayData[$i]->price_per_unit."\",".$arrayData[$i]->unit_of_measure.",NOW()),";
			$maxId += 1;
		}
		
		$query = substr($query,0,-1);
		$work_category_estimates_obj->customExecuteQuery($query);
		
		$result['success'] = true;
		$result['message'] = 'Successfully added estimates';
	}
}

header('Content-type: application/json');
echo json_encode($result);
?>