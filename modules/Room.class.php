<?php 
require_once(dirname(dirname(__FILE__)) . '/config/main.config.php');
require_once(dirname(dirname(__FILE__)) . '/helpers/Captcha.class.php');
require_once(dirname(dirname(__FILE__)) . '/modules/Dynamo.class.php');
require_once(dirname(dirname(__FILE__)) . '/mpdf/mpdf60/mpdf.php');
require_once(dirname(dirname(__FILE__)) . '/modules/attach_mailer_class.php');

class Room {
	var $roomsEmailBody = '';
	
    public function __construct(){  
    }  
	
	public function validateEmail($email){  
        $test = preg_match(EMAIL_PATTERN, $email);  
        return $test;  
    }
	
	public function deleteRoom($roomId) {
		$result['success'] = false;
		$result['message'] = '';
		
		if(!isset($roomId) || empty($roomId)
		){
			$result['message'] = 'One of the required fields is missing.';
		} else {
			$mysqli = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
			if ($mysqli->connect_errno) {
				$result['message'] = "Failed to connect to MySQL: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error;
			}
			
			
			$deleteSql = "DELETE FROM report_rooms WHERE id=?";
			if ($deleteStmt = $mysqli->prepare($deleteSql)) {
				$deleteStmt->bind_param("i", $roomId);
				
				if($deleteStmt->execute()) {
					$deleteSql = "DELETE FROM report_room_items WHERE room_id=?";
					if ($deleteStmt2 = $mysqli->prepare($deleteSql)) {
						$deleteStmt2->bind_param("i", $roomId);
						$deleteStmt2->execute();
						$deleteStmt2->close();
					}
					
					$result['success'] = true;
					$result['message'] = "Room successfully deleted.";
				} else {
					$result['message'] = "Sorry, there has been a problem processing your request.";
				}
				
				$deleteStmt->close();
			} else {
				$result['message'] = "Sorry, there has been a problem processing your request.";
			}
			
			
			$mysqli->close();
		}
		
		header('Content-type: application/json');
		echo json_encode($result);
	}
	
	public function deleteCommunityRoom($roomId) {
		$result['success'] = false;
		$result['message'] = '';
		
		if(!isset($roomId) || empty($roomId)
		){
			$result['message'] = 'One of the required fields is missing.';
		} else {
			$mysqli = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
			if ($mysqli->connect_errno) {
				$result['message'] = "Failed to connect to MySQL: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error;
			}
			
			$deleteSql = "DELETE FROM community_report_rooms WHERE id=?";
			if ($deleteStmt = $mysqli->prepare($deleteSql)) {
				$deleteStmt->bind_param("i", $roomId);
				
				if($deleteStmt->execute()) {
					$deleteSql = "DELETE FROM community_report_room_items WHERE room_id=?";
					if ($deleteStmt2 = $mysqli->prepare($deleteSql)) {
						$deleteStmt2->bind_param("i", $roomId);
						$deleteStmt2->execute();
						$deleteStmt2->close();
					}
					
					$result['success'] = true;
					$result['message'] = "Room successfully deleted.";
				} else {
					$result['message'] = "Sorry, there has been a problem processing your request.";
				}
				
				$deleteStmt->close();
			} else {
				$result['message'] = "Sorry, there has been a problem processing your request.";
			}
			
			
			$mysqli->close();
		}
		
		header('Content-type: application/json');
		echo json_encode($result);
	}
	
