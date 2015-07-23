<?php
if(!isset($_SESSION)){
	session_start();
}

define('__ROOT__', dirname(dirname(__FILE__)));
define('__BASENAME__', basename(__ROOT__));

require_once(__ROOT__ . '/modules/Dynamo.class.php');

if(trim($_REQUEST['community']) != '' && trim($_REQUEST['jobType']) != '' && trim($_REQUEST['propertyType']) != '' && trim($_REQUEST['city']) != '' && trim($_REQUEST['zip']) != '' && trim($_REQUEST['emailList']) != '' && trim($_REQUEST['id']) != '')
{
	$_REQUEST['job_type'] = $_REQUEST['jobType'];
	$_REQUEST['property_type'] = $_REQUEST['propertyType'];
	$_REQUEST['estimates_multiplier'] = $_REQUEST['estimatesMultiplier'];
	$_REQUEST['emails'] = $_REQUEST['emailList'];
	$_REQUEST['estimates_emails'] = $_REQUEST['estimatesEmailList'];
	$_REQUEST['date_created'] = date("Y-m-d H:i:s");
	
	$complex_properties_obj = new Dynamo("complex_properties");
	$complex_properties_array = $complex_properties_obj->getOne();
	
	$_REQUEST['status'] = $complex_properties_array['status'];
	$_REQUEST['created_by'] = $complex_properties_array['created_by'];
	$_REQUEST["reported_by"] = $_SESSION['user_id'];
	
	$community_properties_obj = new Dynamo("community_properties");
	$_REQUEST['property_id'] = $community_property_id = $community_properties_obj->getMaxId();
	
	if($community_properties_obj->add())
	{
		$community_reports_obj = new Dynamo("community_reports");
		$complex_reports_obj = new Dynamo("complex_reports");
		$complex_reports_array = $complex_reports_obj->getOneWhere("property_id = ".$complex_properties_array["id"]);
		
		$_REQUEST['date_reported'] = date("Y-m-d H:i:s");
		$_REQUEST['status_id'] = $_REQUEST['is_submitted'] = $_REQUEST['is_saved'] = $_REQUEST['is_closed'] = 0;
		$_REQUEST['report_id'] = $community_report_id = $community_reports_obj->getMaxId();
		
		if($community_reports_obj->add())
		{
			$community_report_rooms_obj = new Dynamo("community_report_rooms");
			$community_report_rooms_max_id = $community_report_rooms_obj->getMaxId();
			
			$complex_report_rooms_obj = new Dynamo("complex_report_rooms");
			$complex_report_rooms_array = $complex_report_rooms_obj->getAll("WHERE report_id = ".$complex_reports_array["id"]." ORDER BY id");
			
			if(count($complex_report_rooms_array) > 0)
			{
				$query = "INSERT INTO community_report_rooms (`id`,`report_id`,`room_template_id`,`name`,`date_created`,`created_by`) VALUES";
				
				
				$complex_report_room_items_obj = new Dynamo("complex_report_room_items");
				$complex_report_room_items_array = $complex_report_room_items_obj->getAll("WHERE report_id = ".$complex_report_rooms_array[0]["report_id"]." ORDER BY id");
				
				$community_report_room_items_obj = new Dynamo("community_report_room_items");
				$community_report_room_items_max_id = $community_report_room_items_obj->getMaxId();
				
				if(count($complex_report_room_items_array) > 0)
				{
					$query2 = "INSERT INTO community_report_room_items (`id`,`report_id`,`room_id`,`room_template_item_id`,`name`,`status_id`,`date_created`) VALUES";
				}
				
				for($i=0;$i<count($complex_report_rooms_array);$i++)
				{
					$query .= "($community_report_rooms_max_id,".$_REQUEST['report_id'].",".$complex_report_rooms_array[$i]["room_template_id"].",'".addslashes(stripslashes($complex_report_rooms_array[$i]["name"]))."',NOW(),".$_SESSION['user_id']."),";
					
					for($j=0;$j<count($complex_report_room_items_array);$j++)
					{
						if($complex_report_room_items_array[$j]["room_id"] == $complex_report_rooms_array[$i]["id"])
						{
							$query2 .= "($community_report_room_items_max_id,".$_REQUEST['report_id'].",$community_report_rooms_max_id,".$complex_report_room_items_array[$j]["room_template_item_id"].",'".addslashes(stripslashes($complex_report_room_items_array[$j]["name"]))."',".$complex_report_room_items_array[$j]["status_id"].",NOW()),";
							$community_report_room_items_max_id += 1;
						}
					}
					
					$community_report_rooms_max_id += 1;
				}
				
				if(trim($query) != '')
				{
					$query = substr($query,0,-1);
					$community_report_rooms_obj->customExecuteQuery($query);
				}

				if(trim($query2) != '')
				{
					$query2 = substr($query2,0,-1);
					$community_report_room_items_obj->customExecuteQuery($query2);
				}
			}
			
			$result['success'] = true;
			$result['message'] = 'The community has been successfully added!';
		}
	}
}
else
{
	$result['success'] = false;
	$result['message'] = 'Sorry, please fill in all the required values';
}

header('Content-type: application/json');
echo json_encode($result);
?>