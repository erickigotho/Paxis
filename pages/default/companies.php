<?php
require_once(dirname(dirname(dirname(__FILE__))) . '/modules/Company.class.php');

$companyObj = new Company();
$listCompanies = array();

if($companyObj) {
	$listCompanies = $companyObj->getCompanyList(false);
}
?>

<div class="pull-left"><h4>Company List</h4></div>
<div class="pull-right"><a href="add_company.html" class="btn btn-warning"><i class="icon-plus icon-white"></i> Add Company</a></div>
<div class="clearfix"></div>

<div id="status-message"></div>
<table class="common-table">
<tr>
	<th>Company Name</th>
	<th>Date Created</th>
	<th></th>
</tr>
<?php
if(count($listCompanies) > 0) {
	foreach($listCompanies as $company) {
	?>
	<tr>
		<td><?php echo $company['name']; ?></td>
		<td><?php echo date('m/d/Y g:ia', strtotime($company['dateCreated'])); ?></td>
		<td><a href="edit_company.html?companyId=<?php echo $company['id'];?>" class="btn btn-warning"><i class="icon-pencil icon-white"></i> View/Edit</a></td>
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
