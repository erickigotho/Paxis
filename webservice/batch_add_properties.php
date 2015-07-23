<?php
if(!isset($_SESSION)){
	session_start();
}

define('__ROOT__', dirname(dirname(__FILE__)));
define('__BASENAME__', basename(__ROOT__));

require_once(__ROOT__ . '/modules/Dynamo.class.php');
require_once(__ROOT__ . '/modules/Tools.class.php');

$result['success'] = false;
$result['message'] = '';

if(trim($_REQUEST['property_id']) != '' && trim($_REQUEST['data']) != '')
{
	$property_array = json_decode($_REQUEST['data']);
	
	if(count($property_array) > 0)
	{
		$community_properties_object = new Dynamo("community_properties");
		$properties_object = new Dynamo("properties");
		
		//get the property template
		$community_properties_array = $community_properties_object->getAll("WHERE id = ".$_REQUEST['property_id']);
		$community_properties_array = $community_properties_array[0];
		
		if(count($community_properties_array) > 0)
		{
			$community_reports_object = new Dynamo("community_reports");
			$reports_object = new Dynamo("reports");
			
			//get the property report if any
			$community_reports_array = $community_reports_object->getAll("WHERE property_id = ".$community_properties_array['id']);
			$community_reports_array = $community_reports_array[0];
			
			//get the subcontractors for the property if any
			$community_subcontractors_assign_object = new Dynamo("community_subcontractors_assign");
			$subcontractors_assign_object = new Dynamo("subcontractors_assign");
			
			$community_subcontractors_assign_array = $community_subcontractors_assign_object->getAll("WHERE property_id = ".$community_properties_array['id']);
			
			//if there are reports there must be rooms	
			if(count($community_reports_array) > 0)
			{				
				$report_id = $community_reports_array['id'];
				
				$community_report_rooms_object = new Dynamo("community_report_rooms");
				$report_rooms_object = new Dynamo("report_rooms");
				
				//fetch all the report rooms
				$community_report_rooms = $community_report_rooms_object->getAll("WHERE report_id = ".$report_id." ORDER BY id");
				
				$community_report_room_items_object = new Dynamo("community_report_room_items");
				$report_room_items_object = new Dynamo("report_room_items");
				
				//fetch the room items
				$community_report_room_items_array = $community_report_room_items_object->getAll("WHERE report_id = ".$report_id." ORDER BY room_id");
				
				//fetch comments
				$community_report_room_item_comments_object = new Dynamo("community_report_room_item_comments");
				$report_room_item_comments_object = new Dynamo("report_room_item_comments");
				
				$community_report_room_item_comments_array = $community_report_room_item_comments_object->getAll("WHERE report_id = ".$report_id);
			}
		}
								
		$tools = new Tools;
		
		for($i=0;$i<count($property_array);$i++)
		{
			//$next_property_id = $properties_object->getMaxId();
			
			$query = "SHOW TABLE STATUS LIKE 'properties'";
			$property_table_array = $properties_object->customFetchQuery($query);
			$next_property_id = $property_table_array[0]['Auto_increment'];
			
			$_REQUEST['name'] = addslashes(stripslashes($property_array[$i]->name));
			$_REQUEST['address'] = addslashes(stripslashes($property_array[$i]->lot_address));
			$_REQUEST['map_link'] = addslashes(stripslashes($property_array[$i]->map_link));
			
			//$tools->convertArrayToRequest($community_properties_array);
							
			$query = "INSERT INTO properties (`id`,`name`,`address`,`city`,`map_link`,`estimates_multiplier`,`zip`,
			`property_type`,`job_type`,`community`,`date_created`,`status`,`created_by`,`emails`,`estimates_emails`)
			VALUES('".$next_property_id."','".$_REQUEST['name']."','".$_REQUEST['address']."','".addslashes(stripslashes($community_properties_array["city"]))."'
			,\"".$_REQUEST['map_link']."\",\"".$community_properties_array["estimates_multiplier"]."\",\"".$community_properties_array["zip"]."\",".$community_properties_array["property_type"]."
			,".$community_properties_array["job_type"].",'".addslashes(stripslashes($community_properties_array["community"]))."',NOW()
			,".$community_properties_array["status"].",".$_SESSION['user_id'].",\"".$community_properties_array["emails"]."\",\"".$community_properties_array["estimates_emails"]."\")";
			
			//end test area
			//add properties
			if($properties_object->customExecuteQuery($query))
			{
				//add subcontractors
				if(count($community_subcontractors_assign_array) > 0)
				{
					$query = "INSERT INTO subcontractors_assign(`sub_contractor_id`,`property_id`,`work_category_id`)VALUES";
					for($j=0;$j<count($community_subcontractors_assign_array);$j++)
					{
						$query .= "(".$community_subcontractors_assign_array[$j]['sub_contractor_id'].",".$next_property_id.",".$community_subcontractors_assign_array[$j]['work_category_id']."),";
					}
					$query = substr($query,0,-1);
									
					$subcontractors_assign_object->customExecuteQuery($query);
				}
				
				if($_REQUEST['estimates'] == 'true')
				{
					$propertyObjDynamo = new Dynamo("properties");
					$query = "UPDATE properties SET in_estimates = 1 WHERE id = $next_property_id";
					$propertyObjDynamo->customExecuteQuery($query);
					
					$estimatesObj = new Dynamo("estimates");
					$estimateId = $estimatesObj->getMaxId();
					
					$query = "INSERT INTO estimates
					(`id`,`property_id`,`date_created`,`reported_by`,`is_submitted`,`is_saved`,`is_closed`,`report_status`)
					VALUES
					(".$estimateId.",".$next_property_id.",NOW(),".$_SESSION['user_id'].",0,1,0,0)";
					$estimatesObj->customExecuteQuery($query);
					
					$query = '';
					$query_extra = '';
					
					$estimateRoomsObj = new Dynamo("estimate_rooms");
					$estimate_room_id = $estimateRoomsObj->getMaxId();
					
					if(count($community_report_room_items_array2) > 0)
						$community_report_room_items_array = $community_report_room_items_array2;
					else
						$community_report_room_items_array2 = $community_report_room_items_array;
						
					for($j=0;$j<count($community_report_rooms);$j++)
					{
						if($j == 0)
							$query = "INSERT INTO estimate_rooms (`id`,`estimate_id`,`room_template_id`,`name`,`date_created`,`created_by`) VALUES";
						
						$query .= "($estimate_room_id,$estimateId,".$community_report_rooms[$j]["room_template_id"].",\"".$community_report_rooms[$j]["name"]."\",NOW(),".$_SESSION['user_id']."),";
						
						for($k=0;$k<count($community_report_room_items_array);$k++)
						{
							if($community_report_room_items_array2[$k]['room_id'] == $community_report_rooms[$j]['id'])
							{
								$community_report_room_items_array[$k]['room_id'] = $estimate_room_id;
							}
						}
						
						$estimate_room_id += 1;
					}
					
					if(trim($query) != '')
					{
						$query = substr($query,0,-1);
						$estimatesObj->customExecuteQuery($query);
					}
					
					$estimate_room_items = new Dynamo("estimate_room_items");
					$count = 0;
					$query = '';
					for($j=0;$j<count($community_report_room_items_array);$j++)
					{
						if($j == 0 || $count == 0)	
						{
							if(trim($query) != '')
							{
								$query = substr($query,0,-1);
								$estimate_room_items->customExecuteQuery($query);
							}
							
							$query = "INSERT INTO estimate_room_items (`estimate_id`,`room_id`,`room_template_item_id`,`name`,`date_created`) VALUES";
						}
						
						$query .= "($estimateId,".$community_report_room_items_array[$j]['room_id'].",".$community_report_room_items_array[$j]['room_template_item_id'].",\"".$community_report_room_items_array[$j]['name']."\",NOW()),";
						
						if($j == (count($community_report_room_items_array) - 1))
						{
							$query = substr($query,0,-1);
							$estimate_room_items->customExecuteQuery($query);
						}
						
						$count += 1;
						
						if($count >= 100)
							$count = 0;
					}
				}
				else
				{
					if(count($community_reports_array) > 0)
					{
						$report_id = $reports_object->getMaxId();
						$query = "INSERT INTO reports VALUES({$report_id},{$next_property_id},NOW(),'',0,".$community_reports_array['reported_by'].",0,1,0,0)";
						if($reports_object->customExecuteQuery($query))
						{
							if(count($community_report_rooms) > 0)
							{
								$max_report_rooms_id = $report_rooms_object->getMaxId();
								
								$query = "SELECT MAX(room_id)+1 AS room_id FROM report_room_items";
								$array_room_id_max = $report_room_items_object->customFetchQuery($query);
								
								if($array_room_id_max[0]['room_id'] > $max_report_rooms_id)
									$max_report_rooms_id = $array_room_id_max[0]['room_id'];
								
								$max_report_room_items_id = $report_room_items_object->getMaxId();
								
								$query = "SELECT MAX(room_item_id)+1 AS room_item_id FROM report_room_item_comments";
								$array_room_item_comments_max = $report_room_item_comments_object->customFetchQuery($query);
								
								if($array_room_item_comments_max[0]['room_item_id'] > $max_report_room_items_id)
									$max_report_room_items_id = $array_room_item_comments_max[0]['room_item_id'];	
								
								$max_report_room_item_comments_id = $report_room_item_comments_object->getMaxId();
								
								if(count($community_report_rooms) > 0)
									$query = "INSERT INTO `report_rooms` VALUES";
								
								if(count($community_report_room_items_array) > 0)
									$query_room_items = "INSERT INTO `report_room_items` VALUES";
								
								if(count($community_report_room_item_comments_array) > 0)
									$query_room_items_comments = "INSERT INTO `report_room_item_comments` VALUES";
								
								//inserting report rooms
								$count = 0;
								$k_count = 0;
								$m_count = 0;
								
								for($j=0;$j<count($community_report_rooms);$j++)
								{
									$count += 1;
									$query .= "({$max_report_rooms_id},{$report_id},".$community_report_rooms[$j]['room_template_id'].",\"".addslashes($community_report_rooms[$j]['name'])."\",NOW(),".$community_report_rooms[$j]['created_by']."),";
									
									//inserting report room items
									for($k=0;$k<count($community_report_room_items_array);$k++)
									{
										if($community_report_room_items_array[$k]['room_id'] == $community_report_rooms[$j]['id'])
										{
											$k_count += 1;
											$query_room_items .= "({$max_report_room_items_id},{$report_id},{$max_report_rooms_id},".$community_report_room_items_array[$k]['room_template_item_id'].",\"".addslashes(stripslashes($community_report_room_items_array[$k]['name']))."\",".$community_report_room_items_array[$k]['status_id'].",0,NOW()),";
											
											//inserting report room items comments
											for($m=0;$m<count($community_report_room_item_comments_array);$m++)
											{
												if($community_report_room_item_comments_array[$m]['room_item_id'] == $community_report_room_items_array[$k]['id'])
												{
													$m_count += 1;
													$query_room_items_comments .= "(".$max_report_room_item_comments_id.",\"".addslashes(stripslashes($community_report_room_item_comments_array[$m]['comment']))."\",".$community_report_room_item_comments_array[$m]['user_id'].",{$max_report_room_items_id},{$report_id},NOW()),";
													
													if($m_count >= 100)
													{
														$query_room_items_comments = substr($query_room_items_comments,0,-1);
														$report_room_item_comments_object->customExecuteQuery($query_room_items_comments);
														$m_count = 0;
														$query_room_items_comments = "INSERT INTO `report_room_item_comments` VALUES";
													}
													
													$max_report_room_item_comments_id += 1;
												}
											}
									
											if($k_count >= 100)
											{
												$query_room_items = substr($query_room_items,0,-1);
												$report_room_items_object->customExecuteQuery($query_room_items);
												$k_count = 0;
												$query_room_items = "INSERT INTO `report_room_items` VALUES";
											}
											
											$max_report_room_items_id += 1;
										}
									}
									
									if($count >= 100)
									{
										$query = substr($query,0,-1);
										$report_rooms_object->customExecuteQuery($query);
										$count = 0;
										$query = "INSERT INTO `report_rooms` VALUES";
									}
									
									$max_report_rooms_id += 1;
								}
								
								if(count($community_report_rooms) > 0 && trim($query) != '' && stristr($query,","))
								{
									$query = substr($query,0,-1);
									$report_rooms_object->customExecuteQuery($query);
								}
								
								if(count($community_report_room_items_array) > 0)
								{
									$query_room_items = substr($query_room_items,0,-1);
									$report_room_items_object->customExecuteQuery($query_room_items);
								}
								
								if(count($community_report_room_item_comments_array) > 0)
								{
									$query_room_items_comments = substr($query_room_items_comments,0,-1);
									$report_room_item_comments_object->customExecuteQuery($query_room_items_comments);
								}
							}
						}
					}
				}
				//success message
				$result['success'] = true;
				$result['message'] = "You've successfully added the properties!";
			}
		}
	}
}

header('Content-type: application/json');
echo json_encode($result);
?>