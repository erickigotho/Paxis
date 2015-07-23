<?php
require_once(dirname(dirname(dirname(__FILE__))) . '/modules/User.class.php');
require_once(dirname(dirname(dirname(__FILE__))) . '/modules/Dynamo.class.php');

if($_SESSION['user_type'] == 1 && trim($_GET['del']) == 'true' && trim($_GET['id']) != '')
{
	$users_obj = new Dynamo("users");
	$query = "DELETE FROM users WHERE id = ".$_GET['id'];
	$users_obj->customExecuteQuery($query);
}

$userObj = new User();
$listUsers = array();

$companyId = (isset($_SESSION['company_id'])?$_SESSION['company_id']:0);

if($userObj) {
	$userRoleId = (isset($_SESSION['user_type'])?$_SESSION['user_type']:0);
	
	if($userRoleId == 2) {
		$listUsers = $userObj->getAllUsersPerCompany($companyId, false);
	} else {
		$listUsers = $userObj->getAllUsers(false);
	}
}

?>

<div class="pull-left"><h4>Users</h4></div>
<div class="pull-right"><a href="add_user.html" class="btn btn-warning"><i class="icon-plus icon-white"></i> Add User</a></div>
<div class="clearfix"></div>

<div id="status-message"></div>
<table id="userTable" class="common-table">
<tr>
	<th>Username/Email</th>
	<th>Name</th>
	<th>Company</th>
	<th>User Type</th>
	<th>Last Login</th>
	<th>&nbsp;</th>
    <th>&nbsp;</th>
    <?php
	if($_SESSION['user_type'] == 1)
	{
	?>
    <th>&nbsp;</th>
    <?php
	}
	?>
</tr>
<?php
if(count($listUsers) > 0) 
{
	foreach($listUsers as $user) 
	{
	?>
	<tr>
		<td><?php echo $user['email']; ?></td>
		<td><?php echo $user['firstName'] . ' ' . $user['lastName']; ?></td>
		<td><?php echo $user['company']; ?></td>
		<td><?php echo $user['userType']; ?></td>
		<td><?php echo (empty($user['lastLogin'])?'':date('m/d/Y g:ia', strtotime($user['lastLogin']))); ?></td>
		<td><a href="view_user.html?userId=<?php echo $user['id'];?>" class="btn btn-warning"><i class="icon-pencil icon-white"></i> View/Edit</a></td>
        <td><a href="assign_properties.html?userId=<?php echo $user['id'];?>" class="btn btn-warning"><i class="icon-pencil icon-white"></i> Assign Properties</a></td>
        <?php
        if($_SESSION['user_type'] == 1)
		{
		?>
        <td><a href="users.html?id=<?php echo $user['id'];?>&del=true" class="btn btn-danger" onclick="return confirm('Are you sure?');"><i class="icon-trash icon-white"></i> Delete</a></td>
        <?php
		}
		?>
	</tr>
	<?
	}
} 
else 
{
?>
	<tr><td colspan="3">No records found.</td></tr>
<?php
}
?>
</table>