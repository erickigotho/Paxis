	<div class="container">
      
	  <form class="form-signin" id="loginForm" method="POST">
	  <input type="hidden" id="baseName" name="baseName" value="<?php echo __BASENAME__; ?>" />
        <h2 class="form-signin-heading">Please sign in</h2>
        <p>
			Email Address:
			<input type="text" id="email" class="input-block-level" data-validation="required email" data-validation-error-msg="Please enter a valid email address." placeholder="Email address">
		</p>
        <p>
			Password:
			<input type="password" id="password" class="input-block-level" data-validation="required" data-validation-error-msg="Password is required." placeholder="Password">
		</p>
		<div id="status-message"></div>
        <button class="btn btn-large btn-warning" type="submit">Sign in</button>
      </form>

    </div>
	
	<script type="text/javascript">
	window.onload = function() {
		var statusMsg = document.getElementById('status-message');
		var formObj = document.forms['loginForm'];
		
		var baseNameElem = formObj.baseName;
		
		if(!baseNameElem) return;
		
		$.validate({	
			onValidate:function() {
				statusMsg.innerHTML = "Processing...";
			},
			onError: function() {
				statusMsg.innerHTML = "";
			},
			onSuccess : function() {
				var emailElem = document.getElementById('email');
				var passElem = document.getElementById('password');
				
				$.ajax({
					url: baseNameElem.value + "/webservice/login.php",
					type: "POST",
					data: { email: emailElem.value, password: passElem.value }
				}).done(function( data ) {
					var o = jQuery.parseJSON(data);
					
					if(!o.success) {
						statusMsg.innerHTML = '<div class="error-message">' + o.message + '</div>';
					} else {
						window.location = "index.html";
					}
				});;
			  
				return false;
			}
		});
	};
	</script>