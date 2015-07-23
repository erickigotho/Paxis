<?php 
require_once(dirname(dirname(__FILE__)) . '/config/main.config.php');
require_once(dirname(dirname(__FILE__)) . '/helpers/Captcha.class.php');
require_once(dirname(dirname(__FILE__)) . '/modules/Dynamo.class.php');


class Report {
    public function __construct(){  
    }  
	
	public function validateEmail($email){  
        $test = preg_match(EMAIL_PATTERN, $email);  
        return $test;  
    }
	
	public function getReports($userId, $isJson = true) {
		$result['success'] = false;
		$result['message'] = '';
		$result['data'] = '';
		
		$data = array();
		
		$mysqli = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
		if ($mysqli->connect_errno) {
			$result['message'] = "Failed to connect to MySQL: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error;
		}
		
		$sql = "SELECT
					 reports.id
					,users.first_name
					,users.last_name
					,reports.date_reported
					,companies.name
					,reports.is_submitted
					,reports.is_saved
					,reports.is_closed
					,properties.name
					,properties.status
				FROM reports
					INNER JOIN users ON users.id = reports.reported_by
					INNER JOIN companies ON companies.id = users.company_id
					INNER JOIN properties ON properties.id = reports.property_id
				WHERE reports.reported_by=?
				ORDER BY reports.date_reported DESC
				";
		
		if ($stmt = $mysqli->prepare($sql)) {
			$stmt->bind_param("i", $userId);
			$stmt->execute();
			$stmt->bind_result($reportId, $firstName, $lastName, $dateReported, $companyName, $isSubmitted, $isSaved, $isClosed, $properyName, $status);
			
			$dataCtr = 0;
			while ($stmt->fetch()) {
				$data[$dataCtr] = array(
								 'id'=>$reportId
								,'firstName'=>$firstName
								,'lastName'=>$lastName
								,'dateReported'=>$dateReported
								,'companyName'=>$companyName
								,'isSubmitted'=>$isSubmitted
								,'isSaved'=>$isSaved
								,'isClosed'=>$isClosed
								,'properyName'=>$properyName
								,'status'=>$status
							);
				$dataCtr ++;
			}
			
			$stmt->close();	
			
			$result['success'] = true;
			$result['data'] = $data;
		} else {
			$result['message'] = "Sorry, there has been a problem processing your request.";
		}
		
		$mysqli->close();
		
		if($isJson) {
			header('Content-type: application/json');
			echo json_encode($result);
		} else {
			return $data;
		}
	}
	
	public function getReportStatus($isJson = true) {
		$result['success'] = false;
		$result['message'] = '';
		$result['data'] = '';
		
		$data = array();
		
		$mysqli = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
		if ($mysqli->connect_errno) {
			$result['message'] = "Failed to connect to MySQL: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error;
		}
		
		$sql = "SELECT
					 id
					,name
					,class
				FROM report_status
				";
		
		if ($stmt = $mysqli->prepare($sql)) {
			$stmt->execute();
			$stmt->bind_result($id, $name, $className);
			
			$dataCtr = 0;
			while ($stmt->fetch()) {
				$data[$dataCtr] = array(
								 'id'=>$id
								,'name'=>$name
								,'className'=>$className
							);
				$dataCtr ++;
			}
			
			$stmt->close();	
			
			$result['success'] = true;
			$result['data'] = $data;
		} else {
			$result['message'] = "Sorry, there has been a problem processing your request.";
		}
		
		$mysqli->close();
		
		if($isJson) {
			header('Content-type: application/json');
			echo json_encode($result);
		} else {
			return $data;
		}
	}
	
