<?php
require_once(dirname(dirname(dirname(__FILE__))) . '/modules/Dynamo.class.php');

$subContractorsObj = new Dynamo("sub_contractors");
$workCategoriesObj = new Dynamo("work_categories");
$subContractorWorkCategoryObj = new Dynamo("sub_contractor_work_category");

if($_SESSION['user_type'] == 1 && trim($_GET['del']) == 'true' && trim($_GET['id']) != '')
{
	$query = "DELETE FROM sub_contractors WHERE id = ".$_GET['id'];
	$subContractorsObj->customExecuteQuery($query);
	
	$query = "DELETE FROM subcontractors_assign WHERE sub_contractor_id = ".$_GET['id'];
	$subContractorsObj->customExecuteQuery($query);
	
	$query = "DELETE FROM sub_contractor_work_category WHERE sub_contractor_id = ".$_GET['id'];
	$subContractorsObj->customExecuteQuery($query);
}

$listSubContractors = array();

if($subContractorsObj) 
{
	$userRoleId = (isset($_SESSION['user_type'])?$_SESSION['user_type']:0);
	
	if($userRoleId == 2 || $userRoleId == 1) 
	{
		$listSubContractors = $subContractorsObj->getAllWithId();
		$work_categories_array = $workCategoriesObj->getAllWithId();
		$sub_category_work_array = $subContractorWorkCategoryObj->getAll();
		
		for($i=0;$i<count($sub_category_work_array);$i++)
		{
			if(isset($listSubContractors[$sub_category_work_array[$i]['sub_contractor_id']]))
			{
				if(trim($listSubContractors[$sub_category_work_array[$i]['sub_contractor_id']]['work_categories']) != '')
				{
					$listSubContractors[$sub_category_work_array[$i]['sub_contractor_id']]['work_categories'] .= ",".$work_categories_array[$sub_category_work_array[$i]['work_category_id']]['name'];
				}
				else
				{
					$listSubContractors[$sub_category_work_array[$i]['sub_contractor_id']]['work_categories'] = $work_categories_array[$sub_category_work_array[$i]['work_category_id']]['name'];
				}
					
			}
		}
	}
}

?>

<div class="pull-left"><h4>Sub Contractors</h4></div>
<div class="pull-right"><a href="add_sub_contractor.html" class="btn btn-warning"><i class="icon-plus icon-white"></i> Add Sub Contractors</a></div>
<div class="clearfix"></div>

<div id="status-message"></div>
<table id="userTable" class="common-table">
<tr>
	<th>Email</th>
	<th>Name</th>
	<th>Phone Number</th>
	<th>Work Category</th>
	<th>Status</th>
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
if(count($listSubContractors) > 0) {
	foreach($listSubContractors as $subcontractor) {
	?>
	<tr>
		<td><?php echo $subcontractor['email']; ?></td>
		<td><?php echo $subcontractor['first_name'] . ' ' . $subcontractor['last_name']; ?></td>
		<td><?php echo $subcontractor['phone_number']; ?></td>
		
		<td><?php if(trim($subcontractor['work_categories']) != '') echo $subcontractor['work_categories']; else "None selected"; ?></td>
		<td><?php if($subcontractor['is_active'] == 1) print "Active"; else print "Inactive"; ?></td>
		<td><a href="view_sub_contractor.html?id=<?php echo $subcontractor['id'];?>" class="btn btn-warning"><i class="icon-pencil icon-white"></i> View/Edit</a></td>
         <?php
        if($_SESSION['user_type'] == 1)
		{
		?>
        <td><a href="sub_contractors.html?id=<?php echo $subcontractor['id'];?>&del=true" class="btn btn-danger" onclick="return confirm('Are you sure?');"><i class="icon-trash icon-white"></i> Delete</a></td>
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
<?php
}
?>