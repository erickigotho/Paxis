<?php 
require_once(dirname(dirname(__FILE__)) . '/modules/Property.class.php');
require_once(dirname(dirname(__FILE__)) . '/modules/Dynamo.class.php');

$propertyObj = new Property();

$listProperties = array();

if(trim($_POST['searchProperties']) != '')
{
	$listProperties = $propertyObj->searchPropertyArchives($_POST['searchProperties']);	
}
else
{
	$listProperties = $propertyObj->getArchivedProperties(false);
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
		// var_dump($property);
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
			Last Report: <strong><?php echo $property['lastReportBy'];?></strong> with <strong><?php echo $property['lastReportCompany'];?></strong>
			, Closed on <?php echo date('m/d/Y g:ia', strtotime($property["dateClosed"])); ?><?php if(trim($property['lastReportId']) != ''){ ?>, <a href="view_report_archive.html?reportId=<?php echo $property['lastReportId'];?>" class="btn btn-default btn-small">View final report</a><?php } ?>
			<br/>
            <?php
			if(trim($mapLink) != '')
			{
			?>
			<p><?php echo $address; ?> <?php echo $city; ?>, <?php echo $zip; ?> - <a href="javascript:void(0);" class="text-warning" onclick="window.open('<?php echo $mapLink; ?>')">View in Google Maps</a></p>
            <?php
			}
			?>
		</div>
		<div class="pull-right"><a href="edit_archive_property.html?propertyId=<?php echo $id; ?>" class="btn btn-warning"><i class="icon-info-sign icon-white"></i> View Info</a></div>
		<div class="clearfix"></div>
	</div>
	
<?php 
	endforeach;
} else {
?>
<div class="alert alert-warning alert-dismissable"><strong>There are no active properties listed..</strong>  <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button></div>
<?php
}
?>