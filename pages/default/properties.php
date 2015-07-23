<?php
require_once(dirname(dirname(dirname(__FILE__))) . '/modules/Property.class.php');
require_once(dirname(dirname(dirname(__FILE__))) . '/modules/Dynamo.class.php');

$propertyObj = new Property();
$user_properties_obj = new Dynamo("user_properties");

$delete_success = false;
if($_SESSION['user_type'] == 1 && trim($_GET['del']) == 'true' && trim($_GET['id']) != '')
{
	$reports_obj = new Dynamo("reports");
	$reportArray = $reports_obj->getAll("WHERE property_id = ".$_GET['id']);
	
	if(count($reportArray) > 0)
	{
		$reportIdString = '';
		for($i=0;$i<count($reportArray);$i++)
		{
			$reportIdString .= $reportArray[$i]['id'].",";
		}
		
		$reportIdString = substr($reportIdString,0,-1);
		
		$query = "DELETE FROM report_room_item_comments WHERE report_id IN({$reportIdString})";
		$reports_obj->customExecuteQuery($query);
		
		$query = "DELETE FROM report_room_items WHERE report_id IN({$reportIdString})";
		$reports_obj->customExecuteQuery($query);
		
		$query = "DELETE FROM report_rooms WHERE report_id IN({$reportIdString})";
		$reports_obj->customExecuteQuery($query);
		
		$query = "DELETE FROM report_images WHERE report_id IN({$reportIdString})";
		$reports_obj->customExecuteQuery($query);
		
		$query = "DELETE FROM report_comments WHERE report_id IN({$reportIdString})";
		$reports_obj->customExecuteQuery($query);
	}
	
	$estimates_obj = new Dynamo("estimates");
	$estimatesArray = $estimates_obj->getAll(" WHERE property_id = ".$_GET['id']);
	
	if(count($estimatesArray) > 0)
	{
		$estimatesIdString = '';
		for($i=0;$i<count($estimatesArray);$i++)
		{
			$estimatesIdString .= $estimatesArray[$i]['id'].",";
		}
		
		$estimatesIdString = substr($estimatesIdString,0,-1);
		
		$query = "DELETE FROM estimate_rooms WHERE estimate_id IN({$estimatesIdString})";
		$estimates_obj->customExecuteQuery($query);
		
		$query = "DELETE FROM estimate_room_items WHERE estimate_id IN({$estimatesIdString})";
		$estimates_obj->customExecuteQuery($query);
		
		$query = "DELETE FROM estimate_room_items_units WHERE estimate_id IN({$estimatesIdString})";
		$estimates_obj->customExecuteQuery($query);
	}
	
	$query = "DELETE FROM reports WHERE property_id = ".$_GET['id'];
	$reports_obj->customExecuteQuery($query);
	
	$query = "DELETE FROM estimates WHERE property_id = ".$_GET['id'];
	$reports_obj->customExecuteQuery($query);
	
	$query = "DELETE FROM subcontractors_assign WHERE property_id = ".$_GET['id'];
	$reports_obj->customExecuteQuery($query);
	
	$query = "DELETE FROM properties WHERE id = ".$_GET['id'];
	$reports_obj->customExecuteQuery($query);
	
	$delete_success = true;
}
//$propertyObj->searchProperty($search="Arnold");
?>
	
	<?php
$listProperties = array();

if($propertyObj) {
	if($_SESSION['user_type'] == 5)
	{
		$listProperties = $propertyObj->getPropertiesSubContractors(false);
	}
	else
		$listProperties = $propertyObj->getProperties(false);
}

$propertyArray = $user_properties_obj->getAll("WHERE user_id = ".$_SESSION["user_id"]);

if(count($propertyArray) > 0)
{
	$propertyIdsArray = array();
	for($i=0;$i<count($propertyArray);$i++)
	{
		$propertyIdsArray[] = $propertyArray[$i]["property_id"];
	}
	
	$listPropertiesContainer = array();
	for($i=0;$i<count($listProperties);$i++)
	{
		if(in_array($listProperties[$i]["id"],$propertyIdsArray))
		{
			$listPropertiesContainer[] = $listProperties[$i];
		}
	}
	
	$listProperties = $listPropertiesContainer;
}

