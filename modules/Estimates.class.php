<?php 
require_once(dirname(dirname(__FILE__)) . '/config/main.config.php');
require_once(dirname(dirname(__FILE__)) . '/helpers/Captcha.class.php');

class Estimates {
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
	
	public function getPreviousEstimatesId($propertyId, $isJson = true) {
		$result['success'] = false;
		$result['message'] = '';
		$result['data'] = '';
		
		$reportId = 0;
		
		$mysqli = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
		if ($mysqli->connect_errno) {
			$result['message'] = "Failed to connect to MySQL: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error;
		}
		
		$sql = "SELECT id
				FROM estimates
				WHERE property_id=?
				ORDER BY id DESC 
				LIMIT 1";
		
		if ($stmt = $mysqli->prepare($sql)) {
			$stmt->bind_param('i', $propertyId);
			$stmt->execute();
			$stmt->bind_result($id);
			$stmt->fetch();
			
			$estimatesId = $id;
			
			$result['success'] = true;
			$result['estimatesId'] = $estimatesId;
			
			$stmt->close();	
		} else {
			$result['message'] = "Sorry, there has been a problem processing your request.";
		}
		
		$mysqli->close();
		
		if($isJson) {
			header('Content-type: application/json');
			echo json_encode($result);
		} else {
			return $estimatesId;
		}
	}
	
	public function getEstimateDetails_estimates($estimatesId, $isJson = true,$array_users=false,$array_sub_contractors=false) {
		$result['success'] = false;
		$result['message'] = '';
		$result['data'] = '';
		
		$data = array();
		
		$mysqli = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
		if ($mysqli->connect_errno) {
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
			,properties.estimates_emails
			,properties.estimates_multiplier
			,properties.property_type
			,properties.job_type
			,properties.community
			,estimates.date_created
			,estimates.reported_by
		FROM estimates
			INNER JOIN properties ON properties.id = estimates.property_id
		WHERE estimates.id = ?
		";
		$placeholder = $estimatesId;
		
		if ($stmt = $mysqli->prepare($sql)) {
			$stmt->bind_param("i", $placeholder);
			
			if($stmt->execute()) {
				$stmt->bind_result( $propertyId
									, $propertyName
									, $propertyAddress
									, $propertyCity
									, $propertyState
									, $propertyMapLink
									, $propertyEmails
									, $propertyEstimatesEmails
									, $propertyEstimatesMultiplier
									, $propertyType
									, $propertyJobType
									, $propertyCommunity
									, $reportDate
									, $reported_by);
				
				
				while ($stmt->fetch()) {
					$data = array(
									 'propertyId'=>$propertyId
									,'propertyName'=>stripslashes($propertyName)
									,'propertyAddress'=>stripslashes($propertyAddress)
									,'propertyCity'=>stripslashes($propertyCity)
									,'propertyState'=>stripslashes($propertyState)
									,'propertyMapLink'=>stripslashes($propertyMapLink)
									,'propertyEmails'=>stripslashes($propertyEmails)
									,'propertyEstimatesEmails'=>stripslashes($propertyEstimatesEmails)
									,'propertyEstimatesMultiplier'=>stripslashes($propertyEstimatesMultiplier)
									,'propertyType'=>$propertyType
									,'propertyJobType'=>$propertyJobType
									,'propertyCommunity'=>stripslashes($propertyCommunity)
									,'reportStatusId'=>$reportStatusId
									,'firstName'=>stripslashes($array_users[$reported_by]['first_name'])
									,'lastName'=>$array_users[$reported_by]['last_name']
									,'reportDate'=>$reportDate
									,'reported_by'=>$reported_by
								);
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
								,properties.estimates_emails
								,properties.estimates_multiplier
								,properties.property_type
								,properties.job_type
								,properties.community
								,estimates.date_created
								,estimates.reported_by
							FROM estimates
								INNER JOIN properties ON properties.id = estimates.property_id
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
												, $propertyEstimatesEmails
												, $propertyEstimatesMultiplier
												, $propertyType
												, $propertyJobType
												, $propertyCommunity
												, $reportDate
												, $reported_by);
							
							
							while ($stmt->fetch()) 
							{
								$data = array(
									 'propertyId'=>$propertyId
									,'propertyName'=>stripslashes($propertyName)
									,'propertyAddress'=>stripslashes($propertyAddress)
									,'propertyCity'=>stripslashes($propertyCity)
									,'propertyState'=>stripslashes($propertyState)
									,'propertyMapLink'=>stripslashes($propertyMapLink)
									,'propertyEmails'=>stripslashes($propertyEmails)
									,'propertyEstimatesEmails'=>stripslashes($propertyEstimatesEmails)
									,'propertyEstimatesMultiplier'=>$propertyEstimatesMultiplier
									,'propertyType'=>$propertyType
									,'propertyJobType'=>$propertyJobType
									,'propertyCommunity'=>stripslashes($propertyCommunity)
									,'firstName'=>stripslashes($array_users[$reported_by]['first_name'])
									,'lastName'=>stripslashes($array_users[$reported_by]['last_name'])
									,'reportDate'=>$reportDate
									,'reported_by'=>$reported_by
								);
							}
						}
					}
				}	
			}
			
