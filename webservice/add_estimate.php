<?php
$propertyId = isset($_POST['propertyId'])?$_POST['propertyId']:0;

if($propertyId != 0) {
	$propertyObj = new Property();
	$propertyInfo = $propertyObj->getPropertyInfo($propertyId, false);
	
	$roomObj = new Room();
	$listRoomTemplates = $roomObj->getRoomTemplates(false);
	
	if(!$propertyInfo) {	
		echo "No property found.";
		return;
	}
	
	$estimatesObj = new Estimates();
	
	$estimatesId = $estimatesObj->getPreviousEstimatesId($propertyId, false);
	$estimateInfo = $estimatesObj->getEstimateDetails_estimates($estimatesId, false);
	
	$copy_from_report = false;
	
	if(count($estimateInfo['rooms']) <= 0)
	{
		$reportObj = new Report();
	
		$reportId = $reportObj->getPreviousReportId($propertyId, false);
		$reportInfo = $reportObj->getReportDetails($reportId, false);
		
		if(count($reportInfo['rooms']) > 0)
		{
			$estimateInfo = $reportInfo;
			$copy_from_report = true;
		}
	}
	
	if(count($estimateInfo['rooms']) > 0)
	{
		$estimates_obj = new Dynamo("estimates");
		$room_templates_obj = new Dynamo("room_templates");
		$estimate_rooms_obj = new Dynamo("estimate_rooms");
		$estimate_room_items_obj = new Dynamo("estimate_room_items");
		$estimate_room_items_units_obj = new Dynamo("estimate_room_items_units");
		$room_template_items_obj = new Dynamo("room_template_items");
		
		//get previous room item units
		if(!$copy_from_report)
			$estimate_room_items_units_array = $estimate_room_items_units_obj->getAll("WHERE estimate_id = ".$estimatesId);
		
		$estimatesId = $_REQUEST['estimatesId'] = $estimates_obj->getMaxId();
		
		$room_template_items_array =  $room_template_items_obj->getAll();
		
		$query = "INSERT INTO estimates (`id`,`property_id`,`date_created`,`reported_by`,`is_submitted`,`is_saved`,`is_closed`) 
		VALUES(".$_REQUEST['estimatesId'].",".$estimateInfo['propertyId'].",NOW(),".$_SESSION['user_id'].",1,0,2)";

		$estimates_obj->customExecuteQuery($query);
		
		if(count($estimateInfo['rooms']) > 0)
		{
			$query = "INSERT INTO estimate_rooms VALUES";
			$query2 = "INSERT INTO estimate_room_items (`id`,`estimate_id`,`room_id`,`room_template_item_id`,`name`,`date_created`) VALUES";
			
			if(count($estimate_room_items_units_array) > 0)
				$query3 = "INSERT INTO estimate_room_items_units VALUES";
			
			$query_first = false;
			$query_second = false;
			$query_third = false;
			
			$maxIdEstimateRooms = $estimate_rooms_obj->getMaxId();
			$estimateRoomMaxId = $estimate_room_items_obj->getMaxId();
			$estimateRoomItemsUnitsMaxId = $estimate_room_items_units_obj->getMaxId();
			
			for($i=0;$i<count($estimateInfo['rooms']);$i++)
			{	
				$query_first = true;
				$query .= "({$maxIdEstimateRooms},".$_REQUEST['estimatesId'].",".$estimateInfo['rooms'][$i]['roomTemplateId'].",\"".addslashes(stripslashes($estimateInfo['rooms'][$i]['roomName']))."\",NOW(),".$_SESSION['user_id']."),";
				
				for($j=0;$j<count($estimateInfo['rooms'][$i]['items']);$j++)
				{
					for($k=0;$k<count($room_template_items_array);$k++)
					{
						if($room_template_items_array[$k]['name'] == $estimateInfo['rooms'][$i]['items'][$j]['itemName'] && $estimateInfo['rooms'][$i]['roomTemplateId'] == $room_template_items_array[$k]['room_template_id'])
						{
							$room_template_items_id = $room_template_items_array[$k]['id'];
							break;
						}
					}
					
					$query_second = true;
					$query2 .= "(".$estimateRoomMaxId.",".$_REQUEST['estimatesId'].",{$maxIdEstimateRooms},".$room_template_items_id.",'".addslashes(stripslashes($estimateInfo['rooms'][$i]['items'][$j]['itemName']))."',NOW()),";
					
					if(count($estimate_room_items_units_array) > 0)
					{
						for($k=0;$k<count($estimate_room_items_units_array);$k++)
						{
							if($estimate_room_items_units_array[$k]['estimate_room_items_id'] == $estimateInfo['rooms'][$i]['items'][$j]['itemId'])
							{
								$query_third = true;
								$query3 .= "(".$estimateRoomItemsUnitsMaxId.",".$estimatesId.",{$maxIdEstimateRooms},".$estimateRoomMaxId.",".$estimate_room_items_units_array[$k]['work_category_estimates_id'].",'".$estimate_room_items_units_array[$k]['units']."','".$estimate_room_items_units_array[$k]['price_per_unit']."','".addslashes(stripslashes($estimate_room_items_units_array[$k]['scope']))."',2,NOW()),";
								
								$estimateRoomItemsUnitsMaxId += 1;
							}
						}
					}
					
					$estimateRoomMaxId += 1;
				}
				
				$maxIdEstimateRooms += 1;
			}
			
			if($query_first)
			{
				$query = substr($query,0,-1);
				$estimate_rooms_obj->customExecuteQuery($query);
			}
			
			if($query_second)
			{
				$query2 = substr($query2,0,-1);
				$estimate_room_items_obj->customExecuteQuery($query2);
			}
			
			if($query_third)
			{
				$query3 = substr($query3,0,-1);
				$estimate_room_items_units_obj->customExecuteQuery($query3);
			}
		}
	}
}
?>