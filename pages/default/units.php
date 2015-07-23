<?php
require_once(dirname(dirname(dirname(__FILE__))) . '/modules/Dynamo.class.php');

$units = new Dynamo("units");
$unitsArray = array();

if($units) {
	$unitsArray = $units->getAll();
}
?>
<div class="pull-left"><h4>Units</h4></div>
<div class="pull-right"><a href="add_unit.html" class="btn btn-warning"><i class="icon-plus icon-white"></i> Add Unit</a></div>
<div class="clearfix"></div>

<div id="status-message"></div>

<div class="container">
<?php 
if(count($unitsArray) > 0) {
	foreach($unitsArray as $unit):
		$id = $unit['id'];
		$estimate_unit = $unit['estimate_unit'];
		?>
		<div class="row property-list-row" id="templateRow_<?php echo $id; ?>">
			<div class="pull-left">
				<h2><?php echo stripslashes($estimate_unit); ?></h2>
			</div>
			<div class="pull-right"><a href="edit_unit.html?id=<?php echo $id; ?>" class="btn btn-warning"><i class="icon-pencil icon-white"></i> View/Edit</a> &nbsp; <a href="javascript: void(0);" class="btn btn-danger" onClick="deleteUnit('<?php echo $id; ?>', '<?php echo $estimate_unit; ?>')"><i class="icon-trash icon-white"></i> Delete</a></div>
			<div class="clearfix"></div>
		</div>
		<?php
	endforeach;
} else {
	echo "No units added.";
}
?>

<div class="hidden" id="baseName"><?php echo __BASENAME__; ?></div>

<script type="text/javascript">
var baseNameElem = document.getElementById("baseName");
var statusMsg = document.getElementById('status-message');

function deleteUnit(unitId, estimate_unit) {
	if(confirm("Are you sure you want to delete \"" + estimate_unit + "\" ?")) {
		$.ajax({
			url: baseNameElem.innerHTML + "/webservice/delete_unit.php",
			type: "POST",
			data: { id: unitId
				} 
		}).done(function( response ) {
			if(response.success) {
				statusMsg.innerHTML = getAlert('success', response.message);
				$("#templateRow_" + unitId).remove();
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
</script>