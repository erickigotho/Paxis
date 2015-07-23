<?php
if(!isset($_SESSION)){
	session_start();
}

define('__ROOT__', dirname(dirname(__FILE__)));
define('__BASENAME__', basename(__ROOT__));

require_once(__ROOT__ . '/modules/Dynamo.class.php');

if(trim($_REQUEST['community']) != '' && trim($_REQUEST['propertyName']) != '' && trim($_REQUEST['jobType']) != '' && trim($_REQUEST['propertyType']) != '' && trim($_REQUEST['address']) != '' && trim($_REQUEST['city']) != '' && trim($_REQUEST['zip']) != '' && trim($_REQUEST['emailList']) != '' && trim($_REQUEST['id']) != '')
{
	$_REQUEST['name'] = $_REQUEST['propertyName'];
	$_REQUEST['job_type'] = $_REQUEST['jobType'];
	$_REQUEST['property_type'] = $_REQUEST['propertyType'];
	$_REQUEST['map_link'] = $_REQUEST['googleMapLink'];
	$_REQUEST['estimates_multiplier'] = $_REQUEST['estimatesMultiplier'];
	$_REQUEST['emails'] = $_REQUEST['emailList'];
	$_REQUEST['estimates_emails'] = $_REQUEST['estimatesEmailList'];
	
	$_REQUEST['date_updated'] = date("Y-m-d H:i:s");
	$_REQUEST['state'] = '';
	
	$complex_properties_obj = new Dynamo("complex_properties");
	$complex_properties_array = $complex_properties_obj->getOne();
	
	$_REQUEST['date_created'] = $complex_properties_array["date_created"];
	$_REQUEST['status'] = $complex_properties_array['status'];
	$_REQUEST['created_by'] = $complex_properties_array['created_by'];
	$_REQUEST['reported_by'] = $_REQUEST["updated_by"] = $_SESSION['user_id'];
	
	$properties_obj = new Dynamo("properties");
	$_REQUEST['property_id'] = $community_property_id = $properties_obj->getMaxId();
	
	if($properties_obj->add())
	{
		$reports_obj = new Dynamo("reports");
		$complex_reports_obj = new Dynamo("complex_reports");
		$complex_reports_array = $complex_reports_obj->getOneWhere("property_id = ".$complex_properties_array["id"]);
		
		$_REQUEST['date_reported'] = date("Y-m-d H:i:s");
		$_REQUEST['status_id'] = $_REQUEST['is_submitted'] = $_REQUEST['is_closed'] = $_REQUEST['subcontractor'] = 0;
		$_REQUEST['is_saved'] = 1;
		$_REQUEST['report_id'] = $community_report_id = $reports_obj->getMaxId();
		
		if($reports_obj->add())
		{
			$report_rooms_obj = new Dynamo("report_rooms");
			$report_rooms_max_id = $report_rooms_obj->getMaxId();
			
			$complex_report_rooms_obj = new Dynamo("complex_report_rooms");
			$complex_report_rooms_array = $complex_report_rooms_obj->getAll("WHERE report_id = ".$complex_reports_array["id"]." ORDER BY id");
			
			if(count($complex_report_rooms_array) > 0)
			{
				$query = "INSERT INTO report_rooms (`id`,`report_id`,`room_template_id`,`name`,`date_created`,`created_by`) VALUES";
				
				
				$complex_report_room_items_obj = new Dynamo("complex_report_room_items");
				$complex_report_room_items_array = $complex_report_room_items_obj->getAll("WHERE report_id = ".$complex_report_rooms_array[0]["report_id"]." ORDER BY id");
				
				$report_room_items_obj = new Dynamo("report_room_items");
				$report_room_items_max_id = $report_room_items_obj->getMaxId();
				
				if(count($complex_report_room_items_array) > 0)
				{
					$query2 = "INSERT INTO report_room_items (`id`,`report_id`,`room_id`,`room_template_item_id`,`name`,`status_id`,`date_created`) VALUES";
				}
				
				for($i=0;$i<count($complex_report_rooms_array);$i++)
				{
					$query .= "($report_rooms_max_id,".$_REQUEST['report_id'].",".$complex_report_rooms_array[$i]["room_template_id"].",'".addslashes(stripslashes($complex_report_rooms_array[$i]["name"]))."',NOW(),".$_SESSION['user_id']."),";
					
					for($j=0;$j<count($complex_report_room_items_array);$j++)
					{
						if($complex_report_room_items_array[$j]["room_id"] == $complex_report_rooms_array[$i]["id"])
						{
							$query2 .= "($report_room_items_max_id,".$_REQUEST['report_id'].",$report_rooms_max_id,".$complex_report_room_items_array[$j]["room_template_item_id"].",'".addslashes(stripslashes($complex_report_room_items_array[$j]["name"]))."',".$complex_report_room_items_array[$j]["status_id"].",NOW()),";
							$report_room_items_max_id += 1;
						}
					}
					
					$report_rooms_max_id += 1;
				}
				
				if(trim($query) != '')
				{
					$query = substr($query,0,-1);
					$report_rooms_obj->customExecuteQuery($query);
				}

				if(trim($query2) != '')
				{
					$query2 = substr($query2,0,-1);
					$report_room_items_obj->customExecuteQuery($query2);
				}
			}
			
			$result['success'] = true;
			$result['message'] = 'The property has been successfully added!';
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