	public function updateReport($userId, $propertyId, $statusId, $data, $isSave, $isSubmit, $reportId, $propertyName, $propertyAddress, $propertyEmails, $propertyStatus, $propertyCommunity, $propertyType, $propertyJobType, $reportComment) {
		$result['success'] = false;
		$result['message'] = '';
		
		if(empty($userId)
		  || empty($propertyId)
		  || empty($data)
		  ) {
			$result['message'] = 'One of the required parameters is missing.';
		} else {
			$mysqli = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
			if ($mysqli->connect_errno) {
				$result['message'] = "Failed to connect to MySQL: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error;
			}
			
			$this->roomsEmailBody = '';
			
			//DECODE JSON DATA
			$rooms = json_decode($data);
			$isUpdateSuccess = true;
			
			$mysqli->autocommit(FALSE);
			
			
			if(!empty($reportId) && isset($reportId)) {
				
				$updateReportSql = "UPDATE reports
									   SET status_id = ?
										  ,is_submitted = ?
										  ,is_saved = ?
										  ,reported_by = ?
										  ,subcontractor = ?
									WHERE id = ?
									";
				
				if ($updateReportStmt = $mysqli->prepare($updateReportSql)) {
					
					if($_SESSION['user_type'] == 5)
						$subcontractor = 1;
					else
						$subcontractor = 0;
						
					$updateReportStmt->bind_param("iiiiii", $statusId, $isSubmit, $isSave, $userId, $subcontractor, $reportId);
					
					if(!$updateReportStmt->execute()) {
						$isUpdateSuccess = false;
					}
					
					$updateReportStmt->close();
				} 				
				
				//UPDATE REPORT COMMENT
				/*$updateReportSql = "UPDATE report_comments
									   SET comment = ?
										  ,date = NOW()
									WHERE report_id = ?
									  AND user_id = ?
									  AND property_id = ?
									";*/
				if(trim($reportComment) != '')
				{
					$updateReportSql = "INSERT INTO report_comments(`comment`,`report_id`,`user_id`,`property_id`,`date`)
					VALUES(?,?,?,?,NOW())";
					
					if ($updateReportStmt = $mysqli->prepare($updateReportSql)) {
						$updateReportStmt->bind_param("siii", $reportComment, $reportId, $userId, $propertyId);
						
						if(!$updateReportStmt->execute()) {
							$isUpdateSuccess = false;
						}
						
						$updateReportStmt->close();
					} 				
				}
				
				if($isUpdateSuccess) {
					$isUpdateSuccess = $this->updateRooms($mysqli, $rooms, $reportId, $userId);
				}
			} else {
				$result['message'] = "INSERT NISUOD!";
				
				$insertReportSql = "INSERT INTO reports(property_id, date_reported, status_id, reported_by, is_submitted, is_saved) VALUES(?, NOW(), ?, ?, ?, ?)";
				
				if ($insertReportStmt = $mysqli->prepare($insertReportSql)) {
					$insertReportStmt->bind_param("iiiii", $propertyId, $statusId, $userId, $isSubmit, $isSave);
					
					if($insertReportStmt->execute()) {
						$reportId = $mysqli->insert_id;
						$query = "UPDATE report_images SET report_id = ? WHERE property_id = ?";
						if($query_stmt = $mysqli->prepare($query))
						{
							$query_stmt->bind_param("ii",$reportId,$propertyId);
							$query_stmt->execute();
						}
					} else {
						$isUpdateSuccess = false;
					}
					
					$insertReportStmt->close();					
				}
				
				
				//INSERT REPORT COMMENT
				if(!empty($reportComment)) {
					$insertReportSql = "INSERT INTO report_comments(comment, user_id, report_id, property_id, date) VALUES(?, ?, ?, ?, NOW())";
					
					if ($insertReportStmt = $mysqli->prepare($insertReportSql)) {
						$insertReportStmt->bind_param("siii", $reportComment, $userId, $reportId, $propertyId);
						if(!$insertReportStmt->execute()) {
							$isUpdateSuccess = false;
						}
						$insertReportStmt->close();
					}
				}
				
				if($isUpdateSuccess) {
					foreach($rooms as $room) {
						$isUpdateSuccess = $this->insertRooms($mysqli, $room, $reportId, $userId);
					}
				}
			}
			
			if($isUpdateSuccess) {
				
				//GET REPORT GENERAL COMMENTS
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
			
				// $arrReportComments = array();
				$generalComments = '<table width="100%" bgcolor="#fff">';
				
				if ($reportCommentStmt = $mysqli->prepare($sqlReportComment)) {
					$reportCommentStmt->bind_param("i", $propertyId);
					
					if($reportCommentStmt->execute()) {
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
						
						while ($reportCommentStmt->fetch()) {
							$generalComments .= '<tr style="border-bottom: solid 1px #0d254f">';
							$generalComments .= '<td style="text-align: left; font: normal 12px Arial; color: #000">' . date('m/d/Y g:ia', strtotime($reportCommentDate)) .  '</td>';
							$generalComments .= '<td style="text-align: left; font: normal 12px Arial; color: #000">' . $reportComment .  '</td>';
							$generalComments .= '<td style="text-align: left; font: normal 12px Arial; color: #000">by: ' . ($reportCommentFirstName . ' ' . $reportCommentLastName) .  '</td>';
							$generalComments .= '</tr>';
						}
						
						$reportCommentStmt->free_result();
						$reportCommentStmt->close();	
					} 
				}
				
				$generalComments .= '</table>';
				//END GET REPORT GENERAL COMMENTS			
				
				$mysqli->commit();
				
				$result['success'] = true;
				$result['reportId'] = $reportId;
				$result['message'] = 'Report successfully saved!';
				
				//SEND REPORT
				if(($isSave == 0) && ($isSubmit == 1)) {
					$propertyType = ($propertyType==0?"Residential":"Commercial");
					$propertyJobType = ($propertyJobType==0?"New":"Restoration");
					
					if($this->sendEmailReport($propertyEmails
											, $propertyName
											, $propertyAddress
											, $propertyStatus
											, $propertyCommunity
											, $propertyType
											, $propertyJobType
											, $generalComments
											, $reportId
											,$mysqli)) {
						$result['message'] = 'Report successfully saved!';
					} else {
						$result['message'] = 'Report successfully save but there was no email report sent.';
					}
				}
				
			} else {
				$result['message'] = 'Data got rolled back.' . $mysqli->error;
				$mysqli->rollback();
			}
			
			$mysqli->close();
		}
		
		header('Content-type: application/json');
		echo json_encode($result);
	}
	
