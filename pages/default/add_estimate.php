<?php
require_once(dirname(dirname(dirname(__FILE__))) . '/modules/Property.class.php');
require_once(dirname(dirname(dirname(__FILE__))) . '/modules/Report.class.php');
require_once(dirname(dirname(dirname(__FILE__))) . '/modules/Room.class.php');
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
	
	$propertyObjDynamo = new Dynamo("properties");
	$query = "UPDATE properties SET in_estimates = 1 WHERE id = $propertyId";
	$propertyObjDynamo->customExecuteQuery($query);
	
	$estimatesObj = new Estimates();
	
	$estimatesId = $estimatesObj->getPreviousEstimatesId($propertyId, false);
	$estimateInfo = $estimatesObj->getEstimateDetails_estimates($estimatesId, false);
	
	if(trim($estimatesId) != '')
	{
		$estimates_obj = new Dynamo("estimates");
		$estimatesArray = $estimates_obj->getAll("WHERE id = ".$estimatesId);
	
		if($estimatesArray[0]['is_submitted'] == 0)
		{
			?>
			<script type="text/javascript">
				window.location.href = "edit_property_estimate.html?propertyId=<?php print $propertyId; ?>";
			</script>
			<?php
			exit;	
		}
	}
	
	$copy_from_report = false;
	
	if(count($estimateInfo['rooms']) <= 0)
	{
		$reportObj = new Report();
		
		$reportId = $reportObj->getPreviousReportId($propertyId, false);
		
		if($reportId)
			$reportInfo = $reportObj->getReportDetails($reportId, false);
		
		if(count($reportInfo['rooms']) > 0)
		{
			$estimateInfo = $reportInfo;
			$copy_from_report = true;
		}
	}
	
	if(count($estimateInfo['rooms']) > 0)
	{
		$estimates_obj = new Dynamo("estimates");
		$room_templates_obj = new Dynamo("room_templates");
		$estimate_rooms_obj = new Dynamo("estimate_rooms");
		$estimate_room_items_obj = new Dynamo("estimate_room_items");
		$estimate_room_items_units_obj = new Dynamo("estimate_room_items_units");
		$room_template_items_obj = new Dynamo("room_template_items");
		
		//get previous room item units
		if(!$copy_from_report)
			$estimate_room_items_units_array = $estimate_room_items_units_obj->getAll("WHERE estimate_id = ".$estimatesId);
		
		$estimatesId = $_REQUEST['estimatesId'] = $estimates_obj->getMaxId();
		
		$room_template_items_array =  $room_template_items_obj->getAll();
		
		$query = "INSERT INTO estimates (`id`,`property_id`,`date_created`,`reported_by`,`is_submitted`,`is_saved`,`is_closed`) 
		VALUES(".$_REQUEST['estimatesId'].",".$estimateInfo['propertyId'].",NOW(),".$_SESSION['user_id'].",0,1,0)";
		$estimates_obj->customExecuteQuery($query);
		
		if(count($estimateInfo['rooms']) > 0)
		{
			$query = "INSERT INTO estimate_rooms (`id`,`estimate_id`,`room_template_id`,`name`,`date_created`,`created_by`) VALUES";
			$query2 = "INSERT INTO estimate_room_items (`id`,`estimate_id`,`room_id`,`room_template_item_id`,`name`,`date_created`) VALUES";
			
			if(count($estimate_room_items_units_array) > 0)
				$query3 = "INSERT INTO estimate_room_items_units (`id`,`estimate_id`,`room_id`,`estimate_room_items_id`,`work_category_estimates_id`,`units`,`price_per_unit`,`scope`,`status_id`,`timestamp`) VALUES";
			
			$query_first = false;
			$query_second = false;
			$query_third = false;
			
			$maxIdEstimateRooms = $estimate_rooms_obj->getMaxId();
			$estimateRoomMaxId = $estimate_room_items_obj->getMaxId();
			$estimateRoomItemsUnitsMaxId = $estimate_room_items_units_obj->getMaxId();
			
			for($i=0;$i<count($estimateInfo['rooms']);$i++)
			{	
				$query_first = true;
				$query .= "({$maxIdEstimateRooms},".$_REQUEST['estimatesId'].",".$estimateInfo['rooms'][$i]['roomTemplateId'].",\"".$estimateInfo['rooms'][$i]['roomName']."\",NOW(),".$_SESSION['user_id']."),";
				
				for($j=0;$j<count($estimateInfo['rooms'][$i]['items']);$j++)
				{
					for($k=0;$k<count($room_template_items_array);$k++)
					{
						if($room_template_items_array[$k]['name'] == $estimateInfo['rooms'][$i]['items'][$j]['itemName'] && $estimateInfo['rooms'][$i]['roomTemplateId'] == $room_template_items_array[$k]['room_template_id'])
						{
							$room_template_items_id = $room_template_items_array[$k]['id'];
							break;
						}
					}
					
					$query_second = true;
					$query2 .= "(".$estimateRoomMaxId.",".$_REQUEST['estimatesId'].",{$maxIdEstimateRooms},".$room_template_items_id.",'".addslashes(stripslashes($estimateInfo['rooms'][$i]['items'][$j]['itemName']))."',NOW()),";
					
					if(count($estimate_room_items_units_array) > 0)
					{
						for($k=0;$k<count($estimate_room_items_units_array);$k++)
						{
							if($estimate_room_items_units_array[$k]['estimate_room_items_id'] == $estimateInfo['rooms'][$i]['items'][$j]['itemId'])
							{
								$query_third = true;
								$query3 .= "(".$estimateRoomItemsUnitsMaxId.",".$estimatesId.",{$maxIdEstimateRooms},".$estimateRoomMaxId.",".$estimate_room_items_units_array[$k]['work_category_estimates_id'].",'".$estimate_room_items_units_array[$k]['units']."','".$estimate_room_items_units_array[$k]['price_per_unit']."','".addslashes(stripslashes($estimate_room_items_units_array[$k]['scope']))."',2,NOW()),";
								
								$estimateRoomItemsUnitsMaxId += 1;
							}
						}
					}
					
					$estimateRoomMaxId += 1;
				}
				
				$maxIdEstimateRooms += 1;
			}
			
			if($query_first)
			{
				$query = substr($query,0,-1);
				$estimate_rooms_obj->customExecuteQuery($query);
			}
			
			if($query_second)
			{
				$query2 = substr($query2,0,-1);
				$estimate_room_items_obj->customExecuteQuery($query2);
			}
			
			if($query_third)
			{
				$query3 = substr($query3,0,-1);
				$estimate_room_items_units_obj->customExecuteQuery($query3);
			}
		}
		?>
		<script type="text/javascript">
		window.location.href = "edit_estimate.html?propertyId="+<?php print $propertyId; ?>+"&estimatesId="+<?php print $_REQUEST['estimatesId']; ?>;
		</script>
		<?php
		exit;
	}
	
	if(count($estimateInfo['rooms']) <= 0)
	{
?>

	<!-- ADD REPORT -->
	<form method="POST" class="form-horizontal" id="addEstimatesForm" onsubmit="return false">
	<input type="hidden" id="_BASENAME" name="baseName" value="<?php echo __BASENAME__; ?>" />
	<input type="hidden" id="_USERID" name="userId" value="<?php echo $_SESSION['user_id']; ?>" />
	<input type="hidden" id="_PROPERTYID" name="propertyId" value="<?php echo $propertyId; ?>" />
	<input type="hidden" id="_ROOMS" name="rooms" value='<?php echo json_encode($estimateInfo['rooms']); ?>' />
	<input type="hidden" id="_ESTIMATESID" name="estimatesId" value='' />
	<input type="hidden" id="_PROPERTYCOMMUNITY" name="propertyCommunity" value='<?php echo $propertyInfo['community'];?>' />
	<input type="hidden" id="_PROPERTYTYPE" name="propertyType" value='<?php echo $propertyInfo['property_type'];?>' />
	<input type="hidden" id="_PROPERTYJOBTYPE" name="propertyJobType" value='<?php echo $propertyInfo['job_type'];?>' />
	<input type="hidden" id="_PROPERTYNAME" name="propertyName" value='<?php echo $propertyInfo['name'];?>' />
	<input type="hidden" id="_PROPERTYADDRESS" name="propertyName" value='<?php echo $propertyInfo['address'];?>' />
	<input type="hidden" id="_PROPERTYEMAILS" name="propertyName" value='<?php echo $propertyInfo['emails'];?>' />
	<input type="hidden" id="_PROPERTYSTATUS" name="propertyName" value='<?php echo $propertyInfo['status'];?>' />

	
	<div class="pull-left"><h4><?php echo $propertyInfo['community']; ?>, <?php echo $propertyInfo['name']; ?> - Add Estimates</h4></div>
	<div class="pull-right"><a href="edit_property_estimate.html?propertyId=<?php echo $propertyId;?>" class="btn btn-small btn-warning"><i class="icon-info-sign icon-white"></i> Property Details</a></div>
	<div class="clearfix"></div>
	
	<div id="addReportStatus"></div>
	
	<div id="rooms-wrapper" class="accordion">
		<?php
			$tmpRoomIndex = 0;
			if(count($estimateInfo['rooms']) > 0)
			{
			foreach($estimateInfo['rooms'] as $room):
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

	<button class="btn btn-primary" type="submit"  id="submitReportBtn" onclick="submitEstimate();" disabled><i class="icon-ok icon-white"></i> Submit Estimate</button>
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
		<input type="hidden" name="estimatesId" id="estimatesId" value="" />
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
					url: baseNameElem.value + "/webservice/get_room_template_items_estimates.php",
					type: "POST",
					data: { id: roomTemplateElem.options[roomTemplateElem.selectedIndex].value,propertyId:<?php echo $propertyId; ?>,roomName:document.getElementById('roomName').value} 
				}).done(function( response ) {
					if(response.success) {
						statusMsg.innerHTML = getAlert('success', "Room successfully added!");
						window.location.href = $('#_BASENAME').val() + "/edit_estimate.html?propertyId=" + <?php echo $propertyId; ?> + "&estimatesId="+response.data[0].estimatesId;
						//addRoom(roomNameElem.value, roomTemplateElem.options[roomTemplateElem.selectedIndex].value, response.data);
						//return false;
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
		estimatesId = statusFormObj.estimatesId.value;
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
			changeStatus(itemId,3,'incomplete',comment,estimatesId);
			
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
					$('#_ESTIMATESID').val(response.estimatesId);
					
					if(m_isAllItemsCompleted) {
						////Close/Archive property and report.
						if($('#projectStatus').val() == 0) {
							$.ajax({
								url: $('#_BASENAME').val() + "/webservice/archive_property.php",
								type: "POST",
								data: {userId: $('#_USERID').val()
									 , propertyId: $('#_PROPERTYID').val()
									 , estimatesId: response.estimatesId
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
			roomItemsHtml += '<div class="roomitem-action"><button id="complete_'+items[i].itemId+'" class="btn btn-success glyphicon glyphicon-ok-circle" onClick="changeStatus('+items[i].itemId+',1,\'complete\',\'\',0);"></button> <button id="pending_review_'+items[i].itemId+'" class="glyphicon glyphicon-ok btn btn-warning" onClick="changeStatus('+items[i].itemId+',2,\'pending_review\',\'\',0);"></button> <button id="incomplete_'+items[i].itemId+'" class="incomplete_button glyphicon glyphicon-ban-circle btn btn-danger" onClick="changeIncomplete('+items[i].itemId+','+items[i].estimatesId+',' + i + ',' + m_roomIndex + ',this,\'\');"></button> <button id="n_a_'+items[i].itemId+'" class="glyphicon glyphicon-adjust btn btn-success" onClick="changeStatus('+items[i].itemId+',4,\'n_a\',\'\',0);"></button></div>';
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
	}
} else {
?>
	Oops! something went wrong.
<?php
}
?>