	public function getPreviousReportId($propertyId, $isJson = true) {
		$result['success'] = false;
		$result['message'] = '';
		$result['data'] = '';
		
		$reportId = 0;
		
		$mysqli = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
		if ($mysqli->connect_errno) {
			$result['message'] = "Failed to connect to MySQL: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error;
		}
		
		$sql = "SELECT id
				FROM reports
				WHERE property_id=?
				ORDER BY id DESC 
				LIMIT 1";
		
		if ($stmt = $mysqli->prepare($sql)) {
			$stmt->bind_param('i', $propertyId);
			$stmt->execute();
			$stmt->bind_result($id);
			$stmt->fetch();
			
			$reportId = $id;
			
			$result['success'] = true;
			$result['reportId'] = $reportId;
			
			$stmt->close();	
		} else {
			$result['message'] = "Sorry, there has been a problem processing your request.";
		}
		
		$mysqli->close();
		
		if($isJson) {
			header('Content-type: application/json');
			echo json_encode($result);
		} else {
			return $reportId;
		}
	}
	
	public function getReportDetails($reportId, $isJson = true,$array_users=false,$array_sub_contractors=false) 
	{
		$result['success'] = false;
		$result['message'] = '';
		$result['data'] = '';
		
		$data = array();
		
		$mysqli = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
		if ($mysqli->connect_errno) 
		{
			$result['message'] = "Failed to connect to MySQL: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error;
		}
		
		$propertyId = 0;
		$sql = "SELECT	
			 properties.id
			,properties.name
			,properties.address
			,properties.city
			,properties.state
			,properties.map_link
			,properties.emails
			,properties.property_type
			,properties.job_type
			,properties.community
			,reports.status_id
			,reports.date_reported
			,reports.reported_by
			,reports.subcontractor
		FROM reports
			INNER JOIN properties ON properties.id = reports.property_id
		WHERE reports.id = ?
		";
		$placeholder = $reportId;
		
		if ($stmt = $mysqli->prepare($sql)) 
		{
			$stmt->bind_param("i", $placeholder);
			
			if($stmt->execute()) 
			{
				$stmt->bind_result( $propertyId
									, $propertyName
									, $propertyAddress
									, $propertyCity
									, $propertyState
									, $propertyMapLink
									, $propertyEmails
									, $propertyType
									, $propertyJobType
									, $propertyCommunity
									, $reportStatusId
									, $reportDate
									, $reported_by
									, $subcontractor);
				
				
				while ($stmt->fetch()) 
				{
					if($subcontractor == 1)
					{
						$data = array(
										'propertyId'=>$propertyId
										,'propertyName'=>$propertyName
										,'propertyAddress'=>$propertyAddress
										,'propertyCity'=>$propertyCity
										,'propertyState'=>$propertyState
										,'propertyMapLink'=>$propertyMapLink
										,'propertyEmails'=>$propertyEmails
										,'propertyType'=>$propertyType
										,'propertyJobType'=>$propertyJobType
										,'propertyCommunity'=>$propertyCommunity
										,'reportStatusId'=>$reportStatusId
										,'firstName'=>$array_sub_contractors[$reported_by]['first_name']
										,'lastName'=>$array_sub_contractors[$reported_by]['last_name']
										,'reportDate'=>$reportDate
										,'reported_by'=>$reported_by
										,'subcontractor'=>$subcontractor
									);
									
					}
					else
					{
						$data = array(
										 'propertyId'=>$propertyId
										,'propertyName'=>$propertyName
										,'propertyAddress'=>$propertyAddress
										,'propertyCity'=>$propertyCity
										,'propertyState'=>$propertyState
										,'propertyMapLink'=>$propertyMapLink
										,'propertyEmails'=>$propertyEmails
										,'propertyType'=>$propertyType
										,'propertyJobType'=>$propertyJobType
										,'propertyCommunity'=>$propertyCommunity
										,'reportStatusId'=>$reportStatusId
										,'firstName'=>$array_users[$reported_by]['first_name']
										,'lastName'=>$array_users[$reported_by]['last_name']
										,'reportDate'=>$reportDate
										,'reported_by'=>$reported_by
										,'subcontractor'=>$subcontractor
									);
					}
				}
			}
			
			if($propertyId == 0)
			{
				$propertyId = $_REQUEST['propertyId'];
				if(trim($propertyId) != '')
				{
					$placeholder = $propertyId;
					$sql = "SELECT	
								 properties.id
								,properties.name
								,properties.address
								,properties.city
								,properties.state
								,properties.map_link
								,properties.emails
								,properties.property_type
								,properties.job_type
								,properties.community
								,reports.status_id
								,reports.date_reported
								,reports.reported_by
								,reports.subcontractor
							FROM reports
							INNER JOIN properties ON properties.id = reports.property_id
							WHERE properties.id = ?
							";
					$placeholder = $propertyId;
					if ($stmt = $mysqli->prepare($sql)) 
					{
						$stmt->bind_param("i", $placeholder);
						
						if($stmt->execute()) 
						{
							$stmt->bind_result( $propertyId
												, $propertyName
												, $propertyAddress
												, $propertyCity
												, $propertyState
												, $propertyMapLink
												, $propertyEmails
												, $propertyType
												, $propertyJobType
												, $propertyCommunity
												, $reportStatusId
												, $reportDate
												, $reported_by
												, $subcontractor);
							
							
							while ($stmt->fetch()) 
							{
								if($subcontractor == 1)
								{
									$data = array(
										'propertyId'=>$propertyId
										,'propertyName'=>$propertyName
										,'propertyAddress'=>$propertyAddress
										,'propertyCity'=>$propertyCity
										,'propertyState'=>$propertyState
										,'propertyMapLink'=>$propertyMapLink
										,'propertyEmails'=>$propertyEmails
										,'propertyType'=>$propertyType
										,'propertyJobType'=>$propertyJobType
										,'propertyCommunity'=>$propertyCommunity
										,'reportStatusId'=>$reportStatusId
										,'firstName'=>$array_sub_contractors[$reported_by]['first_name']
										,'lastName'=>$array_sub_contractors[$reported_by]['last_name']
										,'reportDate'=>$reportDate
										,'reported_by'=>$reported_by
										,'subcontractor'=>$subcontractor
									);
								}
								else
								{
									$data = array(
										 'propertyId'=>$propertyId
										,'propertyName'=>$propertyName
										,'propertyAddress'=>$propertyAddress
										,'propertyCity'=>$propertyCity
										,'propertyState'=>$propertyState
										,'propertyMapLink'=>$propertyMapLink
										,'propertyEmails'=>$propertyEmails
										,'propertyType'=>$propertyType
										,'propertyJobType'=>$propertyJobType
										,'propertyCommunity'=>$propertyCommunity
										,'reportStatusId'=>$reportStatusId
										,'firstName'=>$array_users[$reported_by]['first_name']
										,'lastName'=>$array_users[$reported_by]['last_name']
										,'reportDate'=>$reportDate
										,'reported_by'=>$reported_by
										,'subcontractor'=>$subcontractor
									);
								}
							}
						}
					}
				}	
			}
			
			$stmt->close();	
			
			//get estimates
			$query = "SELECT MAX(id) AS id FROM estimates WHERE property_id = ".$data['propertyId'];
			$estimatesObj = new Dynamo("estimates");
			$arrayEstimate = $estimatesObj->customFetchOne($query);
			$estimateId = $arrayEstimate['id'];
			
			//GET ROOMS AND ITEMS
			$rooms = array();
			$roomsSql = "SELECT
							 report_rooms.id AS roomId
							,report_rooms.room_template_id
							,report_rooms.name AS roomName
						FROM report_rooms
						WHERE report_rooms.report_id = ".$reportId."
						ORDER BY report_rooms.id
					";
			
			$rooms = array();		
			$dynamo = new Dynamo("report_rooms");
			
			//all room information
			$rooms = $dynamo->customFetchQuery($roomsSql);
			if(count($rooms) > 0) 
			{	
				//get room ids
				$arrayRoomIds = array();
				for($i=0;$i<count($rooms);$i++)
				{
					$rooms[$i]['roomTemplateId'] = $rooms[$i]['room_template_id'];
					$arrayRoomIds[] = $rooms[$i]['roomId'];
					$arrayRoomName[] = $rooms[$i]['roomName'];
				}
				
				$stringRoomIds = implode(",",$arrayRoomIds);
				$stringRoomName = "\"".implode("\",\"",$arrayRoomName)."\"";
				
				//get room items
				$roomItems = array();
				
				$itemsSql = "SELECT
								 report_room_items.id AS itemId
								,report_room_items.room_id
								,report_room_items.room_template_item_id AS itemTemplateId
								,report_room_items.name AS itemName
								,report_room_items.status_id AS statusId
								,report_room_items.is_estimate AS isEstimate
								,report_status.class AS statusClass
								,report_status.name AS statusName
								,room_template_items.work_category_id AS work_category_id
							FROM report_room_items
							   LEFT JOIN report_status 
										  ON report_status.id = report_room_items.status_id
							INNER JOIN room_template_items ON room_template_items.id = report_room_items.room_template_item_id
							WHERE report_room_items.room_id IN (".$stringRoomIds.")
							ORDER BY report_room_items.name
						";
						
				$roomItems = $dynamo->customFetchQuery($itemsSql);
				
				if(count($roomItems) > 0)
				{
					$query = '';
					$itemIdArray = array();
					$arrayRoomName = array();
					$arrayTemplateId = array();
					
					for($i=0;$i<count($roomItems);$i++)
					{
						$itemIdArray[] = $roomItems[$i]['itemId'];
						$itemNameArray[] = $roomItems[$i]['itemName'];
						
						$arrayTemplateId[] = $roomItems[$i]['itemTemplateId'];
						
						if($estimateId > 0 && $roomItems[$i]['isEstimate'] == 0)
						{
							for($j=0;$j<count($rooms);$j++)
							{
								if($rooms[$j]['roomId'] == $roomItems[$i]['room_id'])
								{
									$arrayRoomName[] = $rooms[$j]['roomName'];
									//break;
								}
							}
						}
					}
					
					if(count($arrayRoomName)> 0)
					{
						//Get estimate line items
						$query = "SELECT estimate_room_items_units.id AS estimate_room_items_units_id,
						estimate_room_items_units.status_id,room_template_estimates.item_name, 
						estimate_room_items.room_id,estimate_room_items.room_template_item_id
						FROM estimate_room_items_units INNER JOIN estimate_room_items 
						ON estimate_room_items_units.estimate_room_items_id = estimate_room_items.id
						INNER JOIN room_template_estimates 
						ON room_template_estimates.id = estimate_room_items_units.room_template_estimates_id
						INNER JOIN estimate_rooms ON estimate_rooms.id = estimate_room_items_units.room_id
						WHERE estimate_room_items.room_template_item_id IN(".implode(",",$arrayTemplateId).") 
						AND estimate_room_items.estimate_id = ".$estimateId." 
						AND estimate_room_items_units.estimate_id = ".$estimateId." 
						AND estimate_rooms.name IN(\"".implode("\",\"",$arrayRoomName)."\")";
						
						$array_room_items_unit = array();
						$array_room_items_unit = $dynamo->customFetchQuery($query);
						
						for($i=0;$i<count($array_room_items_unit);$i++)
							for($j=0;$j<count($roomItems);$j++)
								if($array_room_items_unit[$i]['room_template_item_id'] == $roomItems[$j]['itemTemplateId'] && $roomItems[$j]['itemName'] != $array_room_items_unit[$i]['item_name'])
								//if($array_room_items_unit[$i]['room_template_item_id'] == $roomItems[$j]['itemTemplateId'])
									$roomItems[$j]['arrayRoomItemsUnits'][] = $array_room_items_unit[$i];
					}
					
					$itemIdString = implode(",",$itemIdArray);
					$itemNameString = "\"".implode("\",\"",$itemNameArray)."\"";
					
					//GET THE LAST ITEM COMMENT
					$commentsSql = "SELECT
										 report_room_item_comments.id
										,report_room_item_comments.comment
										,report_room_item_comments.date AS commentDate
										,report_room_item_comments.room_item_id
										,report_room_item_comments.order
									FROM report_room_item_comments
									WHERE report_room_item_comments.room_item_id IN(".$itemIdString.")
									ORDER BY report_room_item_comments.`order`";
					
					$arrComments = $dynamo->customFetchQuery($commentsSql);
					if(count($arrComments) > 0)
					{
						for($i=0;$i<count($arrComments);$i++)
						{
							for($j=0;$j<count($roomItems);$j++)
							{
								if($arrComments[$i]['room_item_id'] == $roomItems[$j]['itemId'])
								{
									$arrComments[$i]['comment'] = str_replace("\"","'",$arrComments[$i]['comment']);
									$arrComments[$i][1] = str_replace("\"","'",$arrComments[$i][1]);
									$roomItems[$j]['comments'][] = $arrComments[$i];
								}
							}
						}
					}
					
					//GET THE LAST ITEM IMAGE
					$commentsSql = "SELECT
										 image_name
										,date AS imageUploadDate
										,room_item_id
										,`order`
									FROM report_images
									WHERE room_item_id IN(".$itemIdString.")
									ORDER BY `order`";
					
					$arrComments = $dynamo->customFetchQuery($commentsSql);
					if(count($arrComments) > 0)
					{
						for($i=0;$i<count($arrComments);$i++)
							for($j=0;$j<count($roomItems);$j++)
								if($arrComments[$i]['room_item_id'] == $roomItems[$j]['itemId'])
									$roomItems[$j]['images'][$arrComments[$i]['order']][] = $arrComments[$i];
					}
					
					$roomNameArray = array();
					for($i=0;$i<count($roomItems);$i++)
						for($j=0;$j<count($rooms);$j++)
							if($rooms[$j]['roomId'] == $roomItems[$i]['room_id'])
								$roomNameArray[] = $rooms[$j]['roomName'];
								
					if(count($roomNameArray) > 0)
					{
						$roomNameString = "\"".implode("\",\"",$roomNameArray)."\"";
						//GET ALL THE COMMENTS PER ITEM
						$commentsSql = "SELECT
										 c.id AS itemId
										 ,i.id AS roomItemId
										,i.name AS itemName
										,c.comment AS comment
										,c.order AS `order`
										,c.date AS commentDate
										,u.first_name AS firstName
										,u.last_name AS lastName
										,i.room_template_item_id AS room_template_item_id
										,rm.name AS room_name
									FROM report_room_item_comments c
										  INNER JOIN report_room_items i ON i.id = c.room_item_id
										  INNER JOIN report_rooms rm ON rm.id = i.room_id
										  INNER JOIN reports r ON r.id = rm.report_id
										  INNER JOIN users u ON u.id = c.user_id
									WHERE r.property_id = ".$propertyId."
									  AND i.room_template_item_id IN( ".implode(",",$arrayTemplateId).")
									  AND r.is_submitted = 1
									  AND i.name IN(".$itemNameString.")
									  AND rm.name IN(".$roomNameString.") 
									  ORDER BY c.order";
								  
						$arrComments = $dynamo->customFetchQuery($commentsSql);
						
						if(count($arrComments) > 0)
						{
							for($i=0;$i<count($arrComments);$i++)
							{
								for($j=0;$j<count($roomItems);$j++)
								{
									if($arrComments[$i]['room_template_item_id'] == $roomItems[$j]["itemTemplateId"])
									{
										$arrComments[$i]['commentDate'] = date('m/d/Y g:ia', strtotime($arrComments[$i]['commentDate']));
										$roomItems[$j]['comment_thread'][] = $arrComments[$i];
									}
								}
							}
						}
						
						$imageSql = "SELECT
											 c.id AS itemId
											,c.room_item_id AS roomItemId
											,i.name AS itemName
											,c.image_name AS imageName
											,c.order AS `order`
											,c.date
											,u.first_name AS firstName
											,u.last_name AS lastName
											,i.room_template_item_id AS room_template_item_id
											,rm.name AS room_name
										FROM report_images c
											  INNER JOIN report_room_items i ON i.id = c.room_item_id
											  INNER JOIN report_rooms rm ON rm.id = i.room_id
											  INNER JOIN reports r ON r.id = rm.report_id
											  INNER JOIN users u ON u.id = c.user_id
										WHERE r.property_id = ".$propertyId."
										  AND i.room_template_item_id IN( ".implode(",",$arrayTemplateId).")
										  AND r.is_submitted = 1
										  AND i.name IN(".$itemNameString.")
										  AND rm.name IN(".$roomNameString.")
										  ORDER BY c.order";
						
						$arrComments = $dynamo->customFetchQuery($imageSql);
						
						if(count($arrComments) > 0)
						{
							for($i=0;$i<count($arrComments);$i++)
							{
								for($j=0;$j<count($roomItems);$j++)
								{
									if($arrComments[$i]['room_template_item_id'] == $roomItems[$j]["itemTemplateId"])
									{
										$arrComments[$i]['imageUploadDate'] = date('m/d/Y g:ia', strtotime($arrComments[$i]['date']));
										$roomItems[$j]['image_thread'][] = $arrComments[$i];
									}
								}
							}
						}
					}
					
					for($i=0;$i<count($roomItems);$i++)
					{
						for($j=0;$j<count($rooms);$j++)
						{
							if($rooms[$j]['roomId'] == $roomItems[$i]['room_id'])
							{
								if(!$roomItems[$i]['arrayRoomItemsUnits'])
									$roomItems[$i]['arrayRoomItemsUnits'] = array();
								
								if(!$roomItems[$i]['comments'])
									$roomItems[$i]['comments'] = array();
								
								if(!$roomItems[$i]['images'])
									$roomItems[$i]['images'] = array();
										
								if(!$roomItems[$i]['comment_thread'])
									$roomItems[$i]['comment_thread'] = array();
								
								if(!$roomItems[$i]['image_thread'])
									$roomItems[$i]['image_thread'] = array();
										
								$rooms[$j]['items'][] = $roomItems[$i];
							}
						}
					}
				}
				
					
				$data['rooms'] = $rooms;
			}
			
			//GET REPORT COMMENTS
			$sqlReportComment = "SELECT	
									  report_comments.id,
									  report_comments.comment,
									  users.first_name,
									  users.last_name,
									  report_comments.date,
									  reports.is_submitted,
									  reports.is_saved
								FROM report_comments
									 INNER JOIN users ON users.id = report_comments.user_id
									 INNER JOIN reports ON reports.id = report_comments.report_id
								WHERE report_comments.property_id=?
								";
		
			$arrReportComments = array();
			
			if($reportCommentStmt = $mysqli->prepare($sqlReportComment)) 
			{
				$reportCommentStmt->bind_param("i", $propertyId);
				
				if($reportCommentStmt->execute()) 
				{
					$reportCommentStmt->bind_result( 
										  $reportCommentId
										, $reportComment
										, $reportCommentFirstName
										, $reportCommentLastName
										, $reportCommentDate
										, $reportIsSubmitted
										, $reportIsSaved
										);
					$reportCommentStmt->store_result();
					
					$reportCommentsCtr = 0;
					
					while ($reportCommentStmt->fetch()) 
					{
						$arrReportComments[$reportCommentsCtr] = array(
															 'id' => $reportCommentId
															,'comment' => $reportComment
															,'user' => ($reportCommentFirstName . ' ' . $reportCommentLastName)
															,'date'=> date('m/d/Y g:ia', strtotime($reportCommentDate))
															,'isSubmitted'=> $reportIsSubmitted
															,'isSaved'=> $reportIsSaved
														);
						$reportCommentsCtr++;
					}
					
					$reportCommentStmt->free_result();
					$reportCommentStmt->close();	
				} 
			}
			
			$data['reportComments'] = $arrReportComments;
			
			
			//REPORT
			$reportDateSql = "SELECT date_reported FROM reports WHERE property_id=? ORDER BY date_reported ASC LIMIT 1";
			if ($reportDateStmt = $mysqli->prepare($reportDateSql)) 
			{
				$reportDateStmt->bind_param("i", $propertyId);
				
				if($reportDateStmt->execute()) {
					$reportDateStmt->bind_result($reportFirstDate);
					$reportDateStmt->store_result();
					$reportDateStmt->fetch();
					
					$data['firstReportDate'] = $reportFirstDate;
					
					$reportDateStmt->free_result();
					$reportDateStmt->close();	
				}
			}
			
			
			$result['success'] = true;
			$result['data'] = $data;
		} 
		else 
		{
			$result['message'] = "Sorry, there has been a problem processing your request.";
		}
		
		$mysqli->close();
		
		if($isJson) 
		{
			header('Content-type: application/json');
			echo json_encode($result);
		} 
		else 
		{
			return $data;
		}
	}
	
	public function getReportsSummary($propertyId, $isJson = true,$array_users,$array_sub_contractors,$array_companies) {
		$result['success'] = false;
		$result['message'] = '';
		$result['data'] = '';
		
		$data = array();
		
		$mysqli = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
		if ($mysqli->connect_errno) {
			$result['message'] = "Failed to connect to MySQL: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error;
		}
				
		$sql = "SELECT
					 reports.id
					,reports.reported_by
					,reports.subcontractor
					,reports.date_reported
					,reports.is_submitted
					,reports.is_saved
					,reports.is_closed
					,properties.status
				FROM reports
					INNER JOIN properties ON properties.id = reports.property_id
				WHERE reports.property_id=?
				ORDER BY reports.date_reported DESC
				";
				
		if ($stmt = $mysqli->prepare($sql)) {
			$stmt->bind_param("i", $propertyId);
			$stmt->execute();
			$stmt->bind_result($reportId, $reported_by, $subcontractor, $dateReported, $isSubmitted, $isSaved, $isClosed, $status);
			
			$dataCtr = 0;
			while ($stmt->fetch()) {
				if($subcontractor == 1)
				{
					$data[$dataCtr] = array(
									 'id'=>$reportId
									,'firstName'=>$array_sub_contractors[$reported_by]['first_name']
									,'lastName'=>$array_sub_contractors[$reported_by]['last_name']
									,'dateReported'=>$dateReported
									,'companyName'=>$array_sub_contractors[$reported_by]['company']
									,'isSubmitted'=>$isSubmitted
									,'isSaved'=>$isSaved
									,'isClosed'=>$isClosed
									,'status'=>$status
								);
				}
				else
				{
					$data[$dataCtr] = array(
									 'id'=>$reportId
									,'firstName'=>$array_users[$reported_by]['first_name']
									,'lastName'=>$array_users[$reported_by]['last_name']
									,'dateReported'=>$dateReported
									,'companyName'=>$array_companies[$array_users[$reported_by]['company_id']]['name']
									,'isSubmitted'=>$isSubmitted
									,'isSaved'=>$isSaved
									,'isClosed'=>$isClosed
									,'status'=>$status
								);
				}
				$dataCtr ++;
			}
			
			$stmt->close();	
			
			$result['success'] = true;
			$result['data'] = $data;
		} else {
			$result['message'] = "Sorry, there has been a problem processing your request.";
		}
		
		$mysqli->close();
		
		if($isJson) {
			header('Content-type: application/json');
			echo json_encode($result);
		} else {
			return $data;
		}
	}
		
	public function addRoomTemplate($roomName, $roomTemplateItems) {
		$result['success'] = false;
		$result['message'] = '';
		
		if(empty($roomName) || !isset($roomName) 
			) {
			$result['message'] = 'One of the required fields is missing.';
		} else {
			$mysqli = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
			if ($mysqli->connect_errno) {
				$result['message'] = "Failed to connect to MySQL: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error;
			}
			
			$roomName = $mysqli->real_escape_string($roomName);
			
			$sql = "SELECT name FROM room_templates WHERE name=?";
			if ($stmt = $mysqli->prepare($sql)) {
				$stmt->bind_param("s", $roomName);
				$stmt->execute();
				$stmt->bind_result($dbRoomName);
			    $stmt->fetch();
				$stmt->close();
				
				if(!empty($dbRoomName)) {
					$result['message'] = "The room name you provided already exist.";
				} else {
					$sql = "INSERT INTO room_templates(name, date_created) values(?, NOW())";
					
					if ($insertStmt = $mysqli->prepare($sql)) {
						$insertStmt->bind_param("s", $roomName);
						
						if($insertStmt->execute()) {
							$insertId = $mysqli->insert_id;
							
							//Insert new items.
							if(!empty($roomTemplateItems)) {
								$listItems = explode('|', $roomTemplateItems);
								
								if(count($listItems) > 0) {
									$isItemsInserted = false;
									
									foreach($listItems as $item):
										$insertSql = "INSERT INTO room_template_items(room_template_id, name, date_created) VALUES(?, ?, NOW())";
										
										if ($insertStmt2 = $mysqli->prepare($insertSql)) {
											$insertStmt2->bind_param("is", $insertId , $item);
											
											if($insertStmt2->execute()) {
												$isItemsInserted = true;
											} else {
												$isItemsInserted = false;
												break;
											}
											
											$insertStmt2->close();
										} else {
											$isItemsInserted = false;
											break;
										}
									endforeach;
									
									if($isItemsInserted) {
										$result['success'] = true;
										$result['message'] = "Room successfully added.";
									} else {
										$result['message'] = "Sorry, there has been a problem processing your request.";
									}
								}
							} else {
								$result['success'] = true;
								$result['message'] = "Room successfully added.";
							}
							
						} else {
							$result['message'] = "Sorry, there has been a problem processing your request.";
						}
						
						$insertStmt->close();
					} else {
						$result['message'] = "Sorry, there has been a problem processing your request.";
					}
				}
			}
			
			$mysqli->close();
		}
		
		header('Content-type: application/json');
		echo json_encode($result);
	}
	
	public function getRoomTemplates($isJson = true) {
		$result['success'] = false;
		$result['message'] = '';
		$result['data'] = '';
		
		$data = array();
		
		$mysqli = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
		if ($mysqli->connect_errno) {
			$result['message'] = "Failed to connect to MySQL: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error;
		}
		
		$sql = "SELECT 
					id, 
					name
				FROM room_templates;
				";
		
		if ($stmt = $mysqli->prepare($sql)) {
			$stmt->execute();
			$stmt->bind_result($roomId, $roomName);
			
			$dataCtr = 0;
			while ($stmt->fetch()) {
				$data[$dataCtr] = array(
							 'id'=>$roomId
							,'name'=>$roomName
						);
				
				$dataCtr++;
			}
			
			$stmt->close();	
			
			$result['success'] = true;
			$result['data'] = $data;
		} else {
			$result['message'] = "Sorry, there has been a problem processing your request.";
		}
		
		$mysqli->close();
		
		if($isJson) {
			header('Content-type: application/json');
			echo json_encode($result);
		} else {
			return $data;
		}
	}

	public function getRoomTemplateItems($roomTemplateId, $isJson = true) {
		$result['success'] = false;
		$result['message'] = '';
		$result['data'] = '';
		
		$data = array();
		
		$mysqli = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
		if ($mysqli->connect_errno) {
			$result['message'] = "Failed to connect to MySQL: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error;
		}
		
		$sql = "SELECT 
					 id
					,name
				FROM room_template_items
				WHERE room_template_id=?
				ORDER BY id
				";
		
		if ($stmt = $mysqli->prepare($sql)) {
			$stmt->bind_param("i", $roomTemplateId);
			$stmt->execute();
			$stmt->bind_result($roomItemId, $roomItemDescription);
			
			$dateCtr = 0;
			while ($stmt->fetch()) {
				$data[$dateCtr] = array(
							 'id'=>$roomItemId
							,'name'=>$roomItemDescription
						);
						
				$dateCtr++;
			}
			
			$stmt->close();	
			
			$result['success'] = true;
			$result['data'] = $data;
		} else {
			$result['message'] = "Sorry, there has been a problem processing your request.";
		}
		
		$mysqli->close();
		
		if($isJson) {
			header('Content-type: application/json');
			echo json_encode($result);
		} else {
			return $data;
		}
	}
}
?>
