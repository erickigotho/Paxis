<?php
require_once(dirname(dirname(dirname(__FILE__))) . '/modules/Dynamo.class.php');
$complex_properties_obj = new Dynamo("complex_properties");
$complex_properties_array = $complex_properties_obj->getAll("ORDER BY id");
?>
	<form method="POST" class="form-horizontal" id="addPropertyForm">
	<input type="hidden" id="baseName" name="baseName" value="<?php echo __BASENAME__; ?>" />
	<input type="hidden" id="userId" name="userId" value="<?php echo $_SESSION['user_id']; ?>" />
		<div class="pull-left"><h4>Add Property</h4></div>
		<div class="pull-right"><button class="btn btn-warning" type="submit">Submit Changes</button> &nbsp; <a href="properties.html" class="btn btn-default">Cancel</a></div>
		<div class="clearfix"></div>

		<div id="status-message"></div>
		<div class="control-group">
			<label for="community" class="control-label">Community</label>
			<div class="controls">
				<input type="text" name="community" id="community" class="form-control" placeholder="Community" value="" data-validation="required" data-validation-error-msg="Community is a required field" />
			</div>
		</div>
		<div class="control-group">
			<label for="propertyName" class="control-label">Property Name</label>
			<div class="controls">
				<input type="text" name="propertyName" id="propertyName" class="form-control" placeholder="Property Name" value="" data-validation="required" data-validation-error-msg="Please enter a property name."  />
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
			<label for="address" class="control-label">Address</label>
			<div class="controls">
				<textarea type="text" name="address" id="address" class="form-control" placeholder="Address" data-validation="required" data-validation-error-msg="Address is a required field." ></textarea>
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
			<label for="address" class="control-label">Google Map Link</label>
			<div class="controls">
				<input type="text" name="googleMapLink" id="googleMapLink" class="form-control" placeholder="Map URL" value="" />
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
			<label for="estimatesEmailList" class="control-label">
				Create Estimates
			</label>
			<div class="controls">
				<input type="checkbox" name="create_estimates" id="create_estimates" />
			</div>
		</div>
		<div class="control-group">
			<label for="status" class="control-label">Status</label>
			<div class="controls">
				Open
			</div>
		</div>
	<form>
	
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
	
	<script type="text/javascript">
	window.onload = function() {
		var statusMsg = document.getElementById('status-message');
		var formObj = document.forms['addPropertyForm'];
		
		var baseNameElem = formObj.baseName;
		var propertyNameElem = formObj.propertyName;
		var propertyAddressElem = formObj.address;
		var propertyCityElem = formObj.city;
		var propertyZipElem = formObj.zip;
		var propertyMapLinkElem = formObj.googleMapLink;
		var estimatesMultiplier = formObj.estimatesMultiplier;
		var userIdElem = formObj.userId;
		var emailAddressElem = formObj.emailList;
		var estimatesEmailListElem = formObj.estimatesEmailList;
		var propertyTypeElem = formObj.propertyType;
		var jobTypeElem = formObj.jobType;
		var communityElem = formObj.community;
		var statusVal = 1;
		
		//if(!baseNameElem) return;
		
		$.validate({	
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
					$("#template_name").html("<strong>"+$("#propertyType").find('option:selected').text()+"</strong>");
					$("#addCommunityTemplate").modal('show');	
				}
				else
				{
					statusMsg.innerHTML = getAlert('info', 'Please wait while we process your request...');
					
					create_estimates = $("#create_estimates").is(":checked");
					
					if(create_estimates)
						create_estimates = true;
					else
						create_estimates = false;
					
					
					$.ajax({
						url: baseNameElem.value + "/webservice/add_property.php",
						type: "POST",
						data: { name: propertyNameElem.value
								,address: propertyAddressElem.value
								,city: propertyCityElem.value
								,zip: propertyZipElem.value 
								,link: propertyMapLinkElem.value
								,estimatesMultiplier: estimatesMultiplier.value
								,status: statusVal
								,userId: userIdElem.value
								,emails: emailAddressElem.value
								,propertyType: propertyTypeElem.options[propertyTypeElem.selectedIndex].value
								,jobType: jobTypeElem.options[jobTypeElem.selectedIndex].value
								,estimatesEmailList: estimatesEmailListElem.value
								,community: communityElem.value
								,estimates: create_estimates
							} 
					}).done(function( response ) {
						if(response.success) {
							statusMsg.innerHTML = getAlert('success', response.message);
							
							formObj.reset();
							
							if(create_estimates == 'true')
								window.location.href = $('#baseName').val() + "/estimates.html";
							else
								window.location.href = $('#baseName').val() + "/properties.html";
						} else {
							if(!response.message || response.message == '' || !response) {
								statusMsg.innerHTML = getAlert('error', "Sorry, there has been a problem processing your request1");
							} else {
								statusMsg.innerHTML = getAlert('error', response.message);
							}
						}
					});
				}
				
				return false;
			}
		});
	};
	</script>
    
    <script type="text/javascript">
    function useTemplate()
	{	
		var statusMsg = document.getElementById('status-message');
		var formObj = document.forms['addPropertyForm'];
		
		var baseNameElem = '<?php echo __BASENAME__; ?>';
		var propertyNameElem = formObj.propertyName;
		var propertyAddressElem = formObj.address;
		var propertyCityElem = formObj.city;
		var propertyZipElem = formObj.zip;
		var propertyMapLinkElem = formObj.googleMapLink;
		var estimatesMultiplier = formObj.estimatesMultiplier;
		var userIdElem = formObj.userId;
		var emailAddressElem = formObj.emailList;
		var estimatesEmailListElem = formObj.estimatesEmailList;
		var propertyTypeElem = formObj.propertyType;
		var jobTypeElem = formObj.jobType;
		var communityElem = formObj.community;
		var statusVal = 1;
		
		//if(!baseNameElem) return;
		
		$("#addCommunityTemplate").modal('hide');
		
		statusMsg.innerHTML = getAlert('info', 'Please wait while we process your request...');
		create_estimates = $("#create_estimates").is(":checked");
		
		if(create_estimates)
			create_estimates = true;
		else
			create_estimates = false;
			
		$.ajax({
			url: baseNameElem + "/webservice/add_property.php",
			type: "POST",
			data: { name: propertyNameElem.value
					,address: propertyAddressElem.value
					,city: propertyCityElem.value
					,zip: propertyZipElem.value 
					,link: propertyMapLinkElem.value
					,estimatesMultiplier: estimatesMultiplier.value
					,status: statusVal
					,userId: <?php echo $_SESSION['user_id']; ?>
					,emails: emailAddressElem.value
					,propertyType: propertyTypeElem.options[propertyTypeElem.selectedIndex].value
					,jobType: jobTypeElem.options[jobTypeElem.selectedIndex].value
					,estimatesEmailList: estimatesEmailListElem.value
					,community: communityElem.value
					,estimates: create_estimates
				} 
		}).done(function( response ) {
			if(response.success) {
				property_id = response.propertyId;
				
				if(create_estimates)
				{
					$.ajax({
					url: baseNameElem + "/webservice/use_template_property_estimate.php",
					type: "POST",
					data: { userId: <?php echo $_SESSION['user_id']; ?>
							,complexPropertyId: $("#propertyType").find('option:selected').attr("rel")
							,propertyId: property_id
						}
					}).done(function( response ) {
						if(response.success) {
							statusMsg.innerHTML = getAlert('success', response.message);
							formObj.reset();
							window.location.href = baseNameElem + "/edit_property_estimate.html?propertyId="+property_id;
						}
						else {
							if(!response.message || response.message == '' || !response) {
								statusMsg.innerHTML = getAlert('error', "Sorry, there has been a problem processing your request");
							} else {
								statusMsg.innerHTML = getAlert('error', response.message);
							}
						}
					});
				}
				else
				{
					$.ajax({
					url: baseNameElem + "/webservice/use_template_property.php",
					type: "POST",
					data: { userId: <?php echo $_SESSION['user_id']; ?>
							,complexPropertyId: $("#propertyType").find('option:selected').attr("rel")
							,propertyId: property_id
						}
					}).done(function( response ) {
						if(response.success) {
							statusMsg.innerHTML = getAlert('success', response.message);
							formObj.reset();
							window.location.href = baseNameElem + "/edit_property.html?propertyId="+property_id;
						}
						else {
							if(!response.message || response.message == '' || !response) {
								statusMsg.innerHTML = getAlert('error', "Sorry, there has been a problem processing your request");
							} else {
								statusMsg.innerHTML = getAlert('error', response.message);
							}
						}
					});
				}
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
		var statusMsg = document.getElementById('status-message');
		var formObj = document.forms['addPropertyForm'];
		
		var baseNameElem = '<?php echo __BASENAME__; ?>';
		var propertyNameElem = formObj.propertyName;
		var propertyAddressElem = formObj.address;
		var propertyCityElem = formObj.city;
		var propertyZipElem = formObj.zip;
		var propertyMapLinkElem = formObj.googleMapLink;
		var estimatesMultiplier = formObj.estimatesMultiplier;
		var userIdElem = formObj.userId;
		var emailAddressElem = formObj.emailList;
		var estimatesEmailListElem = formObj.estimatesEmailList;
		var propertyTypeElem = formObj.propertyType;
		var jobTypeElem = formObj.jobType;
		var communityElem = formObj.community;
		var statusVal = 1;
		
		//if(!baseNameElem) return;
		
		$("#addCommunityTemplate").modal('hide');
		
		statusMsg.innerHTML = getAlert('info', 'Please wait while we process your request...');
		
		create_estimates = $("#create_estimates").is(":checked");
		
		if(create_estimates)
			create_estimates = true;
		else
			create_estimates = false;
				
		$.ajax({
			url: baseNameElem + "/webservice/add_property.php",
			type: "POST",
			data: { name: propertyNameElem.value
					,address: propertyAddressElem.value
					,city: propertyCityElem.value
					,zip: propertyZipElem.value 
					,link: propertyMapLinkElem.value
					,estimatesMultiplier: estimatesMultiplier.value
					,status: statusVal
					,userId: <?php echo $_SESSION['user_id']; ?>
					,emails: emailAddressElem.value
					,propertyType: propertyTypeElem.options[propertyTypeElem.selectedIndex].value
					,jobType: jobTypeElem.options[jobTypeElem.selectedIndex].value
					,estimatesEmailList: estimatesEmailListElem.value
					,community: communityElem.value
					,estimates: create_estimates
				} 
		}).done(function( response ) {
			if(response.success) {
				statusMsg.innerHTML = getAlert('success', response.message);
				formObj.reset();
				if(create_estimates == true)
					window.location.href = baseNameElem + "/estimates.html";
				else
					window.location.href = baseNameElem + "/properties.html";
			} else {
				if(!response.message || response.message == '' || !response) {
					statusMsg.innerHTML = getAlert('error', "Sorry, there has been a problem processing your request");
				} else {
					statusMsg.innerHTML = getAlert('error', response.message);
				}
			}
		});
	}
	</script>