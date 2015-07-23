<?php
require_once(dirname(dirname(dirname(__FILE__))) . '/modules/Property.class.php');
require_once(dirname(dirname(dirname(__FILE__))) . '/modules/Room.class.php');
require_once(dirname(dirname(dirname(__FILE__))) . '/modules/Report.class.php');
require_once(dirname(dirname(dirname(__FILE__))) . '/modules/Estimates.class.php');
require_once(dirname(dirname(dirname(__FILE__))) . '/modules/Dynamo.class.php');

$propertyId = isset($_GET['propertyId'])?$_GET['propertyId']:0;

if($propertyId != 0) {
	$propertyObj = new Property();
	$propertyInfo = $propertyObj->getPropertyInfo($propertyId, false);
	
	$roomObj = new Room();
	$listRoomTemplates = $roomObj->getRoomTemplates(false);
	
	if(!$propertyInfo) {	
		echo "No property found.";
		return;
	}
	
	$reportObj = new Report();
	
	$reportId = $reportObj->getPreviousReportId($propertyId, false);
	
	if(trim($reportId) != '')
	{
		$reportInfo = $reportObj->getReportDetails($reportId, false);
		
		$reports_obj = new Dynamo("reports");
		$reportsArray = $reports_obj->getAll("WHERE id = ".$reportId);
		
		if($reportsArray[0]['is_submitted'] == 0)
		{
			?>
			<script type="text/javascript">
				window.location.href = "edit_property.php?propertyId=<?php print $propertyId; ?>";
			</script>
			<?php
			exit;	
		}
	}
	
	$estimatesObj = new Estimates();		
	$estimatesId = $estimatesObj->getPreviousEstimatesId($propertyId, false);
	$estimateInfo = $estimatesObj->getEstimateDetails($estimatesId, false);	
	
	if(count($estimateInfo['rooms']) > 0 && count($reportInfo['rooms']) > 0)
	{
		$arrayRoomsExisting = array();
		for($i=0;$i<count($estimateInfo['rooms']);$i++)
		{
			$found_value = false;
			for($j=0;$j<count($reportInfo['rooms']);$j++)
			{
				if($estimateInfo['rooms'][$i]["roomName"] ==  $reportInfo['rooms'][$j]["roomName"] &&
				 	$estimateInfo['rooms'][$i]["roomTemplateId"] ==  $reportInfo['rooms'][$j]["roomTemplateId"])
				{
					//$arrayRoomsExisting[] = $reportInfo['rooms'][$j];
					$found_value = true;
				}
			}
			
			if($found_value == false)
			{
				for($p=0;$p<count($estimateInfo['rooms'][$i]['items']);$p++)
				{
					for($n=0;$n<count($estimateInfo['rooms'][$i]['items'][$p]['arrayRoomItemsUnits']);$n++)
					{
						$estimateInfo['rooms'][$i]['items'][$p]['arrayRoomItemsUnits'][$n]['item_name'] = $estimateInfo['rooms'][$i]['items'][$p]['arrayRoomItemsUnits'][$n]['estimate_name'];
					}
				}
				
				$arrayRoomsExisting[] = $estimateInfo['rooms'][$i];
				
				for($m=0;$m<count($estimateInfo['rooms'][$i]['items']);$m++)
				{
					if(count($estimateInfo['rooms'][$i]['items'][$m]['arrayRoomItemsUnits']) > 0)
					{
						for($k=0;$k<count($estimateInfo['rooms'][$i]['items'][$m]['arrayRoomItemsUnits']);$k++)
						{
							$array["itemId"] = $estimateInfo['rooms'][$i]['items'][$m]['itemId'];
							$array["itemTemplateId"] = $estimateInfo['rooms'][$i]['items'][$m]['itemTemplateId'];
							
							$array["itemName"] = $estimateInfo['rooms'][$i]['items'][$m]['arrayRoomItemsUnits'][$k]['estimate_name'];
							$array["statusId"] = 2;
							$array["isEstimate"] = 1;
							$array["statusClass"] = "btn-success";
							$array["statusName"] = "N/A";
							$array["work_category_id"] = 0;
							$array["arrayRoomItemsUnits"] = array();
							$array["comments"] = array();
							$array["images"] = array();
							$array["comment_thread"] = array();
							$array["image_thread"] = array();
							
							$arrayRoomsExisting[count($arrayRoomsExisting)-1]['items'][] = $array;
						}
					}
				}
			}
		}
		
		if(count($arrayRoomsExisting) > 0)
			$reportInfo['rooms'] = array_merge($reportInfo['rooms'],$arrayRoomsExisting);
		
		//$reportInfo['rooms'] = $arrayRoomsExisting;
	}
	
	if(count($reportInfo['rooms']) <= 0)
	{
		if(count($estimateInfo) > 0)
			$reportInfo = $estimateInfo;
		
		$new_report = true;
	}
	else
	{
		$new_report = false;
		
		//which came first report or estimate
		
		$usersObj = new Dynamo("users");
		$array_users = $usersObj->getAllWithId_default(false,"id");
		
		$subContractorsObj = new Dynamo("sub_contractors");
		$array_sub_contractors = $subContractorsObj->getAllWithId_default(false,"id");
		
		$companiesObj = new Dynamo("companies");
		$array_companies = $companiesObj->getAllWithId_default(false,"id");
		
		$reports = $reportObj->getReportsSummary($propertyId, false,$array_users,$array_sub_contractors,$array_companies);
		$estimates = $estimatesObj->getEstimatesSummary($propertyId, false,$array_users,$array_companies);
		
		$array_reports_estimates = array();
		for($i=0;$i<count($reports);$i++)
		{
			$reports[$i]['report'] = true;
			$array_reports_estimates[strtotime($reports[$i]['dateReported'])] = $reports[$i];
		}
		for($i=0;$i<count($estimates);$i++)
		{
			$array_reports_estimates[strtotime($estimates[$i]['dateReported'])] = $estimates[$i];
		}
		krsort($array_reports_estimates);
		
		if(count($array_reports_estimates) > 0)
		{
			foreach($array_reports_estimates as $key => $value)
			{
				if($array_reports_estimates[$key]['report'] == 1)
					$report_first = true;
				else
					$report_first = false;
				
				break;
			}
		}
	}
	
	if(count($reportInfo['rooms']) > 0)
	{
		$reports_obj = new Dynamo("reports");
		$room_templates_obj = new Dynamo("room_templates");
		$report_rooms_obj = new Dynamo("report_rooms");
		$report_room_items_obj = new Dynamo("report_room_items");
		$room_template_items_obj = new Dynamo("room_template_items");
		
		$reportId = $_REQUEST['reportId'] = $reports_obj->getMaxId();
		
		$room_template_items_array =  $room_template_items_obj->getAll();
		
		if($_SESSION['user_type'] == 5)
			$subcontractor = 1;
		else
			$subcontractor = 0;
			
		$query = "INSERT INTO reports (`id`,`property_id`,`date_reported`,`status_id`,`reported_by`,`is_submitted`,`is_saved`,`is_closed`,`subcontractor`) 
		VALUES(".$_REQUEST['reportId'].",".$reportInfo['propertyId'].",NOW(),0,".$_SESSION['user_id'].",0,1,0,{$subcontractor})";
		$reports_obj->customExecuteQuery($query);
		
		if(count($reportInfo['rooms']) > 0)
		{
			$query = "INSERT INTO report_rooms VALUES";
			$query2 = "INSERT INTO report_room_items (`id`,`report_id`,`room_id`,`room_template_item_id`,`name`,`status_id`,`is_estimate`,`date_created`) VALUES";
			
			$query_first = false;
			$query_second = false;
			
			$maxIdReportRooms = $report_rooms_obj->getMaxId();
			$reportRoomMaxId = $report_room_items_obj->getMaxId();
			
			for($i=0;$i<count($reportInfo['rooms']);$i++)
			{	
				if(count($reportInfo['rooms'][$i]['items']) <= 0)
					continue;
				
				$query_first = true;
				
				$query .= "({$maxIdReportRooms},".$_REQUEST['reportId'].",".$reportInfo['rooms'][$i]['roomTemplateId'].",\"".$reportInfo['rooms'][$i]['roomName']."\",NOW(),".$_SESSION['user_id']."),";
				
				for($j=0;$j<count($reportInfo['rooms'][$i]['items']);$j++)
				{
					if($new_report) //show only line items with estimates else show all!
					{
						for($k=0;$k<count($room_template_items_array);$k++)
						{
							if($room_template_items_array[$k]['name'] == $reportInfo['rooms'][$i]['items'][$j]['itemName'] && $reportInfo['rooms'][$i]['roomTemplateId'] == $room_template_items_array[$k]['room_template_id'])
							{
								$room_template_items_id = $room_template_items_array[$k]['id'];
								break;
							}
						}
					}
					else
					{
						$room_template_items_id = $reportInfo['rooms'][$i]['items'][$j]['itemTemplateId'];
						if($reportInfo['rooms'][$i]['items'][$j]['isEstimate'] > 0)
							$isEstimate = $reportInfo['rooms'][$i]['items'][$j]['isEstimate'];
						else
							$isEstimate = 0;
					}
					
					$statusId = $reportInfo['rooms'][$i]['items'][$j]['statusId'];
					
					if(trim($statusId) == '')
						$statusId = 2;
					
					/*if($new_report == false && $report_first == false)
						$statusId = 2;*/
						
					$query_second = true;
					
					if(!$isEstimate)
						$isEstimate = 0;
						
					$query2 .= "(".$reportRoomMaxId.",".$_REQUEST['reportId'].",{$maxIdReportRooms},".$room_template_items_id.",'".addslashes(stripslashes($reportInfo['rooms'][$i]['items'][$j]['itemName']))."',".$statusId.",".$isEstimate.",NOW()),";
					
					if(count($reportInfo['rooms'][$i]['items'][$j]['arrayRoomItemsUnits']) > 0 && $new_report)
					{
						$arrayEstimates = $reportInfo['rooms'][$i]['items'][$j]['arrayRoomItemsUnits'];
						
						for($k=0;$k<count($arrayEstimates);$k++)
						{
							$reportRoomMaxId += 1;
							$query2 .= "(".$reportRoomMaxId.",".$_REQUEST['reportId'].",{$maxIdReportRooms},".$room_template_items_id.",'".addslashes(stripslashes($arrayEstimates[$k]['estimate_name']))."',".$statusId.",1,NOW()),";
						}
					}
					else
					{
						$arrayEstimates = $reportInfo['rooms'][$i]['items'][$j]['arrayRoomItemsUnits'];
						
						if($report_first == false)
							$statusId = 2;
							
						for($k=0;$k<count($arrayEstimates);$k++)
						{
							$do_extra_query = true;
							for($m=0;$m<count($reportInfo['rooms'][$i]['items']);$m++)
							{
								if($reportInfo['rooms'][$i]['items'][$m]['itemName'] == $arrayEstimates[$k]['item_name'])
								{
									$do_extra_query = false;	
								}
							}
							
							if($do_extra_query)
							{
								$reportRoomMaxId += 1;
								$query2 .= "(".$reportRoomMaxId.",".$_REQUEST['reportId'].",{$maxIdReportRooms},".$room_template_items_id.",'".addslashes(stripslashes($arrayEstimates[$k]['item_name']))."',".$statusId.",1,NOW()),";
							}
						}
					}
					
					$reportRoomMaxId += 1;
				}
				
				$maxIdReportRooms += 1;
			}
			
			if($query_first)
			{
				$query = substr($query,0,-1);
				$report_rooms_obj->customExecuteQuery($query);
			}
			
			if($query_second)
			{
				$query2 = substr($query2,0,-1);
				$report_room_items_obj->customExecuteQuery($query2);
			}
		}
		
		?>
		<script type="text/javascript">
		window.location.href = "edit_report.html?propertyId="+<?php print $propertyId; ?>+"&reportId="+<?php print $_REQUEST['reportId']; ?>;
		</script>
		<?php
		exit;
	}

	$report_images = new Dynamo("report_images");
	$array_report_images = $report_images->getAllWithId_default("WHERE property_id = ".$propertyId,'room_item_id');
?>

	<!-- ADD REPORT -->
	<form method="POST" class="form-horizontal" id="addReportForm" onsubmit="return false">
	<input type="hidden" id="_BASENAME" name="baseName" value="<?php echo __BASENAME__; ?>" />
	<input type="hidden" id="_USERID" name="userId" value="<?php echo $_SESSION['user_id']; ?>" />
	<input type="hidden" id="_PROPERTYID" name="propertyId" value="<?php echo $propertyId; ?>" />
	<input type="hidden" id="_ROOMS" name="rooms" value='<?php echo json_encode($reportInfo['rooms']); ?>' />
	<input type="hidden" id="_REPORTID" name="reportId" value='' />
	<input type="hidden" id="_PROPERTYCOMMUNITY" name="propertyCommunity" value='<?php echo $propertyInfo['community'];?>' />
	<input type="hidden" id="_PROPERTYTYPE" name="propertyType" value='<?php echo $propertyInfo['property_type'];?>' />
	<input type="hidden" id="_PROPERTYJOBTYPE" name="propertyJobType" value='<?php echo $propertyInfo['job_type'];?>' />
	<input type="hidden" id="_PROPERTYNAME" name="propertyName" value='<?php echo $propertyInfo['name'];?>' />
	<input type="hidden" id="_PROPERTYADDRESS" name="propertyName" value='<?php echo $propertyInfo['address'];?>' />
	<input type="hidden" id="_PROPERTYEMAILS" name="propertyName" value='<?php echo $propertyInfo['emails'];?>' />
	<input type="hidden" id="_PROPERTYSTATUS" name="propertyName" value='<?php echo $propertyInfo['status'];?>' />

	
	<div class="pull-left"><h4><?php echo $propertyInfo['community']; ?>, <?php echo $propertyInfo['name']; ?> - Add Report</h4></div>
	<div class="pull-right"><a href="edit_property.php?propertyId=<?php echo $propertyId;?>" class="btn btn-small btn-warning"><i class="icon-info-sign icon-white"></i> Property Details</a></div>
	<div class="clearfix"></div>
	
	<div id="addReportStatus"></div>
	
	<div id="rooms-wrapper" class="accordion">
		<?php
			$tmpRoomIndex = 0;
			if(count($reportInfo['rooms']) > 0)
			{
			foreach($reportInfo['rooms'] as $room):
				// var_dump($room);
			?>
				<div class="accordion-group" id="room_<?php echo $tmpRoomIndex;?>">
					<div class="accordion-heading">
						<div class="row-fluid">
							<div class="room-name">
								<a href="#collapse_<?php echo $tmpRoomIndex;?>" data-parent="#rooms-wrapper" data-toggle="collapse" class="accordion-toggle"><?php echo $room['roomName'];?></a>
							</div>
							<div class="room-name-action">
								<span class="label label-warning" id="room_status_<?php echo $tmpRoomIndex; ?>">Pending Review</span> &nbsp;
								<button class="btn btn-danger btn-small" onclick="return removeRoom('room_<?php echo $tmpRoomIndex;?>', '<?php echo $tmpRoomIndex;?>', <?php echo $room['roomId']; ?>)"><i class="icon-trash icon-white"></i></button>
							</div>
							<div class="clearfix"></div>
						</div>
						
					</div>
					<div class="accordion-body collapse" id="collapse_<?php echo $tmpRoomIndex;?>">
						<div class="accordion-inner report-room-items">
							<?php 
							if(isset($room['items'])) {
							?>
								<?php
									$roomItemIndex = 0;
									foreach($room['items'] as $item):
										// var_dump($item);
										// $existingComment = empty($item['comments'][0]['comment']) ? "" : htmlentities($item['comments'][0]['comment']);
										$statusClassname = (empty($item['statusClass'])?'btn-warning':$item['statusClass']);
									?>
									<input type="hidden" name="roomStatusId_<?php echo $tmpRoomIndex . '_' . $roomItemIndex;?>" id="roomStatusId_<?php echo $tmpRoomIndex . '_' . $roomItemIndex;?>" value="<?php echo $item['statusId']; ?>"/>
									<input type="hidden" name="roomItemComment_<?php echo $tmpRoomIndex . '_' . $roomItemIndex;?>" id="roomItemComment_<?php echo $tmpRoomIndex . '_' . $roomItemIndex;?>" value=""/>
									<input type="hidden" name="roomItemCommentThread_<?php echo $tmpRoomIndex . '_' . $roomItemIndex;?>" id="roomItemCommentThread_<?php echo $tmpRoomIndex . '_' . $roomItemIndex;?>" value='<?php echo json_encode($item['comment_thread']); ?>'/>
									
									
									<div class="roomitem-group">
										<div class="roomitem-desc"><?php echo $item['itemName']; ?></div>
										<div class="roomitem-action">	
											<a href="#statusModal" role="button" onclick="setRoomItemIndex('<?php echo $tmpRoomIndex;?>','<?php echo $roomItemIndex; ?>', '', this)" data-toggle="modal" class="btn <?php echo $statusClassname; ?>" id="status_btn_<?php echo $tmpRoomIndex;?>_<?php echo $roomItemIndex; ?>"><?php echo (empty($item['statusName'])?'Pending Review':$item['statusName']); ?></a>
										</div>
										<div class="roomitem-comment" id="roomItemCommentContainer_<?php echo $tmpRoomIndex . '_' . $roomItemIndex;?>">&nbsp;</div>
										<div class="roomitem-image-upload" id="roomItemImageContainer_<?php echo $tmpRoomIndex . '_' . $roomItemIndex;?>"></div>
										<div class="clearfix"></div>
									</div>
									<?php
										$roomItemIndex++;
									endforeach;
								?>
							<?php 
							}
							?>
						</div>
					</div>
				</div>
			<?php
				$tmpRoomIndex++;
			endforeach;
			}
		?>
	</div>
	
	<a href="#addRoomModal" role="button" class="btn" data-toggle="modal"><i class="icon-plus"></i> Add Room</a>
	
	<br/><br/><br/>
	<strong>General Comments:</strong>
	<div class="existingComments">
		<table class="table" id="reportExistingComments">
		<?php
		if(isset($reportInfo['reportComments'])) {
			foreach($reportInfo['reportComments'] as $reportComment):
				$comment =  !isset($reportComment['comment'])?'':$reportComment['comment'];
				
				if($reportComment['isSubmitted'] == 1) {
					$commentDate = (!isset($reportComment['date'])?"": $reportComment['date']);
					$commentedBy = !isset($reportComment['user'])?'':$reportComment['user'];
			?>
				<tr>
					<td><?php echo $commentDate;?></td>
					<td><?php echo $comment;?></td>
					<td>by: <?php echo $commentedBy;?></td>
				</tr>
			<?php
				} 
			endforeach;
		}
		?>
		</table>
	</div>
	<textarea name="reportComment" id="reportComment" rows="3" class="span6" placeholder="Write your comment for this report here..."></textarea>
	
	<br /><br />
	<button class="btn btn-primary" type="submit"  id="submitReportBtn" onclick="submitReport();" disabled><i class="icon-ok icon-white"></i> Submit Report</button>
	</form>
	<!-- END ADD REPORT -->
	
	
	<!-- MODAL POPUP -->
	<div id="submitConfirmationModal" class="modal hide fade" tabindex="-1" role="dialog" aria-hidden="true">
		<div class="modal-header">
			<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
			<h3>Submit Report Confirmation</h3>
		</div>
		
		<div class="alert alert-warning hide" id="submitReportStatus"></div>
		
		<form id="submitConfirmForm" method="post" onsubmit="return confirmSubmitReport(this);">
		<input type="hidden" name="projectStatus" id="projectStatus" value="1" />
		<div class="modal-body">
			<div class="itemscompleted-wrapper">
				You have marked all rooms as <span class="label label-success">Complete</span>. Please indicate if this project is <strong>closed</strong>.
				
				<br/>
				<div id="submitReportStatus"></div>
				<br/>
				
				<div class="btn-group radioPropertyStatus" data-toggle-name="is_private" data-toggle="buttons-radio" >
				  <button type="button" value="1" class="btn btn-danger active" data-toggle="button">Open</button>
				  <button type="button" value="0" class="btn btn-success" data-toggle="button">Closed/Archived</button>
				</div>
				<br/><br/>
			</div>
			
			Email reports will be sent to:
			<ul>
			<?php 
				$emails = explode(',', $propertyInfo['emails']);
				
				foreach($emails as $email):
				?>
					<li><?php echo $email; ?></li>
				<?php
				endforeach;
			?>
			</ul>
		</div>
		<div class="modal-footer-custom">
			<button class="btn btn-primary btnSubmitReport">Submit Report</button>
			<button class="btn" data-dismiss="modal" aria-hidden="true">Cancel</button>
		</div>
		</form>
	</div>	
	
	<div id="statusModal" class="modal hide fade" tabindex="-1" role="dialog" aria-hidden="true">
		<div class="modal-header">
			<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
			<h3>Comment</h3>
		</div>
		
		<form id="statusForm" name="statusForm" method="post" onSubmit="return submitReportIncompleteStatus();">
		<input type="hidden" name="itemId" id="itemId" value="" />
		<input type="hidden" name="reportId" id="reportId" value="" />
		<input type="hidden" name="imageItemId" id="imageItemId" value="" />
		<div class="modal-body">
			<div id="setStatusMessage"></div>
			<div class="comment-wrapper">
				<div class="existing-comments">
					<strong>Existing comments for this item: </strong>
					<table class="table table-striped" id="existingComments">
					
					</table>
				</div>
				<textarea name="itemComment" class="comment" id="itemComment" rows="3" placeholder="Write your comment here..."></textarea>
			</div>
			
		</div>
		<div class="modal-footer-custom">
			<div id="file-uploader-report" style="float:left;">		
				<noscript>			
					<p>Please enable JavaScript to use file uploader.</p>
					<!-- or put a simple form for upload here -->
				</noscript>         
			</div>
			<div id="loader"><img src="images/loading.gif" /></div>
			<button class="btn btn-primary">Submit</button>
			<button class="btn" data-dismiss="modal" aria-hidden="true">Close</button>
		</div>
		</form>
	</div>
	
	<div id="statusModal_old" class="modal hide fade" tabindex="-1" role="dialog" aria-hidden="true">
		<div class="modal-header">
			<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
			<h3>Status</h3>
		</div>
		
		<form id="statusForm" method="post" onsubmit="return submitReportItemStatus();">
		<input type="hidden" name="reportStatus" id="reportStatus" value="" />
		<input type="hidden" name="reportStatusName" id="reportStatusName" value="" />
		<input type="hidden" name="reportStatusClassName" id="reportStatusClassName" value="" />
		<input type="hidden" name="imageItemId" id="imageItemId" value="" />
		<div class="modal-body">
			Please choose a status:
			<br/>
			<div id="setStatusMessage"></div>
			
			<div class="btn-group reportStatus" data-toggle="buttons-radio">
				
				<?php
				$listStatus = $reportObj->getReportStatus(false);
				
				foreach($listStatus as $status):
				?>
					<button name="state" type="button" class="btn <?php echo $status['className']; ?>" value="<?php echo $status['id']; ?>" id="reportStatusBtn_<?php echo $status['id']; ?>"><?php echo $status['name']; ?></button>
				<?php
				endforeach;
				?>
				
			</div>
			
			<br/>
			<div class="comment-wrapper">
				<div class="existing-comments">
					<strong>Existing comments for this item: </strong>
					<table class="table table-striped" id="existingComments">
					</table>
				</div>
				<textarea name="itemComment" class="comment" id="itemComment" rows="3" placeholder="Write your comment here..."></textarea>
			</div>
			
		</div>
		<div class="modal-footer-custom">
			<div id="file-uploader-report" style="float:left;">		
				<noscript>			
					<p>Please enable JavaScript to use file uploader.</p>
					<!-- or put a simple form for upload here -->
				</noscript>         
			</div>
			<div id="loader"><img src="images/loading.gif" /></div>
			<button class="btn btn-primary">Submit</button>
			<button class="btn" data-dismiss="modal" aria-hidden="true">Close</button>
		</div>
		</form>
	</div>
	
	<div id="statusModal_old" class="modal hide fade" tabindex="-1" role="dialog" aria-hidden="true">
		<div class="modal-header">
			<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
			<h3>Status</h3>
		</div>
		
		<form id="statusForm" method="post" onsubmit="return submitReportItemStatus();">
		<input type="hidden" name="reportStatus" id="reportStatus" value="" />
		<input type="hidden" name="reportStatusName" id="reportStatusName" value="" />
		<input type="hidden" name="reportStatusClassName" id="reportStatusClassName" value="" />
		<input type="hidden" name="imageItemId" id="imageItemId" value="" />
		<div class="modal-body">
			Please choose a status:
			<br/>
			<div id="setStatusMessage"></div>
			
			<div class="btn-group reportStatus" data-toggle="buttons-radio">
				
				<?php
				$listStatus = $reportObj->getReportStatus(false);
				
				foreach($listStatus as $status):
				?>
					<button name="state" type="button" class="btn <?php echo $status['className']; ?>" value="<?php echo $status['id']; ?>" id="reportStatusBtn_<?php echo $status['id']; ?>"><?php echo $status['name']; ?></button>
				<?php
				endforeach;
				?>
				
			</div>
			
			<br/>
			<div class="comment-wrapper">
				<div class="existing-comments">
					<strong>Existing comments for this item: </strong>
					<table class="table table-striped" id="existingComments">
					</table>
				</div>
				<textarea name="itemComment" class="comment" id="itemComment" rows="3" placeholder="Write your comment here..."></textarea>
			</div>
			
		</div>
		<div class="modal-footer-custom">
			<div id="file-uploader-report" style="float:left;">		
				<noscript>			
					<p>Please enable JavaScript to use file uploader.</p>
					<!-- or put a simple form for upload here -->
				</noscript>         
			</div>
			<div id="loader"><img src="images/loading.gif" /></div>
			<button class="btn btn-primary">Submit</button>
			<button class="btn" data-dismiss="modal" aria-hidden="true">Close</button>
		</div>
		</form>
	</div>
	
	<div id="addRoomModal" class="modal hide fade" tabindex="-1" role="dialog" aria-hidden="true">
		<div class="modal-header">
			<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
			<h3>Add Room</h3>
		</div>
		<form id="addRoomForm" method="post" >
			<div class="modal-body">
				<div id="status-message"></div>
				
				<div class="control-group">
					<label for="roomName" class="control-label">Choose a Room Name:</label>
					<input type="text" name="roomName" id="roomName" class="span4" placeholder="Room Name" data-validation="required" data-validation-error-msg="Please provide room name."/>
				</div>
				
				<div class="control-group">
					<label for="roomTemplate" class="control-label">Choose a Room Template:</label>
					<select name="roomTemplate" id="roomTemplate" data-validation="required" data-validation-error-msg="Please choose your room template.">
					<option value="">-- Choose here --</option>
					<?php
					foreach($listRoomTemplates as $roomTemplate):
					?>
						<option value="<?php echo $roomTemplate["id"]?>"><?php echo $roomTemplate['name'];?></option>
					<?php
					endforeach;
					?>
					</select>
				</div>
				
			</div>
			<div class="modal-footer-custom">
				<button class="btn btn-primary">Save Room</button>
				<button class="btn" data-dismiss="modal" aria-hidden="true">Close</button>
			</div>
		</form>
	</div>
	<!-- END MODAL POPUP -->
	<script type="text/javascript" src="js/fileuploader.js?r=<?php print rand(1,333); ?>"></script>
	<script type="text/javascript">
		function changeStatus(itemId,status,status_name,comment,reportId)
		{
			var statusMsg = document.getElementById('status-message');
			
			$.ajax({
			url: $('#_BASENAME').val() + "/webservice/changeStatus.php",
			type: "POST",
			data: {itemId: itemId
				 , status: status
				 , comment: comment
				 , reportId: reportId
				} 
			}).done(function( response ) {
					if(response.success) {
						var arrayStatus = new Array('complete','pending_review','incomplete','n_a');
						var arrayClasses = new Array('btn btn-success glyphicon glyphicon-ok-circle','btn btn-warning glyphicon glyphicon-warning-sign','btn btn-danger glyphicon glyphicon-ban-circle','btn btn-success glyphicon glyphicon-adjust');
						
						var arrayReplaceClasses = new Array('btn btn-success glyphicon','btn btn-warning glyphicon','btn btn-danger glyphicon','btn btn-success glyphicon');
						
						for(i=0;i<arrayStatus.length;i++)
						{
							var item_name = arrayStatus[i]+"_"+itemId;
							if(status_name == arrayStatus[i])
								$("#"+item_name).attr("class",arrayReplaceClasses[i]+" glyphicon-ok");
							else
								$("#"+item_name).attr("class",arrayClasses[i]);
						}						
					} else {
						if(!response.message || response.message == '' || !response) {
							statusMsg.innerHTML = getAlert('error', "Sorry, there has been a problem processing your request.");
						} else {
							statusMsg.innerHTML = getAlert('error', response.message);
						}
					}
				});
		}
		
		function changeIncomplete(itemId,reportId,paramRoomIndex, paramRoomItemIndex,obj,existingComment)
		{
			var statusFormObj = document.forms['statusForm'];
			statusFormObj.itemId.value = itemId;
			statusFormObj.reportId.value = reportId;
			
			m_tempRoomIndex = paramRoomIndex;
			m_tempRoomItemIndex = paramRoomItemIndex;
			
			$("#imageItemId").val(itemId);
			createUploader();
			$("#itemComment").val('');
			
			if(existingComment && existingComment != '') {
				$("textarea#itemComment").prop('disabled', false);
				$("textarea#itemComment").val(htmlDecode(existingComment));
			}
			
			$("#statusModal").modal('show');
		}
	</script>
	<script type="text/javascript">
	var m_roomIndex = 0;
	var m_listRooms = [];
	var m_tempRoomIndex;
	var m_tempRoomItemIndex;
	var m_isAllItemsCompleted = false;
	
	window.onload = function() {
		
		var statusMsg = document.getElementById('status-message');
		var addRoomFormObj = document.forms['addRoomForm'];
		
		if(!statusMsg || !addRoomFormObj) return;
		

		var baseNameElem = document.getElementById("_BASENAME");
		var userIdElem = document.getElementById("_USERID");
		var roomNameElem = addRoomFormObj.roomName;
		var roomTemplateElem = addRoomFormObj.roomTemplate;
		var roomsElem = document.getElementById("_ROOMS");
		
		var existingRooms = JSON.parse(roomsElem.value);
				
		//[Start] Process existing rooms
		/*for(var i=0; i<existingRooms.length; i++) {
			var arrRoomItems = [];
			
			for(var j=0; j < existingRooms[i].items.length; j++) {
				arrRoomItems.push({id: existingRooms[i].items[j].itemId, roomTemplateItemId:  existingRooms[i].items[j].itemTemplateId, name: existingRooms[i].items[j].itemName, statusId: existingRooms[i].items[j].statusId, comment: existingRooms[i].items[j].comment,imageuploaded: existingRooms[i].items[j].imageuploaded});
			}
			
			m_listRooms.push({roomId: i, roomTemplateId: existingRooms[i].roomTemplateId, roomName: existingRooms[i].roomName, roomItems: arrRoomItems, isNew: 0});
			
			//SET ROOM INDICATOR
			var roomStatusElem = document.getElementById("room_status_" + i);
			setRoomsStatusIndicator(roomStatusElem, existingRooms[i].items);
			
			isSubmitReport();
			
			m_roomIndex++;
		}*/
		
		if(m_listRooms.length) {
			$('#saveBtn').prop('disabled', false);
		}
		//[End] Process existing rooms
		$.validate({
			form: '#addRoomForm',
			onError: function() {
				statusMsg.innerHTML = getAlert('error', 'Oops! something is wrong. Please check the values below.');
			},
			onSuccess : function() {
				statusMsg.innerHTML = "";
				$.ajax({
					url: baseNameElem.value + "/webservice/get_room_template_items.php",
					type: "POST",
					data: { id: roomTemplateElem.options[roomTemplateElem.selectedIndex].value,propertyId:<?php echo $propertyId; ?>,roomName:document.getElementById('roomName').value} 
				}).done(function( response ) {
					if(response.success) {
						statusMsg.innerHTML = getAlert('success', "Room successfully added!");
						window.location.href = $('#_BASENAME').val() + "/edit_report.html?propertyId=" + <?php echo $propertyId; ?> + "&reportId="+response.data[0].reportId;
						//addRoom(roomNameElem.value, roomTemplateElem.options[roomTemplateElem.selectedIndex].value, response.data);
						
						//roomNameElem.setAttribute("class", "span4");
						
						//$("#addRoomModal").modal('hide');
					} else {
						if(!response.message || response.message == '' || !response) {
							statusMsg.innerHTML = getAlert('error', "Sorry, there has been a problem processing your request.");
						} else {
							statusMsg.innerHTML = getAlert('error', response.message);
						}
					}
				});
				
				
				return false;
			}
		});
		
		$("#addRoomModal").on('shown', function(){
			roomNameElem.focus();
		});
		
		$("#addRoomModal").on('hide', function(){
			if(addRoomFormObj) {
				addRoomFormObj.reset();
			}
			
			statusMsg.innerHTML = "";
		});
		
		$(".reportStatus .btn").click(function() {
			var value = $(this).val();
			
			$('#reportStatus').val(value);
			$('#reportStatusName').val($(this).text());
			$('#reportStatusClassName').val($(this).attr('class'));
			
			if(value == 3) {
				$('.add-comment').show();
			} else {
				$('.add-comment').hide();
			}
			
			$('.alert-message').hide();
			
			$("#roomStatusId_" + m_tempRoomIndex + "_" + m_tempRoomItemIndex).val(value);
		});

		
		$("#statusModal").on('shown', function(){
			//Set the button as active based on the current status.
			var itemStatusId = $("#roomStatusId_" + m_tempRoomIndex + "_" + m_tempRoomItemIndex).val();
			var itemComment = $("#roomItemComment_" + m_tempRoomIndex + "_" + m_tempRoomItemIndex).val();
			var jsonExistingComments = $("#roomItemCommentThread_" + m_tempRoomIndex + "_" + m_tempRoomItemIndex).val();
			
			var existingComments = (jsonExistingComments)?JSON.parse(jsonExistingComments):[];
			
			$("textarea#itemComment").val(itemComment);
			$("textarea#itemComment").focus();
			
			$('#reportStatus').val(itemStatusId);
			$("#reportStatusBtn_" + itemStatusId).addClass('active');
			
			if(existingComments.length > 0) {
				for(var c=0; c < existingComments.length; c++) {
					$("#existingComments").append("<tr><td>" + existingComments[c].commentDate + "</td><td>" + existingComments[c].comment + "</td><td> by: " + existingComments[c].firstName + " " + existingComments[c].lastName + "</td></tr>");
				}
			} else {
				$("#existingComments").html("");
				$("#existingComments").append("<tr><td>No comment for this item.</td></tr>");
			}
		});
		
		$("#statusModal").on('hide', function(){
			$('.reportStatus button').removeClass('active');
			$('.add-comment').hide();
			$('#reportStatus').val("");
			$('#itemComment').val("");
			$('#setStatusMessage').html("");
			$("#existingComments").html("");
		});
	
		$(".radioPropertyStatus .btn").click(function() {
			var value = $(this).val();
			
			$('#projectStatus').val(value);
		});
	
		$("#submitConfirmationModal").on('hide', function(){
			$('#submitReportStatus').html('');
			$('#submitReportStatus').hide();
			$('.btnSubmitReport').prop("disabled",false);
		});
	};
	
	function createUploader(){            
		var uploader = new qq.FileUploader({
			element: document.getElementById('file-uploader-report'),
			allowedExtensions:['jpg', 'jpeg', 'png', 'gif'],
			action: 'webservice/file_uploader.php?propertyId='+getQueryVariable('propertyId'),
			onSubmit: function(id, fileName){$("#loader").css("display","block");},
			showMessage: function(message){
            	if(message == "true")
				{
					$("#roomItemImageContainer_" + m_tempRoomIndex + "_" + m_tempRoomItemIndex).html('<span class="icon-image-upload"></span>');
					$("#setStatusMessage").html(getAlert('success', "Image successfully uploaded"));
					$("#loader").css("display","none");
					setTimeout(function(){$("#setStatusMessage").html('')},5000);
				}
				else
				{
					$("#setStatusMessage").html(getAlert('error', "There was a problem with the image upload"));
					$("#loader").css("display","none");
				}
        	},
			
			onComplete: function(id, fileName, responseJSON){
				m_listRooms[m_tempRoomIndex].roomItems[m_tempRoomItemIndex].imageuploaded = responseJSON.filename;
			},
			debug: true
			/*extraDropzones: [qq.getByClass(document, 'qq-upload-extra-drop-area')[0]]*/
		});
	}
	
	function confirmSubmitReport(formObj) {
		addReport(0, 1);
		
		return false;
	}
	
	function checkIfAllItemsCompleted() {
		var isComplete = true;
		
		for(var i=0; i< m_listRooms.length; i++) {
			for(var j=0; j < m_listRooms[i].roomItems.length; j++) {	
				if(m_listRooms[i].roomItems[j].statusId != 1 && m_listRooms[i].roomItems[j].statusId != 4) {
					isComplete = false;
					break;
				}
			}
			
			if(!isComplete) break;
		}
		
		return isComplete;
	}
	
	//If report can be submitted considering the item status.
	function isSubmitReport() {
		var canSubmit = true;
		
		for(var i=0; i< m_listRooms.length; i++) {
			for(var j=0; j < m_listRooms[i].roomItems.length; j++) {	
				if(m_listRooms[i].roomItems[j].statusId == 2) {
					canSubmit = false;
					break;
				}
			}
			
			if(!canSubmit) break;
		}
		
		if(canSubmit) {
			$('#submitReportBtn').prop('disabled', false);
		} else {
			$('#submitReportBtn').prop('disabled', true);
		}
	}
	
	function setRoomsStatusIndicator(statusElem, punchList) {

		var isIncomplete = false;
		
		for(var i=0; i<punchList.length; i++) {
			if(punchList[i].statusId == 3) {
				statusElem.setAttribute("class", "label label-danger");
				statusElem.innerHTML = "Incomplete";
				isIncomplete = true;
				break;
			} else if(punchList[i].statusId == 2) {
				statusElem.setAttribute("class", "label label-warning");
				statusElem.innerHTML = "Pending Review";
				isIncomplete = true;
				break;
			}
		}
		
		if(!isIncomplete) {
			statusElem.setAttribute("class", "label label-success");
			statusElem.innerHTML = "Completed";
		}
	}
	
	function submitReportIncompleteStatus() 
	{
		var statusFormObj = document.forms['statusForm'];
		reportId = statusFormObj.reportId.value;
		itemId = statusFormObj.itemId.value;
		
		var statusIdValue = 3;
		if(statusIdValue == '') 
		{
			$("#setStatusMessage").html(getAlert('error', "Please choose the status for this punchlist item."));
		} 
		else if(statusIdValue == '3' && $('#itemComment').val().length == 0) 
		{
			$("#setStatusMessage").html(getAlert('error', "You're required to enter a comment why this item is incomplete."));
			$('#itemComment').focus();
		} 
		else 
		{
			var comment = $('#itemComment').val();
			changeStatus(itemId,3,'incomplete',comment,reportId);
			
			if(m_listRooms[m_tempRoomIndex]) 
			{	
				m_listRooms[m_tempRoomIndex].roomItems[m_tempRoomItemIndex].statusId=statusIdValue;
				m_listRooms[m_tempRoomIndex].roomItems[m_tempRoomItemIndex].comment = comment;
				
				$("#statusModal").modal('hide');
				
				//Check if all room items are complete.
				var roomStatusElem = document.getElementById("room_status_" + m_tempRoomIndex);
				
				setRoomsStatusIndicator(roomStatusElem, m_listRooms[m_tempRoomIndex].roomItems);
				isSubmitReport();
				
				
				//Change status button state.
				var btn = document.getElementById("status_btn_" +m_tempRoomIndex+ "_" + m_tempRoomItemIndex);
				
				if(btn) {
					btn.innerHTML = $("#reportStatusName").val();
					btn.setAttribute("class", $("#reportStatusClassName").val());
				}
				
				//Update the room item comment preview.
				if(comment != '') {
					$("#roomItemCommentContainer_" + m_tempRoomIndex + "_" + m_tempRoomItemIndex).html('<span class="icon-comment"></span>  ' + comment);
					$("#roomItemComment_" + m_tempRoomIndex + "_" + m_tempRoomItemIndex).val(comment);
				}
			}
		}
		return false;
	}
	
	function submitReportItemStatus() {
		var statusIdValue = $('#reportStatus').val();
		
		if(statusIdValue == '') {
			$("#setStatusMessage").html(getAlert('error', "Please choose the status for this punchlist item."));
		} else if(statusIdValue == '3' && $('#itemComment').val().length == 0) {
			$("#setStatusMessage").html(getAlert('error', "You're required to enter a comment why this item is incomplete."));
			$('#itemComment').focus();
		} else {
			// console.log(JSON.stringify(m_listRooms));
			// console.log(m_tempRoomIndex);
			// console.log('--------------------');
			
			if(m_listRooms[m_tempRoomIndex]) {
				var comment = $('#itemComment').val();
				
				m_listRooms[m_tempRoomIndex].roomItems[m_tempRoomItemIndex].statusId=statusIdValue;
				m_listRooms[m_tempRoomIndex].roomItems[m_tempRoomItemIndex].comment = comment;
				
				$("#statusModal").modal('hide');
				
				//Check if all room items are complete.
				var roomStatusElem = document.getElementById("room_status_" + m_tempRoomIndex);
				
				setRoomsStatusIndicator(roomStatusElem, m_listRooms[m_tempRoomIndex].roomItems);
				isSubmitReport();
				
				
				//Change status button state.
				var btn = document.getElementById("status_btn_" +m_tempRoomIndex+ "_" + m_tempRoomItemIndex);
				
				if(btn) {
					btn.innerHTML = $("#reportStatusName").val();
					btn.setAttribute("class", $("#reportStatusClassName").val());
				}
				
				//Update the room item comment preview.
				if(comment != '') {
					$("#roomItemCommentContainer_" + m_tempRoomIndex + "_" + m_tempRoomItemIndex).html('<span class="icon-comment"></span>  ' + comment);
					$("#roomItemComment_" + m_tempRoomIndex + "_" + m_tempRoomItemIndex).val(comment);
				}
			}
		}
		return false;
	}
	
	function setRoomItemIndex(paramRoomIndex, paramRoomItemIndex, existingComment, obj) {
		m_tempRoomIndex = paramRoomIndex;
		m_tempRoomItemIndex = paramRoomItemIndex;
		
		createUploader();
		
		$("#itemComment").val('');
		
		$("#reportStatusName").val(obj.innerHTML);
		$("#reportStatusClassName").val(obj.className);
		
		if(existingComment && existingComment != '') {
			$("textarea#itemComment").prop('disabled', false);
			$("textarea#itemComment").val(htmlDecode(existingComment));
		}
	}
	
	function htmlDecode(input){
		var e = document.createElement('div');
		e.innerHTML = input;
		return e.childNodes[0].nodeValue;
	}
	
	function saveReport() {
		addReport(1, 0);
		
		return false;
	}
	
	function submitReport() {
		m_isAllItemsCompleted = checkIfAllItemsCompleted();
		
		if(m_isAllItemsCompleted) {
			$('.itemscompleted-wrapper').show();
		} else {
			$('.itemscompleted-wrapper').hide();
		}
		
		$('#submitConfirmationModal').modal('show');
	}
	
	/* Diff: Report Id is added on edit report page. */
	function addReport(isSave, isSubmit) {
		// console.log(JSON.stringify(m_listRooms));
		// return false;
		
		$('#submitReportStatus').show();
		$('#submitReportStatus').html("We are processing your request, please be patient...");
		$('.btnSubmitReport').prop("disabled", true);
			
		var reportCommentValue = (($('#reportComment').val().replace(/^\s+|\s+$/gm,'').length > 0) ? $('#reportComment').val().replace(/^\s+|\s+$/gm,''):'');
		
		// console.log();
		// console.log("Processing...");
		// console.log("reportCommentValue: " + reportCommentValue);
			
		$.ajax({
			url: $('#_BASENAME').val() + "/webservice/update_report.php",
			type: "POST",
			data: {userId: $('#_USERID').val()
				 , propertyId: $('#_PROPERTYID').val()
				 , statusId: 0
				 , save: isSave
				 , submit: isSubmit
				 , data: JSON.stringify(m_listRooms)
				 , propertyName: $('#_PROPERTYNAME').val()
				 , propertyAddress: $('#_PROPERTYADDRESS').val()
				 , propertyEmails: $('#_PROPERTYEMAILS').val()
				 , propertyStatus: $('#projectStatus').val()
				 , propertyCommunity: $('#_PROPERTYCOMMUNITY').val()
				 , propertyType: $('#_PROPERTYTYPE').val()
				 , propertyJobType: $('#_PROPERTYJOBTYPE').val()
				 , reportComment: reportCommentValue
				 } 
		}).done(function( response ) {
			// console.log("response: " + response);
			// console.log("response.success: " + response.success);
			
			if(response.success) {
				if(isSubmit == 1) {	
					$("#addReportStatus").html(getAlert('success', "Report successfully submitted!"));
					$('#_REPORTID').val(response.reportId);
					
					if(m_isAllItemsCompleted) {
						////Close/Archive property and report.
						if($('#projectStatus').val() == 0) {
							$.ajax({
								url: $('#_BASENAME').val() + "/webservice/archive_property.php",
								type: "POST",
								data: {userId: $('#_USERID').val()
									 , propertyId: $('#_PROPERTYID').val()
									 , reportId: response.reportId
									 , status: $('#projectStatus').val()
									} 
							}).done(function( response ) {
								if(response.success) {
									$("#submitReportStatus").html(getAlert('success', "Report successfully submitted!"));
									
									window.location.href = $('#_BASENAME').val() + "/archives.html";
								} else {
									if(!response.message || response.message == '' || !response) {
										$("#submitReportStatus").html(getAlert('error', "Sorry, there has been a problem processing your request."));
									} else {
										$("#submitReportStatus").html(getAlert('error', response.message));
									}
								}
							});
						} else {
							window.location.href = $('#_BASENAME').val() + "/edit_property.html?propertyId=" + $('#_PROPERTYID').val();
						}
					} else { //Not all items are completed.
						$('#submitReportStatus').html(" ");
						$('#submitReportStatus').hide();
						$('.btnSubmitReport').prop("disabled", false);
						
						window.location.href = $('#_BASENAME').val() + "/edit_property.html?propertyId=" + $('#_PROPERTYID').val();
					}
				} else {
					$("#addReportStatus").html(getAlert('success', "Report successfully saved."));
					window.location.href = $('#_BASENAME').val() + "/edit_property.html?propertyId=" + $('#_PROPERTYID').val();
				}
			} else {
				if(!response.message || response.message == '' || !response) {
					$("#addReportStatus").html(getAlert('error', "Sorry, there has been a problem processing your request."));
					
					$('#submitReportStatus').html("Sorry, there has been a problem processing your request.");
				} else {
					$("#addReportStatus").html(getAlert('error', response.message));
					
					$('#submitReportStatus').html(response.message);
				}
			}
		});
		
		return false;
	}
	
	function addRoom(name, paramRoomTemplateId, items) {
		var roomHtml = "";
		
		roomHtml += '<div class="accordion-group" id="room_' + m_roomIndex + '">';
        roomHtml += '<div class="accordion-heading">';
        roomHtml += '<div class="row-fluid"><div class="room-name"><a href="#collapse_' + m_roomIndex + '" data-parent="#rooms-wrapper" data-toggle="collapse" class="accordion-toggle">' + name + '</a></div><div class="room-name-action"><span class="label label-warning" id="room_status_' + m_roomIndex + '">Pending Review</span> &nbsp; <button class="btn btn-danger btn-small" onclick="return removeRoom(\'room_' + m_roomIndex + '\', \'' + m_roomIndex + '\')"><i class="icon-trash icon-white"></i></button></div><div class="clearfix"></div></div>';
        roomHtml += '</div>';
        roomHtml += '<div class="accordion-body collapse" id="collapse_' + m_roomIndex + '">';
        roomHtml += '<div class="accordion-inner report-room-items">';
        
		
		var roomItemsHtml = '';
		
		for(var i=0; i < items.length; i++) {
			roomItemsHtml += '<input type="hidden" name="roomStatusId_' + m_roomIndex + '_' + i + '" id="roomStatusId_' + m_roomIndex + '_' + i + '" value="2" />';
			roomItemsHtml += '<input type="hidden" name="roomItemComment_' + m_roomIndex + '_' + i + '" id="roomItemComment_' + m_roomIndex + '_' + i + '" value="" />';
			roomItemsHtml += '<div class="roomitem-group">';
			roomItemsHtml += '<div class="roomitem-desc">' + items[i].name + '</div>';
			roomItemsHtml += '<div class="roomitem-action"><button id="complete_'+items[i].itemId+'" class="btn btn-success glyphicon glyphicon-ok-circle" onClick="changeStatus('+items[i].itemId+',1,\'complete\',\'\',0);"></button> <button id="pending_review_'+items[i].itemId+'" class="glyphicon glyphicon-ok btn btn-warning" onClick="changeStatus('+items[i].itemId+',2,\'pending_review\',\'\',0);"></button> <button id="incomplete_'+items[i].itemId+'" class="incomplete_button glyphicon glyphicon-ban-circle btn btn-danger" onClick="changeIncomplete('+items[i].itemId+','+items[i].reportId+',' + i + ',' + m_roomIndex + ',this,\'\');"></button> <button id="n_a_'+items[i].itemId+'" class="glyphicon glyphicon-adjust btn btn-success" onClick="changeStatus('+items[i].itemId+',4,\'n_a\',\'\',0);"></button></div>';
			roomItemsHtml += '<div class="roomitem-comment" id="roomItemCommentContainer_' + m_roomIndex + '_' + i + '">&nbsp;</div>';
			roomItemsHtml += '<div class="roomitem-image-upload" id="roomItemImageContainer_'+ m_roomIndex + '_' + i + '"></div>';
			roomItemsHtml += '<div class="clearfix"></div>';
			roomItemsHtml += '</div>';
			
			items[i].statusId = 2;
		}
		
		roomHtml += roomItemsHtml;
		
        roomHtml += '</div>';
        roomHtml += '</div>';
        roomHtml += '</div>';
	
		$("#rooms-wrapper").append(roomHtml);
		
		m_listRooms.push({roomId: m_roomIndex, roomTemplateId: paramRoomTemplateId, roomName: name, roomItems: items, isNew: 1});
		
		m_roomIndex++;
		
		$('#saveBtn').prop('disabled', false);
		
		isSubmitReport();
	}
	
	function removeRoom(roomContainerId, index, roomId) {
		if(confirm("Are you sure you want to delete this room?")) {
			//REMOVE FROM DATABASE
			if(roomId != null) {
				$.ajax({
					url: $('#_BASENAME').val() + "/webservice/delete_room.php",
					type: "POST",
					data: {id: roomId} 
				}).done(function( response ) {
				});
			}
			
			$('#' + roomContainerId).remove();
			
			removeRoomFromList(index);

			if(m_roomIndex <= 0) {
				$('#saveBtn').prop('disabled', true);
			}
			
			m_roomIndex--;
		}
		
		return false;
	}
	
	function removeRoomFromList(paramRoomId) {
		for(var i=0; i<m_listRooms.length; i++) {
			if(m_listRooms[i].roomId == paramRoomId) {
				m_listRooms.splice(i, 1);
				break;
			}
		}
	}
	
	</script>
	
	
<?php
} else {
?>
	Oops! something went wrong.
<?php
}
?>