$report_images = new Dynamo("report_images");
$array_property_images = $report_images->getAll("WHERE property_image = 1");
if(count($array_property_images) > 0)
{
	$array_property_images2 = array();
	for($i=0;$i<count($array_property_images);$i++)
		$array_property_images2[$array_property_images[$i]['property_id']] = $array_property_images[$i];
		
	$array_property_images = $array_property_images2;
}
?>

<div class="pull-left"><h4>Active Properties</h4></div>
<div class="pull-right">
	<div class="pull-left"><a href="add_property.html" class="btn btn-warning"><i class="icon-plus icon-white"></i> Add Property</a></div>
	<div class="pull-right padleft10">
		<form id="searchPropertyForm" name="searchPropertyForm" method="POST" onsubmit="return false">
			<input type="text" name="searchProperties" id="searchProperties" class="input-medium search-query" placeholder="Search Properties" />
			<div id="loading_search_image"><img src="images/loading.gif" /></div><div class="clearfix"></div>
			<input type="hidden" id="baseName" name="baseName" value="<?php echo __BASENAME__; ?>" />
		</form>
	</div>
	<div class="clearfix"></div>
</div>
<div class="clearfix"></div>

<div id="status-message"></div>

<div class="container" id="propertyContainer">
<?php 
if(count($listProperties) > 0) {
	foreach($listProperties as $property):
		$id = $property['id'];
		$name = $property['name'];
		$address = $property['address'];
		$city = $property['city'];
		$zip = $property['zip'];
		$mapLink = $property['mapLink'];
		$property_type = $property['property_type'];
		$job_type = $property['job_type'];
		$community = $property['community'];
?>
	<div class="row property-list-row">
		<div class="pull-left">
			<div class="property-photo<?php if(is_array($array_property_images[$id])){?> property-photo-none<?php } ?>"><?php if(is_array($array_property_images[$id])){?><img src='images/report_uploads/<?php print $array_property_images[$id]['image_name']; ?>' class='main-property-image' /><?php } ?></div>
		</div>
		<div class="pull-left">
			<h2><?php echo stripslashes($community); ?>, <?php echo stripslashes($name); ?></h2>
			<p><?php echo stripslashes($address); ?>, <?php echo $city; ?>, <?php echo $zip; ?> <a href="javascript:void(0);" onclick="window.open('<?php echo $mapLink; ?>')">View in Google Maps</p>
		</div>
        <?php
		if($_SESSION['user_type'] == 1)
		{
		?>
		<div class="pull-right" style="padding-left:10px;"><a href="properties.html?id=<?php echo $id; ?>&del=true" class="btn btn-danger" onclick="return confirm('Are you sure?');"><i class="icon-trash icon-white"></i> Delete</a></div>
        <?php
		}
		?>
        <div class="pull-right"><a href="edit_property.html?propertyId=<?php echo $id; ?>" class="btn btn-warning"><i class="icon-pencil icon-white"></i> View/Edit</a></div>
        
		<div class="clearfix"></div>
	</div>
	
<?php 
	endforeach;
} else {
?>
<div class="alert alert-warning alert-dismissable"><strong>No active properties listed.</strong>  <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button></div>
<?php
}
?>
</div>
<script type="text/javascript">
window.onload = function() {
	var baseNameElem = searchPropertyForm.baseName;
	
	$("#searchProperties").keyup(function(){
		$("#loading_search_image").css("visibility","visible");
		
		var searchProperties = $("#searchProperties").val();
		$.post(baseNameElem.value + "/webservice/properties_search.php",{searchProperties:searchProperties},function(result){
			$("#loading_search_image").css("visibility","hidden");
			$("#propertyContainer").html(result);
		});
	});
	<?php
	if($delete_success == true)
	{
	?>
	var statusMsg = document.getElementById('status-message');
	statusMsg.innerHTML = getAlert('info', 'Property successfully deleted');
	<?php
	}
	?>
};
</script>