<?php
define('__ROOT__', dirname(dirname(__FILE__)));
define('__BASENAME__', basename(__ROOT__));

require_once(__ROOT__ . '/modules/Dynamo.class.php');

$_REQUEST['date_created'] = date("Y-m-d H:i:s");

if(trim($_REQUEST['email']) != '' && trim($_REQUEST['first_name']) != '' && trim($_REQUEST['last_name']) != ''
&& trim($_REQUEST['phone_number']) != '')
{
	$subContractorObj = new Dynamo("sub_contractors");
	
	$sub_contractor_id = $subContractorObj->getMaxId();
	if($subContractorObj->add())
	{
		if(trim($_REQUEST['work_category_id_string']) != '')
		{
			$array_work_category = array();
			$work_category_id_string = substr($_REQUEST['work_category_id_string'],0,-1);
			if(stristr($work_category_id_string,","))
				$array_work_category = explode(",",$work_category_id_string);
			else
				$array_work_category[] = $work_category_id_string;
			
			$query = "INSERT INTO sub_contractor_work_category VALUES";
			
			for($i=0;$i<count($array_work_category);$i++)
				$query .= "({$sub_contractor_id},".$array_work_category[$i].",NOW()),";
			
			$query = substr($query,0,-1);
			
			if($subContractorObj->customExecuteQuery($query))
			{
				$result['success'] = true;
				$result['message'] = 'Sub contractor successfully added!';
			}
		}
		else
		{
			$result['success'] = true;
			$result['message'] = 'Sub contractor successfully added!';
		}	
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