<?php
require_once(dirname(dirname(dirname(__FILE__))) . '/modules/Room.class.php');
require_once(dirname(dirname(dirname(__FILE__))) . '/modules/Dynamo.class.php');

$roomTemplateId = isset($_GET['id'])?$_GET['id']:0;

if($roomTemplateId != 0) {
	$roomObj = new Room();
	$roomInfo = $roomObj->getRoomTemplateInfo($roomTemplateId, false);
	
	if(!$roomInfo) {	
		echo "No room template found.";
		return;
	}
}

$work_categories = new Dynamo("work_categories");

$work_category_array = $work_categories->getAllWithId("WHERE parent_id = 0");

$unitsObj = new Dynamo("units");
$units_array = $unitsObj->getAll();
?>
<div id="estimates" class="modal hide fade" tabindex="-1" role="dialog" aria-hidden="true">
	<div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
        <h3 id="estimates_title">Estimates</h3>
    </div>
	<form id="addEstimatesForm" name="addEstimatesForm" method="post" >
    	<input type="hidden" id="room_template_id" name="room_template_id" value="<?php echo $roomTemplateId; ?>" />
        <input type="hidden" id="room_template_items_id" name="room_template_items_id" value="" />
			<div class="modal-body">
            	<div id="currentEstimates">
                
                </div>
                <div class="estimatestemplate_row">
                    <div class="estimatestemplate_left"><h5>Item Name</h5></div>
                    <div class="estimatestemplate_middle"><h5>Price Per unit</h5></div>
                    <div class="estimatestemplate_right"><h5>Unit of Measure</h5></div>
                    <div class="clearfix"></div>	
                </div>
                <div class="estimatestemplate_row">			
                    <div class="estimatestemplate_left">
                        <input type="text" name="item_name_0" id="item_name_0" class="item_name" value="" />
                    </div>
                    <div class="estimatestemplate_middle">
                        $ <input type="text" name="price_per_unit_0" id="price_per_unit_0" class="price_per_unit" value="" />
                    </div>
                    <div class="estimatestemplate_right">
                        <select name="unit_of_measure_0" id="unit_of_measure_0" class="unit_of_measure">
							<?php
							for($i=0;$i<count($units_array);$i++)
							{
								print "<option value='".$units_array[$i]['id']."'>".$units_array[$i]['estimate_unit']."</option>";	
							}
							?>
                        </select>
                    </div>		
                    <div class="clearfix"></div>	
                </div>
				<div class="other_estimates"></div>
                <a class="btn" role="button" href="#" onclick="return createEstimates();"><i class="icon-plus"></i> Add Estimate</a>
            </div>
            <div class="modal-footer-custom">
				<button class="btn btn-primary">Save Estimates</button>
				<button class="btn" data-dismiss="modal" aria-hidden="true">Close</button>
			</div>
            <input type="hidden" name="addEstimatesForm_id" id="addEstimatesForm_id" value="1" />
     </form>