	public function updateRooms($mysqli, $rooms, $reportId, $userId) {
		$isUpdateSuccess = true;
		//[Start] UPDATE ROOM AND ROOM ITEMS
		if(count($rooms) > 0)
		{
			$string_room_item_id = '';
			for($i=0;$i<count($rooms);$i++)
			{
				for($j=0;$j<count($rooms[$i]->roomItems);$j++)
				{
					$string_room_item_id .= $rooms[$i]->roomItems[$j]->id.",";
				}
			}
			
			$string_room_item_id = substr($string_room_item_id,0,-1);
			
			$report_room_item_comments_obj = new Dynamo("report_room_item_comments");
			
			$query = "SELECT * FROM report_room_item_comments WHERE room_item_id IN ($string_room_item_id) ORDER BY room_item_id,`order`";
			$array_comments = $report_room_item_comments_obj->customFetchQueryWithIdDefault($query,'room_item_id');
			
			$query = "SELECT * FROM report_images WHERE room_item_id IN ($string_room_item_id) ORDER BY room_item_id,`order`";
			$array_images = $report_room_item_comments_obj->customFetchQueryWithIdDefault($query,'room_item_id');
			
			for($i=0;$i<count($rooms);$i++)
			{
				for($j=0;$j<count($rooms[$i]->roomItems);$j++)
				{
					if(trim($rooms[$i]->roomItems[$j]->comment) != '')
					{
						$rooms[$i]->roomItems[$j]->comment = $array_comments[$rooms[$i]->roomItems[$j]->id];
						$rooms[$i]->roomItems[$j]->images = $array_images[$rooms[$i]->roomItems[$j]->id];
					}
				}
			}
		}
		
		foreach($rooms as $room):
			if($room->isNew == 1) {
				$isUpdateSuccess = $this->insertRooms($mysqli, $room, $reportId, $userId);
				
				if(!$isUpdateSuccess) {
					break;
				}
			} else {
				$status_check = true;
				for($i=0;$i<count($room->roomItems);$i++)
				{
					if($room->roomItems[$i]->statusId == 2 || $room->roomItems[$i]->statusId == 3)
					{
						$status_check = false;
						break;
					}
				}
				
				$this->roomsEmailBody .= '<table width="100%" bgcolor="#ffffff">';
				$this->roomsEmailBody .= '<tr>';
				if($status_check)
				{
					$this->roomsEmailBody .= '<th colspan="3" style="text-align: left; font: bold 14px Arial; color: #000" valign="top">' . $room->roomName . ' - <span style="color:#468847;font-weight:normal;">Complete</span></th>';
				}
				else
					$this->roomsEmailBody .= '<th colspan="3" style="text-align: left; font: bold 14px Arial; color: #000" valign="top">' . $room->roomName . '</th>';
				$this->roomsEmailBody .= '</tr>';
				
				$host_url = "http://".$_SERVER['HTTP_HOST'].rtrim(dirname(dirname($_SERVER['PHP_SELF'])), '/\\');
				
				//The only thing that needs updating is just the status (Complete, Pending, and Incomplete) and comments for a "SAVED" room.
				foreach($room->roomItems as $punchlist):
					$updateRoomSql = "UPDATE report_room_items
										 SET status_id = ?
									   WHERE id = ?";
				
					if ($updateRoomStmt = $mysqli->prepare($updateRoomSql)) {
						$updateRoomStmt->bind_param("ii", $punchlist->statusId, $punchlist->id);
						
						if($updateRoomStmt->execute()) 
						{	
							//UPDATE ROOM ITEM COMMENT
							/*if(!empty($punchlist->comment) && isset($punchlist->comment)) {
								$updateRoomCommentSql = "UPDATE report_room_item_comments
															SET  comment = ?
																,date = NOW()
														WHERE room_item_id = ?
														  AND report_id = ?
													";
													
								if ($updateRoomCommentStmt = $mysqli->prepare($updateRoomCommentSql)) {
									$updateRoomCommentStmt->bind_param("sii", $punchlist->comment, $punchlist->id, $reportId);
									$updateRoomCommentStmt->execute();
								
									//If comment is not existing, add it on the room item.
									if($mysqli->affected_rows == 0) {
										$insertRoomCommentSql = "INSERT INTO report_room_item_comments(comment, user_id, room_item_id, report_id, date) VALUES(?, ?, ?, ?, NOW())";
								
										if($insertRoomCommentStmt = $mysqli->prepare($insertRoomCommentSql)) {
											$insertRoomCommentStmt->bind_param("siii", $punchlist->comment, $userId, $punchlist->id, $reportId);
											$insertRoomCommentStmt->execute();
											$insertRoomCommentStmt->close();
										} else {
											$isUpdateSuccess = false;
											break;
										}
									}
									
									$updateRoomCommentStmt->close();
								} else {
									$isUpdateSuccess = false;
									break;
								}
							}*/
						
							$itemStatus = '';
							$itemStatusColor = '';
							
							if($punchlist->statusId == 4) {
								$itemStatus = 'N/A';
								$itemStatusColor = 'green';
							} else if($punchlist->statusId == 3) {
								$itemStatus = 'Incomplete';
								$itemStatusColor = 'red';
							} else if($punchlist->statusId == 2) {
								$itemStatus = 'Pending Review';
								$itemStatusColor = 'orange';
							} else if($punchlist->statusId == 1) {
								$itemStatus = 'Complete';
								$itemStatusColor = 'green';
							}
							
							if($punchlist->isParent == 1)
							{
								$itemStatus = '';	
							}
							
							$style = "";
							$padding_left = "";
							if($punchlist->isEstimate == 1)
							{
								$style = " style='background-color:#f9f9f9'";	
								$padding_left = "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
							}
							
							if($punchlist->statusId == 2 || $punchlist->statusId == 3)
							{
								$this->roomsEmailBody .= "<tr{$style}>";
								$this->roomsEmailBody .= '	<td style="text-align: left; font: normal 12px Arial; color: #000" valign="top">' .$padding_left. $punchlist->name . '</td>';
								$this->roomsEmailBody .= '	<td style="text-align: left; font: normal 12px Arial; color: #000;" valign="top">';
								
								if(count($punchlist->comment) > 0)
								{
									$this->roomsEmailBody .= '<table border="0" cellpadding="2" cellspacing="2">';
									for($i=0;$i<count($punchlist->comment);$i++)
									{
										$this->roomsEmailBody .= '<tr><td valign="top">';
										if(count($punchlist->images) > 0)
										{
											for($j=0;$j<count($punchlist->images);$j++)
											{
												if($punchlist->images[$j]['order'] == $punchlist->comment[$i]['order'])
												{
													$this->roomsEmailBody .= "<a href='".$host_url."/images/report_uploads/".$punchlist->images[$j]['image_name']."'><img src='".$host_url."/images/report_uploads/".$punchlist->images[$j]['image_name']."' height='75' /></a>&nbsp;&nbsp;<br />";
												}
											}
										}
										$this->roomsEmailBody .= '</td><td valign="top">';
										
										if(trim($punchlist->comment[$i]['comment']) != '')
											$this->roomsEmailBody .= "<img src='".$host_url."/images/comments.png' align='top' />&nbsp;<span style='vertical-align:top'>".$punchlist->comment[$i]['comment']."</span>";
										
										$this->roomsEmailBody .= '</td></tr>';
									}
									$this->roomsEmailBody .= '</table>';
								}
								
								$this->roomsEmailBody .= '</td>';
								
								$this->roomsEmailBody .= '	<td style="text-align: right; font: normal 12px Arial; color: ' .$itemStatusColor. '; width: 100px" valign="top">' . $itemStatus . '</td>';
								$this->roomsEmailBody .= '</tr>';
							}
						} else {
							$isUpdateSuccess = false;
							break;
						}
						
						$updateRoomStmt->close();
					} else {	
						$isUpdateSuccess = false;
						break;
					}
				endforeach;
				
				$this->roomsEmailBody .= '</table>';
				$this->roomsEmailBody .= '<br/>';
			}
		endforeach;
		//[End] UPDATE ROOM AND ITEMS 
		
		return $isUpdateSuccess;
	}
	
