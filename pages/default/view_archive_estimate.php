<?php
require_once(dirname(dirname(dirname(__FILE__))) . '/modules/Property.class.php');
require_once(dirname(dirname(dirname(__FILE__))) . '/modules/Room.class.php');
require_once(dirname(dirname(dirname(__FILE__))) . '/modules/Estimates.class.php');
require_once(dirname(dirname(dirname(__FILE__))) . '/modules/Dynamo.class.php');
require_once(dirname(dirname(dirname(__FILE__))) . '/modules/Tools.class.php');

$estimatesId = isset($_GET['estimatesId'])?$_GET['estimatesId']:0;

if($estimatesId != 0) {
	$estimates_obj = new Dynamo("estimates");
	$estimatesArray = $estimates_obj->getAll("WHERE id = ".$estimatesId);
	$estimatesArray = $estimatesArray[0];
	
	$propertyId = $estimatesArray['property_id'];
	
	$arrayOtherEstimates = array();
	$arrayOtherEstimates = $estimates_obj->getAll("WHERE id > ".$estimatesId." AND property_id = ".$propertyId);
	
	$users_obj = new Dynamo("users");
	$userArray = $users_obj->getAll("WHERE id = ".$estimatesArray['reported_by']);
	$userArray = $userArray[0];
	
	if(trim($estimatesArray["property_id"]) != '')
	{
		$properties_obj = new Dynamo("properties");
		$propertyArray = $properties_obj->getAll("WHERE id = ".$propertyId);
		$propertyArray = $propertyArray[0];
		
		$estimates_multiplier = $propertyArray['estimates_multiplier'];
		
		if(!isset($estimates_multiplier) || $estimates_multiplier == 0 || $estimates_multiplier < 0)
			$estimates_multiplier = 1;
			
		$estimate_room_items_units_obj = new Dynamo("estimate_room_items_units");
		$query = "SELECT eu.estimate_id,eu.units,re.room_template_id,re.room_template_items_id,eu.scope,re.item_name,(eu.price_per_unit*$estimates_multiplier) AS price_per_unit,re.unit_of_measure,eri.name,er.name AS estimate_room_name,(eu.units *  eu.price_per_unit * $estimates_multiplier) AS total_cost
		FROM estimate_room_items_units eu 
		INNER JOIN room_template_estimates re ON eu.room_template_estimates_id = re.id
		INNER JOIN estimate_room_items eri ON eri.id = eu.estimate_room_items_id
		INNER JOIN estimate_rooms er ON eri.room_id = er.id
		WHERE eu.estimate_id = ".$estimatesId . " AND eu.units != 0 ORDER BY re.room_template_id,er.name";
		
		$estimate_room_items_units_array = $estimate_room_items_units_obj->customFetchQuery($query);
		
		$estimatesEmailBody = "<table border='1' bordercolor='#D0D7E5' style='border:1px solid #D0D7E5;color:#fff;' cellpadding='0' cellspacing='0'>";
		$email_sub_body = '';
		$cost = 0;
		$total_cost = 0;
		
		$unitsObj = new Dynamo("units");
		$unitsArray = $unitsObj->getAllWithId();
		
		for($i=0;$i<count($estimate_room_items_units_array);$i++)
		{
			if($unitsArray[$estimate_room_items_units_array[$i]['unit_of_measure']])
				$unit_of_measure = $unitsArray[$estimate_room_items_units_array[$i]['unit_of_measure']]['estimate_unit'];
			
			$email_sub_body .= "<tr>
				<td style='text-align:left;font:normal  Arial;color:#fff'>&nbsp;&nbsp;".$estimate_room_items_units_array[$i]['name'] ." - <em>". $estimate_room_items_units_array[$i]['item_name']."</em>&nbsp;&nbsp;</td>
				<td style='text-align:right;font:normal  Arial;color:#fff'>&nbsp;&nbsp;".$estimate_room_items_units_array[$i]['units']. " ".$unit_of_measure."&nbsp;&nbsp;</td>
				<td style='text-align:right;font:normal  Arial;color:#fff'>&nbsp;&nbsp;@ $".number_format($estimate_room_items_units_array[$i]['price_per_unit'], 2, '.', '')."&nbsp;&nbsp;</td>
				<td style='text-align:right;font:normal  Arial;color:#fff'>&nbsp;&nbsp;$".number_format($estimate_room_items_units_array[$i]['total_cost'], 2, '.', '')."&nbsp;&nbsp;</td>
			</tr>";
			
			if(trim($estimate_room_items_units_array[$i]['scope']) != '')
			{
				$email_sub_body .= "<tr>
				<td style='text-align:left;font:normal  Arial;color:#fff;padding:5px;' colspan='4'><strong> - Scope:</strong> ".str_replace("\n","<br />",$estimate_room_items_units_array[$i]['scope'])."</td>
			</tr>";
			}
			
			$cost += $estimate_room_items_units_array[$i]['total_cost'];
			
			if($estimate_room_items_units_array[$i]['room_template_id'] != $estimate_room_items_units_array[$i+1]['room_template_id'] || $estimate_room_items_units_array[$i]['estimate_room_name'] != $estimate_room_items_units_array[$i+1]['estimate_room_name'] || !$estimate_room_items_units_array[$i+1]['room_template_id'])
			{
				$estimatesEmailBody .= "<tr>
					<td colspan='3' style='text-align:left;font:bold  Arial;color:#fff;'>&nbsp;&nbsp;<strong>".$estimate_room_items_units_array[$i]['estimate_room_name']."<strong>&nbsp;&nbsp;</td>
					<td style='text-align:right;font:normal  Arial;color:#fff;'>&nbsp;&nbsp;$".number_format($cost, 2, '.', '')."&nbsp;&nbsp;</td>
				</tr>";
				$estimatesEmailBody .= $email_sub_body;
				$total_cost += $cost;
				if($i == (count($estimate_room_items_units_array) -1))
				{
					$estimatesEmailBody .= "<tr><td colspan='4'>&nbsp;</td></tr>";
					$estimatesEmailBody .= "<tr style='text-align:left;font:bold  Arial;color:#fff'><td colspan='3'>&nbsp;&nbsp;<strong>Total</strong>&nbsp;&nbsp;</td><td style='text-align:right;font:normal Arial;color:#fff'>&nbsp;&nbsp;$".number_format($total_cost, 2, '.', '')."&nbsp;&nbsp;</td></tr>";
				}
				else
					$estimatesEmailBody .= "<tr><td colspan='4'>&nbsp;</td></tr>";
				
				$email_sub_body = '';
				
				
				$cost = 0;
			}
		}
		
		$estimatesEmailBody .= "</table>";
		
		$searchArray = array('__PROPERTYCOMMUNITY__'
							, '__PROPERTYNAME__'
							, '__PROPERTYTYPE__'
							, '__PROPERTYJOBTYPE__'
							, '__ADDRESS__'
							, '__ESTIMATEDATE__'
							, '__ESTIMATEEDBY__'
							, '__ROOMS__');
							
		$replaceArray = array($propertyArray['community']
							, $propertyArray['name']
							, ($propertyArray['property_type']==1?'Commercial':'Residential')
							, ($propertyArray['job_type']==1?'Restoration':'New')
							, $propertyArray['address']
							, date('m/d/Y g:ia')
							, $_SESSION['user_firstname'] . ' ' . $_SESSION['user_lastname']
							, $estimatesEmailBody);
				
		//$emailBody = file_get_contents(dirname(dirname(dirname(__FILE__))) . '/email_template/estimate.tpl');
		$emailBody = $estimatesEmailBody;
	}
?>
	
	<form method="POST" class="form-horizontal" id="addReportForm" onSubmit="return false">
	<input type="hidden" id="_BASENAME" name="baseName" value="<?php echo __BASENAME__; ?>" />
	<input type="hidden" id="_USERID" name="userId" value="<?php echo $_SESSION['user_id']; ?>" />
	<input type="hidden" id="_PROPERTYID" name="propertyId" value="<?php echo $propertyArray['id']; ?>" />
	<input type="hidden" id="_ESTIMATESID" name="estimatesId" value="<?php echo $estimatesId; ?>" />
	<input type="hidden" id="_PROPERTYCOMMUNITY" name="propertyCommunity" value='<?php echo $propertyArray['community'];?>' />
	<input type="hidden" id="_PROPERTYTYPE" name="propertyType" value='<?php echo $propertyArray['property_type'];?>' />
	<input type="hidden" id="_PROPERTYJOBTYPE" name="propertyJobType" value='<?php echo $propertyArray['job_type'];?>' />
	<input type="hidden" id="_ROOMS" name="rooms" value='<?php echo json_encode($propertyArray['rooms']); ?>' />
	<input type="hidden" id="_PROPERTYNAME" name="propertyName" value='<?php echo $propertyArray['name'];?>' />
	<input type="hidden" id="_PROPERTYADDRESS" name="propertyName" value='<?php echo $propertyArray['address'];?>' />
	<input type="hidden" id="_PROPERTYEMAILS" name="propertyName" value='<?php echo $propertyArray['emails'];?>' />
	
	<div class="pull-left"><h4>Estimate for <?php echo stripslashes($propertyArray['community']); ?>, <?php echo stripslashes($propertyArray['name']); ?></h4></div>
	<div class="pull-right"><a href="edit_archive_property.html?propertyId=<?php echo $propertyArray['id']; ?>" class="btn btn-default b">Cancel</a></div>
	<div class="clearfix"></div>
	
	<div class="report-header-wrapper">
		<div class="property-summary">
			<div class="propertydetail-group">
				<div class="property-label">Community</div>
				<div class="property-value"><?php echo $propertyArray['community'];?></div>
			</div>
			<div class="propertydetail-group">
				<div class="property-label">Property Name</div>
				<div class="property-value"><?php echo $propertyArray['name'];?></div>
			</div>
			<div class="propertydetail-group">
				<div class="property-label">Job Type</div>
				<div class="property-value"><?php echo ($propertyArray['job_type']==1?'Restoration':'New');?></div>
			</div>
			<div class="propertydetail-group">
				<div class="property-label">Property Type</div>
				<div class="property-value"><?php echo ($propertyArray['property_type']==1?'Commercial':'Residential');?></div>
			</div>
			<div class="propertydetail-group">
				<div class="property-label">Address</div>
				<div class="property-value"><?php echo $propertyArray['address'];?>, <?php echo $propertyArray['city'];?> <?php echo $propertyArray['state'];?> - <a href="javascript: void(0)" onclick="window.open('<?php echo $propertyArray['map_link'];?>')">View Map</a></div>
			</div>
			<div class="propertydetail-group">
				<div class="property-label">Estimate Date</div>
				<div class="property-value"><?php echo date('m/d/Y g:ia', strtotime($estimatesArray['date_created ']));?></div>
			</div>
		</div>
		<div class="report-summary">
			<div class="report-summary-details">
				<div class="report-label">This estimate was completed by</div>
				<div class="report-value"><strong><?php echo $userArray['first_name'] . " " . $userArray['last_name'];?></strong></div>
				<br/>
				<div class="report-label">Copies of this report were sent to:</div>
				<div class="report-value">
					<ul>
					<?php 
						$emails = explode(',', $propertyArray['estimates_emails']);
						
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
    <div id="rooms-wrapper" class="accordion">
    	<?php print $emailBody; ?>
    </div>
	</div>
	
	</form>
	
    <div id="submitConfirmationModal" class="modal hide fade" tabindex="-1" role="dialog" aria-hidden="true">
		<div class="modal-header">
			<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
			<h3>Submit Estimate Confirmation</h3>
		</div>
		
		<div class="alert alert-warning hide" id="submitReportStatus"></div>
		
		<form id="submitConfirmForm" method="post" onsubmit="return submitEstimate();">
		<input type="hidden" name="projectStatus" id="projectStatus" value="1" />
		<div class="modal-body">
			Email estimates will be sent to:
			<ul>
			<?php 
				if(trim($propertyArray['estimates_emails']) != '')
				{
				$emails = explode(',', $propertyArray['estimates_emails']);
				
				foreach($emails as $email):
				?>
					<li><?php echo $email; ?></li>
				<?php
				endforeach;
				}
				else
				{
					print "There are no emails in your estimate emails field.";	
				}
			?>
			</ul>
		</div>
		<div class="modal-footer-custom">
			<button class="btn btn-primary btnSubmitReport">Submit Estimate</button>
			<button class="btn" data-dismiss="modal" aria-hidden="true">Cancel</button>
		</div>
		</form>
	</div>
    
	<script type="text/javascript">
	var m_roomIndex = 0;
	var m_listRooms = [];
	var baseNameElem = document.getElementById('_BASENAME');
	
	window.onload = function() {
		/*var roomsElem = document.getElementById("_ROOMS");
		var existingRooms = JSON.parse(roomsElem.value);
		
		//[Start] Process existing rooms
		for(var i=0; i<existingRooms.length; i++) 
		{
			var arrRoomItems = [];
			for(var j=0; j < existingRooms[i].items.length; j++) 
			{
				arrRoomItems.push({id: existingRooms[i].items[j].itemId, name: existingRooms[i].items[j].itemName, statusId: existingRooms[i].items[j].statusId});
			}
			
			m_listRooms.push({roomId: i, roomName: existingRooms[i].roomName, roomItems: arrRoomItems});
		
			var roomStatusElem = document.getElementById("room_status_" + i);
			setRoomsStatusIndicator(roomStatusElem, existingRooms[i].items);
			
			m_roomIndex = i;
		}
		
		if(m_listRooms.length) 
		{
			$('#saveBtn').prop('disabled', false);
		}*/
		
		/*$.validate({
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
						window.location.href = baseNameElem.value + "/edit_property.php?propertyId=<?php print $propertyId; ?>"
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
		});*/
		
		//[End] Process existing rooms
	}
	
	function submitEstimate()
	{
		$.ajax({
			url: baseNameElem.value + "/webservice/send_estimate.php",
			type: "POST",
			data: { estimatesId:<?php print $estimatesId; ?>,propertyId:<?php print $propertyId; ?>} 
		}).done(function( response ) {
			if(response.success) {
				window.location.href = baseNameElem.value + "/edit_property.php?propertyId=<?php print $propertyId; ?>"
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
	
	function sendEstimate() {
		$('#submitConfirmationModal').modal('show');
	}
	
	function acceptEstimate()
	{
		accept = window.confirm("Are you sure?");
		if(accept)
		{
			$.ajax({
			url: baseNameElem.value + "/webservice/acceptEstimate.php",
			type: "POST",
			data: { 
					 estimatesId: <?php print $estimatesId; ?>,propertyId:<?php print $estimatesArray['property_id']; ?>
				} 
			}).done(function( response ) {
				if(response.success) {
					//$("#currentEstimates").html(response.message);
					window.location.href = baseNameElem.value + "/edit_property.php?propertyId=<?php print $estimatesArray['property_id']; ?>"
				}
				else
				{
					statusMsg.innerHTML = getAlert('error', "Sorry, there has been a problem processing your request.");
				}
			});	
		}
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
					 propertyId: <?php print $estimatesArray['property_id']; ?>
				} 
			}).done(function( response ) {
				if(response.success) {
					//$("#currentEstimates").html(response.message);
					window.location.href = baseNameElem.value + "/archives.html"
				}
				else
				{
					statusMsg.innerHTML = getAlert('error', "Sorry, there has been a problem processing your request.");
				}
			});	
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
	</script>
<?php 
} else {
?>
	Oops! something went wrong.
<?php
}
?>