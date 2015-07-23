<?php
define('__ROOT__', dirname(dirname(__FILE__)));
define('__BASENAME__', basename(__ROOT__));

require_once(__ROOT__ . '/modules/Dynamo.class.php');
require_once(__ROOT__ . '/config/main.config.php');

$_REQUEST = $_POST;

if(trim($_REQUEST['email']) != '' && trim($_REQUEST['first_name']) != '' && trim($_REQUEST['last_name']) != ''
&& trim($_REQUEST['phone_number']) != '' && trim($_REQUEST['id']) != '')
{
	if(!preg_match(EMAIL_PATTERN, $_REQUEST['email']))
	{
		$result['success'] = false;
		$result['message'] = 'Please enter a valid email address.';
	}
	else if($_REQUEST['password'] != $_REQUEST['passwordConfirm'])
	{
		$result['success'] = false;
		$result['message'] = 'Your passwords do not match.';
	}
	else
	{
		$subContractorObj = new Dynamo("sub_contractors");
		$sub_contractor_work_category = new Dynamo("sub_contractor_work_category");
		
		$sub_contractor_id = $_REQUEST['id'];
		
		$array_sub_categories = $subContractorObj->getOne();
		
		if(trim($_REQUEST['password']) != '')
		{
			$password = $_REQUEST['password'];
			$encryptedPass = base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_256, md5(ENCRYPTION_KEY), $password, MCRYPT_MODE_CBC, md5(md5(ENCRYPTION_KEY))));
			$_REQUEST['password'] = $encryptedPass;
		}
		else
		{
			$_REQUEST['password'] = $array_sub_categories['password'];	
		}
		
		if($subContractorObj->edit())
		{
			$sub_contractor_work_category->deleteCustom("WHERE sub_contractor_id = ".$sub_contractor_id);
			
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
				
				if($sub_contractor_work_category->customExecuteQuery($query))
				{
					$result['success'] = true;
					$result['message'] = 'Sub contractor successfully edited!';
				}
			}
			else
			{
				$result['success'] = true;
				$result['message'] = 'Sub contractor successfully edited!';
			}	
		}
		else
		{
			$result['success'] = false;
			$result['message'] = 'Sorry, there has been a problem processing your request.';
		}
	}
}
else
{
	$result['success'] = false;
	$result['message'] = 'One of the required fields is missing.';
}

header('Content-type: application/json');
echo json_encode($result);
?>