	private function insertRooms($mysqli, $room, $reportId, $userId) {	
		$isUpdateSuccess = true;
		
		//INSERT ROOMS
		$insertRoomsSql = "INSERT INTO report_rooms(report_id, room_template_id, name, date_created, created_by) VALUES(?, ?, ?, NOW(), ?)";

		if($insertRoomsStmt = $mysqli->prepare($insertRoomsSql)) {
			$insertRoomsStmt->bind_param("iisi", $reportId, $room->roomTemplateId, $room->roomName, $userId);
			
			if($insertRoomsStmt->execute()) {
				$insertedRoomId = $mysqli->insert_id;
				
				$this->roomsEmailBody .= '<table width="100%" bgcolor="#ffffff">';
				$this->roomsEmailBody .= '<tr>';
				$this->roomsEmailBody .= '<th colspan="3" style="text-align: left; font: bold 14px Arial; color: #000">' . $room->roomName . '</th>';
				$this->roomsEmailBody .= '</tr>';
				
				//INSERT ROOM ITEMS
				foreach($room->roomItems as $punchlist) {
					
					$insertPunchlistSql = "INSERT INTO report_room_items(report_id, room_id, room_template_item_id, name, status_id, date_created) VALUES(?, ?, ?, ?, ?, NOW())";
					
					if($insertPunchlistStmt = $mysqli->prepare($insertPunchlistSql)) {
						$insertPunchlistStmt->bind_param("iiisi", $reportId, $insertedRoomId, $punchlist->roomTemplateItemId, $punchlist->name, $punchlist->statusId);
						
						if($insertPunchlistStmt->execute()) {
							$insertedRoomItemId = $mysqli->insert_id;
							
							//INSERT ITEM COMMENT
							if(!empty($punchlist->comment) && isset($punchlist->comment)) {
								$insertCommentSql = "INSERT INTO report_room_item_comments(comment, user_id, room_item_id, report_id, date) VALUES(?, ?, ?, ?, NOW())";
						
								if($insertCommentStmt = $mysqli->prepare($insertCommentSql)) {
									$insertCommentStmt->bind_param("siii", $punchlist->comment, $userId, $insertedRoomItemId, $reportId);
									if(!$insertCommentStmt->execute()) {	
										$isUpdateSuccess = false;
										break;
									}
									$insertCommentStmt->close();
								}
							}
							
							//UPDATE IMAGE TABLE
							if(!empty($punchlist->imageuploaded) && isset($punchlist->imageuploaded)) {
								$updateImageSql = "UPDATE report_images SET room_item_id = ? WHERE image_name = ?";
						
								if($updateImageStmt = $mysqli->prepare($updateImageSql)) {
									$updateImageStmt->bind_param("is", $insertedRoomItemId,$punchlist->imageuploaded);
									if(!$updateImageStmt->execute()) {	
										$isUpdateSuccess = false;
										break;
									}
									$updateImageStmt->close();
								}
							}
							
						} else {
							$isUpdateSuccess = false;
							break;
						}
						
						$insertPunchlistStmt->close();
					} else {
						$isUpdateSuccess = false;
						break;
					}
					
					
					
					$itemStatus = '';
					$itemStatusColor = '';
					
					if($punchlist->statusId == 4) {
						$itemStatus = 'N/A';
						$itemStatusColor = 'green';
					} else if($punchlist->statusId == 3) {
						$itemStatus = 'Incomplete';
						$itemStatusColor = 'red';
					} else if($punchlist->statusId == 2) {
						$itemStatus = 'Pending Review';
						$itemStatusColor = 'orange';
					} else if($punchlist->statusId == 1) {
						$itemStatus = 'Complete';
						$itemStatusColor = 'green';
					}
					
					$this->roomsEmailBody .= '<tr>';
					$this->roomsEmailBody .= '	<td style="text-align: left; font: normal 12px Arial; color: #000">' . $punchlist->name . '</td>';
					$this->roomsEmailBody .= '	<td style="text-align: left; font: normal 12px Arial; color: #000">' . (empty($punchlist->comment)?"":$punchlist->comment) . '</td>';
					$this->roomsEmailBody .= '	<td style="text-align: right; font: normal 12px Arial; color: ' .$itemStatusColor. '">' . $itemStatus . '</td>';
					$this->roomsEmailBody .= '</tr>';
					
				} //END LOOP FOR ROOM ITEMS
				
				$this->roomsEmailBody .= '</table>';
				$this->roomsEmailBody .= '<br/>';
				
			} else {
				$isUpdateSuccess = false;
			}
			
			$insertRoomsStmt->close();
		} else {
			$isUpdateSuccess = false;
		}
		
		return $isUpdateSuccess;
	}
	
	
	public function sendEmailReport($propertyEmails
									, $propertyName
									, $propertyAddress
									, $propertyStatus
									, $propertyCommunity
									, $propertyType
									, $propertyJobType
									, $generalComments
									, $reportId
									, $mysqli) {
		$mailto = $propertyEmails;
		$mailSubject = 'Report for ' . $propertyCommunity . ", " . $propertyName;
		
		$host_url = "http://".$_SERVER['HTTP_HOST'].rtrim(dirname(dirname($_SERVER['PHP_SELF'])), '/\\');
		$replace_title = "<a href='".$host_url."/view_report.html?reportId=".$reportId."'>".$mailSubject."</a>";
		
		$searchArray = array('__PROPERTYCOMMUNITY__'
							, '__PROPERTYNAME__'
							, '__PROPERTYTYPE__'
							, '__PROPERTYJOBTYPE__'
							, '__ADDRESS__'
							, '__REPORTDATE__'
							, '__STATUS__'
							, '__REPORTEDBY__'
							, '__ROOMS__'
							, '__GENERALCOMMENTS__');
							
		$replaceArray = array($propertyCommunity
							, $propertyName
							, $propertyType
							, $propertyJobType
							, $propertyAddress
							, date('m/d/Y g:ia')
							, ($propertyStatus==1?'Open':'Closed/Archived')
							, $_SESSION['user_firstname'] . ' ' . $_SESSION['user_lastname']
							, $this->roomsEmailBody
							, $generalComments);
		
		$emailBody = file_get_contents(dirname(dirname(__FILE__)) . '/email_template/report.tpl');
		$emailBody = str_replace($searchArray, $replaceArray, $emailBody);
		$emailBody = str_replace($mailSubject,$replace_title,$emailBody);
		$host_url = "http://".$_SERVER['HTTP_HOST'].rtrim(dirname(dirname($_SERVER['PHP_SELF'])), '/\\');
		$emailBody = str_replace('__HOST__',$host_url,$emailBody);

		$pdf_file = "../mpdf/pdfs/report.pdf";
		@unlink($pdf_file);
		@shell_exec("rm -rf ".$pdf_file);
		
		$emailBody_pdf = str_replace("<img src='$host_url","<img src='..",str_replace("<img src=\"$host_url","<img src=\"..",$emailBody));
		
		preg_match_all("/<img src=[\"\'](.*?)[\"\']/i",$emailBody_pdf,$array);
		for($i=0;$i<count($array[1]);$i++)
		{
			if(!file_exists($array[1][$i]))
				$emailBody_pdf = str_replace($array[1][$i],"",$emailBody_pdf);
		}
		
		$mpdf=new mPDF('c'); 
		$mpdf->WriteHTML($emailBody_pdf);
		$mpdf->Output($pdf_file);
		
		$mailHeaders = "From: Paxis Group <no-reply@Paxisgroup.com> \r\n";
		$mailHeaders .= "Reply-To: Paxis Group <no-reply@Paxisgroup.com>\r\n";
		$mailHeaders .= "Return-Path: Paxis Group <no-reply@Paxisgroup.com>\r\n";
		$mailHeaders .= "Bcc: Wendell Malpas <wendell.malpas@gmail.com>\r\n";
		$mailHeaders .= "X-Mailer: PHP v" .phpversion(). "\r\n";
		$mailHeaders .= "MIME-Version: 1.0\r\n";
		$mailHeaders .= "Content-Type: text/html; charset=utf-8";
		
		if($_SESSION['user_type'] == 5)
		{
			$insertSubContractorSql = "INSERT INTO subcontractor_emails(emailAddress, subject, emailBody, headers, sent, timestamp) VALUES(?, ?, ?, ?, 0, NOW())";
						
			if($insertSubContractorStmt = $mysqli->prepare($insertSubContractorSql)) {
				$insertSubContractorStmt->bind_param("ssss", $mailto, $mailSubject, $emailBody, $mailHeaders);
				if($insertSubContractorStmt->execute())
					return true;
				else
					return false;
			}
		}
		else
		{
			$attachMailer = new attach_mailer("Paxis Group", "no-reply@Paxisgroup.com", $mailto, $cc = "", $bcc="wendell.malpas@gmail.com" , $mailSubject, $emailBody);
			
			if(file_exists($pdf_file))
				$attachMailer->create_attachment_part($pdf_file,"attachment","application/pdf"); 
			
			return $attachMailer->process_mail();
			
			
			//return mail($mailto, $mailSubject, $emailBody, $mailHeaders);
		}
	}
	
