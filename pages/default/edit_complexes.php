<?php
require_once(dirname(dirname(dirname(__FILE__))) . '/modules/Report.class.php');
require_once(dirname(dirname(dirname(__FILE__))) . '/modules/Room.class.php');
require_once(dirname(dirname(dirname(__FILE__))) . '/modules/Dynamo.class.php');

$roomObj = new Room();
$listRoomTemplates = $roomObj->getRoomTemplates(false);

$reportObj = new Report();

if(trim($_REQUEST['id']) != '') 
{
	$complex_properties_object = new Dynamo("complex_properties");
	$array_properties = $complex_properties_object->getOne();
}
?>
<div id="community_left">
	<form method="POST" class="form-horizontal" id="addPropertyForm" onsubmit="return false;">
	<input type="hidden" id="baseName" name="baseName" value="<?php echo __BASENAME__; ?>" />
	<input type="hidden" id="userId" name="userId" value="<?php echo $_SESSION['user_id']; ?>" />
	<input type="hidden" id="property_id" name="property_id" value="<?php echo $_REQUEST['id'] ?>" />
	
		<div class="pull-left"><h4>Edit Complex</h4></div>
		<div class="clearfix"></div>

		<div id="status-message"></div>
		<div class="control-group">
			<label for="community" class="control-label">Template Name</label>
			<div class="controls">
				<input type="text" name="community" id="community" class="form-control" value="<?php print $array_properties['community']; ?>" placeholder="Template Name" data-validation="required" data-validation-error-msg="Template Name is a required field" />
			</div>
		</div>
        <div style="display:none;">
		<div class="control-group">
			<label for="jobType" class="control-label">Job Type</label>
			<div class="controls">
				<select id="jobType" name="jobType" data-validation="required" data-validation-error-msg="Job type is a required field">
				<option value="">-- Choose job type --</option>
				<option value="0"<?php if($array_properties['job_type'] == 0) print " selected='selected'"; ?>>New</option>
				<option value="1"<?php if($array_properties['job_type'] == 1) print " selected='selected'"; ?>>Restoration</option>
				</select>
			</div>
		</div>
		<div class="control-group">
			<label for="propertyType" class="control-label">Property Type</label>
			<div class="controls">
				<select id="propertyType" name="propertyType">
				<option value="">-- Choose property type --</option>
				<option value="0"<?php if($array_properties['property_type'] == 0) print " selected='selected'"; ?>>Residential</option>
				<option value="1"<?php if($array_properties['property_type'] == 1) print " selected='selected'"; ?>>Commercial</option>
				</select>
			</div>
		</div>
		<div class="control-group">
			<label for="address" class="control-label">City St.</label>
			<div class="controls">
				<input type="text" name="city" id="city" class="form-control" placeholder="City" value="<?php print $array_properties['city']; ?>" />
			</div>
		</div>
		<div class="control-group">
			<label for="address" class="control-label">Zip Code</label>
			<div class="controls">
				<input type="text" name="zip" id="zip" class="form-control" placeholder="Zip Code" value="<?php print $array_properties['zip']; ?>" />
			</div>
		</div>
		<div class="control-group">
            <label for="estimates_multiplier" class="control-label">Estimates Multiplier</label>
            <div class="controls">
                <input type="text" name="estimatesMultiplier" id="estimatesMultiplier" class="form-control" placeholder="Estimates Multipler" value="<?php echo $array_properties['estimates_multiplier']; ?>" />
            </div>
        </div>
        <div class="control-group">
			<label for="emailList" class="control-label">
				<strong>Email Address</strong>
				<br/>
				<small>Email reports will be sent to:</small>
			</label>
			<div class="controls">
				<textarea name="emailList" id="emailList" rows="3" placeholder="email_1@abc.com, email_2@abc.com, email_3@abc.com"><?php print $array_properties['emails']; ?></textarea>
			</div>
		</div>
        <div class="control-group">
            <label for="estimatesEmailList" class="control-label">
                <strong>Estimates Email Address</strong>
                <br/>
                <small>Email reports will be sent to:</small>
            </label>
            
            <div class="controls">
                <textarea name="estimatesEmailList" id="estimatesEmailList" rows="3" placeholder="email_1@abc.com, email_2@abc.com, email_3@abc.com" data-validation-error-msg="Enter email address's in comma-separated format."><?php echo $array_properties['estimates_emails']; ?></textarea>
            </div>
        </div>
        
		<div class="control-group">
			<label for="status" class="control-label">Status</label>
			<div class="controls">
				Open
			</div>
		</div>
        </div>
		<br />
		<div class="pull-right"><button class="btn btn-warning" type="submit">Save Changes</button> &nbsp; <a href="complexes.html" class="btn btn-default">Cancel</a><br /><br />
        <a class="btn btn-warning" data-toggle="modal" href="#addCommunityModal">Deploy to Communities</a><br  /><br  />
        <a class="btn btn-warning" data-toggle="modal" href="#addPropertyModal">Deploy to Properties</a><br  />
		</div>
	</form>
	</div>
	<div id="community_right">
		<!-- ADD REPORT -->
	<form method="POST" class="form-horizontal" id="addReportForm" onSubmit="return false">
	<input type="hidden" id="_BASENAME" name="baseName" value="<?php echo __BASENAME__; ?>" />
	<input type="hidden" id="_USERID" name="userId" value="<?php echo $_SESSION['user_id']; ?>" />
	
	<div class="clearfix" style="padding-top:25px;"></div>
	
	<div id="addReportStatus"></div>
	
	<div id="rooms-wrapper" class="accordion">
		<?php
			$tmpRoomIndex = 0;
			$complex_reports_object = new Dynamo("complex_reports");
			$complex_report_rooms_object = new Dynamo("complex_report_rooms");
			$complex_report_room_items_object = new Dynamo("complex_report_room_items");
			$report_status = new Dynamo("report_status");
			$complex_report_room_item_comments = new Dynamo("complex_report_room_item_comments");
			
			$array_report_status = $report_status->getAllWithId();
			
			$complex_reports_array = $complex_reports_object->getAll("WHERE property_id = ".$_REQUEST['id']);
			
			$reportInfo['rooms'] = array();
			$array_rooms = array();
			if(count($complex_reports_array) > 0)
			{ 
				$complex_reports_array = $complex_reports_array[0];
				$complex_report_rooms_array = $complex_report_rooms_object->getAll("WHERE report_id = ".$complex_reports_array['id'] ." ORDER BY room_template_id,id");
				
				$overal_room_array = array();
				$array_room_items = array();
				
				for($i=0;$i<count($complex_report_rooms_array);$i++)
				{
					if(isset($complex_reports_array[$i]['id']))
						$complex_report_room_item_comments_array = $complex_report_room_item_comments->getAllWithId_default("WHERE report_id = ".$complex_reports_array[$i]['id'],"room_item_id");
					
					$array_rooms["roomId"] = $complex_report_rooms_array[$i]['id'];
					$array_rooms["roomTemplateId"] = $complex_report_rooms_array[$i]['room_template_id'];
					$array_rooms["roomName"] = $complex_report_rooms_array[$i]['name'];
					$array_rooms["items"] = array();
					
					$complex_report_room_items_array = $complex_report_room_items_object->getAll("WHERE room_id = ".$complex_report_rooms_array[$i]['id']." ORDER BY room_template_item_id");
					
					for($j=0;$j<count($complex_report_room_items_array);$j++)
					{
						$array_room_items['itemId'] = $complex_report_room_items_array[$j]['id'];
						$array_room_items['itemTemplateId'] = $complex_report_room_items_array[$j]['room_template_item_id'];
						$array_room_items['itemName'] = $complex_report_room_items_array[$j]['name'];
						$array_room_items['statusId'] = $complex_report_room_items_array[$j]['status_id'];
						$array_room_items['work_category_id'] = $complex_report_room_items_array[$j]['work_category_id'];
					
						$array_room_items['statusClass'] = $array_report_status[$complex_report_room_items_array[$j]['status_id']]['class'];
						$array_room_items['statusName'] = $array_report_status[$complex_report_room_items_array[$j]['status_id']]['name'];
						
						$array_room_items['comment_thread'] = array();
						
						if(count($complex_report_room_item_comments_array) > 0)
						{
							$comment_array = array();
							$array_room_items['comments'] = array();
							
							if(trim($complex_report_room_item_comments_array[$complex_report_room_items_array[$j]['id']]['comment']) != '')
							{
								$comment_array['comment'] = $complex_report_room_item_comments_array[$complex_report_room_items_array[$j]['id']]['comment'];
								$comment_array['commentDate'] = $complex_report_room_item_comments_array[$complex_report_room_items_array[$j]['id']]['date'];
								$array_room_items['comments'][] = $comment_array;
							}
						}
						$array_rooms["items"][] = $array_room_items;
					}
					
					$overal_room_array[] = $array_rooms;
				}
			}
			
			$reportInfo['rooms'] = $overal_room_array;
			?>
			<input type="hidden" id="_ROOMS" name="rooms" value='<?php echo json_encode($reportInfo['rooms']); ?>' />
		
			<?php
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
								<span class="label label-warning" id="room_status_<?php echo $tmpRoomIndex; ?>" style="visibility:hidden;">Pending Review</span> &nbsp;
								<button class="btn btn-danger btn-small" onClick="return removeRoom('room_<?php echo $tmpRoomIndex;?>', '<?php echo $tmpRoomIndex;?>', <?php echo $room['roomId']; ?>)"><i class="icon-trash icon-white"></i></button>
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
										
										$existingComment = empty($item['comments'][0]['comment']) ? "" : htmlentities($item['comments'][0]['comment']);
										$statusClassname = (empty($item['statusClass'])?'btn-warning':$item['statusClass']);
									?>
									<input type="hidden" name="roomStatusId_<?php echo $tmpRoomIndex . '_' . $roomItemIndex;?>" id="roomStatusId_<?php echo $tmpRoomIndex . '_' . $roomItemIndex;?>" value="<?php echo $item['statusId']; ?>"/>
									<input type="hidden" name="work_category_id_<?php echo $tmpRoomIndex . '_' . $roomItemIndex;?>" id="work_category_id_<?php echo $tmpRoomIndex . '_' . $roomItemIndex;?>" value="<?php echo $item['work_category_id']; ?>"/>
									<input type="hidden" name="roomItemComment_<?php echo $tmpRoomIndex . '_' . $roomItemIndex;?>" id="roomItemComment_<?php echo $tmpRoomIndex . '_' . $roomItemIndex;?>" value="<?php echo $existingComment; ?>"/>
									<input type="hidden" name="roomItemCommentThread_<?php echo $tmpRoomIndex . '_' . $roomItemIndex;?>" id="roomItemCommentThread_<?php echo $tmpRoomIndex . '_' . $roomItemIndex;?>" value='<?php echo json_encode($item['comment_thread']); ?>'/>
									
									<div class="roomitem-group">
										<div class="roomitem-desc"><?php echo $item['itemName']; ?></div>
										<div class="roomitem-action">	
											<a href="#statusModal" role="button" onClick="setRoomItemIndex('<?php echo $tmpRoomIndex;?>','<?php echo $roomItemIndex; ?>', '<?php echo $existingComment; ?>',<?php echo $item['itemId']; ?>, this)" data-toggle="modal" class="btn <?php echo $statusClassname; ?>" id="status_btn_<?php echo $tmpRoomIndex;?>_<?php echo $roomItemIndex; ?>" style="visibility:hidden;"><?php echo (empty($item['statusName'])?'Pending Review':$item['statusName']); ?></a>
										</div>
										<div class="roomitem-comment" id="roomItemCommentContainer_<?php echo $tmpRoomIndex . '_' . $roomItemIndex;?>"><?php echo (isset($item['comments'][0]['comment'])? '<span class="icon-comment"></span> '. $item['comments'][0]['comment']:'&nbsp;'); ?> </div>
										<div class="roomitem-image-upload" id="roomItemImageContainer_<?php echo $tmpRoomIndex . '_' . $roomItemIndex;?>"><?php echo (isset($array_report_images[$item['itemId']])? '<span class="icon-image-upload"></span>' :''); ?> </div>
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
	
	</form>
	
	<!-- MODAL POPUP -->
	<div id="submitConfirmationModal" class="modal hide fade" tabindex="-1" role="dialog" aria-hidden="true">
		<div class="modal-header">
			<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
			<h3>Submit Report Confirmation</h3>
		</div>
		
		<div class="alert alert-warning hide" id="submitReportStatus"></div>
	</div>	
	
	<div id="statusModal" class="modal hide fade" tabindex="-1" role="dialog" aria-hidden="true">
		<div class="modal-header">
			<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
			<h3>Status</h3>
		</div>
		
		<form id="statusForm" method="post" onSubmit="return submitReportItemStatus();">
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
			<div id="" style="float:left;">		
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
		<form id="addRoomForm" method="post">
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
	
    <div id="addCommunityModal" class="modal hide fade" tabindex="-1" role="dialog" aria-hidden="true">
		<div class="modal-header">
			<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
			<h3>Deploy to Community</h3>
		</div>
		<form id="addCommunityForm" method="post">
			<div class="modal-body">
				<div id="status-message-community"></div>
				
                <div class="control-group">
                    <label for="community" class="control-label">Community</label>
                    <div>
                        <input type="text" name="community" id="community" class="form-control" value="<?php print $array_properties['community']; ?>" placeholder="Community" data-validation="required" data-validation-error-msg="Community is a required field" />
                    </div>
                </div>
        
				<div class="control-group">
                    <label for="jobType" class="control-label">Job Type</label>
                    <div>
                        <select id="jobType" name="jobType" data-validation="required" data-validation-error-msg="Job type is a required field">
                        <option value="">-- Choose job type --</option>
                        <option value="0">New</option>
                        <option value="1">Restoration</option>
                        </select>
                    </div>
                </div>
                <div class="control-group">
                    <label for="propertyType" class="control-label">Property Type</label>
                    <div>
                        <select id="propertyType" name="propertyType" data-validation="required" data-validation-error-msg="Property type is a required field">
                        <option value="">-- Choose property type --</option>
                        <option value="0">Residential</option>
                        <option value="1">Commercial</option>
                        </select>
                    </div>
                </div>
                <div class="control-group">
                    <label for="address" class="control-label">City St.</label>
                    <div>
                        <input type="text" name="city" id="city" class="form-control" placeholder="City" data-validation="required" data-validation-error-msg="City is a required field." value="" />
                    </div>
                </div>
                <div class="control-group">
                    <label for="address" class="control-label">Zip Code</label>
                    <div>
                        <input type="text" name="zip" id="zip" class="form-control" placeholder="Zip Code" value="" data-validation="required" data-validation-error-msg="Zip code is a required field."  />
                    </div>
                </div>
                <div class="control-group">
                    <label for="estimates_multiplier" class="control-label">Estimates Multiplier</label>
                    <div>
                        <input type="text" name="estimatesMultiplier" id="estimatesMultiplier" class="form-control" placeholder="Estimates Multipler" value="" />
                    </div>
                </div>
                <div class="control-group">
                    <label for="emailList" class="control-label">
                        <strong>Email Address</strong>
                        <br/>
                        <small>Email reports will be sent to:</small>
                    </label>
                    <div>
                        <textarea name="emailList" id="emailList" rows="3" placeholder="email_1@abc.com, email_2@abc.com, email_3@abc.com" data-validation="required" data-validation-error-msg="Enter email address's in comma-separated format."></textarea>
                    </div>
                </div>
                <div class="control-group">
                    <label for="estimatesEmailList" class="control-label">
                        <strong>Estimates Email Address</strong>
                        <br/>
                        <small>Email reports will be sent to:</small>
                    </label>
                    
                    <div>
                        <textarea name="estimatesEmailList" id="estimatesEmailList" rows="3" placeholder="email_1@abc.com, email_2@abc.com, email_3@abc.com" data-validation-error-msg="Enter email address's in comma-separated format."></textarea>
                    </div>
                </div>
			</div>
			<div class="modal-footer-custom">
				<button class="btn btn-primary">Create Community</button>
				<button class="btn" data-dismiss="modal" aria-hidden="true">Close</button>
			</div>
		</form>
	</div>
    
    <div id="addPropertyModal" class="modal hide fade" tabindex="-1" role="dialog" aria-hidden="true">
		<div class="modal-header">
			<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
			<h3>Deploy to Property</h3>
		</div>
		<form id="addPropertyDeployForm" name="addPropertyDeployForm" method="post">
			<div class="modal-body">
				<div id="status-message-property"></div>
				
                <div class="control-group">
					<label for="community" class="control-label">Community</label>
					<div>
						<input type="text" name="community" id="community" class="form-control" placeholder="Community" value="<?php print $array_properties['community']; ?>" data-validation="required" data-validation-error-msg="Community is a required field" />
					</div>
				</div>
                
				<div>
					<label for="propertyName" class="control-label">Property Name</label>
					<div>
						<input type="text" name="propertyName" id="propertyName" class="form-control" placeholder="Property Name" value="" data-validation="required" data-validation-error-msg="Please enter a property name." />
					</div>
				</div>
				<div>
					<label for="jobType" class="control-label">Job Type</label>
					<div>
						<select id="jobType" name="jobType" data-validation="required" data-validation-error-msg="Job type is a required field">
						<option value="">-- Choose job type --</option>
						<option value="0">New</option>
						<option value="1">Restoration</option>
						</select>
					</div>
				</div>
				<div>
					<label for="propertyType" class="control-label">Property Type</label>
					<div>
						<select id="propertyType" name="propertyType" data-validation="required" data-validation-error-msg="Property type is a required field">
						<option value="">-- Choose property type --</option>
						<option value="0">Residential</option>
						<option value="1">Commercial</option>
						</select>
					</div>
				</div>
				<div>
					<label for="address" class="control-label">Address</label>
					<div>
						<textarea name="address" id="address" class="form-control" placeholder="Address" data-validation="required" data-validation-error-msg="Address is a required field."></textarea>
					</div>
				</div>
				<div>
					<label for="address" class="control-label">City St.</label>
					<div>
						<input type="text" name="city" id="city" class="form-control" placeholder="City" value="" data-validation="required" data-validation-error-msg="City is a required field." />
					</div>
				</div>
				<div>
					<label for="address" class="control-label">Zip Code</label>
					<div>
						<input type="text" name="zip" id="zip" class="form-control" placeholder="Zip Code" value="" data-validation="required" data-validation-error-msg="Zip code is a required field." />
					</div>
				</div>
				<div>
					<label for="address" class="control-label">Google Map Link</label>
					<div>
						<input type="text" name="googleMapLink" id="googleMapLink" class="form-control" placeholder="Map URL" value="" />
					</div>
				</div>
                <div>
					<label for="estimates_multiplier" class="control-label">Estimates Multiplier</label>
					<div>
						<input type="text" name="estimatesMultiplier" id="estimatesMultiplier" class="form-control" placeholder="Estimates Multipler" value="" />
					</div>
				</div>
				<div>
					<label for="emailList" class="control-label">
						<strong>Email Address</strong>
						<br/>
						<small>Email reports will be sent to:</small>
                        
					</label>
					
					<div>
						<textarea name="emailList" id="emailList" rows="3" placeholder="email_1@abc.com, email_2@abc.com, email_3@abc.com" data-validation="required" data-validation-error-msg="Enter email address's in comma-separated format."></textarea>
					</div>
				</div>
                <div>
					<label for="estimatesEmailList" class="control-label">
						<strong>Estimates Email Address</strong>
						<br/>
						<small>Email reports will be sent to:</small>
					</label>
					
					<div>
						<textarea name="estimatesEmailList" id="estimatesEmailList" rows="3" placeholder="email_1@abc.com, email_2@abc.com, email_3@abc.com" data-validation-error-msg="Enter email address's in comma-separated format."></textarea>
					</div>
				</div>
			</div>
			<div class="modal-footer-custom">
				<button class="btn btn-primary">Create Property</button>
				<button class="btn" data-dismiss="modal" aria-hidden="true">Close</button>
			</div>
		</form>
	</div>
	<?php
		$complex_subcontractors_assign_object = new Dynamo("complex_subcontractors_assign");
		$complex_subcontractors_assign_array = $complex_subcontractors_assign_object->getAll("WHERE property_id = ".$_REQUEST['id']);
		
		$community_subcontractors_ids_assign_array = array();
		for($i=0;$i<count($complex_subcontractors_assign_array);$i++)
		{
			$community_subcontractors_ids_assign_array[$complex_subcontractors_assign_array[$i]['work_category_id']][] = $complex_subcontractors_assign_array[$i]['sub_contractor_id'];
		}
		
		$work_categories_object = new Dynamo("work_categories");
		$work_categories_array = $work_categories_object->getAll("ORDER BY id");
		
		$sub_contractors_object = new Dynamo("sub_contractors");
		$sub_contractors_array = $sub_contractors_object->getAll("INNER JOIN sub_contractor_work_category ON sub_contractors.id = sub_contractor_work_category.sub_contractor_id ORDER BY sub_contractors.first_name");
		
		$work_sub_contractors_array = array();
		for($i=0;$i<count($sub_contractors_array);$i++)
		{
			if(!is_array($community_subcontractors_ids_assign_array[$sub_contractors_array[$i]['work_category_id']]))
					$community_subcontractors_ids_assign_array[$sub_contractors_array[$i]['work_category_id']] = array();
					
				if(in_array($sub_contractors_array[$i]['id'],$community_subcontractors_ids_assign_array[$sub_contractors_array[$i]['work_category_id']]))
				{
					$selected = " checked='checked'";
				}
				else
					$selected = "";
				
				$work_sub_contractors_array[$sub_contractors_array[$i]['work_category_id']] .= "<label style='float:left;width:150px;padding-right:20px;white-space:nowrap'><input type=\"checkbox\" class=\"sub_contractor_id_sub\" name='' value='".$sub_contractors_array[$i]['id']."'".$selected." /> ".$sub_contractors_array[$i]['first_name']." ".$sub_contractors_array[$i]['last_name']."</label>";
		}
	?>
	
	<div id="editSubcontractorsModal" class="modal hide fade" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                <h3>Edit Subcontractors</h3>
            </div>
            <form id="editSubContractorForm" name="editSubContractorForm" method="post">
                <input type="hidden" id="property_id" name="property_id" value="<?php echo $_REQUEST['id'] ?>" />
                <div class="modal-body">
                    <div id="status-message"></div>
                    
                    <?php
                    for($i=0;$i<count($work_categories_array);$i++)
                    {
                        
                    ?>
                    <div class="control-group">
                        <label for="roomName" class="control-label"><strong><?php print $work_categories_array[$i]['name']; ?></strong>:<?php if(trim($work_sub_contractors_array[$work_categories_array[$i]['id']]) == ''){print " No subcontractor available for this category";} ?></label>
                        <?php
                        if(trim($work_sub_contractors_array[$work_categories_array[$i]['id']]) != '')
                        {
                        ?>
                        <input type="hidden" name="work_category_id_sub_<?php print $i+1; ?>" id="work_category_id_sub_<?php print $i+1; ?>" class="work_category_id_sub" value="<?php print $work_categories_array[$i]['id']; ?>" />
                        <!--<select name="sub_contractor_id_sub_<?php print $i+1; ?>" class="sub_contractor_id_sub" multiple> -->
                            <?php
                                print str_replace("name=''","name='sub_contractor_id_sub_".$work_categories_array[$i]['id']."'",$work_sub_contractors_array[$work_categories_array[$i]['id']]);
                            ?>
							<div style="clear:both;"></div>
                        <!--</select> -->
                        <?php
                        }
                        ?>
                    </div>
                    <?php
                    }
                    ?>
                    
                </div>
                <div class="modal-footer-custom">
                    <button class="btn btn-primary">Save Sub Contractors</button>
                    <button class="btn" data-dismiss="modal" aria-hidden="true">Close</button>
                </div>
            </form>
        </div>
	
	<div id="createPropertiesModal" class="modal hide fade" tabindex="-1" role="dialog" aria-hidden="true">
		<div class="modal-header">
			<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
			<h3>Create Properties</h3>
		</div>
		<form id="createPropertiesForm" name="createPropertiesForm" method="post">
			<input type="hidden" id="property_id" name="property_id" value="<?php echo $_REQUEST['id'] ?>" />
			<div class="modal-body">
				<div id="status-message"></div>
				
				<div class="createproperties_row">
					<div class="createproperties_left"><h5>Lot Name</h5></div>
					<div class="createproperties_middle"><h5>Lot Address</h5></div>
					<div class="createproperties_right"><h5>Maps Link</h5></div>
					<div class="clearfix"></div>	
				</div>
				
				<div class="createproperties_row">			
					<div class="createproperties_left">
						<input type="text" name="lot_name_0" id="lot_name_0" class="lot_name" value="" />
					</div>
					<div class="createproperties_middle">
						<input type="text" name="lot_address_0" id="lot_address_0" class="lot_address" value="" />
					</div>
					<div class="createproperties_right">
						<input type="text" name="map_link_0" id="map_link_0" class="map_link"  />
					</div>		
					<div class="clearfix"></div>	
				</div>
				<div class="other_properties"></div>
                <div style="padding-bottom:5px;">
                	<label for="create_estimates" class="control-label">Create Estimates: <input type="checkbox" name="create_estimates" id="create_estimates" value="true" style="position:relative;top:-2px;" /></label>
                	
                </div>
				<a class="btn" role="button" href="" onclick="return createProperty();"><i class="icon-plus"></i> Add Property</a> 
			</div>
			<div class="modal-footer-custom">
				<button class="btn btn-primary">Save Properties</button>
				<button class="btn" data-dismiss="modal" aria-hidden="true">Close</button>
			</div>
			<input type="hidden" name="createPropertiesForm_id" id="createPropertiesForm_id" value="1" />
		</form>
	</div>
