<?php
if(!isset($_SESSION)){
	session_start();
}

define('__ROOT__', dirname(dirname(__FILE__)));
define('__BASENAME__', basename(__ROOT__));

require_once(__ROOT__ . '/modules/Dynamo.class.php');

$result['success'] = false;
$result['message'] = '';

$propertyId = isset($_POST['propertyId'])?$_POST['propertyId']:0;
$complexPropertyId = isset($_POST['complexPropertyId'])?$_POST['complexPropertyId']:0;
$userId = isset($_POST['userId'])?$_POST['userId']:0;

//add community reports
if($propertyId > 0 && $complexPropertyId > 0)
{
	$complex_reports_obj = new Dynamo("complex_reports");
	$complex_reports_array = $complex_reports_obj->getAll("WHERE property_id = ".$complexPropertyId);
	$community_reports_obj = new Dynamo("community_reports");
	
	$complex_report_rooms_obj = new Dynamo("complex_report_rooms");
	$community_report_rooms_obj = new Dynamo("community_report_rooms");
	
	$complex_report_room_items_obj = new Dynamo("complex_report_room_items");
	$community_report_room_items_obj = new Dynamo("community_report_room_items");
	
	if(count($complex_reports_array) > 0 )
	{
		$query = "INSERT INTO community_reports(`id`,`property_id`,`date_reported`,`status_id`,`reported_by`,`is_submitted`,`is_saved`,`is_closed`) VALUES";
		
		$community_reports_id = $community_reports_obj->getMaxId();
		$community_report_rooms_id = $community_report_rooms_obj->getMaxId();
		$community_report_room_items_id = $community_report_room_items_obj->getMaxId();
		
		for($i=0;$i<count($complex_reports_array);$i++)
		{	
			$query .= "($community_reports_id,$propertyId,NOW(),0,$userId,0,0,0),";
			
			$complex_report_rooms_array = $complex_report_rooms_obj->getAll("WHERE report_id = ".$complex_reports_array[$i]["id"]);
			$complex_report_room_items_array = $complex_report_room_items_obj->getAll("WHERE report_id = ".$complex_reports_array[$i]["id"]);
			
			if(count($complex_report_rooms_array) > 0)
			{
				//add report rooms
				if(trim($query2) == '')
				{
					$query2 = "INSERT INTO community_report_rooms(`id`,`report_id`,`room_template_id`,`name`,`date_created`,`created_by`) VALUES";
				}
				
				//add report room items		
				if(count($complex_report_room_items_array) > 0 && trim($query3) == '')
				{
					$query3 = "INSERT INTO community_report_room_items(`id`,`report_id`,`room_id`,`room_template_item_id`,`name`,`status_id`,`date_created`) VALUES";	
				}
				
				for($j=0;$j<count($complex_report_rooms_array);$j++)
				{
					$query2 .= "($community_report_rooms_id,$community_reports_id,".$complex_report_rooms_array[$j]['room_template_id'].",'".addslashes(stripslashes($complex_report_rooms_array[$j]['name']))."',NOW(),$userId),";
					
					if(count($complex_report_room_items_array) > 0)
					{
						for($k=0;$k<count($complex_report_room_items_array);$k++)
						{
							if($complex_report_rooms_array[$j]['id'] == $complex_report_room_items_array[$k]['room_id'])
							{
								$query3 .= "($community_report_room_items_id,$community_reports_id,$community_report_rooms_id,".$complex_report_room_items_array[$k]['room_template_item_id'].",'".addslashes(stripslashes($complex_report_room_items_array[$k]['name']))."',2,NOW()),";
								$community_report_room_items_id += 1;
							}
						}
					}
					else
					{
						$result['success'] = false;
						$result['message'] = 'Sorry, there has been a problem processing your request.1';
					}
					
					$community_report_rooms_id += 1;
				}
			}
			else
			{
				$result['success'] = false;
				$result['message'] = 'Sorry, there has been a problem processing your request.2';
			}
			
			$community_reports_id += 1;
		}
		
		if(trim($query) != '')
		{
			$query = substr($query,0,-1);
			$community_reports_obj->customExecuteQuery($query);	
		}
		
		if(trim($query2) != '')
		{
			$query2 = substr($query2,0,-1);
			$community_report_rooms_obj->customExecuteQuery($query2);	
		}
		
		if(trim($query3) != '')
		{
			$query3 = substr($query3,0,-1);
			$community_report_room_items_obj->customExecuteQuery($query3);	
		}
	}
	else
	{
		$result['success'] = false;
		$result['message'] = 'Sorry, there has been a problem processing your request.3';
	}
}
else
{
	$result['success'] = false;
	$result['message'] = 'Sorry, there has been a problem processing your request.4';
}

if(trim($result['message']) == '')
{
	$result['success'] = true;
	$result['message'] = 'The template has been successfully implemented';
}

header('Content-type: application/json');
echo json_encode($result);
?>