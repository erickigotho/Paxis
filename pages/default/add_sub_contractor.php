<?php
require_once(dirname(dirname(dirname(__FILE__))) . '/modules/Dynamo.class.php');

$work_categories = new Dynamo("work_categories");
$work_categories_array = $work_categories->getAll("WHERE parent_id = 0");
?>	
		<form method="POST" class="form-horizontal" id="addSubContractorForm" onSubmit="return false;">
		<input type="hidden" id="baseName" name="baseName" value="<?php echo __BASENAME__; ?>" />
		<div class="pull-left"><h4>Add SubContractor</h4></div>
		<div class="pull-right"><button class="btn btn-warning" type="submit">Submit Changes</button> &nbsp; <a href="sub_contractors.html" class="btn btn-default">Cancel</a></div>
		<div class="clearfix"></div>
		
		<div id="status-message"></div>
		
		<div class="control-group">
			<label for="email" class="control-label">Email</label>
			<div class="controls">
				<input type="text" name="email" id="email" class="form-control" placeholder="Email address" value="" data-validation="required email" data-validation-error-msg="Please enter a valid email address."  />
			</div>
		</div>
		<div class="control-group">
            <label class="control-label">Password</label>
            <div class="controls">
                <input type="password" name="pass_confirmation" id="pass_confirmation" data-validation="strength" data-validation-strength="2" autocomplete="off" placeholder="Password"/>
				 <input type="password" name="pass" id="pass" data-validation="required confirmation" data-validation-error-msg="Password does not match the confirm password." autocomplete="off" placeholder="Confirm Password"/> 
            </div>
        </div>
		<div class="control-group">
			<label for="firstName" class="control-label">Name</label>
			<div class="controls form-inline">
				<input type="text" name="firstName" id="firstName" class="form-control" placeholder="First Name" value="" data-validation="required" data-validation-error-msg="First name is required." />
				<input type="text" name="lastName" id="lastName" class="form-control" placeholder="Last Name" value="" data-validation="required" data-validation-error-msg="Last name is required." />
			</div>
		</div>
		<div class="control-group">
			<label for="phone" class="control-label">Phone Number</label>
			<div class="controls">
				<input type="text" name="phone" id="phone" class="form-control" placeholder="Phone Number" value="" data-validation="required" data-validation-error-msg="Please enter your phone number."  />
			</div>
		</div>
		<div class="control-group">
			<label for="phone" class="control-label">Company</label>
			<div class="controls">
				<input type="text" name="company" id="company" class="form-control" placeholder="Company" value="" />
			</div>
		</div>
		<div class="control-group">
			<label for="email" class="control-label">Address</label>
			<div class="controls">
				<textarea name="address" id="address" class="form-control" placeholder="Address" value="" data-validation="" data-validation-error-msg="Please enter a valid address."></textarea>
			</div>
		</div>
		<div class="control-group">
			<label for="work_category_id" class="control-label">Work Category</label>
			<div class="controls">
				<?php
				if(count($work_categories_array) > 0) {
					foreach($work_categories_array as $work_category) 
					{
					?>
						<input type="checkbox" name="work_category_id" id="work_category_id_<?php echo $work_category['id']; ?>" value="<?php echo $work_category['id']; ?>" /> <?php echo $work_category['name']; ?><br />
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
				<label class="radio inline"><input type="radio" name="isActive" class="isActive" id="active" value="1" checked /> Active</label>
				<label class="radio inline"><input type="radio" name="isActive" class="isActive" id="inactive" value="0"/> Inactive</label>
			</div>
		</div>
		</form>
		<script type="text/javascript">
		
		window.onload = function() {
			var statusMsg = document.getElementById('status-message');
			var formObj = document.forms['addSubContractorForm'];
			var baseNameElem = formObj.baseName;
			var emailElem = formObj.email;
			var firstNameElem = formObj.firstName;
			var lastNameElem = formObj.lastName;
			var phoneElem = formObj.phone;
			var companyElem = formObj.company;
			var addrElem = formObj.address;
			var workCategoryIdElem = formObj.work_category_id;
			var passElem = formObj.pass;
			var passConfirmElem = formObj.pass_confirmation;
			
			if(!baseNameElem) return;
			
			$.validate({
				form: '#addSubContractorForm',	
				modules: 'security',
				onValidate:function() {
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
						return false;
					}
					
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
						url: baseNameElem.value + "/webservice/add_sub_contractor.php",
						type: "POST",
						data: { email: emailElem.value
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
							formObj.reset();
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
		};
		
		</script>