	public function deleteRoomTemplate($roomTemplateId) {
		$result['success'] = false;
		$result['message'] = '';
		
		if(!isset($roomTemplateId) || empty($roomTemplateId)
		){
			$result['message'] = 'One of the required fields is missing.';
		} else {
			$mysqli = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
			if ($mysqli->connect_errno) {
				$result['message'] = "Failed to connect to MySQL: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error;
			}
			
			
			$deleteSql = "DELETE FROM room_templates WHERE id=?";
			if ($deleteStmt = $mysqli->prepare($deleteSql)) {
				$deleteStmt->bind_param("i", $roomTemplateId);
				
				if($deleteStmt->execute()) {
					$deleteSql = "DELETE FROM room_template_items WHERE room_template_id=?";
					if ($deleteStmt2 = $mysqli->prepare($deleteSql)) {
						$deleteStmt2->bind_param("i", $roomTemplateId);
						$deleteStmt2->execute();
						$deleteStmt2->close();
					}
					
					$result['success'] = true;
					$result['message'] = "Room template successfully deleted.";
				} else {
					$result['message'] = "Sorry, there has been a problem processing your request.";
				}
				
				$deleteStmt->close();
			} else {
				$result['message'] = "Sorry, there has been a problem processing your request.";
			}
			
			
			$mysqli->close();
		}
		
		header('Content-type: application/json');
		echo json_encode($result);
	}
	
