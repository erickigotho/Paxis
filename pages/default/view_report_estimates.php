<?php
require_once(dirname(dirname(dirname(__FILE__))) . '/modules/Report.class.php');
require_once(dirname(dirname(dirname(__FILE__))) . '/modules/Dynamo.class.php');
require_once(dirname(dirname(dirname(__FILE__))) . '/modules/Tools.class.php');

$reportId = isset($_GET['reportId'])?$_GET['reportId']:0;

if($reportId != 0) {
	$reportObj = new Report();
	
	$usersObj = new Dynamo("users");
	$array_users = $usersObj->getAllWithId_default(false,"id");
	$subContractorsObj = new Dynamo("sub_contractors");
	$array_sub_contractors = $subContractorsObj->getAllWithId_default(false,"id");
	
	$propertyInfo = $reportInfo = $reportObj->getReportDetails($reportId, false,$array_users,$array_sub_contractors);
	
	$propertyId = $propertyInfo['propertyId'];
	
	$report_images = new Dynamo("report_images");
	
	$array_report_images_count = $report_images->getAll("WHERE property_id = ".$reportInfo['propertyId']);
	$array_report_images = $report_images->getAllWithId_default("WHERE property_id = ".$reportInfo['propertyId'],'room_item_id');
	
	for($i=0;$i<count($array_report_images_count);$i++)
	{
		$array_report_images[$array_report_images_count[$i]['room_item_id']]['count'] += 1;
		
		if($array_report_images_count[$i]['image_name'] != $array_report_images[$array_report_images_count[$i]['room_item_id']]['image_name'])
		{
			$array_report_images[$array_report_images_count[$i]['room_item_id']]['extra_images'] .= $array_report_images_count[$i]['image_name'].",";
		}
	}
	
	$main_image_array = $report_images->getAll("WHERE property_image = 1 AND property_id = ".$reportInfo['propertyId']);
	
	if(count($main_image_array) > 0)
		$main_image_array = $main_image_array[0];
	?>
	
	<form method="POST" class="form-horizontal" id="addReportForm" onsubmit="return false">
	<input type="hidden" id="_BASENAME" name="baseName" value="<?php echo __BASENAME__; ?>" />
	<input type="hidden" id="_USERID" name="userId" value="<?php echo $_SESSION['user_id']; ?>" />
	<input type="hidden" id="_ROOMS" name="rooms" value='<?php echo json_encode($reportInfo['rooms']); ?>' />    
    <input type="hidden" id="_PROPERTYNAME" name="propertyName" value="<?php echo $propertyInfo['propertyName'];?>" />
    <input type="hidden" id="_PROPERTYADDRESS" name="propertyName" value="<?php echo $propertyInfo['propertyAddress'];?>" />                    
	<input type="hidden" id="_PROPERTYCOMMUNITY" name="propertyCommunity" value="<?php echo $propertyInfo['propertyCommunity'];?>" />
    <input type="hidden" id="_PROPERTYEMAILS" name="propertyEmails" value="<?php echo $propertyInfo['propertyEmails'];?>" />
    
	<div class="pull-left"><h4>Report for <?php echo stripslashes($reportInfo['propertyCommunity']); ?>, <?php echo stripslashes($reportInfo['propertyName']); ?></h4></div>
	<div class="pull-right"><a href="edit_property.html?propertyId=<?php echo $reportInfo['propertyId']; ?>" class="btn btn-default b">Cancel</a></div>
	<div class="clearfix"></div>
	
    <div id="addReportStatus"></div>
    
	<div class="report-header-wrapper">
		<div class="property-summary">
			<div class="propertydetail-group">
				<div class="property-label">Community</div>
				<div class="property-value"><?php echo $reportInfo['propertyCommunity'];?></div>
			</div>
			<div class="propertydetail-group">
				<div class="property-label">Property Name</div>
				<div class="property-value"><?php echo $reportInfo['propertyName'];?></div>
			</div>
			<div class="propertydetail-group">
				<div class="property-label">Job Type</div>
				<div class="property-value"><?php echo ($reportInfo['propertyJobType']==0?"New":"Restoration");?></div>
			</div>
			<div class="propertydetail-group">
				<div class="property-label">Property Type</div>
				<div class="property-value"><?php echo ($reportInfo['propertyType']==0?"Residential":"Commercial");?></div>
			</div>
			<div class="propertydetail-group">
				<div class="property-label">Address</div>
				<div class="property-value"><?php echo $reportInfo['propertyAddress'];?>, <?php echo $reportInfo['propertyCity'];?> <?php echo $reportInfo['propertyState'];?> - <a href="javascript: void(0)" onclick="window.open('<?php echo $reportInfo['propertyMapLink'];?>')">View Map</a></div>
			</div>
			<div class="propertydetail-group">
				<div class="property-label">Report Date</div>
				<div class="property-value"><?php echo date('m/d/Y g:ia', strtotime($reportInfo['firstReportDate']));?></div>
			</div>
			<div class="propertydetail-group">
				<div class="property-label">Status</div>
				<div class="property-value"><?php echo ($reportInfo['reportStatusId']==0)?'Open':'Closed';?></div>
			</div>
		</div>
		<div class="report-summary">
			<div class="report-summary-details">
				<div class="report-label">This report was completed by</div>
				<div class="report-value"><strong><?php echo $reportInfo['firstName'] . " " . $reportInfo['lastName'];?></strong></div>
				<br/>
				<div class="report-label">Copies of this report were sent to:</div>
				<div class="report-value">
					<ul>
					<?php 
						$emails = explode(',', $reportInfo['propertyEmails']);
						
						foreach($emails as $email):
						?>
							<li><?php echo $email; ?></li>
						<?php
						endforeach;
					?>
					</ul>
				</div>
			</div>
			<div class="report-summary-photo<?php if(count($main_image_array) > 0){ ?> property-photo-none<?php } ?>">
				<?php
					if(count($main_image_array) > 0)
						print "<img src='images/report_uploads/".$main_image_array["image_name"]."' class='archive-property-image' />";
					else
					{
				?>
				&nbsp;
				<?php
					}
				?>
			</div>
		</div>
		<div class="clearfix"></div>
	</div>
	<br/>
	<div class="row-fluid" id="rooms-wrapper">
		<?php
			// echo json_encode($reportInfo['rooms']);
			// var_dump($reportInfo['rooms']);
			
			$tmpRoomIndex = 0;
			
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
								<span class="label label-warning" id="room_status_<?php echo $tmpRoomIndex; ?>">Pending Review</span>
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
									?>
									<div class="roomitem-group<?php if($item['isEstimate'] == 1) print ' roomitem-estimate'; ?>">
										<div class="roomitem-desc"><?php echo $item['itemName']; ?></div>
										<div class="roomitem-action">
											<?php
											if($item['isEstimate'] == 0 && count($item['arrayRoomItemsUnits']) > 0)
											{
												if($item['arrayRoomItemsUnits'][0]['item_name'] != $room['items'][$roomItemIndex+1]['itemName'])
												{
													$itemClassName = '';
												
													switch($item['statusId']) {
														case '1':
														case '4':
															$itemClassName = 'label-success';
															break;
														case '3':
															$itemClassName = 'label-danger';
															break;
														default:
															$itemClassName = 'label-warning';
															break;
													}
													?>
													<span class="label <?php echo $itemClassName;?>"><?php echo (empty($item['statusName'])?'Pending Review':$item['statusName']); ?></span>
                                           <?php	
														
												}
											}
											else
											{
												$itemClassName = '';
												
												switch($item['statusId']) {
													case '1':
													case '4':
														$itemClassName = 'label-success';
														break;
													case '3':
														$itemClassName = 'label-danger';
														break;
													default:
														$itemClassName = 'label-warning';
														break;
												}
												?>
												<span class="label <?php echo $itemClassName;?>"><?php echo (empty($item['statusName'])?'Pending Review':$item['statusName']); ?></span>
                                           <?php
											}
										   ?>
										</div>
                                        <script type="text/javascript">
											$jq1(document).ready(function() {
												$jq1("a[rel=light_box_<?php print $tmpRoomIndex."_".$roomItemIndex; ?>]").fancybox({
													'transitionIn'		: 'none',
													'transitionOut'		: 'none',
													'titlePosition' 	: 'inside',
													'titleFormat'		: function(title, currentArray, currentIndex, currentOpts) {
														return '<span id="fancybox-title-over">Image ' + (currentIndex + 1) + ' / ' + currentArray.length + (title.length ? ' &nbsp; ' + title : '') + ' </span>';
														//$("#fancybox-img").attr("title",title);
														//return '<span id="fancybox-title-over">' + title + ' </span>';
													}
												});	
											});
											</script>
										<div class="roomitem-comment"><?php echo (isset($item['comments'][0]['comment'])? '<span class="icon-comment"></span> '. $item['comments'][0]['comment']:'&nbsp;'); ?> </div>
										<div class="roomitem-image-upload"<?php if($array_report_images[$item['itemId']]['count'] > 1)print " style='padding-right:10px;'"; ?>><?php echo (isset($array_report_images[$item['itemId']])? '<a rel="light_box_'.$tmpRoomIndex.'_'.$roomItemIndex.'" href="images/report_uploads/'.$array_report_images[$item['itemId']]['image_name'].'" title="'.$item['itemName'].'"><span class="icon-image-upload"></span></a>'.($array_report_images[$item['itemId']]['count'] > 1? '<span class="badge">'.$array_report_images[$item['itemId']]['count'].'</span></a>': '') :''); ?> </div>
                                        <?php
											if(trim($array_report_images[$item['itemId']]['extra_images']) != '')
											{
												$array_report_images[$item['itemId']]['extra_images'] = substr($array_report_images[$item['itemId']]['extra_images'],0,-1);
												$extraImagesArray = explode(",",$array_report_images[$item['itemId']]['extra_images']);
												
												for($i=0;$i<count($extraImagesArray);$i++)
												{
													print "<a rel='light_box_".$tmpRoomIndex."_".$roomItemIndex."' href='images/report_uploads/".$extraImagesArray[$i]."' title='".$item['itemName']."'><img src='' style='display:none;' /></a>";
												}
											}
										?>
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
		?>
	</div>
	<br/><br/>
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
	<div style="padding-top:5px;">
    <button class="btn btn-primary" type="submit"  id="submitReportBtn" onClick="submitReport();"><i class="icon-ok icon-white"></i> Resend Report</button>
    </div>
	</form>
    
    <div id="submitConfirmationModal" class="modal hide fade" tabindex="-1" role="dialog" aria-hidden="true">
		<div class="modal-header">
			<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
			<h3>Submit Report Confirmation</h3>
		</div>
		
		<div class="alert alert-warning hide" id="submitReportStatus">here</div>
		
		<form id="submitConfirmForm" method="post" onSubmit="return confirmSubmitReport();">
		<input type="hidden" name="projectStatus" id="projectStatus" value="1" />
		<div class="modal-body">
			Email reports will be sent to:
			<ul>
			<?php 
				
				$emails = explode(',', $propertyInfo['propertyEmails']);
				
				foreach($emails as $email):
				?>
					<li><?php echo $email; ?></li>
				<?php
				endforeach;
			?>
			</ul>
            <div id="subcontractor_emails_container">
            <?php
				if($_SESSION['user_type'] != 5)
				{
					$tools = new Tools;
					$array_sub_contractors = $tools->getSubContractorToEmail($reportId,$propertyId,new Dynamo("subcontractors_assign"));
					if(count($array_sub_contractors) > 0)
					{
						print "and to the following sub contractors:
						<ul>";
						for($i=0;$i<count($array_sub_contractors);$i++)
						{
							print "<li>".$array_sub_contractors[$i]['email']."</li>";
						}
						print "</ul>";
					}
				}
			?>
            </div>
            
		</div>
		<div class="modal-footer-custom">
			<button class="btn btn-primary btnSubmitReport">Submit Report</button>
			<button class="btn" data-dismiss="modal" aria-hidden="true">Cancel</button>
		</div>
		</form>
	</div>
    
	<script type="text/javascript">
	var m_roomIndex = 0;
	var m_listRooms = [];
	
	window.onload = function() {
		var roomsElem = document.getElementById("_ROOMS");
		var existingRooms = JSON.parse(roomsElem.value);
		
		//[Start] Process existing rooms
		for(var i=0; i<existingRooms.length; i++) {
			var arrRoomItems = [];
			for(var j=0; j < existingRooms[i].items.length; j++) {
				arrRoomItems.push({id: existingRooms[i].items[j].itemId, name: existingRooms[i].items[j].itemName, statusId: existingRooms[i].items[j].statusId});
			}
			
			m_listRooms.push({roomId: i, roomName: existingRooms[i].roomName, roomItems: arrRoomItems});
			
			//SET ROOM INDICATOR
			var roomStatusElem = document.getElementById("room_status_" + i);
			
			setRoomsStatusIndicator(roomStatusElem, existingRooms[i].items);
			
			m_roomIndex = i;
		}
		
		if(m_listRooms.length) {
			$('#saveBtn').prop('disabled', false);
		}
		//[End] Process existing rooms
	}
	
	function confirmSubmitReport()
	{
		$('#submitReportStatus').show();
		$('#submitReportStatus').html("We are processing your request, please be patient...");
		$.ajax({
			url: "<?php echo __BASENAME__; ?>/webservice/get_room_info.php",
			type: "POST",
			data: {reportId: <?php echo $reportId; ?>} 
			}).done(function(response ) {		
				m_roomIndex = 0;
				m_listRooms = [];
				var existingRooms = response;
				
				//[Start] Process existing rooms
				for(var i=0; i<existingRooms.length; i++) 
				{
					var arrRoomItems = [];
					
					for(var j=0; j < existingRooms[i].items.length; j++) {
						if(existingRooms[i].items[j].arrayRoomItemsUnits.length > 0 && existingRooms[i].items[j].isEstimate == 0)
						{
							parent_exists = true;
						}
						else
						{
							parent_exists = false;
						}
						
						if(existingRooms[i].items[j].comments.length > 0)
						{
							if(existingRooms[i].items[j].images.length > 0)
							{
								arrRoomItems.push({id: existingRooms[i].items[j].itemId, name: existingRooms[i].items[j].itemName, statusId: existingRooms[i].items[j].statusId,isEstimate: existingRooms[i].items[j].isEstimate,isParent: parent_exists, comment: existingRooms[i].items[j].comments[0].comment, image:existingRooms[i].items[j].images[0].image_name});
							}
							else
							{		
								arrRoomItems.push({id: existingRooms[i].items[j].itemId, name: existingRooms[i].items[j].itemName, statusId: existingRooms[i].items[j].statusId,isEstimate: existingRooms[i].items[j].isEstimate,isParent: parent_exists,comment: existingRooms[i].items[j].comments[0].comment});
							}
						}
						else
						{
							arrRoomItems.push({id: existingRooms[i].items[j].itemId, name: existingRooms[i].items[j].itemName, statusId: existingRooms[i].items[j].statusId,isEstimate: existingRooms[i].items[j].isEstimate,isParent: parent_exists});	
						}
					}
					
					m_listRooms.push({roomId: i, roomName: existingRooms[i].roomName, roomItems: arrRoomItems, isNew: 0});
											
					m_roomIndex++;
				}
				
				
				$.ajax({
					url: "<?php echo __BASENAME__; ?>/webservice/update_report.php",
					type: "POST",
					data: {userId: <?php echo $_SESSION['user_id']; ?>
						 , propertyId: <?php echo $propertyInfo['propertyId']; ?>
						 , statusId: 0
						 , save: 0
						 , submit: 1
						 , data: JSON.stringify(m_listRooms)
						 , reportId: <?php echo $reportId; ?>
						 , propertyName: $('#_PROPERTYNAME').val()
						 , propertyAddress: $('#_PROPERTYADDRESS').val()
						 , propertyEmails: $('#_PROPERTYEMAILS').val()
						 , propertyStatus: 1
						 , propertyCommunity: $('#_PROPERTYCOMMUNITY').val()
						 , propertyType: <?php echo $propertyInfo['propertyType']; ?>
						 , propertyJobType: <?php echo $propertyInfo['propertyJobType']; ?>
						 , reportComment: ''
						} 
				}).done(function( response ) {
					// console.log("response: " + response.message);
					
					if(response.success) 
					{	
						$('#submitReportStatus').hide();
						$("#addReportStatus").html(getAlert('success', "Report successfully submitted!"));
						$('#submitConfirmationModal').modal('hide');					
					}
					else
					{
						$('#submitReportStatus').hide();
						$('#addReportStatus').html(getAlert('error', "Sorry there was a problem with your request."));	
						$('#submitConfirmationModal').modal('hide');					
					}
				});					
			});
			
			return false;
	}
	
	function submitReport() {
		<?php
		if($_SESSION['user_type'] != 5)
		{
		?>
		loadSubcontractorEmails();
		<?php
		}
		?>
		
		$('#submitConfirmationModal').modal('show');
	}
	
	function loadSubcontractorEmails()
	{
		$.ajax({
			url: $('#_BASENAME').val() + "/webservice/get_subcontractor_emails.php",
			type: "POST",
			data: {propertyId: $('#_PROPERTYID').val()
				 , reportId: $('#_REPORTID').val()
				} 
		}).done(function( response ) {
			if(response.html != '')
			{
				$("#subcontractor_emails_container").html(response.html);
			}
			else
			{
				$("#subcontractor_emails_container").html('');
			}
		});
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
	</script>
<?php 
} else {
?>
	Oops! something went wrong.
<?php
}
?>