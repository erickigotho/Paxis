<?php
require_once(dirname(dirname(dirname(__FILE__))) . '/modules/User.class.php');
require_once(dirname(dirname(dirname(__FILE__))) . '/modules/Company.class.php');

$userId = isset($_GET['userId'])?$_GET['userId']:0;

$userObj = new User();
$userInfo = $userObj->getUserInfo($userId, false);

$companyObj = new Company();
$listCompanies = array();
$listUserTypes = array();

if($companyObj) {
	$listCompanies = $companyObj->getCompanyList(false);
}

if($userObj) {
	$listUserTypes = $userObj->getUserTypes(false);
}

//GET BASE URL ADDRESS + DIRECTORY
$temp = $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];

$arrUrl = explode('/', $temp);
$resultArr = array();

array_pop($arrUrl);

foreach($arrUrl as $data) {
	array_push($resultArr, $data);
}

$baseUrlAddress = 'http://' . implode('/', $resultArr);
//--END
?>	
		<form method="POST" class="form-horizontal" id="addUserForm">
		<input type="hidden" id="baseName" name="baseName" value="<?php echo __BASENAME__; ?>" />
		<input type="hidden" id="baseUrlAddress" name="baseUrlAddress" value="<?php echo $baseUrlAddress; ?>" />
		<div class="pull-left"><h4>Add User</h4></div>
		<div class="pull-right"><button class="btn btn-warning" type="submit">Submit Changes</button> &nbsp; <a href="users.html" class="btn btn-default">Cancel</a></div>
		<div class="clearfix"></div>
		
		<div id="status-message"></div>
		
		<div class="control-group">
			<label for="email" class="control-label">Email</label>
			<div class="controls">
				<input type="text" name="email" id="email" class="form-control" placeholder="Email address" value="" data-validation="required email" data-validation-error-msg="Please enter a valid email address."  />
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
						<option value="<?php echo $companyId; ?>"><?php echo $companyName; ?></option>
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
				if(count($listUserTypes) > 0) {
					foreach($listUserTypes as $userType) {
						$userTypeId = $userType['id'];
						?>
						<option value="<?php echo $userTypeId; ?>"><?php echo $userType['name']; ?></option>
						<?php
					}
				}
				?>
				</select>
			</div>
		</div>
		<div class="control-group">
			<label for="phone" class="control-label">Phone Number</label>
			<div class="controls">
				<input type="text" name="phone" id="phone" class="form-control" placeholder="Phone Number" value="" data-validation="required" data-validation-error-msg="Please enter your phone number."  />
			</div>
		</div>
		<div class="control-group">
			<label for="statusActive" class="control-label">Status</label>
			<div class="controls">
				<label class="radio inline"><input type="radio" name="isActive" class="isActive" id="active" value="1" checked /> Active</label>
				<label class="radio inline"><input type="radio" name="isActive" class="isActive" id="inactive" value="0"/> Inactive</label>
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
			<label for="chkSendEmailPassword" class="control-label">&nbsp;</label>
			<div class="controls">
				<label class="checkbox inline"><input type="checkbox" id="chkSendEmailPassword" name="chkSendEmailPassword"> Send email with new password</label>
			</div>
		</div>

		</form>
		
		<script type="text/javascript">
		
		window.onload = function() {
			var statusMsg = document.getElementById('status-message');
			var formObj = document.forms['addUserForm'];
			
			var baseNameElem = formObj.baseName;
			var baseUrlElem = formObj.baseUrlAddress;
			var userIdElem = formObj.userId;
			var emailElem = formObj.email;
			var passElem = formObj.pass;
			var passConfirmElem = formObj.pass_confirmation;
			var firstNameElem = formObj.firstName;
			var lastNameElem = formObj.lastName;
			var companyElem = formObj.company;
			var userTypeElem = formObj.userType;
			var phoneElem = formObj.phone;
			
			if(!baseNameElem) return;
			
			$.validate({	
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
					
					
					$.ajax({
						url: baseNameElem.value + "/webservice/add_user.php",
						type: "POST",
						data: { email: emailElem.value
								,password: passElem.value
								,firstName: firstNameElem.value
								,lastName: lastNameElem.value 
								,companyId: companyElem.options[companyElem.selectedIndex].value
								,userType: userTypeElem.options[userTypeElem.selectedIndex].value
								,isActive: isActiveVal
								,phone: phoneElem.value
								,baseUrl: baseUrlElem.value
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