</div><!-- end of div -->
<div class="clearfix"></div>
	
	<script type="text/javascript">
	var formObjProp = document.forms['createPropertiesForm'];
	function createProperty()
	{
		var propertyHTML = '';
		
		idReached = parseInt(formObjProp.createPropertiesForm_id.value);
		propertyHTML += '<div class="createproperties_row">';
		propertyHTML += '<div class="createproperties_left">';
		propertyHTML += '<input type="text" name="lot_name_'+idReached+'" id="lot_name_'+idReached+'" class="lot_name" value="" />';
		propertyHTML += '</div>';
		propertyHTML += '<div class="createproperties_middle">';
		propertyHTML += '<input type="text" name="lot_address_'+idReached+'" id="lot_address_'+idReached+'" class="lot_address" value="" />';
		propertyHTML += '</div>';
		propertyHTML += '<div class="createproperties_right">';
		propertyHTML += '<input type="text" name="map_link_'+idReached+'" id="map_link_'+idReached+'" class="map_link" />';
		propertyHTML += '</div>';
		propertyHTML += '<div class="clearfix"></div>';
		propertyHTML += '</div>';
		
		$(".other_properties").append(propertyHTML);
		
		idReached = idReached + 1;
		formObjProp.createPropertiesForm_id.value = idReached;
		return false;
	}
	
	var m_roomIndex = 0;
	var m_listRooms = [];
	var m_tempRoomIndex;
	var m_tempRoomItemIndex;
	var m_isAllItemsCompleted = false;
	
	window.onload = function() {
		formObjProp.createPropertiesForm_id.value = 1;
		var statusMsg = document.getElementById('status-message');
		var formObj = document.forms['addPropertyForm'];
		
		var baseNameElem = formObj.baseName;
		var propertyIdElem = formObj.property_id;
		var propertyCityElem = formObj.city;
		var estimatesMultiplier = formObj.estimatesMultiplier;
		var estimatesEmailList = formObj.estimatesEmailList;
		var propertyZipElem = formObj.zip;
		var userIdElem = formObj.userId;
		var emailAddressElem = formObj.emailList;
		var propertyTypeElem = formObj.propertyType;
		var jobTypeElem = formObj.jobType;
		var communityElem = formObj.community;
		var statusVal = 1;
		
		if(!baseNameElem) return;
		
		$.validate({	
			form: '#addPropertyForm',
			modules: 'security',
			onValidate:function() {
			},
			onError: function() {
				statusMsg.innerHTML = getAlert('error', 'Oops! something is wrong. Please check the values below.');
			},
			onSuccess : function() {
				statusMsg.innerHTML = "";
				statusMsg.innerHTML = getAlert('info', 'Please wait while we process your request...');
				
				$.ajax({
					url: baseNameElem.value + "/webservice/edit_complexes.php",
					type: "POST",
					data: { id: propertyIdElem.value
							,city: propertyCityElem.value
							,estimates_multiplier: estimatesMultiplier.value
							,zip: propertyZipElem.value 
							,status: statusVal
							,userId: userIdElem.value
							,emails: emailAddressElem.value
							,estimates_emails: estimatesEmailList.value
							,propertyType: propertyTypeElem.options[propertyTypeElem.selectedIndex].value
							,jobType: jobTypeElem.options[jobTypeElem.selectedIndex].value
							,community: communityElem.value
							,data: JSON.stringify(m_listRooms)
						} 
				}).done(function( response ) {
					if(response.success) {
						statusMsg.innerHTML = getAlert('success', response.message);
					} else {
						if(!response.message || response.message == '' || !response) {
							statusMsg.innerHTML = getAlert('error', "Sorry, there has been a problem processing your request");
						} else {
							statusMsg.innerHTML = getAlert('error', response.message);
						}
					}
				});
			  
				return false;
			}
		});
		
		var formObjSub = document.forms['editSubContractorForm'];
		
		$.validate({	
			form: '#editSubContractorForm',
			modules: 'security',
			onValidate:function() {
			},
			onError: function() {
				statusMsg.innerHTML = getAlert('error', 'Oops! something is wrong. Please check the values below.');
			},
			onSuccess : function() {
				statusMsg.innerHTML = "";
				statusMsg.innerHTML = getAlert('info', 'Please wait while we process your request...');
				
				var sub_contractor_assign = [];
				
				sub_contractor_id_sub = $(".sub_contractor_id_sub");
				//work_category_id_sub = $(".work_category_id_sub");
				
				for(i=0;i<sub_contractor_id_sub.length;i++)
				{
					subcontractor_name = sub_contractor_id_sub.eq(i).attr("name");
					subcontractor_work_category_id = subcontractor_name.replace("sub_contractor_id_sub_","")
					
					//for(j=0;j<sub_contractor_id_sub[i].length;j++)
					{
						if(sub_contractor_id_sub[i].checked)
						{
							sub_contractor_assign.push({sub_contractor_id:sub_contractor_id_sub[i].value,work_category_id:subcontractor_work_category_id});
						}
					}
				}
				
				$.ajax({
					url: baseNameElem.value + "/webservice/edit_sub_contractor.php",
					type: "POST",
					data: { property_id: formObjSub.property_id.value
							,data: JSON.stringify(sub_contractor_assign)
						} 
				}).done(function( response ) {
					if(response.success) {
						statusMsg.innerHTML = getAlert('success', response.message);
						$("#editSubcontractorsModal").modal('hide');
					} else {
						if(!response.message || response.message == '' || !response) {
							statusMsg.innerHTML = getAlert('error', "Sorry, there has been a problem processing your request");
							$("#editSubcontractorsModal").modal('hide');
						} else {
							statusMsg.innerHTML = getAlert('error', response.message);
							$("#editSubcontractorsModal").modal('hide');
						}
					}
				});
			  
				return false;
			}
		});
		
		/*$.validate({	
			form: '#editSubContractorForm',
			modules: 'security',
			onValidate:function() {
			},
			onError: function() {
				statusMsg.innerHTML = getAlert('error', 'Oops! something is wrong. Please check the values below.');
			},
			onSuccess : function() {
				statusMsg.innerHTML = "";
				statusMsg.innerHTML = getAlert('info', 'Please wait while we process your request...');
				
				var sub_contractor_assign = [];
				
				sub_contractor_id_sub = $(".sub_contractor_id_sub");
				work_category_id_sub = $(".work_category_id_sub");
				
				
				for(i=0;i<sub_contractor_id_sub.length;i++)
				{
					if(sub_contractor_id_sub[i].value != '')
					{
						sub_contractor_assign.push({sub_contractor_id:sub_contractor_id_sub[i].value,work_category_id:work_category_id_sub[i].value});
					}
				}
				
				$.ajax({
					url: baseNameElem.value + "/webservice/edit_sub_contractor.php",
					type: "POST",
					data: { property_id: formObjSub.property_id.value
							,data: JSON.stringify(sub_contractor_assign)
						} 
				}).done(function( response ) {
					if(response.success) {
						statusMsg.innerHTML = getAlert('success', response.message);
						$("#editSubcontractorsModal").modal('hide');
					} else {
						if(!response.message || response.message == '' || !response) {
							statusMsg.innerHTML = getAlert('error', "Sorry, there has been a problem processing your request");
							$("#editSubcontractorsModal").modal('hide');
						} else {
							statusMsg.innerHTML = getAlert('error', response.message);
							$("#editSubcontractorsModal").modal('hide');
						}
					}
				});
			  
				return false;
			}
		});*/
		
		$.validate({	
			form: '#createPropertiesForm',
			modules: 'security',
			onValidate:function() {
			},
			onError: function() {
				statusMsg.innerHTML = getAlert('error', 'Oops! something is wrong. Please check the values below.');
			},
			onSuccess : function() {
				statusMsg.innerHTML = "";
				statusMsg.innerHTML = getAlert('info', 'Please wait while we process your request...');
				
				var property_details = [];
				
				lot_name = $(".lot_name");
				lot_address = $(".lot_address");
				map_link = $(".map_link");
				
				for(i=0;i<lot_name.length;i++)
				{
					if(lot_name[i].value != '')
					{
						property_details.push({name:lot_name[i].value,lot_address:lot_address[i].value,map_link:map_link[i].value});
					}
				}
				
				//create_estimates = $("#create_estimates").val();
				create_estimates = $("#create_estimates").is(":checked");
				if(create_estimates)
					create_estimates = true;
				else
					create_estimates = false;
				
				$.ajax({
					url: baseNameElem.value + "/webservice/batch_add_properties.php",
					type: "POST",
					data: { property_id: formObjProp.property_id.value
							,estimates: create_estimates
							,data: JSON.stringify(property_details)
						} 
				}).done(function( response ) {
					if(response.success) {
						statusMsg.innerHTML = getAlert('success', response.message);
						$("#createPropertiesModal").modal('hide');
						//formObjProp.reset();
					} else {
						if(!response.message || response.message == '' || !response) {
							statusMsg.innerHTML = getAlert('error', "Sorry, there has been a problem processing your request");
							$("#createPropertiesModal").modal('hide');
						} else {
							statusMsg.innerHTML = getAlert('error', response.message);
							$("#createPropertiesModal").modal('hide');
						}
					}
				});
			  
				return false;
			}
		});
		
		var statusMsg = document.getElementById('status-message');
		var addRoomFormObj = document.forms['addRoomForm'];
		var addCommunityObj = document.forms['addCommunityForm'];
		var addPropertyDeployObj = document.forms['addPropertyDeployForm'];
		
		if(!statusMsg || !addRoomFormObj) return;
		

		var baseNameElem = document.getElementById("_BASENAME");
		var userIdElem = document.getElementById("_USERID");
		var roomNameElem = addRoomFormObj.roomName;
		var roomTemplateElem = addRoomFormObj.roomTemplate;
		var roomsElem = document.getElementById("_ROOMS");
		
		var existingRooms = JSON.parse(roomsElem.value);
		
		//[Start] Process existing rooms
		if(existingRooms != null)
		{
			for(var i=0; i<existingRooms.length; i++) {
			
				var arrRoomItems = [];
				
				for(var j=0; j < existingRooms[i].items.length; j++) {
					arrRoomItems.push({id: existingRooms[i].items[j].itemId, roomTemplateItemId:  existingRooms[i].items[j].itemTemplateId, name: existingRooms[i].items[j].itemName, statusId: existingRooms[i].items[j].statusId,work_category_id: existingRooms[i].items[j].work_category_id, comment: existingRooms[i].items[j].comment,imageuploaded: existingRooms[i].items[j].imageuploaded});
				}
				
				m_listRooms.push({roomId: i, roomTemplateId: existingRooms[i].roomTemplateId, roomName: existingRooms[i].roomName, roomItems: arrRoomItems, isNew: 0});
				
				//SET ROOM INDICATOR
				var roomStatusElem = document.getElementById("room_status_" + i);
				setRoomsStatusIndicator(roomStatusElem, existingRooms[i].items);
				
				isSubmitReport();
				
				m_roomIndex++;
			}
						
			if(m_listRooms.length) {
				$('#saveBtn').prop('disabled', false);
			}
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
					url: baseNameElem.value + "/webservice/get_community_template_items.php",
					type: "POST",
					data: { id: roomTemplateElem.options[roomTemplateElem.selectedIndex].value } 
				}).done(function( response ) {
					if(response.success) {
						//statusMsg.innerHTML = getAlert('success', "Room successfully added!");
						addRoom(roomNameElem.value, roomTemplateElem.options[roomTemplateElem.selectedIndex].value, response.data,response.room_id);
						
						roomNameElem.setAttribute("class", "span4");
						
						//adding a room to the db
						$.ajax({
							url: baseNameElem.value + "/webservice/edit_complexes.php",
							type: "POST",
							data: { id: propertyIdElem.value
									,city: propertyCityElem.value
									,estimates_multiplier: estimatesMultiplier.value
									,estimates_emails: estimatesEmailList.value
									,zip: propertyZipElem.value 
									,status: statusVal
									,userId: userIdElem.value
									,emails: emailAddressElem.value
									,propertyType: propertyTypeElem.options[propertyTypeElem.selectedIndex].value
									,jobType: jobTypeElem.options[jobTypeElem.selectedIndex].value
									,community: communityElem.value
									,data: JSON.stringify(m_listRooms)
								} 
						}).done(function( response ) {
							if(response.success) {
								statusMsg.innerHTML = getAlert('success', "Room successfully added!");
								//statusMsg.innerHTML = getAlert('success', response.message);
							} else {
								if(!response.message || response.message == '' || !response) {
									statusMsg.innerHTML = getAlert('error', "Sorry, there has been a problem processing your request");
								} else {
									statusMsg.innerHTML = getAlert('error', response.message);
								}
							}
						});
						
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
		
		$.validate({
			form: '#addCommunityForm',
			onError: function() {
				statusMsg.innerHTML = getAlert('error', 'Oops! something is wrong. Please check the values below.');
			},
			onSuccess : function() {
				statusMsg.innerHTML = "";
				$.ajax({
					url: baseNameElem.value + "/webservice/complex_add_community.php",
					type: "POST",
					data: { id: <?php print $_GET['id']; ?>,
					community:addCommunityObj.community.value,
					jobType:addCommunityObj.jobType.value,
					propertyType:addCommunityObj.propertyType.value,
					city:addCommunityObj.city.value,
					zip:addCommunityObj.zip.value,
					estimatesMultiplier:addCommunityObj.estimatesMultiplier.value,
					emailList:addCommunityObj.emailList.value,
					estimatesEmailList:addCommunityObj.estimatesEmailList.value}
				}).done(function( response ) {
					if(response.success) {
						$("#addCommunityModal").modal('hide');
						addCommunityObj.reset();
						statusMsg.innerHTML = getAlert('success', "The community has been successfully added!");
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
		
		$.validate({
			form: '#addPropertyDeployForm',
			onError: function() {
				statusMsg.innerHTML = getAlert('error', 'Oops! something is wrong. Please check the values below.');
			},
			onSuccess : function() {
				statusMsg.innerHTML = "";
				
				$.ajax({
					url: baseNameElem.value + "/webservice/complex_add_property.php",
					type: "POST",
					data: { id: <?php print $_GET['id']; ?>,
					community:addPropertyDeployObj.community.value,
					propertyName:addPropertyDeployObj.propertyName.value,
					jobType:addPropertyDeployObj.jobType.value,
					propertyType:addPropertyDeployObj.propertyType.value,
					address:addPropertyDeployObj.address.value,
					city:addPropertyDeployObj.city.value,
					zip:addPropertyDeployObj.zip.value,
					googleMapLink:addPropertyDeployObj.googleMapLink.value,
					estimatesMultiplier:addPropertyDeployObj.estimatesMultiplier.value,
					emailList:addPropertyDeployObj.emailList.value,
					estimatesEmailList:addPropertyDeployObj.estimatesEmailList.value}
				}).done(function( response ) {
					if(response.success) {
						$("#addPropertyModal").modal('hide');
						addPropertyDeployObj.reset();
						statusMsg.innerHTML = getAlert('success', "The property has been added successfully");
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
	</script>
	
	<!-- END MODAL POPUP -->
	
	<script type="text/javascript">
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
				 , propertyComplex: $('#_PROPERTYCOMMUNITY').val()
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
	
	function addRoom(name, paramRoomTemplateId, items,room_id) {
		var roomHtml = "";
		
		roomHtml += '<div class="accordion-group" id="room_' + m_roomIndex + '">';
        roomHtml += '<div class="accordion-heading">';
        roomHtml += '<div class="row-fluid"><div class="room-name"><a href="#collapse_' + m_roomIndex + '" data-parent="#rooms-wrapper" data-toggle="collapse" class="accordion-toggle">' + name + '</a></div><div class="room-name-action"><span class="label label-warning" id="room_status_' + m_roomIndex + '" style="visibility:hidden;">Pending Review</span> &nbsp; <button class="btn btn-danger btn-small" onclick="return removeRoom(\'room_' + m_roomIndex + '\', \'' + m_roomIndex + '\',' + room_id + ')"><i class="icon-trash icon-white"></i></button></div><div class="clearfix"></div></div>';
        roomHtml += '</div>';
        roomHtml += '<div class="accordion-body collapse" id="collapse_' + m_roomIndex + '">';
        roomHtml += '<div class="accordion-inner report-room-items">';
        
		
		var roomItemsHtml = '';
		
		for(var i=0; i < items.length; i++) {
			roomItemsHtml += '<input type="hidden" name="roomStatusId_' + m_roomIndex + '_' + i + '" id="roomStatusId_' + m_roomIndex + '_' + i + '" value="2" />';
			roomItemsHtml += '<input type="hidden" name="work_category_id_' + m_roomIndex + '_' + i + '" id="work_category_id_' + m_roomIndex + '_' + i + '" value="'+items[i].work_category_id+'" />';
			roomItemsHtml += '<input type="hidden" name="roomItemComment_' + m_roomIndex + '_' + i + '" id="roomItemComment_' + m_roomIndex + '_' + i + '" value="" />';

			roomItemsHtml += '<div class="roomitem-group">';
			roomItemsHtml += '<div class="roomitem-desc">' + items[i].name + '</div>';
			roomItemsHtml += '<div class="roomitem-action"><a href="#statusModal" role="button" onclick="setRoomItemIndex(\'' +m_roomIndex+ '\',\'' + i + '\', \'\', this)" data-toggle="modal" class="btn btn-warning" id="status_btn_' + m_roomIndex + '_' + i + '" style="visibility:hidden;">Pending Review</a></div>';
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
					url: $('#_BASENAME').val() + "/webservice/delete_community_room.php",
					type: "POST",
					data: {id: roomId,reportId: <?php print $_REQUEST['id']; ?>} 
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