	public function updateRoomTemplateInfo($roomTemplateId, $roomTemplateName,$roomTemplateItems, $itemsIdArray, $workCategoryItems) {
		$result['success'] = false;
		$result['message'] = '';
		
		//$room_template_estimates = new Dynamo("room_template_estimates");
		
		if(!isset($roomTemplateId) || empty($roomTemplateId)
		){
			$result['message'] = 'One of the required fields is missing.';
		} else {
			$mysqli = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
			if ($mysqli->connect_errno) {
				$result['message'] = "Failed to connect to MySQL: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error;
			}
			
			$updateSql = "UPDATE room_templates
								 SET name = ?
							  WHERE id = ?";
			
			if ($updateStmt = $mysqli->prepare($updateSql)) {
				$updateStmt->bind_param("si", $roomTemplateName, $roomTemplateId);
				
				if($updateStmt->execute()) {
					//get existing items
					$query = "SELECT id FROM room_template_items where room_template_id = ?";
					if ($stmt = $mysqli->prepare($query)) 
					{
						$stmt->bind_param("i", $roomTemplateId);
						$stmt->execute();
						$stmt->bind_result($id);
						
						$dataCtr = 0;
						while ($stmt->fetch()) {
							$existing_room_templates_items[$dataCtr] = $id;
							$dataCtr ++;
						}
					}
					
					//get changeable estimates
					$query = "SELECT id FROM estimates WHERE is_submitted = 0";
					if ($stmt = $mysqli->prepare($query)) 
					{
						$stmt->execute();
						$stmt->bind_result($id);
						
						$dataCtr = 0;
						while ($stmt->fetch()) {
							$estimateIdArray[$dataCtr] = $id;
							$dataCtr ++;
						}
						
						if(count($estimateIdArray) > 0)
						{
							$estimateIdString = implode(",",$estimateIdArray);
							
							$query = "SELECT id,estimate_id FROM estimate_rooms WHERE estimate_id IN(".$estimateIdString.") AND room_template_id = ?";
							if ($stmt = $mysqli->prepare($query)) 
							{
								$stmt->bind_param("i",  $roomTemplateId);	
								$stmt->execute();
								$stmt->bind_result($id,$estimate_id);
								
								$dataCtr = 0;
								while ($stmt->fetch()) {
									$estimateRoomIdArray[$dataCtr] = array('id'=>$id,'estimate_id'=>$estimate_id);
									$dataCtr ++;
								}
							}
						}
					}
					
					//get ids to delete if any
					$arrayDel = array_diff($existing_room_templates_items,$itemsIdArray);
					
					if(count($arrayDel) > 0)
					{
						$stringDel = implode(",",$arrayDel);
						$deleteSql = "DELETE FROM room_template_items WHERE id IN(".$stringDel.")";
						
						if ($deleteStmt = $mysqli->prepare($deleteSql)) {
							$deleteStmt->execute();
							$deleteStmt->close();
						}
						
						$estimate_room_items_obj = new Dynamo("estimate_room_items");
						$query = "SELECT id, estimate_id FROM estimate_room_items WHERE room_template_item_id IN(".$stringDel.") AND estimate_id IN(".$estimateIdString.")";
						$estimate_room_items_array = $estimate_room_items_obj->customFetchQuery($query);
						for($i=0;$i<count($estimate_room_items_array);$i++)
						{
							$query = "DELETE FROM estimate_room_items_units WHERE estimate_id = ".$estimate_room_items_array[$i]['estimate_id']." AND estimate_room_items_id = ".$estimate_room_items_array[$i]['id'];
							
							$estimate_room_items_obj->customExecuteQuery($query);
						}
						
						$query = "DELETE FROM estimate_room_items WHERE room_template_item_id IN(".$stringDel.") AND estimate_id IN(".$estimateIdString.")";
						if ($deleteStmt = $mysqli->prepare($deleteSql)) {
							$deleteStmt->execute();
							$deleteStmt->close();
						}
					}
					
					/*$deleteSql = "DELETE FROM room_template_items WHERE room_template_id=?";
					if ($deleteStmt = $mysqli->prepare($deleteSql)) {
						$deleteStmt->bind_param("i", $roomTemplateId);
						$deleteStmt->execute();
						$deleteStmt->close();
					}
					*/
					//insert work categories
					if(!empty($workCategoryItems))
					{
						$listCategoryItems = explode('|', $workCategoryItems);
					}
					
					//Insert new items.
					if(!empty($roomTemplateItems)) {
						$listItems = explode('|', $roomTemplateItems);
						
						if(count($listItems) > 0) {
							$isItemsInserted = false;
							
							$countWorkCategory = -1;
							foreach($listItems as $item):
								$countWorkCategory += 1;
								if($itemsIdArray[$countWorkCategory])
								{
									$insertSql = "UPDATE room_template_items SET room_template_id = ?, name = ?, work_category_id = ? WHERE id = ".$itemsIdArray[$countWorkCategory];
									$room_template_items_id = $itemsIdArray[$countWorkCategory];
								}
								else
								{
									$insertSql = "INSERT INTO room_template_items(room_template_id, name, work_category_id, date_created) VALUES(?, ?,?, NOW())";
									$room_template_items_obj = new Dynamo('room_template_items');
									
									$query = "SHOW TABLE STATUS LIKE 'room_template_items'";
									$roomTemplatesArray = $room_template_items_obj->customFetchQuery($query);
									
									$room_template_items_id = $roomTemplatesArray[0]['Auto_increment'];
									
									if(count($estimateRoomIdArray) > 0)
									{	
										$query = "INSERT INTO estimate_room_items(`estimate_id`,`room_id`,`room_template_item_id`,`name`,`date_created`)";
										
										for($i=0;$i<count($estimateRoomIdArray);$i++)
										{
											$query .= "VALUES(".$estimateRoomIdArray[$i]['estimate_id'].",".$estimateRoomIdArray[$i]['id'].",".$roomTemplatesArray[0]['Auto_increment'].",\"".$item."\",NOW()),";
										}
										
										$query = substr($query,0,-1);
										
										$room_template_items_obj->customExecuteQuery($query);
									}
								}
								
								if ($insertStmt = $mysqli->prepare($insertSql)) {
									$insertStmt->bind_param("isi", $roomTemplateId, $item,$listCategoryItems[$countWorkCategory]);
									
									if($insertStmt->execute()) {		
										/*$query = "SELECT * FROM room_template_estimates WHERE room_template_id = $roomTemplateId AND room_template_items_id = $room_template_items_id";

										$array_room_template_estimates = array();
										$array_room_template_estimates = $room_template_estimates->customFetchQuery($query);
										
										if(count($array_room_template_estimates) <= 0)
										{
											$id = $room_template_estimates->getMaxId();
											
											$query = "INSERT INTO room_template_estimates (`id`,`room_template_id`,`room_template_items_id`,`item_name`,`unit_of_measure`,`timestamp`) VALUES($id,$roomTemplateId,$room_template_items_id,\"".addslashes(stripslashes($item))."\",1,NOW())";
											$room_template_estimates->customExecuteQuery($query);
										}*/
										
										$isItemsInserted = true;
									} else {
										$isItemsInserted = false;
										break;
									}
									
									$insertStmt->close();
								} else {
									$isItemsInserted = false;
									break;
								}
							endforeach;
							
							if($isItemsInserted) {
								$result['success'] = true;
								$result['message'] = "Room template successfully updated.";
							} else {
								$result['message'] = "Sorry, there has been a problem processing your request.";
							}
						}
					} else {
						$result['success'] = true;
						$result['message'] = "Room template successfully updated.";
					}
					
				} else {
					if(strrpos($mysqli->error, 'Duplicate entry') == false) {
						$result['message'] = "The room name you provided already exist.";
					} else {
						$result['message'] = "Sorry, there has been a problem processing your request.";
					}
				}
				
				$updateStmt->close();
			} else {
				//$mysqli->error
				$result['message'] = 'Sorry, there has been a problem processing your request.';
			}
			
			$mysqli->close();
		}
		
		header('Content-type: application/json');
		echo json_encode($result);
	}
	
