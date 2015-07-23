<?php
require_once(dirname(dirname(dirname(__FILE__))) . '/modules/Room.class.php');

$roomObj = new Room();
$listRooms = array();

if($roomObj) {
	$listRoomTemplates = $roomObj->getRoomTemplates(false);
}
?>
<div class="pull-left"><h4>Room Templates</h4></div>
<div class="pull-right"><a href="add_room_template.html" class="btn btn-warning"><i class="icon-plus icon-white"></i> Add Room</a></div>
<div class="clearfix"></div>

<div id="status-message"></div>

<div class="container">
<?php 
if(count($listRoomTemplates) > 0) {
	foreach($listRoomTemplates as $roomTemplate):
		$id = $roomTemplate['id'];
		$name = $roomTemplate['name'];
		?>
		<div class="row property-list-row" id="templateRow_<?php echo $id; ?>">
			<div class="pull-left">
				<h2><?php echo stripslashes($name); ?></h2>
			</div>
			<div class="pull-right"><a href="edit_room_template.html?id=<?php echo $id; ?>" class="btn btn-warning"><i class="icon-pencil icon-white"></i> View/Edit</a> &nbsp; <a href="javascript: void(0);" class="btn btn-danger" onclick="deleteRoomTemplate('<?php echo $id; ?>', '<?php echo $name; ?>')"><i class="icon-trash icon-white"></i> Delete</a></div>
			<div class="clearfix"></div>
		</div>
		<?php
	endforeach;
} else {
	echo "No room templates listed.";
}
?>

<div class="hidden" id="baseName"><?php echo __BASENAME__; ?></div>

<script type="text/javascript">
var baseNameElem = document.getElementById("baseName");
var statusMsg = document.getElementById('status-message');

function deleteRoomTemplate(roomTemplateId, roomTemplateName) {
	if(confirm("Are you sure you want to delete \"" + roomTemplateName + "\" template?")) {
		$.ajax({
			url: baseNameElem.innerHTML + "/webservice/delete_room_template.php",
			type: "POST",
			data: { id: roomTemplateId
				} 
		}).done(function( response ) {
			if(response.success) {
				statusMsg.innerHTML = getAlert('success', response.message);
				
				$("#templateRow_" + roomTemplateId).remove();
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