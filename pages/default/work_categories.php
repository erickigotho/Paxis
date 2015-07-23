<?php
require_once(dirname(dirname(dirname(__FILE__))) . '/modules/Dynamo.class.php');

$workCategoryObj = new Dynamo("work_categories");
$workCategoryArray = array();

if($workCategoryObj) {
	$workCategoryArray = $workCategoryObj->getAll("WHERE parent_id = 0");
}
?>
<div class="pull-left"><h4>Work Categories</h4></div>
<div class="pull-right"><a href="add_work_category.html" class="btn btn-warning"><i class="icon-plus icon-white"></i> Add Work Category</a></div>
<div class="clearfix"></div>

<div id="status-message"></div>
<?php
$unitsObj = new Dynamo("units");
$units_array = $unitsObj->getAll();
?>
<div id="estimates" class="modal hide fade" tabindex="-1" role="dialog" aria-hidden="true">
	<div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
        <h3 id="estimates_title">Estimates</h3>
    </div>
	<form id="addEstimatesForm" name="addEstimatesForm" method="post">
        <input type="hidden" id="work_category_id" name="work_category_id" value="" />
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

<div class="container">
<?php 
if(count($workCategoryArray) > 0) {
	foreach($workCategoryArray as $workCategory):
		$id = $workCategory['id'];
		$name = $workCategory['name'];
		?>
		<div class="row property-list-row" id="templateRow_<?php echo $id; ?>">
			<div class="pull-left">
				<h2><?php echo stripslashes($name); ?></h2>
			</div>
			<div class="pull-right"><a class="btn btn-warning" href="#estimates" role="button" class="btn" data-toggle="modal" onclick="getLineItemEstimates(<?php print $id; ?>,'<?php print $name; ?>');"><i class="icon-pencil icon-white"></i> Estimate</a> &nbsp; <a href="edit_work_category.html?id=<?php echo $id; ?>" class="btn btn-warning"><i class="icon-pencil icon-white"></i> View/Edit</a> &nbsp; <a href="javascript: void(0);" class="btn btn-danger" onClick="deleteWorkCategory('<?php echo $id; ?>', '<?php echo $name; ?>')"><i class="icon-trash icon-white"></i> Delete</a></div>
			<div class="clearfix"></div>
		</div>
		<?php
		$subWorkCategoryArray = $workCategoryObj->getAll("WHERE parent_id = ".$id);
		if(count($subWorkCategoryArray) > 0)
		{
			foreach($subWorkCategoryArray as $subWorkCategory):
			$id = $subWorkCategory['id'];
			$name = $subWorkCategory['name'];
		?>
        <div class="row property-list-row" id="templateRow_<?php echo $id; ?>">
			<div class="pull-left" style="padding-left:50px;">
				<h2><?php echo stripslashes($name); ?></h2>
			</div>
			<div class="pull-right"><a class="btn btn-warning" href="#estimates" role="button" class="btn" data-toggle="modal" onclick="getLineItemEstimates(<?php print $id; ?>,'<?php print $name; ?>');"><i class="icon-pencil icon-white"></i> Estimate</a> &nbsp; <a href="edit_work_category.html?id=<?php echo $id; ?>" class="btn btn-warning"><i class="icon-pencil icon-white"></i> View/Edit</a> &nbsp; <a href="javascript: void(0);" class="btn btn-danger" onClick="deleteWorkCategory('<?php echo $id; ?>', '<?php echo $name; ?>')"><i class="icon-trash icon-white"></i> Delete</a></div>
			<div class="clearfix"></div>
		</div>
        <?php
			endforeach;
			$subWorkCategoryArray = array();
		}
	endforeach;
} else {
	echo "No work categories listed.";
}
?>

<div class="hidden" id="baseName"><?php echo __BASENAME__; ?></div>

<script type="text/javascript">
var baseNameElem = document.getElementById("baseName");
var statusMsg = document.getElementById('status-message');
var formObjProp = document.forms['addEstimatesForm'];
var baseNameElem = '<?php echo __BASENAME__; ?>';

function deleteWorkCategory(workCategoryId, categoryName) {
	if(confirm("Are you sure you want to delete \"" + categoryName + "\" ?")) {
		$.ajax({
			url: baseNameElem.innerHTML + "/webservice/delete_work_category.php",
			type: "POST",
			data: { id: workCategoryId
				} 
		}).done(function( response ) {
			if(response.success) {
				statusMsg.innerHTML = getAlert('success', response.message);
				$("#templateRow_" + workCategoryId).remove();
			} else {
				if(!response.message || response.message == '' || !response) {
					statusMsg.innerHTML = getAlert('error', "Sorry, there has been a problem processing your request");
				} else {
					statusMsg.innerHTML = getAlert('error', response.message);
				}
			}
		});
	}
}

function getLineItemEstimates(work_category_id,item_name)
{
	$("#currentEstimates").html("");
	$(".other_estimates").html("");
	
	$("#item_name_0").val('');
	$("#price_per_unit_0").val('');
	$("#unit_of_measure_0").val('1');
	
	$("#estimates_title").html("Estimates - "+item_name);
	
	formObjProp.work_category_id.value = work_category_id;
	formObjProp.addEstimatesForm_id.value = 1;
	
	$.ajax({
		url: baseNameElem + "/webservice/getLineItemEstimates.php",
		type: "POST",
		data: { 
				 workCategoryId: work_category_id
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

function createEstimates()
{
	idReached = parseInt(formObjProp.addEstimatesForm_id.value);
	estimatesHTML = getEstimatesBody(idReached);
	
	$(".other_estimates").append(estimatesHTML);
	
	idReached = idReached + 1;
	formObjProp.addEstimatesForm_id.value = idReached;
	return false;
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
	<?php
		for($i=0;$i<count($units_array);$i++)
		{
			print "estimatesHTML += '<option value=\"".$units_array[$i]['id']."\">".$units_array[$i]['estimate_unit']."</option>';
";	
		}
	?>
	
	estimatesHTML += '</select>';
	estimatesHTML += '</div>';
	
	estimatesHTML += '<div class="clearfix"></div>';
	estimatesHTML += '</div>';
	
	return estimatesHTML;
}

window.onload = function() {
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
				url: baseNameElem + "/webservice/batch_add_estimates.php",
				type: "POST",
				data: { work_category_id: formObjProp.work_category_id.value
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