			$stmt->close();	
			
			$query = "SELECT is_closed FROM estimates WHERE id = ?";
			if ($queryStmt = $mysqli->prepare($query)) 
			{
				$queryStmt->bind_param("i", $estimatesId);
				if($queryStmt->execute())
				{
					$queryStmt->bind_result($is_closed);
					while ($queryStmt->fetch()) 
					{
						$arrayEstimatesCheck = array("is_closed"=>$is_closed);	
					}
				}
			}
						
			//GET ROOMS AND ITEMS
			$rooms = array();
			
			/*if($arrayEstimatesCheck['is_closed'] != 2)
			{*/
				$roomsSql = "SELECT
								 id AS roomId
								,room_template_id
								,name AS roomName
							FROM estimate_rooms
							WHERE estimate_id = ?
							ORDER BY id
						";
	
				if ($roomsStmt = $mysqli->prepare($roomsSql)) 
				{
					$roomsStmt->bind_param("i", $estimatesId);
					
					if($roomsStmt->execute()) {
						$roomsStmt->bind_result($roomId, $room_template_id, $roomName);
						$roomsStmt->store_result();
						
						$roomsCtr = 0;
						while ($roomsStmt->fetch()) {
							$rooms[$roomsCtr] = array(
											'roomId' => $roomId
											,'roomTemplateId' => $room_template_id
											,'roomName' => $roomName
										);
		
							//GET PUNCHLIST ITEMS
							/*$query = "SELECT estimate_id,room_id,estimate_room_items_id,room_template_estimates_id,units,status_id FROM estimate_room_items_units WHERE room_id = ?";
							if ($queryStmt = $mysqli->prepare($query))
							{
								$queryStmt->bind_param("i", $roomId);
								if($queryStmt->execute())
								{
									$queryStmt->bind_result($estimate_id,$room_id,$estimate_room_items_id,$room_template_estimates_id,$units,$status_id );
									$queryStmt->store_result();
									$itemCtr = 0;
									$arrayRoomItemsUnits = array();
									while ($queryStmt->fetch()) {	
											$arrayRoomItemsUnits[] = array(
														'estimateId' => $estimate_id
														,'roomId' => $room_id
														,'estimateRoomItemsId' => $estimate_room_items_id
														,'roomTemplateEstimatesId' => $room_template_estimates_id
														,'units' => $units
														,'status_id' => $status_id
														);
										
										$itemCtr++;
									}
								}
							}*/
							
							$roomItems = array();
							//if(count($arrayRoomItemsUnits) > 0)
							//{
								$itemsSql = "SELECT
												 estimate_room_items.id 
												,estimate_room_items.room_template_item_id
												,estimate_room_items.name
												,room_template_items.work_category_id
											FROM estimate_room_items
											INNER JOIN room_template_items ON room_template_items.id = estimate_room_items.room_template_item_id
											WHERE estimate_room_items.room_id = ?
											ORDER BY estimate_room_items.name
										";
									
								if ($roomItemsStmt = $mysqli->prepare($itemsSql)) {
									$roomItemsStmt->bind_param("i", $roomId);
									
									if($roomItemsStmt->execute()) {
										$roomItemsStmt->bind_result($itemId, $room_template_item_id, $itemName, $work_category_id);
										$roomItemsStmt->store_result();
										$itemCtr = 0;
										while ($roomItemsStmt->fetch()) {	
											$array_room_items_unit = array();
											/*for($i=0;$i<count($arrayRoomItemsUnits);$i++)
											{
												if($arrayRoomItemsUnits[$i]['estimateRoomItemsId'] == $itemId)
													$array_room_items_unit[] = $arrayRoomItemsUnits[$i];
											}
											
											if(count($array_room_items_unit) > 0)
											{*/
												$roomItems[$itemCtr] = array(
															'itemId' => $itemId
															,'itemTemplateId' => $room_template_item_id
															,'itemName' => htmlentities(str_replace('"',"'",$itemName), ENT_QUOTES)
															,'work_category_id' => $work_category_id
															//,'arrayRoomItemsUnits' => $array_room_items_unit
															);
												
											
												$itemCtr++;
											//}
										}
										
										$rooms[$roomsCtr]['items'] = $roomItems;
									} else {
										$rooms[$roomsCtr]['error'] = $mysqli->error;
									}
									
									$roomItemsStmt->free_result();
									$roomItemsStmt->close();
								} else {
									$rooms[$roomsCtr]['error'] = $mysqli->error;
								}
							//}
							
							$roomsCtr++;
						}
						
						$roomsStmt->free_result();
						$roomsStmt->close();
					}
	
					$data['rooms'] = $rooms;
				}
			//}
						
