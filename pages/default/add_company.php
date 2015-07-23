<?php
require_once(dirname(dirname(dirname(__FILE__))) . '/modules/Company.class.php');
?>

	<form method="POST" class="form-horizontal" id="addCompanyForm">
	<input type="hidden" id="baseName" name="baseName" value="<?php echo __BASENAME__; ?>" />
	<div class="pull-left"><h4>Add Company</h4></div>
	<div class="pull-right"><button class="btn btn-primary b" type="submit">Submit Changes</button> &nbsp; <a href="companies.html" class="btn btn-default">Cancel</a></div>
	<div class="clearfix"></div>

	<div id="status-message"></div>

	<div class="control-group">
		<label for="email" class="control-label">Company Name</label>
		<div class="controls">
			<input type="text" name="companyName" id="companyName" class="form-control" placeholder="Company Name" data-validation="required" data-validation-error-msg="Please provide a company name." autocomplete="off" />
		</div>
	</div>

	</form>
	
	<script type="text/javascript">
	window.onload = function() {
		var statusMsg = document.getElementById('status-message');
		var formObj = document.forms['addCompanyForm'];
		
		var baseNameElem = formObj.baseName;
		var companyName = formObj.companyName;
		
		if(!companyName || !baseNameElem) return;
		
		$.validate({
			onError: function() {
				statusMsg.innerHTML = getAlert('error', 'Oops! something is wrong. Please check the values below.');
			},
			onSuccess : function() {
				statusMsg.innerHTML = "";
				
				$.ajax({
					url: baseNameElem.value + "/webservice/add_company.php",
					type: "POST",
					data: { companyName: companyName.value} 
				}).done(function( response ) {
					if(response.success) {
						statusMsg.innerHTML = getAlert('success', response.message);
						
						companyName.value = '';
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
	};
	</script>