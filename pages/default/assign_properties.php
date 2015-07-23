<?php
require_once(dirname(dirname(dirname(__FILE__))) . '/modules/Dynamo.class.php');

if(trim($_GET['userId']) != '')
{
	$user_properties_obj = new Dynamo("user_properties");
	
	if($_POST['assign_properties'] == 1)
	{
		$query = "DELETE FROM user_properties WHERE user_id = ".$_GET['userId'];
		$user_properties_obj->customExecuteQuery($query);
		
		$user_properties_id = $user_properties_obj->getMaxId();
		if(count($_POST['propertyId']) > 0)
		{
			$query = "INSERT INTO user_properties (`id`,`user_id`,`property_id`,`timestamp`) VALUES";
			
			for($i=0;$i<count($_POST['propertyId']);$i++)
			{
				$query .= "($user_properties_id,".$_GET['userId'].",".$_POST['propertyId'][$i].",NOW()),";
			}
			
			$query = substr($query,0,-1);
			$user_properties_obj->customExecuteQuery($query);
		}
	}
	
	$users_obj = new Dynamo("users");
	$userArray = $users_obj->getAll("WHERE id = ".$_GET['userId']);
	$userArray = $userArray[0];
	
	$properties_obj = new Dynamo("properties");
	$propertyArray = $properties_obj->getAll("WHERE status = 1");
	
	$userPropertiesArray = $user_properties_obj->getAll("WHERE user_id = ".$_GET['userId']);
	$userPropertyArray = array();
	for($i=0;$i<count($userPropertiesArray);$i++)
	{
		$userPropertyArray[] = $userPropertiesArray[$i]['property_id'];
	}
	?>
	<form method="POST" class="form-horizontal" id="addUserForm">
	<div class="pull-left"><h4>Assign Properties to <?php print $userArray["first_name"] . " ".$userArray["last_name"]; ?></h4></div>
	<div class="pull-right"><button class="btn btn-warning" type="submit">Submit Changes</button> &nbsp; <a href="users.html" class="btn btn-default">Cancel</a></div>
	<div class="clearfix"></div>
	
	<div id="status-message"></div>
	<table id="userTable" class="common-table">
	<?php
	if(count($propertyArray) > 0) 
	{
		for($i=0;$i<count($propertyArray);$i+=2)
		{
			$checked = '';
			$checked2 = '';
			
			if(in_array($propertyArray[$i]["id"],$userPropertyArray))
				$checked = ' checked';
			
			if(in_array($propertyArray[$i+1]["id"],$userPropertyArray))
				$checked2 = ' checked';
			
			print "<tr>";
			if($propertyArray[$i]["id"])
			{
				print "<td><input type='checkbox' name='propertyId[]' value='".$propertyArray[$i]["id"]."'".$checked." /> ".$propertyArray[$i]["community"].", ".$propertyArray[$i]["name"]."</td>";
			}
			else
			{
				print "<td>&nbsp;</td>";
			}
				
			if($propertyArray[$i+1]["id"])
			{
				print "<td><input type='checkbox' name='propertyId[]' value='".$propertyArray[$i+1]["id"]."'".$checked2." /> ".$propertyArray[$i+1]["community"].", ".$propertyArray[$i+1]["name"]."</td>";
			}
			else
			{
				print "<td>&nbsp;</td>";
			}
			
			print "</tr>";
		}
	} 
	else 
	{
	?>
		<tr><td colspan="2">No records found.</td></tr>
	<?php
	}
	?>
	</table>
    <input type="hidden" name="assign_properties" value="1" />
    </form>
<?php
	if($_POST['assign_properties'] == 1)
	{
?>
<script type="text/javascript">
window.onload = function() {
	var statusMsg = document.getElementById('status-message');
	statusMsg.innerHTML = getAlert('success', 'You have successfully assigned properties');
};
</script>    
<?php	
	}
}
?>