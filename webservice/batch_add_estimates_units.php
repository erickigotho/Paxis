<?php
if(!isset($_SESSION)){
	session_start();
}

define('__ROOT__', dirname(dirname(__FILE__)));
define('__BASENAME__', basename(__ROOT__));

require_once(__ROOT__ . '/modules/Dynamo.class.php');

$result['success'] = false;
$result['message'] = '';

$estimate_room_items_units_obj = new Dynamo("estimate_room_items_units");
$subcontractors_assign_obj = new Dynamo("subcontractors_assign");

if(trim($_REQUEST['propertyId']) != ''  && trim($_REQUEST['work_category_id']) != '')
{
	$query = "DELETE FROM subcontractors_assign WHERE property_id = ".$_REQUEST['propertyId']." AND work_category_id = ".$_REQUEST['work_category_id'];
	$subcontractors_assign_obj->customExecuteQuery($query);
}

if(trim($_REQUEST['subcontractor_details']) != '')
{
	$subcontractor_details_array = json_decode($_REQUEST['subcontractor_details']);
	if(count($subcontractor_details_array) > 0)
	{
		$query = "INSERT INTO subcontractors_assign (`sub_contractor_id`,`property_id`,`work_category_id`) VALUES";
		for($i=0;$i<count($subcontractor_details_array);$i++)
		{	
			$query .= "(".$subcontractor_details_array[$i]->subcontractorId.",".$_REQUEST['propertyId'].",".$_REQUEST['work_category_id']."),";
		}
		
		$query = substr($query,0,-1);
		$subcontractors_assign_obj->customExecuteQuery($query);
	}
}

if(trim($_POST['data']) != '')
{	
	$arrayData = json_decode($_POST['data']);

	if(count($arrayData) > 0)
	{
		$maxId = $estimate_room_items_units_obj->getMaxId();
		$query = '';
		$query2 = '';
		for($i=0;$i<count($arrayData);$i++)
		{
			if($arrayData[$i]->estimate_room_items_units_id == 0)
			{
				if(trim($query) == '')
					$query = "INSERT INTO estimate_room_items_units VALUES";
					
				$query .= "({$maxId},".$_POST['estimate_id'].",".$_POST['room_id'].",".$_REQUEST['estimate_room_items_id'].",".$arrayData[$i]->work_category_estimates_id.",\"".$arrayData[$i]->unit_value."\",\"".$arrayData[$i]->price_per_unit."\",\"".addslashes(stripslashes($arrayData[$i]->scope))."\",2,NOW()),";
				
				$maxId += 1;
			}
			else
			{
				$query2 = "UPDATE estimate_room_items_units SET units = \"".$arrayData[$i]->unit_value."\",price_per_unit = \"".$arrayData[$i]->price_per_unit."\",scope = \"".addslashes(stripslashes($arrayData[$i]->scope))."\" WHERE estimate_id = ".$_POST['estimate_id']." AND estimate_room_items_id = ".$_POST['estimate_room_items_id']." AND  work_category_estimates_id = ".$arrayData[$i]-> work_category_estimates_id . " AND room_id = ".$_POST['room_id'];
				$estimate_room_items_units_obj->customExecuteQuery($query2);
				$query2 = '';	
			}
		}
		
		if(trim($query) != '')
		{
			$query = substr($query,0,-1);
			$estimate_room_items_units_obj->customExecuteQuery($query);
		}
		
		if(trim($_POST["estimate_id"]) != '' && trim($_REQUEST["estimate_room_items_id"]) != '')
		{	
			$estimatesId = $_POST["estimate_id"];
			$estimate_room_items_id = $_REQUEST["estimate_room_items_id"];
			
			$query_estimates = "SELECT estimate_room_items_units.*, work_category_estimates.item_name, work_category_estimates.unit_of_measure FROM estimate_room_items_units 
			INNER JOIN  work_category_estimates ON estimate_room_items_units.work_category_estimates_id =  work_category_estimates.id 
			WHERE estimate_room_items_units.estimate_id = ".$estimatesId." AND estimate_room_items_units.estimate_room_items_id = ".$estimate_room_items_id." AND estimate_room_items_units.units > 0";
			
			$results = mysql_query($query_estimates) or die(mysql_error());
			$num_of_items = mysql_num_rows($results);
			
			if($num_of_items > 0)
				$result["comment"] = '<span class="icon-comment" style="cursor:pointer;" onClick="getLineItemEstimates('.$estimatesId.",".$_POST["itemTemplateId"].",".$_POST["room_id"].",".$_POST["itemId"].",".$_POST["work_category_id"].",'".$_POST["item_name"]."','".$_POST["tmpRoomIndex"]."','".$_POST["roomItemIndex"].'\');"></span><span class="badge-small" style="cursor:pointer;" onClick="getLineItemEstimates('.$estimatesId.",".$_POST["itemTemplateId"].",".$_POST["room_id"].",".$_POST["itemId"].",'".$_POST["item_name"]."','".$_POST["tmpRoomIndex"]."','".$_POST["roomItemIndex"].'\');">'.$num_of_items.'</span></div>';
			else
				$result["comment"] = '&nbsp;';
		}
		else
			$result["comment"] = '&nbsp;';
		
		$result['success'] = true;
		$result['message'] = 'Successfully added estimates';
	}
}

header('Content-type: application/json');
echo json_encode($result);
?>