<?php
require_once(dirname(dirname(dirname(__FILE__))) . '/modules/Property.class.php');
require_once(dirname(dirname(dirname(__FILE__))) . '/modules/Room.class.php');
require_once(dirname(dirname(dirname(__FILE__))) . '/modules/Report.class.php');
require_once(dirname(dirname(dirname(__FILE__))) . '/modules/Dynamo.class.php');

$propertyId = isset($_GET['propertyId'])?$_GET['propertyId']:0;

/*$file = "test.txt";
$fp = fopen($file,"w");

if($fp)
{
	ob_start();
	print '<pre>';
	print_r($_REQUEST);
	print '</pre>';
	$string = ob_get_clean();
	fwrite($fp,$string);
}
exit;*/

$roomObj = new Room();
$listRoomTemplates = $roomObj->getRoomTemplates(false);

$reportObj = new Report();	
if($propertyId != 0) {
	$propertyObj = new Property();
	$propertyInfo = $propertyObj->getPropertyInfo($propertyId, false);

	if(!$propertyInfo) {	
		echo "No property found.";
		return;
	}
	
	
	
	$reportId = $reportObj->getPreviousReportId($propertyId, false);
	
	$reportInfo = $reportObj->getReportDetails($reportId, false);
}

$complex_properties_obj = new Dynamo("complex_properties");
$complex_properties_array = $complex_properties_obj->getAll("ORDER BY id");
?>
<div id="community_left">
	<form method="POST" class="form-horizontal" id="addPropertyForm" onsubmit="return false;">
	<input type="hidden" id="baseName" name="baseName" value="<?php echo __BASENAME__; ?>" />
	<input type="hidden" id="userId" name="userId" value="<?php echo $_SESSION['user_id']; ?>" />
		<div class="pull-left"><h4>Add Community</h4></div>
		<div class="clearfix"></div>

		<div id="status-message"></div>
		<div class="control-group">
			<label for="community" class="control-label">Community</label>
			<div class="controls">
				<input type="text" name="community" id="community" class="form-control" placeholder="Community" value="" data-validation="required" data-validation-error-msg="Community is a required field" />
			</div>
		</div>
		<div class="control-group">
			<label for="jobType" class="control-label">Job Type</label>
			<div class="controls">
				<select id="jobType" name="jobType" data-validation="required" data-validation-error-msg="Job type is a required field">
				<option value="">-- Choose job type --</option>
				<option value="0">New</option>
				<option value="1">Restoration</option>
				</select>
			</div>
		</div>
		<div class="control-group">
			<label for="propertyType" class="control-label">Property Type</label>
			<div class="controls">
				<select id="propertyType" name="propertyType" data-validation="required" data-validation-error-msg="Property type is a required field">
				<option value="">-- Choose property type --</option>
				<option value="0">Residential</option>
				<option value="1">Commercial</option>
                <?php
				for($i=0;$i<count($complex_properties_array);$i++)
				{
					print "<option value=\"".($i+2)."\" rel='".$complex_properties_array[$i]["id"]."'>".$complex_properties_array[$i]["community"]."</option>";	
				}
				?>
				</select>
			</div>
		</div>
		<div class="control-group">
			<label for="address" class="control-label">City St.</label>
			<div class="controls">
				<input type="text" name="city" id="city" class="form-control" placeholder="City" value="" data-validation="required" data-validation-error-msg="City is a required field."  />
			</div>
		</div>
		<div class="control-group">
			<label for="address" class="control-label">Zip Code</label>
			<div class="controls">
				<input type="text" name="zip" id="zip" class="form-control" placeholder="Zip Code" value="" data-validation="required" data-validation-error-msg="Zip code is a required field."  />
			</div>
		</div>
         <div class="control-group">
			<label for="estimates_multiplier" class="control-label">Estimates Multiplier</label>
			<div class="controls">
				<input type="text" name="estimatesMultiplier" id="estimatesMultiplier" class="form-control" placeholder="Estimates Multipler" value="" />
			</div>
		</div>
		<div class="control-group">
			<label for="emailList" class="control-label">
				<strong>Email Address</strong>
				<br/>
				<small>Email reports will be sent to:</small>
			</label>
			<div class="controls">
				<textarea name="emailList" id="emailList" rows="3" placeholder="email_1@abc.com, email_2@abc.com, email_3@abc.com" data-validation="required" data-validation-error-msg="Enter email address's in comma-separated format."></textarea>
			</div>
		</div>
        <div class="control-group">
			<label for="estimatesEmailList" class="control-label">
				<strong>Estimates Email Address</strong>
				<br/>
				<small>Email reports will be sent to:</small>
			</label>
			<div class="controls">
				<textarea name="estimatesEmailList" id="estimatesEmailList" rows="3" placeholder="email_1@abc.com, email_2@abc.com, email_3@abc.com" data-validation-error-msg="Enter email address's in comma-separated format."></textarea>
			</div>
		</div>
		<div class="control-group">
			<label for="status" class="control-label">Status</label>
			<div class="controls">
				Open
			</div>
		</div>
		<br />
		<div class="pull-right"><button class="btn btn-warning" type="submit">Save Changes</button> &nbsp; <a href="community.html" class="btn btn-default">Cancel</a>
		</div>
		
		
	<form>
	</div>
	<div id="community_right">
		<!-- ADD REPORT -->
	<form method="POST" class="form-horizontal" id="addReportForm" onsubmit="return false">
	<input type="hidden" id="_BASENAME" name="baseName" value="<?php echo __BASENAME__; ?>" />
	<input type="hidden" id="_USERID" name="userId" value="<?php echo $_SESSION['user_id']; ?>" />
	<input type="hidden" id="_ROOMS" name="rooms" value='<?php echo json_encode($reportInfo['rooms']); ?>' />
	
	<div class="clearfix" style="padding-top:25px;"></div>
	
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
	
	<!--<a href="#addRoomModal" role="button" class="btn" data-toggle="modal"><i class="icon-plus"></i> Add Room</a> -->
	
	</form>
	
	
	<!-- MODAL POPUP -->
	<div id="addCommunityTemplate" class="modal hide fade" tabindex="-1" role="dialog" aria-hidden="true">
		<div class="modal-header">
			<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
			<h3>Confirm Template Use</h3>
		</div>
		
		<form id="statusForm" method="post" onsubmit="return submitReportItemStatus();">
		<div class="modal-body">
			Would you like to use the <span id="template_name"></span> template.
		</div>
		
		<div class="modal-footer-custom">
            <button class="btn btn-primary" onclick="useTemplate();">Yes</button>
            <button class="btn" onclick="dontUseTemplate();">No</button>
        </div>
		</form>
	</div>	
	
	<div id="statusModal" class="modal hide fade" tabindex="-1" role="dialog" aria-hidden="true">
		<div class="modal-header">
			<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
			<h3>Status</h3>
		</div>
		
		<form id="statusForm" method="post" onsubmit="return submitReportItemStatus();">
		<div class="modal-body">
			
			<div class="btn-group reportStatus" data-toggle="buttons-radio">
				
			</div>
			
		</div>
		
			<button class="btn btn-primary">Submit</button>
			<button class="btn" data-dismiss="modal" aria-hidden="true">Close</button>
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
</div><!-- end of div -->
<div class="clearfix"></div>
	
	<script type="text/javascript">
	var m_roomIndex = 0;
	var m_listRooms = [];
	var m_tempRoomIndex;
	var m_tempRoomItemIndex;
	var m_isAllItemsCompleted = false;
	
	window.onload = function() {
		var statusMsg = document.getElementById('status-message');
		var formObj = document.forms['addPropertyForm'];
		
		var baseNameElem = formObj.baseName;
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
				if(propertyTypeElem.options[propertyTypeElem.selectedIndex].value > 1)
				{
					propertyTypeSelect = $("#propertyType").find('option:selected').text();
					$("#template_name").html("<strong>"+propertyTypeSelect+"</strong>");
					$("#addCommunityTemplate").modal('show');	
				}
				else
				{
					statusMsg.innerHTML = getAlert('info', 'Please wait while we process your request...');
					
					$.ajax({
						url: baseNameElem.value + "/webservice/add_community.php",
						type: "POST",
						data: { city: propertyCityElem.value
								,zip: propertyZipElem.value 
								,estimates_multiplier: estimatesMultiplier.value
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
							
							formObj.reset();
							
							window.location.href = $('#baseName').val() + "/edit_community.html?id="+response.property_id;
						} else {
							if(!response.message || response.message == '' || !response) {
								statusMsg.innerHTML = getAlert('error', "Sorry, there has been a problem processing your request");
							} else {
								statusMsg.innerHTML = getAlert('error', response.message);
							}
						}
					});
				}
				
				return false;
			}
		});
		
		var statusMsg = document.getElementById('status-message');
		var addRoomFormObj = document.forms['addRoomForm'];
		
		if(!statusMsg || !addRoomFormObj) return;
		

		var baseNameElem = document.getElementById("_BASENAME");
		var userIdElem = document.getElementById("_USERID");
		var roomNameElem = addRoomFormObj.roomName;
		var roomTemplateElem = addRoomFormObj.roomTemplate;
		var roomsElem = document.getElementById("_ROOMS");
		
		var existingRooms = JSON.parse(roomsElem.value);
		
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
					data: { id: roomTemplateElem.options[roomTemplateElem.selectedIndex].value } 
				}).done(function( response ) {
					if(response.success) {
						statusMsg.innerHTML = getAlert('success', "Room successfully added!");
						
						addRoom(roomNameElem.value, roomTemplateElem.options[roomTemplateElem.selectedIndex].value, response.data);
						
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
	<script type="text/javascript" src="js/fileuploader.js?r=<?php print rand(1,333); ?>"></script>
	
	<script type="text/javascript">
	function useTemplate()
	{
		var m_listRooms = [];
		
		var statusMsg = document.getElementById('status-message');
		var formObj = document.forms['addPropertyForm'];
		
		var baseNameElem = '<?php echo __BASENAME__; ?>';
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
		
		//if(!baseNameElem) return;
		
		$("#addCommunityTemplate").modal('hide');
		
		statusMsg.innerHTML = getAlert('info', 'Please wait while we process your request...');
		$.ajax({
			url: baseNameElem + "/webservice/add_community.php",
			type: "POST",
			data: { city: propertyCityElem.value
					,zip: propertyZipElem.value 
					,estimates_multiplier: estimatesMultiplier.value
					,status: statusVal
					,userId: <?php echo $_SESSION['user_id']; ?>
					,emails: emailAddressElem.value
					,estimates_emails: estimatesEmailList.value
					,propertyType: propertyTypeElem.options[propertyTypeElem.selectedIndex].value
					,jobType: jobTypeElem.options[jobTypeElem.selectedIndex].value
					,community: communityElem.value
					,data: JSON.stringify(m_listRooms)
				} 
		}).done(function( response ) {
			if(response.success) {
				property_id = response.property_id;
				
				$.ajax({
				url: baseNameElem + "/webservice/use_template.php",
				type: "POST",
				data: { userId: <?php echo $_SESSION['user_id']; ?>
						,complexPropertyId: $("#propertyType").find('option:selected').attr("rel")
						,propertyId: property_id
					}
				}).done(function( response ) {
					if(response.success) {
						statusMsg.innerHTML = getAlert('success', response.message);
						formObj.reset();
						window.location.href = $('#baseName').val() + "/edit_community.html?id="+property_id;
					}
					else {
						if(!response.message || response.message == '' || !response) {
							statusMsg.innerHTML = getAlert('error', "Sorry, there has been a problem processing your request");
						} else {
							statusMsg.innerHTML = getAlert('error', response.message);
						}
					}
				});
			} else {
				if(!response.message || response.message == '' || !response) {
					statusMsg.innerHTML = getAlert('error', "Sorry, there has been a problem processing your request");
				} else {
					statusMsg.innerHTML = getAlert('error', response.message);
				}
			}
		});
	}
	
	function dontUseTemplate()
	{
		var m_listRooms = [];
		
		var statusMsg = document.getElementById('status-message');
		var formObj = document.forms['addPropertyForm'];
		
		var baseNameElem = '<?php echo __BASENAME__; ?>';
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
		
		//if(!baseNameElem) return;
		
		$("#addCommunityTemplate").modal('hide');
		
		statusMsg.innerHTML = getAlert('info', 'Please wait while we process your request...');
					
		$.ajax({
			url: baseNameElem + "/webservice/add_community.php",
			type: "POST",
			data: { city: propertyCityElem.value
					,zip: propertyZipElem.value
					,estimates_multiplier: estimatesMultiplier.value
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
				
				formObj.reset();
				
				window.location.href = $('#baseName').val() + "/edit_community.html?id="+response.property_id;
			} else {
				if(!response.message || response.message == '' || !response) {
					statusMsg.innerHTML = getAlert('error', "Sorry, there has been a problem processing your request");
				} else {
					statusMsg.innerHTML = getAlert('error', response.message);
				}
			}
		});
	}
	
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
			roomItemsHtml += '<input type="hidden" name="work_category_id_' + m_roomIndex + '_' + i + '" id="work_category_id_' + m_roomIndex + '_' + i + '" value="'+items[i].work_category_id+'" />';
			roomItemsHtml += '<input type="hidden" name="roomItemComment_' + m_roomIndex + '_' + i + '" id="roomItemComment_' + m_roomIndex + '_' + i + '" value="" />';

			roomItemsHtml += '<div class="roomitem-group">';
			roomItemsHtml += '<div class="roomitem-desc">' + items[i].name + '</div>';
			roomItemsHtml += '<div class="roomitem-action"><a href="#statusModal" role="button" onclick="setRoomItemIndex(\'' +m_roomIndex+ '\',\'' + i + '\', \'\', this)" data-toggle="modal" class="btn btn-warning" id="status_btn_' + m_roomIndex + '_' + i + '">Pending Review</a></div>';
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
	
	function createTemplate()
	{
		
	}
	</script>