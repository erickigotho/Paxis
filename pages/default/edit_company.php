<?php
require_once(dirname(dirname(dirname(__FILE__))) . '/modules/Company.class.php');

$companyId = isset($_GET['companyId'])?$_GET['companyId']:0;

if($companyId != 0) {
	$companyObj = new Company();
	$companyInfo = $companyObj->getCompanyInfo($companyId, false);
	
	if(count($companyInfo) > 0) {
?>

	<form method="POST" class="form-horizontal" id="addCompanyForm">
	<input type="hidden" id="baseName" name="baseName" value="<?php echo __BASENAME__; ?>" />
	<input type="hidden" id="companyId" name="companyId" value="<?php echo $companyId;?>" />
	<div class="pull-left"><h4>Edit <?php echo $companyInfo['name']; ?></h4></div>
	<div class="pull-right"><button class="btn btn-primary b" type="submit">Submit Changes</button> &nbsp; <a href="companies.html" class="btn btn-default">Cancel</a></div>
	<div class="clearfix"></div>

	<div id="status-message"></div>

	<div class="control-group">
		<label for="email" class="control-label">Company Name</label>
		<div class="controls">
			<input type="text" name="companyName" id="companyName" value="<?php echo $companyInfo['name'];?>" class="form-control" placeholder="Company Name" data-validation="required" data-validation-error-msg="Please provide a company name." autocomplete="off" />
		</div>
	</div>

	</form>
	
	<script type="text/javascript">
	window.onload = function() {
		var statusMsg = document.getElementById('status-message');
		var formObj = document.forms['addCompanyForm'];
		
		var baseNameElem = formObj.baseName;
		var companyId = formObj.companyId;
		var companyName = formObj.companyName;
		
		if(!companyName || !baseNameElem) return;
		
		$.validate({
			onError: function() {
				statusMsg.innerHTML = getAlert('error', 'Oops! something is wrong. Please check the values below.');
			},
			onSuccess : function() {
				statusMsg.innerHTML = getAlert('info', 'Please wait while we process your request...');
				
				$.ajax({
					url: baseNameElem.value + "/webservice/update_company.php",
					type: "POST",
					data: { companyId: companyId.value, companyName: companyName.value} 
				}).done(function( response ) {
					if(response.success) {
						statusMsg.innerHTML = getAlert('success', response.message);
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
<?php	
	} else {
	?>
		Oops! something went wrong. No company information found.
	<?php
	}
} else {
?>
	Oops! something went wrong.
<?php
}
?>