<?php
require_once(dirname(dirname(dirname(__FILE__))) . '/modules/User.class.php');
require_once(dirname(dirname(dirname(__FILE__))) . '/modules/Company.class.php');

$userId = isset($_GET['userId'])?$_GET['userId']:0;

if($userId != 0) {
	$userObj = new User();
	$userInfo = $userObj->getUserInfo($userId, false);
	
	$companyObj = new Company();
	$listCompanies = array();

	if($companyObj) {
		$listCompanies = $companyObj->getCompanyList(false);
	}
	
	if(count($userInfo) > 0) {
?>
		<form method="POST" class="form-horizontal" id="userInfoForm" name="userInfoForm" onsubmit="return false;">
		<input type="hidden" id="baseName" name="baseName" value="<?php echo __BASENAME__; ?>" />
		<input type="hidden" name="userId" id="userId" value="<?php echo $userInfo['id']; ?>" />
		<div class="pull-left"><h4>Editing <?php echo $userInfo['firstName'] . ' ' . $userInfo['lastName']; ?></h4></div>
		<div class="pull-right"><button class="btn btn-warning" type="submit">Submit Changes</button> &nbsp; <a href="users.html" class="btn btn-default">Cancel</a></div>
		<div class="clearfix"></div>
		
		<div id="status-message"></div>
		
		<div class="control-group">
			<label for="email" class="control-label">Email</label>
			<div class="controls">
				<input type="text" name="email" id="email" class="form-control" placeholder="Email address" value="<?php echo $userInfo['email']; ?>" data-validation="required email" data-validation-error-msg="Please enter a valid email address."  />
			</div>
		</div>
		<div class="control-group">
			<label for="firstName" class="control-label">Name</label>
			<div class="controls form-inline">
				<input type="text" name="firstName" id="firstName" class="form-control" placeholder="First Name" value="<?php echo $userInfo['firstName']; ?>" data-validation="required" data-validation-error-msg="First name is required." />
				<input type="text" name="lastName" id="lastName" class="form-control" placeholder="Last Name" value="<?php echo $userInfo['lastName']; ?>" data-validation="required" data-validation-error-msg="Last name is required." />
			</div>
		</div>
		<div class="control-group">
			<label for="company" class="control-label">Company</label>
			<div class="controls">
			
				<select name="company" id="company" data-validation="required" data-validation-error-msg="Select the company for this user.">
				<option value="">-- Select Company --</option>
				<?php
				if(count($listCompanies) > 0) {
					foreach($listCompanies as $company) {
						$companyId = $company['id'];
						$companyName = $company['name'];
					?>
						<option value="<?php echo $companyId; ?>" <?php echo (($userInfo['companyId']==$companyId)?'selected="selected"':''); ?>><?php echo $companyName; ?></option>
					<?php
					}
				}
				?>
				</select>
			</div>
		</div>
		<div class="control-group">
			<label for="userType" class="control-label">User Type</label>
			<div class="controls">
				<select name="userType" id="userType" data-validation="required" data-validation-error-msg="User type is required.">
				<option value="">-- User Type --</option>
				<?php
				$arrUserTypes = $userObj->getUserTypes(false);
				
				foreach($arrUserTypes as $userType) {
					$userTypeId = $userType['id'];
					?>
					<option value="<?php echo $userTypeId; ?>" <?php echo (($userInfo['userType']==$userTypeId)?'selected="selected"':''); ?>><?php echo $userType['name']; ?></option>
					<?php
				}
				?>
				</select>
			</div>
		</div>
		<div class="control-group">
			<label for="phone" class="control-label">Phone Number</label>
			<div class="controls">
				<input type="text" name="phone" id="phone" class="form-control" placeholder="Phone Number" value="<?php echo $userInfo['phoneNumber']; ?>" data-validation="required" data-validation-error-msg="Please enter your phone number."  />
			</div>
		</div>
		<div class="control-group">
			<label for="statusActive" class="control-label">Status</label>
			<div class="controls">
				<label class="radio inline"><input type="radio" name="isActive" class="isActive" id="active" value="1" <?php echo ($userInfo['isActive']==1?'checked':''); ?> /> Active</label>
				<label class="radio inline">
				<input type="radio" name="isActive" class="isActive" id="inactive" value="0" <?php echo ($userInfo['isActive']==0?'checked':''); ?> /> Inactive</label>
			</div>
		</div>
		<div class="control-group">
			<label class="control-label">Last Login</label>
			<div class="controls" id="lastLogin">
				<?php echo date('m/d/Y g:ia', strtotime($userInfo['lastLogin'])); ?>
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
			<label for="chkSendEmailPassword" class="control-label">&nbsp;</label>
			<div class="controls">
				<label class="checkbox inline"><input type="checkbox" id="chkSendEmailPassword" name="chkSendEmailPassword"> Send email with new password</label>
			</div>
		</div>

		</form>
		
		<script type="text/javascript">
		
		window.onload = function() {
			var statusMsg = document.getElementById('status-message');
			var formObj = document.forms['userInfoForm'];
			
			var baseNameElem = formObj.baseName;
			var userIdElem = formObj.userId;
			var emailElem = formObj.email;
			var passElem = formObj.pass;
			var passConfirmElem = formObj.pass_confirmation;
			var chkSendEmailPasswordElem = formObj.chkSendEmailPassword
			var firstNameElem = formObj.firstName;
			var lastNameElem = formObj.lastName;
			var companyElem = formObj.company;
			var userTypeElem = formObj.userType;
			var phoneElem = formObj.phone;
			var isContinue = false;
			
			if(!baseNameElem) return;
			
			$.validate({	
				form: '#userInfoForm',
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
					
					if(chkSendEmailPasswordElem.checked == 1)
						chkSendEmailPasswordElem = 1;
					else
						chkSendEmailPasswordElem = 0;
					
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
						
						$.ajax({
							url: baseNameElem.value + "/webservice/update_user.php",
							type: "POST",
							data: { id: userIdElem.value
									,email: emailElem.value
									,password: passElem.value
									,firstName: firstNameElem.value
									,lastName: lastNameElem.value 
									,chkSendEmailPassword: chkSendEmailPasswordElem
									,companyId: companyElem.options[companyElem.selectedIndex].value
									,userType: userTypeElem.options[userTypeElem.selectedIndex].value
									,isActive: isActiveVal
									,phone: phoneElem.value
								} 
						}).done(function( response ) {
							if(response.success) {
								statusMsg.innerHTML = getAlert('success', response.message);
								passElem.value = '';
								passConfirmElem.value = '';
							} else {
								if(!response.message || response.message == '' || !response) {
									statusMsg.innerHTML = getAlert('error', "Sorry, there has been a problem processing your request.");
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
	} else {
	?>
		Oops! something went wrong. No user information found.
	<?php
	}
} else {
?>
	Oops! something went wrong.
<?php
}
?>