			//REPORT
			$reportDateSql = "SELECT date_created FROM estimates WHERE property_id=? ORDER BY date_created ASC LIMIT 1";
			if ($reportDateStmt = $mysqli->prepare($reportDateSql)) {
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
	
	public function getEstimateDetails($estimatesId, $isJson = true,$array_users=false,$array_sub_contractors=false) {
		$result['success'] = false;
		$result['message'] = '';
		$result['data'] = '';
		
		$data = array();
		
		$mysqli = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
		if ($mysqli->connect_errno) {
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
			,properties.estimates_emails
			,properties.estimates_multiplier
			,properties.property_type
			,properties.job_type
			,properties.community
			,estimates.date_created
			,estimates.reported_by
		FROM estimates
			INNER JOIN properties ON properties.id = estimates.property_id
		WHERE estimates.id = ?
		";
		$placeholder = $estimatesId;
		
		if ($stmt = $mysqli->prepare($sql)) {
			$stmt->bind_param("i", $placeholder);
			
			if($stmt->execute()) {
				$stmt->bind_result( $propertyId
									, $propertyName
									, $propertyAddress
									, $propertyCity
									, $propertyState
									, $propertyMapLink
									, $propertyEmails
									, $propertyEstimatesEmails
									, $propertyEstimatesMultiplier
									, $propertyType
									, $propertyJobType
									, $propertyCommunity
									, $reportDate
									, $reported_by);
				
				
				while ($stmt->fetch()) {
					$data = array(
									 'propertyId'=>$propertyId
									,'propertyName'=>stripslashes($propertyName)
									,'propertyAddress'=>stripslashes($propertyAddress)
									,'propertyCity'=>stripslashes($propertyCity)
									,'propertyState'=>stripslashes($propertyState)
									,'propertyMapLink'=>stripslashes($propertyMapLink)
									,'propertyEmails'=>stripslashes($propertyEmails)
									,'propertyEstimatesEmails'=>stripslashes($propertyEstimatesEmails)
									,'propertyEstimatesMultiplier'=>stripslashes($propertyEstimatesMultiplier)
									,'propertyType'=>$propertyType
									,'propertyJobType'=>$propertyJobType
									,'propertyCommunity'=>stripslashes($propertyCommunity)
									,'reportStatusId'=>$reportStatusId
									,'firstName'=>stripslashes($array_users[$reported_by]['first_name'])
									,'lastName'=>$array_users[$reported_by]['last_name']
									,'reportDate'=>$reportDate
									,'reported_by'=>$reported_by
								);
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
								,properties.estimates_emails
								,properties.estimates_multiplier
								,properties.property_type
								,properties.job_type
								,properties.community
								,estimates.date_created
								,estimates.reported_by
							FROM estimates
								INNER JOIN properties ON properties.id = estimates.property_id
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
												, $propertyEstimatesEmails
												, $propertyEstimatesMultiplier
												, $propertyType
												, $propertyJobType
												, $propertyCommunity
												, $reportDate
												, $reported_by);
							
							
							while ($stmt->fetch()) 
							{
								$data = array(
									 'propertyId'=>$propertyId
									,'propertyName'=>stripslashes($propertyName)
									,'propertyAddress'=>stripslashes($propertyAddress)
									,'propertyCity'=>stripslashes($propertyCity)
									,'propertyState'=>stripslashes($propertyState)
									,'propertyMapLink'=>stripslashes($propertyMapLink)
									,'propertyEmails'=>stripslashes($propertyEmails)
									,'propertyEstimatesEmails'=>stripslashes($propertyEstimatesEmails)
									,'propertyEstimatesMultiplier'=>$propertyEstimatesMultiplier
									,'propertyType'=>$propertyType
									,'propertyJobType'=>$propertyJobType
									,'propertyCommunity'=>stripslashes($propertyCommunity)
									,'firstName'=>stripslashes($array_users[$reported_by]['first_name'])
									,'lastName'=>stripslashes($array_users[$reported_by]['last_name'])
									,'reportDate'=>$reportDate
									,'reported_by'=>$reported_by
								);
							}
						}
					}
				}	
			}
			
			$stmt->close();	
			
			$query = "SELECT is_closed FROM estimates WHERE id = ?";
			if ($queryStmt = $mysqli->prepare($query)) 
			{
				$queryStmt->bind_param("i", $estimatesId);
				if($queryStmt->execute())
				{
					$queryStmt->bind_result($is_closed);
					while ($queryStmt->fetch()) 
					{
						$arrayEstimatesCheck = array("is_closed"=>$is_closed);	
					}
				}
			}
			
			//GET ROOMS AND ITEMS
			$rooms = array();
			
			if($arrayEstimatesCheck['is_closed'] == 2)
			{
				$roomsSql = "SELECT
								 id AS roomId
								,room_template_id
								,name AS roomName
							FROM estimate_rooms
							WHERE estimate_id = ?
							ORDER BY id
						";
	
				if ($roomsStmt = $mysqli->prepare($roomsSql)) 
				{
					$roomsStmt->bind_param("i", $estimatesId);
					
					if($roomsStmt->execute()) {
						$roomsStmt->bind_result($roomId, $room_template_id, $roomName);
						$roomsStmt->store_result();
						
						$roomsCtr = 0;
						while ($roomsStmt->fetch()) {
							$rooms[$roomsCtr] = array(
											'roomId' => $roomId
											,'roomTemplateId' => $room_template_id
											,'roomName' => $roomName
										);
							
							$query = "SELECT * FROM work_category_estimates";
							if ($queryStmt = $mysqli->prepare($query))
							{
								if($queryStmt->execute())
								{
									$queryStmt->bind_result($work_category_estimates_id,$work_category_id,$item_name,$price_per_unit,$unit_of_measure,$timestamp );
									$queryStmt->store_result();
									$itemCtr = 0;
									$roomTemplateEstimates = array();
									while ($queryStmt->fetch()) {	
											$workCategoryEstimates[$work_category_estimates_id] = array(
														'work_category_estimates_id' => $work_category_estimates_id
														,'work_category_id' => $work_category_id
														,'item_name' => $item_name
														,'price_per_unit' => $price_per_unit
														,'unit_of_measure' => $unit_of_measure
														,'timestamp' => $timestamp
														);
										
										$itemCtr++;
									}
								}
							}
							
							//GET PUNCHLIST ITEMS
							$query = "SELECT estimate_id,room_id,estimate_room_items_id,work_category_estimates_id,units,status_id FROM estimate_room_items_units WHERE room_id = ?";
							
							if ($queryStmt = $mysqli->prepare($query))
							{
								$queryStmt->bind_param("i", $roomId);
								if($queryStmt->execute())
								{
									$queryStmt->bind_result($estimate_id,$room_id,$estimate_room_items_id,$work_category_estimates_id,$units,$status_id );
									$queryStmt->store_result();
									$itemCtr = 0;
									$arrayRoomItemsUnits = array();
									while ($queryStmt->fetch()) {
											$arrayRoomItemsUnits[] = array(
														'estimateId' => $estimate_id
														,'roomId' => $room_id
														,'estimateRoomItemsId' => $estimate_room_items_id
														,'workCategoryEstimatesId' => $work_category_estimates_id
														,'units' => $units
														,'status_id' => $status_id
														);
										
										$itemCtr++;
									}
									
									for($k=0;$k<count($arrayRoomItemsUnits);$k++)
									{
										$arrayRoomItemsUnits[$k]['estimate_name'] = $workCategoryEstimates[$arrayRoomItemsUnits[$k]["workCategoryEstimatesId"]]['item_name'];	
									}
								}
							}
							
							$roomItems = array();
							if(count($arrayRoomItemsUnits) > 0)
							{
								$itemsSql = "SELECT
												 estimate_room_items.id 
												,estimate_room_items.room_template_item_id
												,estimate_room_items.name
												,room_template_items.work_category_id
											FROM estimate_room_items
											INNER JOIN room_template_items ON room_template_items.id = estimate_room_items.room_template_item_id
											WHERE estimate_room_items.room_id = ?
											ORDER BY estimate_room_items.id
										";
									
								if ($roomItemsStmt = $mysqli->prepare($itemsSql)) {
									$roomItemsStmt->bind_param("i", $roomId);
									
									if($roomItemsStmt->execute()) {
										$roomItemsStmt->bind_result($itemId, $room_template_item_id, $itemName, $work_category_id);
										$roomItemsStmt->store_result();
										$itemCtr = 0;
										while ($roomItemsStmt->fetch()) {	
											$array_room_items_unit = array();
											for($i=0;$i<count($arrayRoomItemsUnits);$i++)
											{
												if($arrayRoomItemsUnits[$i]['estimateRoomItemsId'] == $itemId)
													$array_room_items_unit[] = $arrayRoomItemsUnits[$i];
											}
											
											if(count($array_room_items_unit) > 0)
											{
												$roomItems[$itemCtr] = array(
															'itemId' => $itemId
															,'itemTemplateId' => $room_template_item_id
															,'itemName' => htmlentities(str_replace('"',"'",$itemName), ENT_QUOTES)
															,'work_category_id' => $work_category_id
															,'arrayRoomItemsUnits' => $array_room_items_unit
															);
												
											
												$itemCtr++;
											}
										}
										
										$rooms[$roomsCtr]['items'] = $roomItems;
									} else {
										$rooms[$roomsCtr]['error'] = $mysqli->error;
									}
									
									$roomItemsStmt->free_result();
									$roomItemsStmt->close();
								} else {
									$rooms[$roomsCtr]['error'] = $mysqli->error;
								}
							}
							
							$roomsCtr++;
						}
						
						$roomsStmt->free_result();
						$roomsStmt->close();
					}
	
					$data['rooms'] = $rooms;
				}
			}
						
			//REPORT
			$reportDateSql = "SELECT date_created FROM estimates WHERE property_id=? ORDER BY date_created ASC LIMIT 1";
			if ($reportDateStmt = $mysqli->prepare($reportDateSql)) {
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
	
	public function getEstimatesSummary($propertyId, $isJson = true,$array_users,$array_companies) {
		$result['success'] = false;
		$result['message'] = '';
		$result['data'] = '';
		
		$data = array();
		
		$mysqli = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
		if ($mysqli->connect_errno) {
			$result['message'] = "Failed to connect to MySQL: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error;
		}
				
		$sql = "SELECT
					 estimates.id
					,estimates.reported_by
					,estimates.date_created
					,estimates.is_submitted
					,estimates.is_saved
					,estimates.is_closed
				FROM estimates
					INNER JOIN properties ON properties.id = estimates.property_id
				WHERE estimates.property_id=?
				ORDER BY estimates.date_created DESC
				";
				
		if ($stmt = $mysqli->prepare($sql)) {
			$stmt->bind_param("i", $propertyId);
			$stmt->execute();
			$stmt->bind_result($estimatesId, $reported_by, $dateReported, $isSubmitted, $isSaved, $isClosed);
			
			$dataCtr = 0;
			while ($stmt->fetch()) {
				$data[$dataCtr] = array(
								 'id'=>$estimatesId
								,'firstName'=>$array_users[$reported_by]['first_name']
								,'lastName'=>$array_users[$reported_by]['last_name']
								,'dateReported'=>$dateReported
								,'companyName'=>$array_companies[$array_users[$reported_by]['company_id']]['name']
								,'isSubmitted'=>$isSubmitted
								,'isSaved'=>$isSaved
								,'isClosed'=>$isClosed
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