	public function getRoomTemplateInfo($roomTemplateId, $isJson = true) {
		$result['success'] = false;
		$result['message'] = '';
		$result['data'] = '';
		
		$data = array();
		
		$mysqli = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
		if ($mysqli->connect_errno) {
			$result['message'] = "Failed to connect to MySQL: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error;
		}
		
		$sql = "SELECT 
					 name
				FROM room_templates
				WHERE id=?
				";
		
		if ($stmt = $mysqli->prepare($sql)) {
			$stmt->bind_param("i", $roomTemplateId);
			$stmt->execute();
			$stmt->bind_result($roomName);
			
			while ($stmt->fetch()) {
				$data = array(
							 'id'=>$roomTemplateId
							,'name'=>$roomName
						);
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
	
	public function addRoomTemplate($roomName, $roomTemplateItems, $workCategoryItems) {
		$result['success'] = false;
		$result['message'] = '';
		$result['id'] = '';
		
		//$room_template_estimates = new Dynamo("room_template_estimates");
													
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
							$result['id'] = $insertId = $mysqli->insert_id;
							
							//insert work categories
							if(!empty($workCategoryItems))
								$listCategoryItems = explode('|', $workCategoryItems);
								
							//Insert new items.
							if(!empty($roomTemplateItems)) {
								$listItems = explode('|', $roomTemplateItems);
								
								if(count($listItems) > 0) {
									$isItemsInserted = false;
									
									$countWorkCategory = -1;
									
									foreach($listItems as $item):
										$countWorkCategory += 1;
										
										$insertSql = "INSERT INTO room_template_items(room_template_id, name, work_category_id, date_created) VALUES(?, ?, ?, NOW())";
										
										if ($insertStmt2 = $mysqli->prepare($insertSql)) {
											$insertStmt2->bind_param("isi", $insertId , $item,$listCategoryItems[$countWorkCategory]);
											
											if($insertStmt2->execute()) {
												$room_template_items_id = $mysqli->insert_id;
												/*$query = "SELECT * FROM room_template_estimates WHERE room_template_id = $insertId AND room_template_items_id = $room_template_items_id";
												
												$array_room_template_estimates = array();
												$array_room_template_estimates = $room_template_estimates->customFetchQuery($query);
												
												if(count($array_room_template_estimates) <= 0)
												{
													$id = $room_template_estimates->getMaxId();
													
													$query = "INSERT INTO room_template_estimates (`id`,`room_template_id`,`room_template_items_id`,`item_name`,`unit_of_measure`,`timestamp`) VALUES($id,$insertId,$room_template_items_id,\"".addslashes(stripslashes($item))."\",1,NOW())";
													$room_template_estimates->customExecuteQuery($query);
												}*/
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
		$result['room_id'] = '';
		
		$data = array();
		
		$mysqli = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
		if ($mysqli->connect_errno) {
			$result['message'] = "Failed to connect to MySQL: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error;
		}
		
		$query = "SELECT MAX(id) + 1 AS id FROM `community_report_rooms`";
		if ($stmt = $mysqli->prepare($query)) 
		{
			$stmt->execute();
			$stmt->bind_result($room_id);
			while ($stmt->fetch()) {
				$result['room_id'] = $room_id;	
			}
		}	
			
		$sql = "SELECT 
					 id
					,name
					,work_category_id
				FROM room_template_items
				WHERE room_template_id=?
				ORDER BY name
				";
		
		if ($stmt = $mysqli->prepare($sql)) {
			$stmt->bind_param("i", $roomTemplateId);
			$stmt->execute();
			$stmt->bind_result($roomItemId, $roomItemDescription,$workCategoryId);
			
			$dateCtr = 0;
			while ($stmt->fetch()) {
				$data[$dateCtr] = array(
							 'roomTemplateItemId'=>$roomItemId
							,'name'=>$roomItemDescription
							,'work_category_id'=>$workCategoryId
							,'statusId'=>''
							,'comment'=>""
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
