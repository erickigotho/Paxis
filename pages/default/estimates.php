<?php
require_once(dirname(dirname(dirname(__FILE__))) . '/modules/Property.class.php');
require_once(dirname(dirname(dirname(__FILE__))) . '/modules/Dynamo.class.php');

$propertyObj = new Property();
$user_properties_obj = new Dynamo("user_properties");

//$propertyObj->searchProperty($search="Arnold");

$listProperties = array();

if($propertyObj) {
	if($_SESSION['user_type'] == 5)
	{
		$listProperties = $propertyObj->getPropertiesSubContractors(false);
	}
	else
		$listProperties = $propertyObj->getEstimatesProperties(false);
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

<div class="pull-left"><h4>Estimates Properties</h4></div>
<div class="pull-right">
	<div class="pull-left"></div>
	<div class="pull-right padleft10">
		
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
		<div class="pull-right"><a href="edit_property_estimate.html?propertyId=<?php echo $id; ?>" class="btn btn-warning"><i class="icon-pencil icon-white"></i> View/Edit</a></div>
		<div class="clearfix"></div>
	</div>
	
<?php 
	endforeach;
} else {
?>
<div class="alert alert-warning alert-dismissable"><strong>No estimates properties listed.</strong>  <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button></div>
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
};
</script>