</div>
		<form method="POST" class="form-horizontal" id="addUserForm" onsubmit="return false;">
		<input type="hidden" id="baseName" name="baseName" value="<?php echo __BASENAME__; ?>" />
		<input type="hidden" id="roomTemplateId" name="roomTemplateId" value="<?php echo $roomInfo['id']; ?>" />
		<div class="pull-left"><h4>Edit <?php echo $roomInfo['name']; ?></h4></div>
		<div class="pull-right"><button class="btn btn-primary b" type="submit">Submit Changes</button> &nbsp; <a href="room_templates.html" class="btn btn-default">Cancel</a></div>
		<div class="clearfix"></div>
		
		<div id="status-message"></div>
		
		<div class="control-group room-group room-name-group">
			<label for="roomName" class="control-label">Room Name</label>
			<div class="controls">
				<input type="text" name="roomName" id="roomName" class="form-control span5" placeholder="Room Name" value="<?php echo $roomInfo['name']; ?>" data-validation="required" data-validation-error-msg="Room name is a required field."  />
			</div>
		</div>

		<div class="room-items-wrapper">
			<?php
			$totalItemTextboxes = 6;
			$itemTextboxesCtr = 0;
			$listItems = $roomObj->getRoomTemplateItems($roomTemplateId, false);
			
			foreach($listItems as $item):
			?>	
				<div class="control-group room-group" id="room-group<?php echo $itemTextboxesCtr; ?>">
					<label for="item<?php echo $itemTextboxesCtr; ?>" class="control-label">&nbsp;</label>
					<div class="controls">
						<input type="text" name="item<?php echo $itemTextboxesCtr; ?>" id="item<?php echo $itemTextboxesCtr; ?>" class="form-control span5 room-items" placeholder="Room Item Description" value="<?php echo $item['name']; ?>" />
                        <input type="hidden" name="itemId<?php echo $itemTextboxesCtr; ?>" id="itemId<?php echo $itemTextboxesCtr; ?>" class="room-items-id" value="<?php echo $item['roomTemplateItemId']; ?>" />
						   <?php
								if(count($work_category_array) > 0)
								{
							?>
							<select name="work_category_id<?php echo $itemTextboxesCtr; ?>" id="work_category_id<?php echo $itemTextboxesCtr; ?>" class="work-category-items">
								<option value="">--Select Work Category--</option>
								<?php
									foreach($work_category_array as $work_category)
									{
									?>
										<option value="<?php print $work_category['id']; ?>"<?php if($item['work_category_id'] == $work_category['id']){ ?> selected="selected"<?php } ?>><?php print $work_category['name']; ?></option>
                                        
                                        <?php
										$sub_work_category_array = $work_categories->getAllWithId("WHERE parent_id = ".$work_category['id']);
										if(count($sub_work_category_array) > 0)
										{
											foreach($sub_work_category_array as $sub_work_category)
											{
											?>
												<option value="<?php print $sub_work_category['id']; ?>"<?php if($item['work_category_id'] == $sub_work_category['id']){ ?> selected="selected"<?php } ?>>&nbsp; &nbsp; &nbsp; <?php print $sub_work_category['name']; ?></option>
										<?php
											}
										}
									}
								?>
							</select>
							<?php
								}
							?>
                            
						<a href="javascript: void(0);" class="btn btn-danger" onclick="removeItem('<?php echo $itemTextboxesCtr; ?>');"><i class="icon-trash icon-white"></i></a>
					</div>
				</div>
			<?php
				$itemTextboxesCtr++;
			endforeach;
			?>
		</div>	
		<div class="control-group room-group">
			<label class="control-label">&nbsp;</label>
			<div class="controls">
				<a href="javascript: void(0);" class="btn btn-info" onclick="addItem()"><i class="icon-plus icon-white"></i> Add New Item</a>
			</div>
		</div>
		

		</form>
				
		
		<script type="text/javascript">
		var formObjProp = document.forms['addEstimatesForm'];
		
		function getEstimatesHeader()
		{
			 var estimatesHTML = '';
			
			estimatesHTML += '<div class="estimatestemplate_row">';
			estimatesHTML += '<div class="estimatestemplate_left"><h5>Item Name</h5></div>';
			estimatesHTML += '<div class="estimatestemplate_middle"><h5>Price Per unit</h5></div>';
			estimatesHTML += '<div class="estimatestemplate_right"><h5>Unit of Measure</h5></div>';
			estimatesHTML += '<div class="clearfix"></div>';
			estimatesHTML += '</div>';
			
			return estimatesHTML;
		}
		
		function getEstimatesBody(idReached)
		{
			var estimatesHTML = '';
			
			estimatesHTML += '<div class="estimatestemplate_row">';
			estimatesHTML += '<div class="estimatestemplate_left">';
			estimatesHTML += '<input type="text" name="item_name_'+idReached+'" id="item_name_'+idReached+'" class="item_name" value="" />';
			estimatesHTML += '</div>';
			estimatesHTML += '<div class="estimatestemplate_middle">';
			estimatesHTML += '$ <input type="text" name="price_per_unit_'+idReached+'" id="price_per_unit_'+idReached+'" class="price_per_unit" value="" />';
			estimatesHTML += '</div>';
			
			estimatesHTML += '<div class="estimatestemplate_right">';
			estimatesHTML += '<select name="unit_of_measure_'+idReached+'" id="unit_of_measure_'+idReached+'" class="unit_of_measure">';
			estimatesHTML += '<option value="1">LF</option>';
			estimatesHTML += '<option value="2">SF</option>';
			estimatesHTML += '<option value="3">Ac</option>';
			estimatesHTML += '<option value="4">SU</option>';
			estimatesHTML += '<option value="5">CF</option>';
			estimatesHTML += '<option value="6">SY</option>';
			estimatesHTML += '<option value="7">Ea</option>';
			estimatesHTML += '</select>';
			estimatesHTML += '</div>';
			
			estimatesHTML += '<div class="clearfix"></div>';
			estimatesHTML += '</div>';
			
			return estimatesHTML;
		}
		
		function createEstimates()
		{
			idReached = parseInt(formObjProp.addEstimatesForm_id.value);
			estimatesHTML = getEstimatesBody(idReached);
			
			$(".other_estimates").append(estimatesHTML);
			
			idReached = idReached + 1;
			formObjProp.addEstimatesForm_id.value = idReached;
			return false;
		}
	
		function getLineItemEstimates(room_template_items_id,item_name)
		{
			$("#currentEstimates").html("");
			$(".other_estimates").html("");
			
			$("#item_name_0").val('');
			$("#price_per_unit_0").val('');
			$("#unit_of_measure_0").val('1');
			
			$("#estimates_title").html("Estimates - "+item_name);
			
			var formObj = document.forms['addUserForm'];		
			var baseNameElem = formObj.baseName;
			
			formObjProp.room_template_items_id.value = room_template_items_id;
			formObjProp.addEstimatesForm_id.value = 1;
			
			$.ajax({
				url: baseNameElem.value + "/webservice/getLineItemEstimates.php",
				type: "POST",
				data: { 
						 roomTemplateItemsId: room_template_items_id
					} 
			}).done(function( response ) {
				if(response.success) {
					$("#currentEstimates").html(response.message);
				}
				else
				{
					$("#currentEstimates").html("");
				}
			});
		}
		
		function removeItem(index) {
			$("#room-group" + index).remove();
		}
		
		function addItem() {
			var roomItemIndex = $(".room-items").length + 1;
			
			$(".room-items-wrapper").append(
									'<div class="control-group room-group" id="room-group' + roomItemIndex + '">' + 
									'<label for="item7" class="control-label">&nbsp;</label>' + 
									'<div class="controls">' + 
										'<input type="text" name="item' + roomItemIndex + '" id="item' + roomItemIndex + '" class="form-control span5 room-items" placeholder="Room Item Description" value="" /><?php if(count($work_category_array) > 0) { ?> <select name="work_category_id' + roomItemIndex + '" id="work_category_id' + roomItemIndex + '" class="work-category-items"><option value="">--Select Work Category--</option><?php foreach($work_category_array as $work_category){ ?><option value="<?php print $work_category['id']; ?>"<?php if($item['work_category_id'] == $work_category['id']){ ?>selected="selected"<?php } ?>><?php print $work_category['name']; ?></option> <?php $sub_work_category_array = $work_categories->getAllWithId("WHERE parent_id = ".$work_category['id']); if(count($sub_work_category_array) > 0){foreach($sub_work_category_array as $sub_work_category){ ?><option value="<?php print $sub_work_category['id']; ?>">&nbsp; &nbsp; &nbsp; <?php print $sub_work_category['name']; ?></option><?php } } } ?></select><?php } ?> <a href="javascript: void(0);" class="btn btn-danger" onclick="removeItem(\'' + roomItemIndex + '\');"><i class="icon-trash icon-white"></i></a>' + 
									'</div>' + 
									'</div>'
								);
		}
		window.onload = function() {
			var statusMsg = document.getElementById('status-message');
			var formObj = document.forms['addUserForm'];		
			var baseNameElem = formObj.baseName;
			var userIdElem = formObj.userId;
			var roomTemplateId = formObj.roomTemplateId;
			var roomName = formObj.roomName;
			
			if(!baseNameElem) return;
			
			$.validate({	
				form: '#addUserForm',
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
					var roomTemplatesItemsId = [];
					
					$(".room-items").each(function() {
						if(this.value.replace(/^\s+|\s+$/gm,'').length > 0) {
							roomTemplateItems.push(this.value);
						}
					});
					
					$(".room-items-id").each(function() {
						if(this.value.replace(/^\s+|\s+$/gm,'').length > 0) {
							roomTemplatesItemsId.push(this.value);
						}
						else
							roomTemplatesItemsId.push(0);
					});
							
					$(".work-category-items").each(function() {
						if(this.value.replace(/^\s+|\s+$/gm,'').length > 0) {
							workCategoryItems.push(this.value);
						}
						else
							workCategoryItems.push(0);
					});
					
					$.ajax({
						url: baseNameElem.value + "/webservice/update_room_template.php",
						type: "POST",
						data: { 
								 id: roomTemplateId.value
								,roomTemplatesItemsIdArray: roomTemplatesItemsId 
								,name: roomName.value
								,items: roomTemplateItems.join('|')
								,workcategories: workCategoryItems.join('|')
							} 
					}).done(function( response ) {
						if(response.success) {
							statusMsg.innerHTML = getAlert('success', response.message);
							window.location.href = baseNameElem.value + "/edit_room_template.php?id="+roomTemplateId.value;
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
			
			$.validate({	
				form: '#addEstimatesForm',
				modules: 'security',
				onValidate:function() {
				},
				onError: function() {
					statusMsg.innerHTML = getAlert('error', 'Oops! something is wrong. Please check the values below.');
				},
				onSuccess : function() {
					statusMsg.innerHTML = "";
					statusMsg.innerHTML = getAlert('info', 'Please wait while we process your request...');
					var estimate_details = [];
					
					item_name = $(".item_name");
					price_per_unit = $(".price_per_unit");
					unit_of_measure = $(".unit_of_measure");
					
					for(i=0;i<item_name.length;i++)
					{
						if(item_name[i].value != '')
						{
							estimate_details.push({item_name:item_name[i].value,price_per_unit:price_per_unit[i].value,unit_of_measure:unit_of_measure[i].value});
						}
					}
					
					$.ajax({
						url: baseNameElem.value + "/webservice/batch_add_estimates.php",
						type: "POST",
						data: { room_template_id: formObjProp.room_template_id.value
								,room_template_items_id: formObjProp.room_template_items_id.value
								,data: JSON.stringify(estimate_details)
							} 
					}).done(function( response ) {
						if(response.success) {
							statusMsg.innerHTML = getAlert('success', response.message);
							$("#estimates").modal('hide');
						} else {
							if(!response.message || response.message == '' || !response) {
								statusMsg.innerHTML = getAlert('error', 'No estimates have been added');
								$("#estimates").modal('hide');
							} else {
								statusMsg.innerHTML = getAlert('error', response.message);
								$("#estimates").modal('hide');
							}
						}
					});
				  
					return false;
				}
			});
		};
		</script>