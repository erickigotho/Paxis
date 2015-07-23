<?php
if(!isset($_SESSION)){
	session_start();
}

define('__ROOT__', dirname(dirname(__FILE__)));
define('__BASENAME__', basename(__ROOT__));

require_once(__ROOT__ . '/modules/Dynamo.class.php');

$complex_properties_object = new Dynamo("complex_properties");
$complex_reports_object = new Dynamo("complex_reports");
$complex_report_rooms_object = new Dynamo("complex_report_rooms");
$complex_report_room_items_object = new Dynamo("complex_report_room_items");
$complex_report_room_item_comments = new Dynamo("complex_report_room_item_comments");

$result['success'] = false;
$result['message'] = '';

/*ob_start();
print '<pre>';
print_r($_REQUEST);
print_r(json_decode($_REQUEST['data']));
print '</pre>';
$string = ob_get_clean();

$fp = fopen("test.txt","w");
if($fp)
{
	fwrite($fp,$string);
	fclose($fp);
}
exit;*/

if(trim($_REQUEST['community']) != '')
{
	$_REQUEST['state'] = null;
	$_REQUEST['property_type'] = $_REQUEST['propertyType'];
	$_REQUEST['job_type'] = $_REQUEST['jobType'];
	$_REQUEST['created_by'] = $_REQUEST['userId'];
	$_REQUEST['emails'] = $_REQUEST['emails'];
	$_REQUEST['date_created'] = date("Y-m-d H:i:s",time());
	
	$complex_property_id = $complex_properties_object->getMaxId();
	if($complex_properties_object->add())
	{
		$result['success'] = true;
		$result['message'] = 'Complex successfully added!';
		$result['property_id'] = $complex_property_id;
		
		if(trim($_REQUEST['data']) != '')
		{
			$_REQUEST['property_id'] = $complex_property_id;
			$_REQUEST['date_reported'] = date("Y-m-d H:i:s",time());
			$_REQUEST['status_id'] = 0;
			$_REQUEST['user_id'] = $_REQUEST['created_by'] = $_REQUEST['reported_by'] = $_REQUEST['userId'];
			$_REQUEST['is_submitted'] = 0;
			$_REQUEST['is_saved'] = 0;
			$_REQUEST['is_closed'] = 0;
			
			$report_id = $_REQUEST['report_id'] = $complex_reports_object->getMaxId();
			$array_data = json_decode($_REQUEST['data']);
			if(count($array_data) > 0)
			{
				if($complex_reports_object->add())
				{			
					if(count($array_data) > 0)
					{
						for($i=0;$i<count($array_data);$i++)
						{
							$_REQUEST['room_template_id'] = $array_data[$i]->roomTemplateId;
							$_REQUEST['name'] = $array_data[$i]->roomName;
							$_REQUEST['date_created'] = date("Y-m-d H:i:s",time());
							
							$_REQUEST['room_id'] = $complex_report_rooms_object->getMaxId();
							if($complex_report_rooms_object->add())
							{
								for($j=0;$j<count($array_data[$i]->roomItems);$j++)
								{
									$roomItems = $array_data[$i]->roomItems;
									$_REQUEST['room_template_item_id'] = $roomItems[$j]->roomTemplateItemId;
									$_REQUEST['name'] = $roomItems[$j]->name;
									$_REQUEST['status_id'] = $roomItems[$j]->statusId;
									$_REQUEST['work_category_id'] = $roomItems[$j]->work_category_id;
									$_REQUEST['date'] = $_REQUEST['date_created'] = date("Y-m-d H:i:s",time());
									$_REQUEST['comment'] = $roomItems[$j]->comment;
									
									$complex_report_room_items_id = $complex_report_room_items_object->getMaxId();
									if($complex_report_room_items_object->add())
									{
										if(trim($_REQUEST['comment']) != '')
										{
											$_REQUEST['room_item_id'] = $complex_report_room_items_id;
											if($complex_report_room_item_comments->add())
											{}
											else
											{
												$result['success'] = false;
												$result['message'] = 'Sorry, there has been a problem processing your request.1';
											}
										}
									}
									else
									{
										$result['success'] = false;
										$result['message'] = 'Sorry, there has been a problem processing your request.3';			
									}
								}
							}
							else
							{
								$result['success'] = false;
								$result['message'] = 'Sorry, there has been a problem processing your request.4';			
							}	
						}
					}
				}
				else
				{
					$result['success'] = false;
					$result['message'] = 'Sorry, there has been a problem processing your request.5';
				}
			}
		}
	}
}
else
{
	$result['success'] = false;
	$result['message'] = 'Sorry, there has been a problem processing your request.6';
}

header('Content-type: application/json');
echo json_encode($result);
?>