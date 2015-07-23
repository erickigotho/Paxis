<?php
require_once(dirname(dirname(dirname(__FILE__))) . '/modules/Dynamo.class.php');

$sub_contractors = new Dynamo("sub_contractors");

$work_categories = new Dynamo("work_categories");

if($_SESSION['user_type'] == 5)
	$readonly = true;	
else
	$readonly = false;
	
$work_categories_array = $work_categories->getAll("WHERE parent_id = 0");
$sub_contractor_work_category = new Dynamo("sub_contractor_work_category");

if(trim($_REQUEST['id']) != '')
{
	$array_sub_contractor = $sub_contractors->getOne();
	if(count($array_sub_contractor) > 0)
	{
		$array_sub_work = $sub_contractor_work_category->getAllWithId_default("WHERE sub_contractor_id = ".$_REQUEST['id'],"work_category_id");
?>	
		<form method="POST" class="form-horizontal" id="addSubContractorForm" onSubmit="return false;">
		<input type="hidden" id="baseName" name="baseName" value="<?php echo __BASENAME__; ?>" />
		<input type="hidden" id="id" name="id" value="<?php print $_REQUEST['id']; ?>" />
		<div class="pull-left"><h4>View SubContractor</h4></div>
		<div class="pull-right"><button class="btn btn-warning" type="submit">Submit Changes</button> &nbsp;<?php if($_SESSION['user_type'] != 5){?> <a href="sub_contractors.html" class="btn btn-default">Cancel</a><?php } ?></div>
		<div class="clearfix"></div>
		
		<div id="status-message"></div>
		
		<div class="control-group">
			<label for="email" class="control-label">Email</label>
			<div class="controls">
				<input type="text" name="email" id="email" class="form-control" placeholder="Email address" value="<?php print $array_sub_contractor['email']; ?>" data-validation="required email" data-validation-error-msg="Please enter a valid email address."<?php if($readonly) print ' disabled="disabled"'; ?>  />
			</div>
		</div>
		<div class="control-group">
            <label class="control-label">Password</label>
            <div class="controls">
                <input type="password" name="pass_confirmation" id="pass_confirmation" data-validation="strength" data-validation-strength="2" data-validation-optional="true" autocomplete="off" placeholder="Password"/>
				 <input type="password" name="pass" id="pass" data-validation="confirmation" data-validation-optional="true" data-validation-error-msg="Password does not match the confirm password." autocomplete="off" placeholder="Confirm Password"/> 
            </div>
        </div>
		<div class="control-group">
			<label for="firstName" class="control-label">Name</label>
			<div class="controls form-inline">
				<input type="text" name="firstName" id="firstName" class="form-control" placeholder="First Name" value="<?php print $array_sub_contractor['first_name']; ?>" data-validation="required" data-validation-error-msg="First name is required."<?php if($readonly) print ' disabled="disabled"'; ?> />
				<input type="text" name="lastName" id="lastName" class="form-control" placeholder="Last Name" value="<?php print $array_sub_contractor['last_name']; ?>" data-validation="required" data-validation-error-msg="Last name is required."<?php if($readonly) print ' disabled="disabled"'; ?> />
			</div>
		</div>
		<div class="control-group">
			<label for="phone" class="control-label">Phone Number</label>
			<div class="controls">
				<input type="text" name="phone" id="phone" class="form-control" placeholder="Phone Number" value="<?php print $array_sub_contractor['phone_number']; ?>" data-validation="required" data-validation-error-msg="Please enter your phone number."<?php if($readonly) print ' disabled="disabled"'; ?>  />
			</div>
		</div>
		<div class="control-group">
			<label for="phone" class="control-label">Company</label>
			<div class="controls">
				<input type="text" name="company" id="company" class="form-control" placeholder="Company" value="<?php print $array_sub_contractor['company']; ?>" />
			</div>
		</div>
		<div class="control-group">
			<label for="email" class="control-label">Address</label>
			<div class="controls">
				<textarea name="address" id="address" class="form-control" placeholder="Address"<?php if($readonly) print ' disabled="disabled"'; ?>><?php print $array_sub_contractor['address']; ?></textarea>
			</div>
		</div>
		<div class="control-group">
			<label for="work_category_id" class="control-label">Work Category</label>
			<div class="controls">
				<?php
				if(count($work_categories_array) > 0) 
				{
					foreach($work_categories_array as $work_category) 
					{
					?>
						<input type="checkbox" name="work_category_id" id="work_category_id_<?php echo $work_category['id']; ?>" value="<?php echo $work_category['id']; ?>"<?php if(isset($array_sub_work[$work_category['id']])) print " checked=\"checked\"";  ?><?php if($readonly) print ' disabled="disabled"'; ?> /> <?php echo $work_category['name']; ?><br />
					<?php
						$sub_work_categories_array = $work_categories->getAll("WHERE parent_id = ".$work_category['id']);
						if(count($sub_work_categories_array) > 0)
						{
							?>
                            <script type="text/javascript">
								$("#work_category_id_<?php echo $work_category['id']; ?>").click(function(){
									if($(this).is(':checked')) 
									{
										$(".work_category_id_<?php echo $work_category['id']; ?>").prop('checked', true);
									}
									else
									{
										$(".work_category_id_<?php echo $work_category['id']; ?>").prop('checked', false);
									}
								});
							</script>
                            <?php
							foreach($sub_work_categories_array as $sub_work_category) 
							{
								?>
									<input type="checkbox" name="work_category_id" style="margin-left:30px;" class="work_category_id_<?php echo $work_category['id']; ?>" value="<?php echo $sub_work_category['id']; ?>"<?php if(isset($array_sub_work[$sub_work_category['id']])) print " checked=\"checked\"";  ?><?php if($readonly) print ' disabled="disabled"'; ?> /> <?php echo $sub_work_category['name']; ?><br />
								<?php
							}
						}
					}
				}
				?>
			</div>
		</div>
		<div class="control-group">
			<label for="statusActive" class="control-label">Status</label>
			<div class="controls">
				<label class="radio inline"><input type="radio" name="isActive" class="isActive" id="active" value="1"<?php if($array_sub_contractor['is_active'] == 1) print " checked=\"checked\""; ?><?php if($readonly) print ' disabled="disabled"'; ?> /> Active</label>
				<label class="radio inline"><input type="radio" name="isActive" class="isActive" id="inactive" value="0"<?php if($array_sub_contractor['is_active'] == 0) print " checked=\"checked\""; ?><?php if($readonly) print ' disabled="disabled"'; ?> /> Inactive</label>
			</div>
		</div>
		</form>
		<div id="test_div">
		
		</div>
		<script type="text/javascript">
		
		window.onload = function() {
			var statusMsg = document.getElementById('status-message');
			var formObj = document.forms['addSubContractorForm'];
			var idElem = formObj.id;
			var baseNameElem = formObj.baseName;
			var passElem = formObj.pass;
			var passConfirmElem = formObj.pass_confirmation;
			var emailElem = formObj.email;
			var firstNameElem = formObj.firstName;
			var lastNameElem = formObj.lastName;
			var phoneElem = formObj.phone;
			var companyElem = formObj.company;
			var addrElem = formObj.address;
			var workCategoryIdElem = formObj.work_category_id;
			var isContinue = false;
			
			if(!baseNameElem) return;
			
			$.validate({
				form: "#addSubContractorForm",
				modules: 'security',
				onValidate:function() {
					/*if((passElem.value.replace(/^\s+|\s+$/gm,'').length > 0 && passConfirmElem.value.replace(/^\s+|\s+$/gm,'').length == 0)
					  || (passConfirmElem.value.replace(/^\s+|\s+$/gm,'').length > 0 && passElem.value.replace(/^\s+|\s+$/gm,'').length == 0)
					  ) {
						passElem.value = '...';
						passElem.focus();
						
						passElem.blur();
						passElem.value = '';
						passElem.focus();
						
						isContinue = false;
					} else {
						isContinue = true;
					}*/
				},
				onError: function() {
					statusMsg.innerHTML = getAlert('error', 'Oops! something is wrong. Please check the values below.');
				},
				onSuccess : function() {
					statusMsg.innerHTML = "";
					var isActiveVal;
					
					if(passElem.value != passConfirmElem.value)
					{
						statusMsg.innerHTML = getAlert('error', 'The passwords do not match');		
						isContinue = false;
					}
					else
						isContinue = true;
					
					if(isContinue) {
						statusMsg.innerHTML = getAlert('info', 'Please wait while we process your request...');
						
						for(var i=0; i<formObj.isActive.length; i++) {
							if(formObj.isActive[i].checked) {
								isActiveVal = formObj.isActive[i].value;
								break;
							}
						}
											
						var work_category_string = '';
						for(var i=0; i<formObj.work_category_id.length; i++) {
							if(formObj.work_category_id[i].checked) {
								work_category_string += formObj.work_category_id[i].value + ",";
							}
						}
						
						$.ajax({
							url: baseNameElem.value + "/webservice/update_sub_contractor.php",
							type: "POST",
							data: { id: idElem.value
									,email: emailElem.value
									,password: passElem.value
									,passwordConfirm: passConfirmElem.value
									,first_name: firstNameElem.value
									,last_name: lastNameElem.value 
									,phone_number: phoneElem.value
									,company: companyElem.value
									,address: addrElem.value
									,work_category_id_string: work_category_string
									,is_active: isActiveVal
								} 
						}).done(function( response ) {
							if(response.success) {
								statusMsg.innerHTML = getAlert('success', response.message);
								passElem.value = '';
								passConfirmElem.value = '';
							} else {
								if(!response.message || response.message == '' || !response) {
									statusMsg.innerHTML = getAlert('error', "Sorry, there has been a problem processing your request");
								} else {
									statusMsg.innerHTML = getAlert('error', response.message);
									if(response.message.indexOf('You cannot reuse that password yet') > -1) {
										passElem.value = '';
										passElem.focus();
										passElem.blur();
										
										passConfirmElem.value = '';
										passConfirmElem.focus();
										passConfirmElem.blur();
										passConfirmElem.focus();
									}
								}
							}
						});
				  	}
					
					return false;
				}
			});
		};
		
		</script>
<?php
	}
	else
	{
		?>
		Oops! something went wrong. No sub contractor information found.
		<?php
	}
}
else
{
	?>
Oops! something went wrong.
	<?php
}
?>