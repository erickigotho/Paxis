<?php
require_once(dirname(dirname(dirname(__FILE__))) . '/modules/Dynamo.class.php');

$work_categories = new Dynamo("work_categories");
$parent_work_categories = $work_categories->getAll("WHERE parent_id = 0");
?>
		<form method="POST" class="form-horizontal" id="workCategoryForm">
		<input type="hidden" id="baseName" name="baseName" value="<?php echo __BASENAME__; ?>" />
		<div class="pull-left"><h4>Add Work Category</h4></div>
		<div class="pull-right"><button class="btn btn-warning" type="submit">Submit Changes</button> &nbsp; <a href="work_categories.html" class="btn btn-default">Cancel</a></div>
		<div class="clearfix"></div>
		
		<div id="status-message"></div>
		
        <?php
		if(count($parent_work_categories) > 0)
		{
		?>
        <div class="control-group">
			<label for="email" class="control-label">Select Parent</label>
			<div class="controls">
            	<select name="parent_id" id="parent_id">
                	<option value="0">No Parent</option>
                    <?php
					for($i=0;$i<count($parent_work_categories);$i++)
					{
						print "<option value='".$parent_work_categories[$i]['id']."'>".$parent_work_categories[$i]['name']."</option>";
					}
					?>
                </select>
			</div>
		</div>
        <?php
		}
		else
		{
		?>
	        <input type="hidden" name="parent_id" id="parent_id" value="0" />
        <?php
		}
		?>
		<div class="control-group">
			<label for="email" class="control-label">Work Category Name</label>
			<div class="controls">
				<input type="text" name="name" id="name" class="form-control" placeholder="Work Category Name" value="" data-validation="required" data-validation-error-msg="Please enter a work category name."  />
			</div>
		</div>
		</form>
		
		<script type="text/javascript">
		
		window.onload = function() {
			var statusMsg = document.getElementById('status-message');
			var formObj = document.forms['workCategoryForm'];
			
			var baseNameElem = formObj.baseName;
			var nameElem = formObj.name;
			var parentIdElem = formObj.parent_id;
			
			var isContinue = false;
			
			if(!baseNameElem) return;
			
			$.validate({	
				modules: 'security',
				onValidate:function() {
					if(nameElem.value.replace(/^\s+|\s+$/gm,'').length == 0)
					{
						nameElem.focus();
						
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
							url: baseNameElem.value + "/webservice/add_work_category.php",
							type: "POST",
							data: { name: nameElem.value,parent_id: parentIdElem.value
								} 
						}).done(function( response ) {
							if(response.success) {
								statusMsg.innerHTML = getAlert('success', response.message);
								nameElem.value = '';
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