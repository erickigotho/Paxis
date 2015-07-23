<?php
require_once(dirname(dirname(dirname(__FILE__))) . '/modules/Property.class.php');
require_once(dirname(dirname(dirname(__FILE__))) . '/modules/Report.class.php');
require_once(dirname(dirname(dirname(__FILE__))) . '/modules/Room.class.php');
require_once(dirname(dirname(dirname(__FILE__))) . '/modules/Estimates.class.php');
require_once(dirname(dirname(dirname(__FILE__))) . '/modules/Dynamo.class.php');
require_once(dirname(dirname(dirname(__FILE__))) . '/modules/Tools.class.php');

$propertyId = isset($_GET['propertyId'])?$_GET['propertyId']:0;
$estimatesId = isset($_GET['estimatesId'])?$_GET['estimatesId']:0;

if($propertyId != 0 && $estimatesId != 0) {
	$propertyObj = new Property();
	$propertyInfo = $propertyObj->getPropertyInfo($propertyId, false);
	
	$roomObj = new Room();
	$listRoomTemplates = $roomObj->getRoomTemplates(false);
	
	if(!$propertyInfo) {	
		echo "No property found.";
		return;
	}
	
	$estimatesObj = new Estimates();
	$estimatesInfo = $estimatesObj->getEstimateDetails_estimates($estimatesId, false);
	
	$disable_room_addition = false;
	$reportObj = new Report();
	
	$reportId = $reportObj->getPreviousReportId($propertyId, false);
	if($reportId)
		$reportInfo = $reportObj->getReportDetails($reportId, false);
	
	if(count($reportInfo['rooms']) > 0)
	{
		$disable_room_addition = true;	
	}
	
	$estimate_room_items_units_obj = new Dynamo("estimate_room_items_units");
	
	$query = "SELECT estimate_room_items_units.*, work_category_estimates.item_name, work_category_estimates.unit_of_measure FROM estimate_room_items_units 
	INNER JOIN work_category_estimates ON estimate_room_items_units.work_category_estimates_id = work_category_estimates.id 
	WHERE estimate_room_items_units.estimate_id = ".$estimatesId;
	
	$estimate_room_items_units_array = $estimate_room_items_units_obj->customFetchQuery($query);
	
	if(count($estimate_room_items_units_array) > 0)
	{
		$arrayCount = array();
		for($i=0;$i<count($estimate_room_items_units_array);$i++)
		{
			if($estimate_room_items_units_array[$i]['units'] > 0)
				$arrayCount[$estimate_room_items_units_array[$i]['estimate_room_items_id']] += 1; 
		}
	}
	
?>
	
	<form method="POST" class="form-horizontal" id="addReportForm" onSubmit="return false">
	<input type="hidden" id="_BASENAME" name="baseName" value="<?php echo __BASENAME__; ?>" />
	<input type="hidden" id="_USERID" name="userId" value="<?php echo $_SESSION['user_id']; ?>" />
	<input type="hidden" id="_PROPERTYID" name="propertyId" value="<?php echo $propertyId; ?>" />
	<input type="hidden" id="_ESTIMATESID" name="estimatesId" value="<?php echo $estimatesId; ?>" />
	<input type="hidden" id="_PROPERTYCOMMUNITY" name="propertyCommunity" value='<?php echo $propertyInfo['community'];?>' />
	<input type="hidden" id="_PROPERTYTYPE" name="propertyType" value='<?php echo $propertyInfo['property_type'];?>' />
	<input type="hidden" id="_PROPERTYJOBTYPE" name="propertyJobType" value='<?php echo $propertyInfo['job_type'];?>' />
	<input type="hidden" id="_ROOMS" name="rooms" value='<?php echo json_encode($estimatesInfo['rooms']); ?>' />
	<input type="hidden" id="_PROPERTYNAME" name="propertyName" value='<?php echo $propertyInfo['name'];?>' />
	<input type="hidden" id="_PROPERTYADDRESS" name="propertyName" value='<?php echo $propertyInfo['address'];?>' />
	<input type="hidden" id="_PROPERTYEMAILS" name="propertyName" value='<?php echo $propertyInfo['emails'];?>' />
	<input type="hidden" id="_PROPERTYSTATUS" name="propertyName" value='<?php echo $propertyInfo['status'];?>' />

	<div class="pull-left"><h4><?php echo $propertyInfo['community']; ?>, <?php echo $propertyInfo['name']; ?> - Edit Estimate</h4></div>
	<div class="pull-right"><a href="edit_property_estimate.html?propertyId=<?php echo $propertyId;?>" class="btn btn-small btn-warning"><i class="icon-info-sign icon-white"></i> Property Details</a></div>
	<div class="clearfix"></div>
	<div id="status-message"></div>
    
	<div id="addReportStatus"></div>
	
	<div id="rooms-wrapper" class="accordion">
		<?php
			$tmpRoomIndex = 0;
			
			if(count($estimatesInfo['rooms']) > 0)
			{
				$units = new Dynamo("units");
				$arrayUnits = $units->getAllWithId();
			foreach($estimatesInfo['rooms'] as $room):
				// var_dump($room);
			?>
				<div class="accordion-group" id="room_<?php echo $tmpRoomIndex;?>">
					<div class="accordion-heading">
						<div class="row-fluid">
							<div class="room-name">
								<a href="#collapse_<?php echo $tmpRoomIndex;?>" data-parent="#rooms-wrapper" data-toggle="collapse" class="accordion-toggle"><?php echo $room['roomName'];?></a>
							</div>
							<div class="room-name-action">
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
									?>
									<input type="hidden" name="roomStatusId_<?php echo $tmpRoomIndex . '_' . $roomItemIndex;?>" id="roomStatusId_<?php echo $tmpRoomIndex . '_' . $roomItemIndex;?>" value="<?php echo $item['statusId']; ?>"/>
									
									<div class="roomitem-group">
										<div class="roomitem-desc"><?php echo $item['itemName']; ?> 
                                        </div>
										<div class="roomitem-action">
                                        	
                                            <button aria-hidden="true" class="btn" onClick="getLineItemEstimates(<?php print $estimatesId.",".$item['itemTemplateId'].",".$room['roomId'].",".$item['itemId'].",".$item['work_category_id'].",'".$item['itemName']."','".$tmpRoomIndex."','".$roomItemIndex; ?>');"><i class="icon-plus"></i> Add Estimate</button>
										</div>
                                        
                                        
										<div id="roomItemCommentContainer_<?php echo $tmpRoomIndex . '_' . $roomItemIndex;?>" class="roomitem-comment estimate-comment" onClick="getLineItemEstimates(<?php print $estimatesId.",".$item['itemTemplateId'].",".$room['roomId'].",".$item['itemId'].",".$item['work_category_id'].",'".$item['itemName']."','".$tmpRoomIndex."','".$roomItemIndex; ?>');"><?php if(trim($arrayCount[$item['itemId']]) != ''){ ?><span class="icon-comment"></span><span class="badge-small"><?php print $arrayCount[$item['itemId']]; ?></span> <?php } ?> </div> 
                                        
										<div class="clearfix"></div>
									</div>
                                    <div class="roomitem-group roomitem-estimate-item" id="room_estimate_item_<?php echo $tmpRoomIndex . '_' . $roomItemIndex;?>">
                                    	<div class="roomitem-desc" id="roomitem_desc_<?php echo $tmpRoomIndex . '_' . $roomItemIndex;?>">
                                        <?php
											/*$arrayUnits[1] = "LF";
											$arrayUnits[2] = "SF";
											$arrayUnits[3] = "Ac";
											$arrayUnits[4] = "SU";
											$arrayUnits[5] = "CF";
											$arrayUnits[6] = "SY";
											$arrayUnits[7] = "Ea";
											*/
											for($j=0;$j<count($estimate_room_items_units_array);$j++)
											{
												if($item['itemId'] == $estimate_room_items_units_array[$j]["estimate_room_items_id"])
												{
													print $estimate_room_items_units_array[$j]["item_name"] . " ".$arrayUnits[$estimate_room_items_units_array[$j]["unit_of_measure"]]."<br />";
												}
											}
										?>
                                        </div>
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
     
     <?php
	 //if($disable_room_addition == false)
	 {
	 ?>
	<a href="#addRoomModal" role="button" class="btn" data-toggle="modal"><i class="icon-plus"></i> Add Room</a>
	<?php
	 }
	?>
	<br/><br/>
	<button class="btn btn-primary" type="submit"  id="submitReportBtn" onClick="sendEstimate();"><i class="icon-ok icon-white"></i> Send Estimate</button>
	</form>
	<!-- END ADD REPORT -->
	
    <div id="estimates" class="modal hide fade" tabindex="-1" role="dialog" aria-hidden="true">
	<div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
        <h3 id="estimates_header">Estimates</h3>
    </div>
	<form id="addEstimatesForm" name="addEstimatesForm" method="post">
    	<input type="hidden" name="estimate_room_items_id" id="estimate_room_items_id" value="" />
        <input type="hidden" name="estimates_room_id" id="estimates_room_id" value="" />
        
        <input type="hidden" name="itemTemplateId" id="itemTemplateId" value="" />
        <input type="hidden" name="room_id" id="room_id" value="" />
        <input type="hidden" name="itemId" id="itemId" value="" />
        <input type="hidden" name="workCategoryId" id="workCategoryId" value="" />
        <input type="hidden" name="item_name" id="item_name" value="" />
        <input type="hidden" name="tmpRoomIndex" id="tmpRoomIndex" value="" />
        <input type="hidden" name="roomItemIndex" id="roomItemIndex" value="" />
        
			<div class="modal-body">
            	<div id="currentEstimates">
                	
                </div>
    		 </div>
             <div class="modal-footer-custom">
                <button class="btn btn-primary">Submit</button>
                <button class="btn" data-dismiss="modal" aria-hidden="true">Cancel</button>
			</div>
            <input type="hidden" name="tmpRoomIndex" id="tmpRoomIndex" value="" />
			<input type="hidden" name="roomItemIndex" id="roomItemIndex" value="" />
     </form>
     </div>
     
	<!-- MODAL POPUP -->
	<div id="submitConfirmationModal" class="modal hide fade" tabindex="-1" role="dialog" aria-hidden="true">
		<div class="modal-header">
			<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
			<h3>Submit Estimate Confirmation</h3>
		</div>
		
		<div class="alert alert-warning hide" id="submitReportStatus"></div>
		
		<form id="submitConfirmForm" method="post">
		<input type="hidden" name="projectStatus" id="projectStatus" value="1" />
		<div class="modal-body">
			Email estimates will be sent to:
			<ul>
			<?php 
				$emails = explode(',', $propertyInfo['estimates_emails']);
				
				foreach($emails as $email):
				?>
					<li><?php echo $email; ?></li>
				<?php
				endforeach;
			?>
			</ul>
		</div>
		<div class="modal-footer-custom">
			<button class="btn btn-primary btnSubmitReport">Submit Estimate</button>
			<button class="btn" data-dismiss="modal" aria-hidden="true">Cancel</button>
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
	var m_existingStatusId;
	var baseNameElem = document.getElementById("_BASENAME");
	window.onload = function() {
		
		var statusMsg = document.getElementById('status-message');
		var addRoomFormObj = document.forms['addRoomForm'];
		
		if(!statusMsg || !addRoomFormObj) return;
		
		
		var userIdElem = document.getElementById("_USERID");
		var roomNameElem = addRoomFormObj.roomName;
		var roomTemplateElem = addRoomFormObj.roomTemplate;
		var roomsElem = document.getElementById("_ROOMS");
		
		var existingRooms = JSON.parse(roomsElem.value);
		
		//[Start] Process existing rooms
		for(var i=0; i<existingRooms.length; i++) 
		{
			var arrRoomItems = [];
			for(var j=0;j<existingRooms[i].items.length;j++) 
			{
				arrRoomItems.push({id: existingRooms[i].items[j].itemId, name: existingRooms[i].items[j].itemName});	
			}
			m_listRooms.push({roomId: existingRooms[i].roomId, roomName: existingRooms[i].roomName, roomItems: arrRoomItems, isNew: 0});
			
			m_roomIndex++;
		}
		
		//[End] Process existing rooms
		$.validate({
			form: '#submitConfirmForm',
			onError: function() {
				statusMsg.innerHTML = getAlert('error', 'Oops! something is wrong. Please check the values below.');
			},
			onSuccess : function() {
				statusMsg.innerHTML = "";
				$.ajax({
					url: baseNameElem.value + "/webservice/send_estimate.php",
					type: "POST",
					data: { estimatesId:<?php print $estimatesId; ?>,propertyId:<?php print $propertyId; ?>} 
				}).done(function( response ) {
					if(response.success) {
						window.location.href = baseNameElem.value + "/edit_property_estimate.html?propertyId=<?php print $propertyId; ?>"
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
			form: '#addRoomForm',
			onError: function() {
				statusMsg.innerHTML = getAlert('error', 'Oops! something is wrong. Please check the values below.');
			},
			onSuccess : function() {
				statusMsg.innerHTML = "";
				$.ajax({
					url: baseNameElem.value + "/webservice/get_room_template_items_estimates.php",
					type: "POST",
					data: { id: roomTemplateElem.options[roomTemplateElem.selectedIndex].value,estimatesId:<?php print $estimatesId; ?>,roomName:document.getElementById('roomName').value,propertyId:<?php print $propertyId; ?>} 
				}).done(function( response ) {
					if(response.success) {
						statusMsg.innerHTML = getAlert('success', "Room successfully added!");
						addRoom(roomNameElem.value, roomTemplateElem.options[roomTemplateElem.selectedIndex].value, response.data,response.roomId);
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
			form: '#addEstimatesForm',
			modules: 'security',
			onValidate:function() {
			},
			onError: function() {
				statusMsg.innerHTML = getAlert('error', 'Oops! something is wrong. Please check the values below.');
			},
			onSuccess : function() {
				statusMsg.innerHTML = "";
				statusMsg.innerHTML = getAlert('info', 'Please wait while we process your request...');
				var estimate_details = [];
                var subcontractor_details_array = [];
				
				units = $(".units");
				price_per_unit = $(".price_per_unit");
                scope = $(".scope");
                subcontractor_id = $(".subcontractor_id");
                
				for(i=0;i<units.length;i++)
				{
					if(units[i].value != '')
					{
						estimate_room_room_templates = (units[i].name).replace("units_","");
						array_estimate_room_room_templates = estimate_room_room_templates.split("_");
						estimate_room_items_units_id = array_estimate_room_room_templates[0];
						work_category_estimates_id = array_estimate_room_room_templates[1];
										estimate_details.push({unit_value:units[i].value,estimate_room_items_units_id:estimate_room_items_units_id,work_category_estimates_id:work_category_estimates_id,price_per_unit:price_per_unit[i].value,scope:scope[i].value});
					}
				}
                
                for(i=0;i<subcontractor_id.length;i++)
                {
                	if(subcontractor_id[i].checked)
					{
                		subcontractor_details_array.push({subcontractorId:subcontractor_id[i].value});
                    }
                }
				
				$.ajax({
					url: baseNameElem.value + "/webservice/batch_add_estimates_units.php",
					type: "POST",
					data: { estimate_id:<?php print $estimatesId; ?>,estimate_room_items_id:$("#estimate_room_items_id").val(),room_id:$("#estimates_room_id").val(),data: JSON.stringify(estimate_details),itemTemplateId:$("#itemTemplateId").val(),room_id:$("#room_id").val(),itemId:$("#itemId").val(),item_name:$("#item_name").val(),tmpRoomIndex:$("#tmpRoomIndex").val(),roomItemIndex:$("#roomItemIndex").val(),work_category_id:$("#work_category_id").val(),propertyId:<?php print $propertyId; ?>,subcontractor_details:JSON.stringify(subcontractor_details_array)} 
				}).done(function( response ) {
					if(response.success) {
                        tmpRoomIndex = document.getElementById('tmpRoomIndex').value;
                        roomItemIndex = document.getElementById('roomItemIndex').value;
                        if(response.comment)
                        {
                        	$("#roomItemCommentContainer_"+tmpRoomIndex+"_"+roomItemIndex).html(response.comment);
                        }	
                        else
                        {
                        	$("#roomItemCommentContainer_"+tmpRoomIndex+"_"+roomItemIndex).html('');
                        	//$("#roomItemCommentContainer_"+tmpRoomIndex+"_"+roomItemIndex).html('&nbsp;');
                        }
                        
						statusMsg.innerHTML = getAlert('success', response.message);
						$("#estimates").modal('hide');
					} else {
						if(!response.message || response.message == '' || !response) {
							statusMsg.innerHTML = getAlert('error', 'No estimates have been added');
							$("#estimates").modal('hide');
						} else {
							statusMsg.innerHTML = getAlert('error', response.message);
							$("#estimates").modal('hide');
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
	};
	
	function getLineItemEstimates(estimatesId,room_template_items_id,room_id,estimate_room_items_id,work_category_id,item_name,tmpRoomIndex,roomItemIndex)
	{
		$("#estimates_header").html("Estimates - "+item_name);
		$("#currentEstimates").html("Loading...");
		$("#estimates_room_id").val(room_id);
		$("#estimate_room_items_id").val(estimate_room_items_id);
        $("#tmpRoomIndex").val(tmpRoomIndex);
        $("#roomItemIndex").val(roomItemIndex);
        
        $("#itemTemplateId").val(room_template_items_id);
        $("#room_id").val(room_id);
        $("#itemId").val(estimate_room_items_id);
        $("#item_name").val(item_name);
         $("#workCategoryId").val(work_category_id);
        $("#tmpRoomIndex").val(tmpRoomIndex);
        $("#roomItemIndex").val(roomItemIndex);
       
		
		$.ajax({
			url: baseNameElem.value + "/webservice/getLineItemsEstimatesUnit.php",
			type: "POST",
			data: { 
					 roomTemplateItemsId: room_template_items_id,estimate_id: estimatesId,work_category_id:work_category_id, room_id: room_id,propertyId:<?php print $propertyId; ?>
				} 
		}).done(function( response ) {
			if(response.success) {
				$("#currentEstimates").html(response.message);
			}
			else
			{
				$("#currentEstimates").html("No estimates created for this line item. Please create estimates in <a href='work_categories.html' style='text-decoration:underline;color:#000;'>Work Categories</a> before continuing");
			}
		});
		
		$("#estimates").modal('show');
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
	
	function sendEstimate() {
		$('#submitConfirmationModal').modal('show');
	}
	
	function archiveEstimate()
	{
		statusMsg = document.getElementById('status-message');	
		archive = window.confirm("Are you sure?");
		if(archive)
		{
			$.ajax({
			url: baseNameElem.value + "/webservice/archiveEstimate.php",
			type: "POST",
			data: { 
					 estimatesId: <?php print $estimatesId; ?>
				} 
			}).done(function( response ) {
				if(response.success) {
					//$("#currentEstimates").html(response.message);
					window.location.href = baseNameElem.value + "/edit_property_estimate.html?propertyId="+<?php print $propertyId; ?>
				}
				else
				{
					statusMsg.innerHTML = getAlert('error', "Sorry, there has been a problem processing your request.");
				}
			});	
		}
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
				 , estimatesId: $('#_ESTIMATESID').val()
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
							window.location.href = $('#_BASENAME').val() + "/edit_property_estimate.html?propertyId=" + $('#_PROPERTYID').val();
						}
					} else { //Not all items are completed.
						$('#submitReportStatus').html(" ");
						$('#submitReportStatus').hide();
						$('.btnSubmitReport').prop("disabled", false);
						
						window.location.href = $('#_BASENAME').val() + "/edit_property_estimate.html?propertyId=" + $('#_PROPERTYID').val();
					}
				} else {
					$("#addReportStatus").html(getAlert('success', "Report successfully saved."));
					// window.location.href = $('#_BASENAME').val() + "/edit_property_estimate.html?propertyId=" + $('#_PROPERTYID').val();
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
        roomHtml += '<div class="row-fluid"><div class="room-name"><a href="#collapse_' + m_roomIndex + '" data-parent="#rooms-wrapper" data-toggle="collapse" class="accordion-toggle">' + name + '</a></div><div class="room-name-action"><button class="btn btn-danger btn-small" onclick="return removeRoom(\'room_' + m_roomIndex + '\', \'' + m_roomIndex + '\','+roomId+')"><i class="icon-trash icon-white"></i></button></div><div class="clearfix"></div></div>';
        roomHtml += '</div>';
        roomHtml += '<div class="accordion-body collapse" id="collapse_' + m_roomIndex + '">';
        roomHtml += '<div class="accordion-inner report-room-items">';
        
		
		var roomItemsHtml = '';
		
		for(var i=0; i < items.length; i++) {
			roomItemsHtml += '<input type="hidden" name="roomStatusId_' + m_roomIndex + '_' + i + '" id="roomStatusId_' + m_roomIndex + '_' + i + '" value="2" />';
			roomItemsHtml += '<input type="hidden" name="roomItemComment_' + m_roomIndex + '_' + i + '" id="roomItemComment_' + m_roomIndex + '_' + i + '" value="" />';
			roomItemsHtml += '<div class="roomitem-group">';
			roomItemsHtml += '<div class="roomitem-desc">' + items[i].name + '</div>';
			roomItemsHtml += '<div class="roomitem-action"><button aria-hidden="true" class="btn" onClick="getLineItemEstimates(<?php print $estimatesId ?>,'+items[i].roomTemplateItemId+','+items[i].roomId+','+items[i].itemId+','+items[i].work_category_id+',\''+items[i].name+'\','+m_roomIndex+','+i+');"><i class="icon-plus"></i> Add Estimate</button></div>';
            roomItemsHtml += '<div id="roomItemCommentContainer_'+m_roomIndex+'_'+i+'" class="roomitem-comment"> </div>'; 
			roomItemsHtml += '<div class="clearfix"></div>';
			roomItemsHtml += '</div>';
			//$room['roomId'].",".$item['itemId'];
			//items[i].room_template_item_id
		}
		
		roomHtml += roomItemsHtml;
		
        roomHtml += '</div>';
        roomHtml += '</div>';
        roomHtml += '</div>';
	
		$("#rooms-wrapper").append(roomHtml);
		
		m_listRooms.push({roomId: m_roomIndex, roomTemplateId: paramRoomTemplateId, roomName: name, roomItems: items, isNew: 1});
		
		m_roomIndex++;
		
		/*$('#saveBtn').prop('disabled', false);*/
	}
	
	function removeRoom(roomContainerId, index, roomId) {
		if(confirm("Are you sure you want to delete this room?")) {
			//REMOVE FROM DATABASE
			if(roomId != null) {
				$.ajax({
					url: $('#_BASENAME').val() + "/webservice/delete_room_estimate.php",
					type: "POST",
					data: {id: roomId} 
				}).done(function( response ) {
					$.ajax({
						url: $('#_BASENAME').val() + "/webservice/get_room_info_estimates.php",
						type: "POST",
						data: {estimatesId: <?php echo $estimatesId; ?>
							} 
						}).done(function( response ) {		
							m_roomIndex += 1;
							m_listRooms = [];
							
							var existingRooms = response;
							
							//[Start] Process existing rooms
							for(var i=0; i<existingRooms.length; i++) {
								var arrRoomItems = [];
								
								arrRoomItems.push({id: existingRooms[i].items[j].itemId, name: existingRooms[i].items[j].itemName});	
																
								m_listRooms.push({roomId: i, roomName: existingRooms[i].roomName, roomItems: arrRoomItems, isNew: 0});
								
								//SET ROOM INDICATOR
								
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
	</script>
	
<?php
} else {
?>
	Oops! something went wrong.
<?php
}
?>