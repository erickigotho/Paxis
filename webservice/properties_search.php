<?php
if(!isset($_SESSION)){
	session_start();
}

require_once(dirname(dirname(__FILE__)) . '/modules/Property.class.php');
require_once(dirname(dirname(__FILE__)) . '/modules/Dynamo.class.php');

$propertyObj = new Property();
$user_properties_obj = new Dynamo("user_properties");

$listProperties = array();

if(trim($_POST['searchProperties']) != '')
{
	$listProperties = $propertyObj->searchProperty($_POST['searchProperties']);	
}
else
{
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