<?php
require_once(dirname(dirname(dirname(__FILE__))) . '/modules/Report.class.php');
	
$userId = $_SESSION['user_id'];

$reportObj = new Report();
$reports = $reportObj->getReports($userId, false);

// var_dump($reports);
?>
<div class="pull-left"><h4>Reports</h4></div>
<div class="pull-right">&nbsp;</div>
<div class="clearfix"></div>

<div id="status-message"></div>
<table id="reportsTable" class="common-table">
<tr>
	<th>Property</th>
	<th>Completed by</th>
	<th>Company</th>
	<th>User Type</th>
	<th>Date</th>
	<th>Result</th>
	<th>&nbsp;</th>
</tr>
<?php 
if(count($reports) == 0) {
?>
<tr>
	<td colspan="7">No reports found.</td>
</tr>
<?php
} else {
	foreach($reports as $report):
		// var_dump($report);
	?>
	<tr>
		<td><?php echo $report['properyName']; ?></td>
		<td><?php echo $report['firstName'] . ' ' . $report['lastName']; ?></td>
		<td><?php echo $report['companyName']; ?></td>
		<td>&nbsp;</td>
		<td><?php echo date('m/d/Y g:ia', strtotime($report['dateReported'])); ?></td>
		<td><?php echo ($report['status']==0?'Closed':'Open'); ?></td>
		<td><a href="#" class="btn btn-default btn-small"><i class="icon-info-sign"></i> View</a></td>
	</tr>
	<?php
	endforeach;
}
?>
</table>