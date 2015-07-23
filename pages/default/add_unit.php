<?php
require_once(dirname(dirname(dirname(__FILE__))) . '/modules/Dynamo.class.php');

?>
		<form method="POST" class="form-horizontal" id="unitForm">
		<input type="hidden" id="baseName" name="baseName" value="<?php echo __BASENAME__; ?>" />
		<div class="pull-left"><h4>Add Unit</h4></div>
		<div class="pull-right"><button class="btn btn-warning" type="submit">Submit Changes</button> &nbsp; <a href="units.html" class="btn btn-default">Cancel</a></div>
		<div class="clearfix"></div>
		
		<div id="status-message"></div>
		
		<div class="control-group">
			<label for="email" class="control-label">Estimate Unit</label>
			<div class="controls">
				<input type="text" name="estimate_unit" id="estimate_unit" class="form-control" placeholder="Estimate Unit" value="" data-validation="required" data-validation-error-msg="Please enter an estimate unit."  />
			</div>
		</div>
		</form>
		
		<script type="text/javascript">
		
		window.onload = function() {
			var statusMsg = document.getElementById('status-message');
			var formObj = document.forms['unitForm'];
			
			var baseNameElem = formObj.baseName;
			var estimateUnit = formObj.estimate_unit;
			var isContinue = false;
			
			if(!baseNameElem) return;
			
			$.validate({	
				modules: 'security',
				onValidate:function() {
					if(estimateUnit.value.replace(/^\s+|\s+$/gm,'').length == 0)
					{
						estimateUnit.focus();
						
						isContinue = false;
					} else {
						isContinue = true;
					}
				},
				onError: function() {
					statusMsg.innerHTML = getAlert('error', 'Oops! something is wrong. Please check the values below.');
				},
				onSuccess : function() {
					statusMsg.innerHTML = "";
					var isActiveVal;
					
					if(isContinue) {
						statusMsg.innerHTML = getAlert('info', 'Please wait while we process your request...');
						
						$.ajax({
							url: baseNameElem.value + "/webservice/add_unit.php",
							type: "POST",
							data: { estimate_unit: estimateUnit.value
								} 
						}).done(function( response ) {
							if(response.success) {
								statusMsg.innerHTML = getAlert('success', response.message);
								estimateUnit.value = '';
							} else {
								if(!response.message || response.message == '' || !response) {
									statusMsg.innerHTML = getAlert('error', "Sorry, there has been a problem processing your request.");
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