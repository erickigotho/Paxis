<?php
require_once(dirname(dirname(dirname(__FILE__))) . '/modules/User.class.php');
require_once(dirname(dirname(dirname(__FILE__))) . '/modules/Company.class.php');

$userId = isset($_SESSION['user_id'])?$_SESSION['user_id']:0;

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
		<form method="POST" class="form-horizontal" id="userInfoForm">
		<input type="hidden" id="baseName" name="baseName" value="<?php echo __BASENAME__; ?>" />
		<input type="hidden" name="userId" id="userId" value="<?php echo $userInfo['id']; ?>" />
		<input type="hidden" name="email" id="email" value="<?php echo $userInfo['email']; ?>" />
		<input type="hidden" name="firstName" id="firstName" value="<?php echo $userInfo['firstName']; ?>" />
		<input type="hidden" name="lastName" id="lastName" value="<?php echo $userInfo['lastName']; ?>" />
		<input type="hidden" name="company" id="company" value="<?php echo $userInfo['companyId']; ?>" />
		<input type="hidden" name="userType" id="userType" value="<?php echo $userInfo['userType']; ?>" />
		<input type="hidden" name="isActive" id="isActive" value="<?php echo $userInfo['isActive']; ?>" />
		
		
		<div class="pull-left"><h4>My Profile</h4></div>
		<div class="pull-right">&nbsp;</div>
		<div class="clearfix"></div>
		
		<div id="status-message"></div>
		
		<div class="control-group">
			<label for="email" class="control-label">Email</label>
			<div class="controls controls-text">
				<?php echo $userInfo['email']; ?>
			</div>
		</div>
		<div class="control-group">
			<label for="firstName" class="control-label">Name</label>
			<div class="controls form-inline controls-text">
				<?php echo $userInfo['firstName']; ?> <?php echo $userInfo['lastName']; ?>
			</div>
		</div>
		<div class="control-group">
			<label for="company" class="control-label">Company</label>
			<div class="controls controls-text">
				 <?php echo $userInfo['companyName']; ?>
			</div>
		</div>
		<div class="control-group">
			<label for="userType" class="control-label">User Type</label>
			<div class="controls controls-text">
				<?php echo $userInfo['userTypeName']; ?>
			</div>
		</div>
		<div class="control-group">
			<label class="control-label">Phone Number</label>
			<div class="controls controls-text">
				<?php echo $userInfo['phoneNumber']; ?>
			</div>
		</div>
		<div class="control-group">
			<label for="statusActive" class="control-label">Status</label>
			<div class="controls controls-text">
				<?php echo (($userInfo['isActive']==0)?"Inactive":"Active"); ?>
			</div>
		</div>
		<div class="control-group">
			<label class="control-label">Last Login</label>
			<div class="controls controls-text" id="lastLogin">
				<?php echo date('m/d/Y g:ia', strtotime($userInfo['lastLogin'])); ?>
			</div>
		</div>
		
		<div class="control-group">
            <label class="control-label">Reset Password</label>
            <div class="controls">
                <input type="password" name="pass_confirmation" id="pass_confirmation" data-validation="strength" data-validation-strength="2" data-validation-optional="true" autocomplete="off" placeholder="Password"/>
				 <input type="password" name="pass" id="pass" data-validation="confirmation" data-validation-optional="true" data-validation-error-msg="Password does not match the confirm password." autocomplete="off" placeholder="Confirm Password"/> 
            </div>
        </div>
		<div class="control-group">
            <label class="control-label">&nbsp;</label>
            <div class="controls">
                <button class="btn btn-warning" type="submit">Submit Changes</button>
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
			var firstNameElem = formObj.firstName;
			var lastNameElem = formObj.lastName;
			var companyElem = formObj.company;
			var userTypeElem = formObj.userType;
			var isActiveElem = formObj.isActive;
			var isContinue = false;
			
			if(!baseNameElem) return;
			
			$.validate({	
				modules: 'security',
				onValidate:function() {
					if((passElem.value.replace(/^\s+|\s+$/gm,'').length > 0 && passConfirmElem.value.replace(/^\s+|\s+$/gm,'').length == 0)
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
					}
				},
				onError: function() {
					statusMsg.innerHTML = getAlert('error', 'Oops! something is wrong. Please check the values below.');
				},
				onSuccess : function() {
					statusMsg.innerHTML = "";
					
					if(isContinue) {
						statusMsg.innerHTML = getAlert('info', 'Please wait while we process your request...');
						
						$.ajax({
							url: baseNameElem.value + "/webservice/update_user.php",
							type: "POST",
							data: { id: userIdElem.value
									,email: emailElem.value
									,password: passElem.value
									,firstName: firstNameElem.value
									,lastName: lastNameElem.value 
									,companyId: companyElem.value
									,userType: userTypeElem.value
									,isActive: isActiveElem.value
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
						});;
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