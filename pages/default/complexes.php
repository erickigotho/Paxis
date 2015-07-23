<?php
require_once(dirname(dirname(dirname(__FILE__))) . '/modules/Dynamo.class.php');

if($_SESSION['user_type'] == 1 && trim($_GET['del']) == 'true' && trim($_GET['id']) != '')
{
	$complex_reports_obj = new Dynamo("complex_reports");
	$complex_reports_array = $complex_reports_obj->getAll("WHERE property_id = ".$_GET['id']);
	
	$string_community_report = '';
	for($i=0;$i<count($complex_reports_array);$i++)
	{
		$string_community_report .= $complex_reports_array[$i]['id'].",";
	}
	
	if(trim($string_community_report) != '')
	{
		$string_community_report = substr($string_community_report,0,-1);
		$query = "DELETE FROM complex_report_rooms WHERE report_id IN ($string_community_report)";
		$complex_reports_obj->customExecuteQuery($query);
		
		$query = "DELETE FROM community_report_room_items WHERE report_id IN($string_community_report)";
		$complex_reports_obj->customExecuteQuery($query);
	}
	
	$query = "DELETE FROM complex_properties WHERE id = ".$_GET['id'];
	$complex_reports_obj->customExecuteQuery($query);
	
	$query = "DELETE FROM complex_reports WHERE property_id = ".$_GET['id'];
	$complex_reports_obj->customExecuteQuery($query);
	
	$query = "DELETE FROM complex_subcontractors_assign WHERE property_id = ".$_GET['id'];
	$complex_reports_obj->customExecuteQuery($query);
}

$complex_properties_object = new Dynamo("complex_properties");
$list_complex_properties = array();

if($complex_properties_object) {
	$list_complex_properties = $complex_properties_object->getAll();
}
?>

<div class="pull-left"><h4>Complex List</h4></div>
<div class="pull-right"><a href="add_complexes.html" class="btn btn-warning"><i class="icon-plus icon-white"></i> Add Complex</a></div>
<div class="clearfix"></div>

<div id="status-message"></div>
<table class="common-table">
<?php
if(count($list_complex_properties) > 0) 
{
?>
<tr>
	<th>Complex Name</th>
	<th>Date Created</th>
	<th></th>
    <?php
	if($_SESSION['user_type'] == 1)
	{
	?>
    <th></th>
    <?php
	}
	?>
</tr>
<?php
}
if(count($list_complex_properties) > 0) {
	foreach($list_complex_properties as $community_property) {
	?>
	<tr>
		<td><?php echo $community_property['community']; ?></td>
		<td><?php echo date('m/d/Y g:ia', strtotime($community_property['date_created'])); ?></td>
		<td><a href="edit_complexes.html?id=<?php echo $community_property['id'];?>" class="btn btn-warning"><i class="icon-pencil icon-white"></i> View/Edit</a></td>
         <?php
        if($_SESSION['user_type'] == 1)
		{
		?>
        <td><a href="complexes.html?id=<?php echo $community_property['id'];?>&del=true" class="btn btn-danger" onclick="return confirm('Are you sure?');"><i class="icon-trash icon-white"></i> Delete</a></td>
        <?php
		}
		?>
	</tr>
	<?
	}
} else {
?>
	<tr><td colspan="3">No records found.</td></tr>
<?php
}
?>
</table>