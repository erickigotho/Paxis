<?php
require_once(dirname(dirname(dirname(__FILE__))) . '/modules/Dynamo.class.php');

$work_categories = new Dynamo("work_categories");
$work_category_array = $work_categories->getAllWithId("WHERE parent_id = 0");
?>	
		<form method="POST" class="form-horizontal" id="addUserForm">
		<input type="hidden" id="baseName" name="baseName" value="<?php echo __BASENAME__; ?>" />
		<div class="pull-left"><h4>Add Room</h4></div>
		<div class="pull-right"><button class="btn btn-primary b" type="submit">Submit Changes</button> &nbsp; <a href="room_templates.html" class="btn btn-default">Cancel</a></div>
		<div class="clearfix"></div>
		
		<div id="status-message"></div>
		
		<div class="control-group room-group room-name-group">
			<label for="roomName" class="control-label">Room Name</label>
			<div class="controls">
				<input type="text" name="roomName" id="roomName" class="form-control span5" placeholder="Room Name" value="" data-validation="required" data-validation-error-msg="Room name is a required field."  />
			</div>
		</div>

		<div class="room-items-wrapper">
			<div class="control-group room-group" id="room-group1">
				<label for="item1" class="control-label">&nbsp;</label>
				<div class="controls">
					<input type="text" name="item1" id="item1" class="form-control span5 room-items" placeholder="Room Item Description" value="" />
					 <?php
						if(count($work_category_array) > 0)
						{
					?>
					<select name="work_category_id1" id="work_category_id1" class="work-category-items">
						<option value="">--Select Work Category--</option>
						<?php
							foreach($work_category_array as $work_category)
							{
							?>
								<option value="<?php print $work_category['id']; ?>"<?php if($item['work_category_id'] == $work_category['id']){ ?> selected="selected"<?php } ?>><?php print $work_category['name']; ?></option>
                                <?php
								$sub_work_category_array = $work_categories->getAllWithId("WHERE parent_id = ".$work_category['id']);
								foreach($sub_work_category_array as $sub_work_category)
								{
								?>
									<option value="<?php print $sub_work_category['id']; ?>"<?php if($item['work_category_id'] == $sub_work_category['id']){ ?> selected="selected"<?php } ?>>&nbsp; &nbsp; &nbsp; <?php print $sub_work_category['name']; ?></option>
							<?php
								}
							}
						?>
					</select>
					<?php
						}
					?>
					<a href="javascript: void(0);" class="btn btn-danger" onclick="removeItem('1');"><i class="icon-trash icon-white"></i></a>
				</div>
			</div>
		</div>
		
		<div class="control-group room-group">
			<label class="control-label">&nbsp;</label>
			<div class="controls">
				<a href="javascript: void(0);" class="btn btn-info" onclick="addItem()">Add New Item</a>
			</div>
		</div>
		

		</form>
		
		
		
		<script type="text/javascript">
		function removeItem(index) {
			$("#room-group" + index).remove();
		}
		
		function addItem() {
			var roomItemIndex = $(".room-items").length + 1;
			
			$(".room-items-wrapper").append(
									'<div class="control-group room-group" id="room-group' + roomItemIndex + '"><label for="item' + roomItemIndex + '" class="control-label">&nbsp;</label><div class="controls"><input type="text" name="item' + roomItemIndex + '" id="item' + roomItemIndex + '" class="form-control span5 room-items" placeholder="Room Item Description" value="" /><?php if(count($work_category_array) > 0){ ?><select name="work_category_id' + roomItemIndex + '" id="work_category_id' + roomItemIndex + '" class="work-category-items"><option value="">--Select Work Category--</option><?php if(count($work_category_array) > 0){foreach($work_category_array as $work_category){ ?><option value="<?php print $work_category['id']; ?>"<?php if($item['work_category_id'] == $work_category['id']){ ?> selected="selected"<?php } ?>><?php print $work_category['name']; ?></option><?php $sub_work_category_array = $work_categories->getAllWithId("WHERE parent_id = ".$work_category['id']);if(count($sub_work_category_array) > 0){foreach($sub_work_category_array as $sub_work_category){ ?><option value="<?php print $sub_work_category['id']; ?>"<?php if($item['work_category_id'] == $sub_work_category['id']){ ?> selected="selected"<?php } ?>>&nbsp; &nbsp; &nbsp; <?php print $sub_work_category['name']; ?></option><?php } } } } ?></select><?php } ?> <a href="javascript: void(0);" class="btn btn-danger" onclick="removeItem(\'' + roomItemIndex + '\');"><i class="icon-trash icon-white"></i></a> </div></div>'
								);
		}
	</script>
    
    <script type="text/javascript">
		window.onload = function() {
			var statusMsg = document.getElementById('status-message');
			var formObj = document.forms['addUserForm'];
			
			var baseNameElem = formObj.baseName;
			var userIdElem = formObj.userId;
			var roomName = formObj.roomName;
			
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
					
					statusMsg.innerHTML = getAlert('info', 'Please wait while we process your request...');
					
					//Process room template items.
					var roomTemplateItems = [];
					var workCategoryItems = [];
					
					$(".room-items").each(function() {
						if(this.value.replace(/^\s+|\s+$/gm,'').length > 0) {
							roomTemplateItems.push(this.value);
						}
					});
					
					$(".work-category-items").each(function() {
						if(this.value.replace(/^\s+|\s+$/gm,'').length > 0) {
							workCategoryItems.push(this.value);
						}
						else
							workCategoryItems.push(0);
					});
					
					$.ajax({
						url: baseNameElem.value + "/webservice/add_room_template.php",
						type: "POST",
						data: { 
								 roomName: roomName.value
								,items: roomTemplateItems.join('|')
								,workcategories: workCategoryItems.join('|')
							} 
					}).done(function( response ) {
						if(response.success) {
							statusMsg.innerHTML = getAlert('success', response.message);
							if(response.id)
								window.location.href = baseNameElem.value + "/edit_room_template.php?id="+response.id;
							else
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
