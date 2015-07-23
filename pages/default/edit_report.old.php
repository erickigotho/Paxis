<?php
require_once(dirname(dirname(dirname(__FILE__))) . '/modules/Property.class.php');
require_once(dirname(dirname(dirname(__FILE__))) . '/modules/Room.class.php');
require_once(dirname(dirname(dirname(__FILE__))) . '/modules/Report.class.php');
require_once(dirname(dirname(dirname(__FILE__))) . '/modules/Dynamo.class.php');
require_once(dirname(dirname(dirname(__FILE__))) . '/modules/Tools.class.php');

$propertyId = isset($_GET['propertyId'])?$_GET['propertyId']:0;
$reportId = isset($_GET['reportId'])?$_GET['reportId']:0;

if($propertyId != 0 && $reportId != 0) {
	$propertyObj = new Property();
	$propertyInfo = $propertyObj->getPropertyInfo($propertyId, false);
	
	$roomObj = new Room();
	$listRoomTemplates = $roomObj->getRoomTemplates(false);
	
	if(!$propertyInfo) {	
		echo "No property found.";
		return;
	}
	
	$reportObj = new Report();
	$reportInfo = $reportObj->getReportDetails($reportId, false);
	
	if($_SESSION['user_type'] == 5)
	{
		$subcontractors_assign_obj = new Dynamo("subcontractors_assign");
		$subcontractor_array = $subcontractors_assign_obj->getAll("WHERE property_id = ".$propertyId." AND sub_contractor_id = ".$_SESSION['user_id']);
		
		$subcontractor_work_category_ids_array = array();
		for($i=0;$i<count($subcontractor_array);$i++)
		{
			$subcontractor_work_category_ids_array[] = $subcontractor_array[$i]['work_category_id'];
		}
	}
	
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
	
?>
	<!-- ADD REPORT -->
	<form method="POST" class="form-horizontal" id="addReportForm" onSubmit="return false">
	<input type="hidden" id="_BASENAME" name="baseName" value="<?php echo __BASENAME__; ?>" />
	<input type="hidden" id="_USERID" name="userId" value="<?php echo $_SESSION['user_id']; ?>" />
	<input type="hidden" id="_PROPERTYID" name="propertyId" value="<?php echo $propertyId; ?>" />
	<input type="hidden" id="_REPORTID" name="reportId" value="<?php echo $reportId; ?>" />
	<input type="hidden" id="_PROPERTYCOMMUNITY" name="propertyCommunity" value='<?php echo $propertyInfo['community'];?>' />
	<input type="hidden" id="_PROPERTYTYPE" name="propertyType" value='<?php echo $propertyInfo['property_type'];?>' />
	<input type="hidden" id="_PROPERTYJOBTYPE" name="propertyJobType" value='<?php echo $propertyInfo['job_type'];?>' />
	<input type="hidden" id="_ROOMS" name="rooms" value='<?php echo json_encode($reportInfo['rooms']); ?>' />
	<input type="hidden" id="_PROPERTYNAME" name="propertyName" value='<?php echo $propertyInfo['name'];?>' />
	<input type="hidden" id="_PROPERTYADDRESS" name="propertyName" value='<?php echo $propertyInfo['address'];?>' />
	<input type="hidden" id="_PROPERTYEMAILS" name="propertyName" value='<?php echo $propertyInfo['emails'];?>' />
	<input type="hidden" id="_PROPERTYSTATUS" name="propertyName" value='<?php echo $propertyInfo['status'];?>' />
	
	<div class="pull-left"><h4><?php echo $propertyInfo['community']; ?>, <?php echo $propertyInfo['name']; ?> - Edit Report</h4></div>
	<div class="pull-right"><a href="edit_property.php?propertyId=<?php echo $propertyId;?>" class="btn btn-small btn-warning"><i class="icon-info-sign icon-white"></i> Property Details</a></div>
	<div class="clearfix"></div>
	<div id="status-message"></div>
    
	<div id="addReportStatus"></div>
	
	<div id="rooms-wrapper" class="accordion">
		<?php
			$tmpRoomIndex = 0;
			$addRoom = true;
			
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
								<?php if($_SESSION['user_type'] != 5){ ?><a href="#markCompleteModal" data-toggle="modal" onclick="setRoomId(<?php print $room['roomId']; ?>);"><?php } ?><span class="label label-warning" id="room_status_<?php echo $tmpRoomIndex; ?>">Pending Review</span><?php if($_SESSION['user_type'] != 5){ ?></a><?php } ?> &nbsp;
								<?php if($_SESSION['user_type'] != 5){ ?><button class="btn btn-danger btn-small" onClick="return removeRoom('room_<?php echo $tmpRoomIndex;?>', '<?php echo $tmpRoomIndex;?>', <?php echo $room['roomId']; ?>)"><i class="icon-trash icon-white"></i></button><?php } ?>
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
										if($item['isEstimate'] == 1)
											$addRoom = false;
										// var_dump($item);
										$existingComment = empty($item['comments'][0]['comment']) ? "" : htmlentities($item['comments'][0]['comment']);
										$statusClassname = (empty($item['statusClass'])?'btn-warning':$item['statusClass']);
									?>
									<input type="hidden" name="roomStatusId_<?php echo $tmpRoomIndex . '_' . $roomItemIndex;?>" id="roomStatusId_<?php echo $tmpRoomIndex . '_' . $roomItemIndex;?>" value="<?php echo $item['statusId']; ?>"/>
									<input type="hidden" name="roomItemComment_<?php echo $tmpRoomIndex . '_' . $roomItemIndex;?>" id="roomItemComment_<?php echo $tmpRoomIndex . '_' . $roomItemIndex;?>" value="<?php echo $existingComment; ?>"/>
									<input type="hidden" name="roomItemCommentThread_<?php echo $tmpRoomIndex . '_' . $roomItemIndex;?>" id="roomItemCommentThread_<?php echo $tmpRoomIndex . '_' . $roomItemIndex;?>" value='<?php echo json_encode($item['comment_thread']); ?>'/>
                                    <input type="hidden" name="roomItemImageThread_<?php echo $tmpRoomIndex . '_' . $roomItemIndex;?>" id="roomItemImageThread_<?php echo $tmpRoomIndex . '_' . $roomItemIndex;?>" value='<?php echo json_encode($item['image_thread']); ?>'/>
									<div class="roomitem-group<?php if($item['isEstimate'] == 1) print ' roomitem-estimate'; ?>">
										<div class="roomitem-desc"><?php if($item['isEstimate'] == 1) echo '<strong>';echo $item['itemName'];if($item['isEstimate'] == 1) echo '</strong>'; ?>
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
										<div class="roomitem-action">
                                        	<?php
												if($item['isEstimate'] == 0 && count($item['arrayRoomItemsUnits']) > 0)
												{}
												else
												{ 

													if($_SESSION['user_type'] == 5 && ($item['statusId'] == 1 || $item['statusId'] == 4))
													{
												?>
														<span class="label label-success"><?php print $item['statusName']; ?></span>
												<?php
													}
													else
													{
														if(!is_array($subcontractor_work_category_ids_array))
															$subcontractor_work_category_ids_array = array();
															
														if(!in_array($item['work_category_id'],$subcontractor_work_category_ids_array) && $_SESSION['user_type'] == 5)
														{
															if($item['statusId'] == 2)
																print '<span class="label label-warning">'.$item['statusName'].'</span>';
															else if($item['statusId'] == 3)
																print '<span class="label label-danger">'.$item['statusName'].'</span>';
														}
														else
														{
												?>
												<?php if($_SESSION['user_type'] != 5){ ?><button id="complete_<?php echo $item['itemId']; ?>" class="btn btn-success glyphicon <?php if($item['statusName'] == "Complete") echo "glyphicon-ok";else{ ?>glyphicon-ok-circle<?php } ?>" onclick="changeStatus(<?php echo $item['itemId']; ?>,1,'complete','',0);"></button><?php } ?> <button id="pending_review_<?php echo $item['itemId']; ?>" class="glyphicon <?php if(trim($item['statusName']) == '' || trim($item['statusName']) == "Pending Review") echo "glyphicon-ok";else{ ?>glyphicon-warning-sign<?php } ?> btn btn-warning" <?php if($_SESSION['user_type'] == 5){ ?>onclick="changePending(<?php echo $item['itemId']; ?>,<?php echo $reportId; ?>,<?php echo $tmpRoomIndex;?>,<?php echo $roomItemIndex; ?>,this,'<?php echo $existingComment; ?>');" <?php } else{ ?> onclick="changeStatus(<?php echo $item['itemId']; ?>,2,'pending_review','',0);"<?php } ?>></button> <button id="incomplete_<?php echo $item['itemId']; ?>" class="incomplete_button glyphicon <?php if($item['statusName'] == "Incomplete") echo "glyphicon-ok";else{ ?>glyphicon-ban-circle<?php } ?> btn btn-danger" onclick="changeIncomplete(<?php echo $item['itemId']; ?>,<?php echo $reportId; ?>,<?php echo $tmpRoomIndex;?>,<?php echo $roomItemIndex; ?>,this,'<?php echo $existingComment; ?>');"></button> <?php if($_SESSION['user_type'] != 5){ ?><button id="n_a_<?php echo $item['itemId']; ?>" class="glyphicon <?php if($item['statusName'] == "N/A") echo "glyphicon-ok";else{ ?>glyphicon-adjust<?php } ?> btn btn-success" onclick="changeStatus(<?php echo $item['itemId']; ?>,4,'n_a','',0);"></button><?php } ?>
												<?php
														}
													}
												}
											?>
											<!-- <a href="#statusModal" role="button" onClick="setRoomItemIndex('<?php echo $tmpRoomIndex;?>','<?php echo $roomItemIndex; ?>', '<?php echo $existingComment; ?>',<?php echo $item['itemId']; ?>, this)" data-toggle="modal" class="btn <?php echo $statusClassname; ?>" id="status_btn_<?php echo $tmpRoomIndex;?>_<?php echo $roomItemIndex; ?>"><?php echo (empty($item['statusName'])?'Pending Review':$item['statusName']); ?></a> -->
										</div>
                                        <?php
										if(count($item['arrayRoomItemsUnits']) <= 0)
										{
										?>
										<div class="roomitem-comment" id="roomItemCommentContainer_<?php echo $tmpRoomIndex . '_' . $roomItemIndex;?>"><?php echo (isset($item['comments'][0]['comment'])? '<span class="icon-comment"></span> '. $item['comments'][0]['comment']:'&nbsp;'); ?> </div>
										<div class="roomitem-image-upload" id="roomItemImageContainer_<?php echo $tmpRoomIndex . '_' . $roomItemIndex;?>"<?php if($array_report_images[$item['itemId']]['count'] > 1)print " style='padding-right:10px;'"; ?>><?php echo (isset($array_report_images[$item['itemId']])? '<a rel="light_box_'.$tmpRoomIndex.'_'.$roomItemIndex.'" href="images/report_uploads/'.$array_report_images[$item['itemId']]['image_name'].'" title="'.$item['itemName'].'"><span class="icon-image-upload"></span></a>'.($array_report_images[$item['itemId']]['count'] > 1? '<span class="badge">'.$array_report_images[$item['itemId']]['count'].'</span></a>': '') :''); ?> </div>
                                        <?php
											if(trim($array_report_images[$item['itemId']]['extra_images']) != '')
											{
												$array_report_images[$item['itemId']]['extra_images'] = substr($array_report_images[$item['itemId']]['extra_images'],0,-1);
												$extraImagesArray = explode(",",$array_report_images[$item['itemId']]['extra_images']);
												
												for($i=0;$i<count($extraImagesArray);$i++)
												{
													print "<a rel='light_box_".$tmpRoomIndex.'_'.$roomItemIndex."' href='images/report_uploads/".$extraImagesArray[$i]."' title=\"".$item['itemName']."\"><img src='' style='display:none;' /></a>";
												}
											}
										}
										?>
										<div class="clearfix"></div>
                                        <?php
										if(count($item['arrayRoomItemsUnits']) <= 0)
										{
										if(count($item['comment_thread']) > 0)
										{
										?>
                                        <table class="comments_image_thread table table-striped">
                                        	<?php
											for($i=0;$i<count($item['comment_thread']);$i++)
											{
											?>
                                            	<tr>
                                                	<td>
												<?php
													if($i == 0)
														print "<img src='images/big-comments.png' /> ";
                                                    print $item['comment_thread'][$i]['comment'];
													if(count($item['image_thread']) > 0)
														print "<br />";
													for($j=0;$j<count($item['image_thread']);$j++)
													{
														if($item['image_thread'][$j]['roomItemId'] == $item['comment_thread'][$i]['roomItemId'])
														{
														?>
                                                        	<a tabindex="1" rel="light_box_<?php print $tmpRoomIndex.'_'.$roomItemIndex; ?>" href="images/report_uploads/<?php print $item['image_thread'][$j]['imageName']; ?>" title="<?php print $item['image_thread'][$j]['itemName']; ?>"><img src="images/report_uploads/<?php print $item['image_thread'][$j]['imageName']; ?>" style="height:50px;" /></a>&nbsp;
                                                        <?php	
														}
													}
                                                ?>
                                                	</td>
                                                </tr>
											<?php
											}
										?>
                                         </table>   
                                        <?php
										}
										}
										?>
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
	<?php
	if($addRoom == true)
	{
		if($_SESSION['user_type'] != 5)
		{
		?>
		<a href="#addRoomModal" role="button" class="btn" data-toggle="modal"><i class="icon-plus"></i> Add Room</a>
		<?php
		}
	}
	?>
	<br/><br/><br/>
	<strong>General Comments:</strong>
	<div class="existingComments">
		<table class="table" id="reportExistingComments">
		<?php
		$existingComment = '';
		
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
				} else {
					$existingComment = $comment;
				}
			endforeach;
		}
		?>
		</table>
	</div>
	<textarea name="reportComment" id="reportComment" rows="3" class="span6" placeholder="Write your comment for this report here..."><?php echo $existingComment;?></textarea>
	
	<br /><br />
	<button class="btn btn-primary" type="submit"  id="submitReportBtn" onClick="submitReport();" disabled><i class="icon-ok icon-white"></i> Submit Report</button>
	</form>
	<!-- END ADD REPORT -->
	
	
	<!-- MODAL POPUP -->
	<div id="submitConfirmationModal" class="modal hide fade" tabindex="-1" role="dialog" aria-hidden="true">
		<div class="modal-header">
			<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
			<h3>Submit Report Confirmation</h3>
		</div>
		
		<div class="alert alert-warning hide" id="submitReportStatus"></div>
		
		<form id="submitConfirmForm" method="post" onSubmit="return confirmSubmitReport(this);">
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
	
	<div id="statusModal" class="modal hide fade" tabindex="-1" role="dialog" aria-hidden="true">
		<div class="modal-header">
			<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
			<h3>Comment</h3>
		</div>
		
		<form id="statusForm" name="statusForm" method="post" onSubmit="return submitReportIncompleteStatus();">
		<input type="hidden" name="itemId" id="itemId" value="" />
		<input type="hidden" name="reportId" id="reportId" value="" />
		<input type="hidden" name="imageItemId" id="imageItemId" value="" />
        <input type="hidden" name="statusIdValue_item" id="statusIdValue_item" value="3" />
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
    
    <div id="markCompleteModal" class="modal hide fade" tabindex="-1" role="dialog" aria-hidden="true">
    	<div class="modal-header">
			<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
			<h3>Mark line items as complete</h3>
		</div>
            <form id="markRoomComplete" method="post">
            	<input type="hidden" id="roomId" name="roomId" value="" />
            <div class="modal-footer-custom">
	            <button data-toggle="button" class="btn btn-success" type="button" onclick="markLineAsComplete();">Mark line items as complete</button>    
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
		var m_roomIndex = 0;
		var m_listRooms = [];
		var m_tempRoomIndex;
		var m_tempRoomItemIndex;
		var m_isAllItemsCompleted = false;
		var m_existingStatusId;
		
		var statusMsg = document.getElementById('status-message');
		
		function changeStatusComplete(itemId,status,status_name,comment,reportId)
		{
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
		
		function checkStatusCompleteAll()
		{
			$.ajax({
				url: $('#_BASENAME').val() + "/webservice/get_room_info.php",
				type: "POST",
				data: {reportId: <?php echo $reportId; ?>
					} 
				}).done(function( response ) {		
					m_roomIndex = 0;
					m_listRooms = [];
					
					var existingRooms = response;
					
					//[Start] Process existing rooms
					for(var i=0; i<existingRooms.length; i++) {
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
						
						//SET ROOM INDICATOR
						var roomStatusElem = document.getElementById("room_status_" + i);
						setRoomsStatusIndicator(roomStatusElem, existingRooms[i].items);
						<?php
							if($_SESSION['user_type'] == 5)
							{
						?>
								isSubmitReport_subcontractor(m_listRooms);
						<?php
							}
							else
							{
						?>
								isSubmitReport(m_listRooms);
						<?php
							}
						?>
						
						m_roomIndex++;
					}					
				});		
		}
		
		function changeStatus(itemId,status,status_name,comment,reportId)
		{
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
						
						$.ajax({
						url: $('#_BASENAME').val() + "/webservice/get_room_info.php",
						type: "POST",
						data: {reportId: <?php echo $reportId; ?>
							} 
						}).done(function( response ) {		
							m_roomIndex = 0;
							m_listRooms = [];
							
							var existingRooms = response;
							
							//[Start] Process existing rooms
							for(var i=0; i<existingRooms.length; i++) {
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
								
								//SET ROOM INDICATOR
								var roomStatusElem = document.getElementById("room_status_" + i);
								setRoomsStatusIndicator(roomStatusElem, existingRooms[i].items);
								<?php
									if($_SESSION['user_type'] == 5)
									{
								?>
										isSubmitReport_subcontractor(m_listRooms);
								<?php
									}
									else
									{
								?>
										isSubmitReport(m_listRooms);
								<?php
									}
								?>
								
								m_roomIndex++;
							}					
						});											
					} else {
						if(!response.message || response.message == '' || !response) {
							statusMsg.innerHTML = getAlert('error', "Sorry, there has been a problem processing your request.");
						} else {
							statusMsg.innerHTML = getAlert('error', response.message);
						}
					}
				});
		}
		
		function changePending(itemId,reportId,paramRoomIndex, paramRoomItemIndex,obj,existingComment)
		{
			var statusFormObj = document.forms['statusForm'];
			statusFormObj.itemId.value = itemId;
			statusFormObj.reportId.value = reportId;
			statusFormObj.statusIdValue_item.value = 2;
			
			m_tempRoomIndex = paramRoomIndex;
			m_tempRoomItemIndex = paramRoomItemIndex;
			
			//statusFormObj.imageItemId.value = imageItemId;
			$("#imageItemId").val(itemId);
			createUploader();
			$("#itemComment").val('');
			
			if(existingComment && existingComment != '') {
				$("textarea#itemComment").prop('disabled', false);
				$("textarea#itemComment").val(htmlDecode(existingComment));
			}
			
			$("#statusModal").modal('show');
		}
		
		function changeIncomplete(itemId,reportId,paramRoomIndex, paramRoomItemIndex,obj,existingComment)
		{
			var statusFormObj = document.forms['statusForm'];
			statusFormObj.itemId.value = itemId;
			statusFormObj.reportId.value = reportId;
			statusFormObj.statusIdValue_item.value = 3;
			
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
	function isset(a) {
		if (a) {
			if (typeof a === "object") { return Object.keys(a).length > 0 ? true : false; }
			return (a === undefined || a === null || a === "") ? false : true;
		}
		else { console.log("Empty value: isset()"); }
	}
	
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
		for(var i=0; i<existingRooms.length; i++) {
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
			m_listRooms.push({roomId: existingRooms[i].roomId, roomName: existingRooms[i].roomName, roomItems: arrRoomItems, isNew: 0});
			
			//SET ROOM INDICATOR
			var roomStatusElem = document.getElementById("room_status_" + i);
			setRoomsStatusIndicator(roomStatusElem, existingRooms[i].items);
			
			<?php
				if($_SESSION['user_type'] == 5)
				{
			?>
					isSubmitReport_subcontractor(m_listRooms);
			<?php
				}
				else
				{
			?>
					isSubmitReport(m_listRooms);
			<?php
				}
			?>
			
			m_roomIndex++;
		}
		
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
					data: { id: roomTemplateElem.options[roomTemplateElem.selectedIndex].value,reportId:<?php print $reportId; ?>,roomName:document.getElementById('roomName').value} 
				}).done(function( response ) {
					if(response.success) {
						statusMsg.innerHTML = getAlert('success', "Room successfully added!");
						
						addRoom(roomNameElem.value, roomTemplateElem.options[roomTemplateElem.selectedIndex].value, response.data,response.roomId);
						
						roomNameElem.setAttribute("class", "span4");
						
						$("#addRoomModal").modal('hide');
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
				
		/*$(".incomplete_button").click(function(){
			$("#statusModal").modal('show');
		});*/
		
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
			var jsonExistingImages = $("#roomItemImageThread_" + m_tempRoomIndex + "_" + m_tempRoomItemIndex).val();
			
			var existingComments = (jsonExistingComments)?JSON.parse(jsonExistingComments):[];
			var existingImages = (jsonExistingImages)?JSON.parse(jsonExistingImages):[];
			
			$("textarea#itemComment").val(itemComment);
			$("textarea#itemComment").focus();
			
			$('#reportStatus').val(itemStatusId);
			$("#reportStatusBtn_" + itemStatusId).addClass('active');
			
			if(existingComments.length > 0) {
				for(var c=0; c < existingComments.length; c++) {
					//$("#existingComments").append("<tr><td>" + existingComments[c].commentDate + "</td><td>" + existingComments[c].comment + "</td><td> by: " + existingComments[c].firstName + " " + existingComments[c].lastName + "</td></tr>");
					htmlComments = "<tr><td>" + existingComments[c].commentDate + "</td><td>" + existingComments[c].comment + "</td><td> by: " + existingComments[c].firstName + " " + existingComments[c].lastName + "</td></tr>";
					if(existingImages.length > 0)
					{
						firstImage = 0;
						for(var d=0; d < existingImages.length; d++) 
						{
							if(existingImages[d].roomItemId == 	existingComments[c].roomItemId)
							{
								if(firstImage == 0)
									htmlComments += "<tr><td colspan='3'>";
										
								htmlComments += "<a href='images/report_uploads/"+existingImages[d].imageName+"' rel='light_box_"+ m_tempRoomIndex + "_" + m_tempRoomItemIndex+"' tabindex='1'><img src='images/report_uploads/"+existingImages[d].imageName+"' style='height:50px;' /></a>&nbsp;";
								
								firstImage += 1;
							}
						}
						if(firstImage > 0)
							htmlComments += "</td></tr>";
					}
					$("#existingComments").append(htmlComments);
				}
			} else {
				$("#existingComments").html("");
				$("#existingComments").append("<tr><td>No comment for this item.</td></tr>");
			}
			
			/*if(existingImages.length > 0) {
				for(var c=0; c < existingImages.length; c++) {
					$("#existingImages").append("<tr><td>" + existingImages[c].imageUploadDate + "</td><td><a href='images/report_uploads/"+existingImages[c].imageName+"' rel='light_box_"+ m_tempRoomIndex + "_" + m_tempRoomItemIndex + ' tabindex='1'><span class='icon-image-upload'></span></a></td><td> by: " + existingImages[c].firstName + " " + existingImages[c].lastName + "</td></tr>");
				}
			} else {
				$("#existingImages").html("");
				$("#existingImages").append("<tr><td>No image for this item.</td></tr>");
			}*/
		});
		
		$("#statusModal").on('hide', function(){
			$('.reportStatus button').removeClass('active');
			$('.add-comment').hide();
			$('#reportStatus').val("");
			$('#itemComment').val("");
			$('#setStatusMessage').html("");
			$("#existingComments").html("");
			$("#existingImages").html("");
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
	
	var markRoomCompleteForm = document.getElementById("markRoomComplete");
	var baseNameElem = document.getElementById("_BASENAME");
	
	function setRoomId(roomId)
	{		
		markRoomCompleteForm.roomId.value = roomId;
	}
	
	function markLineAsComplete()
	{
		$.ajax({
			url: baseNameElem.value + "/webservice/mark_room_complete.php",
			type: "POST",
			data: { roomId: markRoomCompleteForm.roomId.value} 
		}).done(function( response ) {
			if(response.success) {
				checkStatusCompleteAll()
				
				for(i=0;i<response.message.length;i++)
				{
					changeStatusComplete(response.message[i],1,'complete','',0);
				}
				
				statusMsg.innerHTML = getAlert('success', "Room successfully marked as complete");
			} else {
				if(!response.message || response.message == '' || !response) {
					statusMsg.innerHTML = getAlert('error', "Sorry, there has been a problem processing your request.");
				} else {
					statusMsg.innerHTML = getAlert('error', response.message);
				}
			}
			
			$("#markCompleteModal").modal('hide');
		});
	}
	
	function reportButtonClick(value)
	{		
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
	}
	
	function getImageItemId()
	{
		return $("#imageItemId").val();
	}
	
	function loadjscssfile(filename, filetype){
	 if (filetype=="js"){ //if filename is a external JavaScript file
	  var fileref=document.createElement('script')
	  fileref.setAttribute("type","text/javascript")
	  fileref.setAttribute("src", filename)
	 }
	 else if (filetype=="css"){ //if filename is an external CSS file
	  var fileref=document.createElement("link")
	  fileref.setAttribute("rel", "stylesheet")
	  fileref.setAttribute("type", "text/css")
	  fileref.setAttribute("href", filename)
	 }
	 if (typeof fileref!="undefined")
	  document.getElementsByTagName("head")[0].appendChild(fileref)
	}

	function createUploader(){
		var uploader = new qq.FileUploader({
			element: document.getElementById('file-uploader-report'),
			allowedExtensions:['jpg', 'jpeg', 'png', 'gif'],
			action: 'webservice/file_uploader.php?reportId='+getQueryVariable('reportId')+'&propertyId='+getQueryVariable('propertyId')+'&room_item_id='+getImageItemId(),
			onSubmit: function(id, fileName){
				$("#loader").css("display","block");
			},
			showMessage: function(message){
            	if(message == "true")
				{
					/*$("#roomItemImageContainer_" + m_tempRoomIndex + "_" + m_tempRoomItemIndex).html('<a rel="light_box_2" href="images/report_uploads/"><span class="icon-image-upload"></span></a>');
					*/
					$("#setStatusMessage").html(getAlert('success', "Image successfully uploaded"));
					$("#loader").css("display","none");
				}
				else
				{
					$("#setStatusMessage").html(getAlert('error', "There was a problem with the image upload"));
					$("#loader").css("display","none");
				}
        	},
			
			onComplete: function(id, fileName, responseJSON){
				title = $("#roomItemImageContainer_" + m_tempRoomIndex + "_" + m_tempRoomItemIndex).parent().find("div.roomitem-desc").html();
				image_text = $("#roomItemImageContainer_" + m_tempRoomIndex + "_" + m_tempRoomItemIndex).html();
				
				if(m_listRooms[m_tempRoomIndex]) 
				{	
					m_listRooms[m_tempRoomIndex].roomItems[m_tempRoomItemIndex].image = responseJSON.filename;
				}
				
				if(image_text.replace(/\s+/,"") != '')
				{
					image_text = $("#roomItemImageContainer_" + m_tempRoomIndex + "_" + m_tempRoomItemIndex).html();
					
					//add the badge
					if(image_text.search("badge") != -1)
					{
						$("#roomItemImageContainer_" + m_tempRoomIndex + "_" + m_tempRoomItemIndex).append('<a rel="light_box_'+ m_tempRoomIndex + '_' + m_tempRoomItemIndex+'" href="images/report_uploads/'+responseJSON.filename+'?r='+Math.random()+'" tabindex="1" title="'+title+'"><img src="" style="display:none;" /></a>');
						
						badge = parseInt($("#roomItemImageContainer_" + m_tempRoomIndex + "_" + m_tempRoomItemIndex).find("span.badge").html());
						$("#roomItemImageContainer_" + m_tempRoomIndex + "_" + m_tempRoomItemIndex).find("span.badge").html(badge+1);
					}
					else
					{
						$("#roomItemImageContainer_" + m_tempRoomIndex + "_" + m_tempRoomItemIndex).append('<span class="badge">2</span>'+'<a rel="light_box_'+ m_tempRoomIndex + '_' + m_tempRoomItemIndex+'" href="images/report_uploads/'+responseJSON.filename+'?r='+Math.random()+'" tabindex="1" title="'+title+'"><img src="" style="display:none;" /></a>');
					}
				}
				else
				{
					$("#roomItemImageContainer_" + m_tempRoomIndex + "_" + m_tempRoomItemIndex).html('<a rel="light_box_'+ m_tempRoomIndex + '_' + m_tempRoomItemIndex+'" href="images/report_uploads/'+responseJSON.filename+'" tabindex="1" title="'+title+'"><span class="icon-image-upload"></span></a>');
				}
				
				$jq1("a[rel=light_box_"+ m_tempRoomIndex + "_" + m_tempRoomItemIndex+"]").fancybox({
					'transitionIn'		: 'none',
					'transitionOut'		: 'none',
					'titlePosition' 	: 'inside',
					'titleFormat'		: function(title, currentArray, currentIndex, currentOpts) {
						return '<span id="fancybox-title-over">Image ' + (currentIndex + 1) + ' / ' + currentArray.length + (title.length ? ' &nbsp; ' + title : '') + ' </span>';
						//$("#fancybox-img").attr("title",title);
						//return '<span id="fancybox-title-over">' + title + ' </span>';
					}
				});
				
				loadjscssfile("js/lightboxfunctions.js", "js");
			},
			
			debug: true
			/*extraDropzones: [qq.getByClass(document, 'qq-upload-extra-drop-area')[0]]*/
		});
	}

	//window.onload = createUploader;
	
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
	function isSubmitReport(m_listRooms) {
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
	
	function isSubmitReport_subcontractor(m_listRooms) {
		var canSubmit = true;
		
		for(var i=0; i< m_listRooms.length; i++) {
			for(var j=0; j < m_listRooms[i].roomItems.length; j++) {	
				var button_exists = "pending_review_"+m_listRooms[i].roomItems[j].id;
				if(m_listRooms[i].roomItems[j].statusId == 3 && document.getElementById(button_exists)) {
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
		var isNA = false;
		var status_breaker = false;
		
		for(var i=0; i<punchList.length; i++) {
			if(punchList[i].statusId == 3) {
				if(!status_breaker)
				{
					statusElem.setAttribute("class", "label label-danger");
					statusElem.innerHTML = "Incomplete";
					isIncomplete = true;
				}
			} else if(punchList[i].statusId == 2) {
				status_breaker = true;
				statusElem.setAttribute("class", "label label-warning");
				statusElem.innerHTML = "Pending Review";
				isIncomplete = true;
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
		var statusIdValue = statusFormObj.statusIdValue_item.value;
		
		if(statusIdValue == 2)
		{
			var comment = $('#itemComment').val();
			changeStatus(itemId,2,'pending_review',comment,reportId);
			
			if(m_listRooms[m_tempRoomIndex]) 
			{	
				//alert(m_listRooms[m_tempRoomIndex].roomItems[m_tempRoomItemIndex].image);
				m_listRooms[m_tempRoomIndex].roomItems[m_tempRoomItemIndex].statusId=statusIdValue;
				m_listRooms[m_tempRoomIndex].roomItems[m_tempRoomItemIndex].comment = comment;
				
				$("#statusModal").modal('hide');
				
				//Check if all room items are complete.
				var roomStatusElem = document.getElementById("room_status_" + m_tempRoomIndex);
				
				setRoomsStatusIndicator(roomStatusElem, m_listRooms[m_tempRoomIndex].roomItems);
				
				isSubmitReport_subcontractor(m_listRooms);
				
				
				
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
			return false;
		}
		else
		{
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
					
					<?php
						if($_SESSION['user_type'] == 5)
						{
					?>
							isSubmitReport_subcontractor(m_listRooms);
					<?php
						}
						else
						{
					?>
							isSubmitReport(m_listRooms);
					<?php
						}
					?>
					
					
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
	}
	
	function submitReportPendingStatus() 
	{
		var statusFormObj = document.forms['statusFormPending'];
		reportId = statusFormObj.reportId.value;
		itemId = statusFormObj.itemId.value;
		
		var statusIdValue = 2;
		if(statusIdValue == '') 
		{
			$("#setStatusMessage").html(getAlert('error', "Please choose the status for this punchlist item."));
		}  
		else 
		{
			var comment = statusFormObj.itemComment.value;
			
			changeStatus(itemId,2,'pending_review',comment,reportId);
			
			if(m_listRooms[m_tempRoomIndex]) 
			{	
				m_listRooms[m_tempRoomIndex].roomItems[m_tempRoomItemIndex].statusId=statusIdValue;
				m_listRooms[m_tempRoomIndex].roomItems[m_tempRoomItemIndex].comment = comment;
				
				$("#statusModalPending").modal('hide');
				
				//Check if all room items are complete.
				var roomStatusElem = document.getElementById("room_status_" + m_tempRoomIndex);
				
				setRoomsStatusIndicator(roomStatusElem, m_listRooms[m_tempRoomIndex].roomItems);
				
				isSubmitReport_subcontractor(m_listRooms);

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
				
				<?php
					if($_SESSION['user_type'] == 5)
					{
				?>
						isSubmitReport_subcontractor(m_listRooms);
				<?php
					}
					else
					{
				?>
						isSubmitReport(m_listRooms);
				<?php
					}
				?>
				
				
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
	
	function setRoomItemIndex(paramRoomIndex, paramRoomItemIndex, existingComment,itemId, obj) {
		m_tempRoomIndex = paramRoomIndex;
		m_tempRoomItemIndex = paramRoomItemIndex;
		
		$("#imageItemId").val(itemId);
		createUploader();
		$("#itemComment").val('');
		$("#hasImageUploaded").val('');
		
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
		<?php
		if($_SESSION['user_type'] != 5)
		{
		?>
		loadSubcontractorEmails();
		<?php
		}
		?>
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
		$("#addReportStatus").html("");
		
		var reportCommentValue = (($('#reportComment').val().replace(/^\s+|\s+$/gm,'').length > 0) ? $('#reportComment').val().replace(/^\s+|\s+$/gm,''):'');
		
		$.ajax({
			url: $('#_BASENAME').val() + "/webservice/update_report.php",
			type: "POST",
			data: {userId: $('#_USERID').val()
				 , propertyId: $('#_PROPERTYID').val()
				 , statusId: 0
				 , save: isSave
				 , submit: isSubmit
				 , data: JSON.stringify(m_listRooms)
				 , reportId: $('#_REPORTID').val()
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
			// console.log("response: " + response.message);
			
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
					// window.location.href = $('#_BASENAME').val() + "/edit_property.html?propertyId=" + $('#_PROPERTYID').val();
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
	
	function addRoom(name, paramRoomTemplateId, items,roomId) {
		var roomHtml = "";
		
		roomHtml += '<div class="accordion-group" id="room_' + m_roomIndex + '">';
        roomHtml += '<div class="accordion-heading">';
        roomHtml += '<div class="row-fluid"><div class="room-name"><a href="#collapse_' + m_roomIndex + '" data-parent="#rooms-wrapper" data-toggle="collapse" class="accordion-toggle">' + name + '</a></div><div class="room-name-action"><a href="#markCompleteModal" data-toggle="modal" onclick="setRoomId('+roomId+');"><span class="label label-warning" id="room_status_' + m_roomIndex + '">Pending Review</span></a> &nbsp; <button class="btn btn-danger btn-small" onclick="return removeRoom(\'room_' + m_roomIndex + '\', \'' + m_roomIndex + '\')"><i class="icon-trash icon-white"></i></button></div><div class="clearfix"></div></div>';
        roomHtml += '</div>';
        roomHtml += '<div class="accordion-body collapse" id="collapse_' + m_roomIndex + '">';
        roomHtml += '<div class="accordion-inner report-room-items">';
        
		
		var roomItemsHtml = '';
		
		for(var i=0; i < items.length; i++) {
			roomItemsHtml += '<input type="hidden" name="roomStatusId_' + m_roomIndex + '_' + i + '" id="roomStatusId_' + m_roomIndex + '_' + i + '" value="2" />';
			roomItemsHtml += '<input type="hidden" name="roomItemComment_' + m_roomIndex + '_' + i + '" id="roomItemComment_' + m_roomIndex + '_' + i + '" value="" />';
			roomItemsHtml += '<div class="roomitem-group">';
			roomItemsHtml += '<div class="roomitem-desc">' + items[i].name + '</div>';
			roomItemsHtml += '<div class="roomitem-action"><button id="complete_'+items[i].itemId+'" class="btn btn-success glyphicon glyphicon-ok-circle" onClick="changeStatus('+items[i].itemId+',1,\'complete\',\'\',0);"></button> <button id="pending_review_'+items[i].itemId+'" class="glyphicon glyphicon-ok btn btn-warning" onClick="changeStatus('+items[i].itemId+',2,\'pending_review\',\'\',0);"></button> <button id="incomplete_'+items[i].itemId+'" class="incomplete_button glyphicon glyphicon-ban-circle btn btn-danger" onClick="changeIncomplete('+items[i].itemId+','+items[i].reportId+',' + m_roomIndex + ',' + i + ',this,\'\');"></button> <button id="n_a_'+items[i].itemId+'" class="glyphicon glyphicon-adjust btn btn-success" onClick="changeStatus('+items[i].itemId+',4,\'n_a\',\'\',0);"></button></div>';
			roomItemsHtml += '<div class="roomitem-comment" id="roomItemCommentContainer_' + m_roomIndex + '_' + i + '"></div>';
			roomItemsHtml += '<div class="roomitem-image-upload" id="roomItemImageContainer_' + m_roomIndex + '_' + i + '"></div>';
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
		
		<?php
			if($_SESSION['user_type'] == 5)
			{
		?>
				isSubmitReport_subcontractor(m_listRooms);
		<?php
			}
			else
			{
		?>
				isSubmitReport(m_listRooms);
		<?php
			}
		?>
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
					$.ajax({
						url: $('#_BASENAME').val() + "/webservice/get_room_info.php",
						type: "POST",
						data: {reportId: <?php echo $reportId; ?>
							} 
						}).done(function( response ) {		
							m_roomIndex = 0;
							m_listRooms = [];
							
							var existingRooms = response;
							
							//[Start] Process existing rooms
							for(var i=0; i<existingRooms.length; i++) {
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
								
								//SET ROOM INDICATOR
								var roomStatusElem = document.getElementById("room_status_" + i);
								setRoomsStatusIndicator(roomStatusElem, existingRooms[i].items);
								
								<?php
									if($_SESSION['user_type'] == 5)
									{
								?>
										isSubmitReport_subcontractor(m_listRooms);
								<?php
									}
									else
									{
								?>
										isSubmitReport(m_listRooms);
								<?php
									}
								?>
								
								m_roomIndex++;
							}					
						});	
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
	
	function setMainImage(imageUrl)
	{
		var statusMsg = document.getElementById('status-message');
		statusMsg.innerHTML = getAlert('error', "Processing requst...");
		 $.ajax({url:"webservice/set_main_image.php?image_name="+imageUrl,success:function(result){
			statusMsg.innerHTML = getAlert('success', "Image successfully set as main image");
		  }});
	}
	</script>
	
<?php
} else {
?>
	Oops! something went wrong.
<?php
}
?>