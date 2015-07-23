<?php
require_once(dirname(dirname(dirname(__FILE__))) . '/modules/Property.class.php');
require_once(dirname(dirname(dirname(__FILE__))) . '/modules/Report.class.php');
require_once(dirname(dirname(dirname(__FILE__))) . '/modules/Estimates.class.php');
require_once(dirname(dirname(dirname(__FILE__))) . '/modules/Dynamo.class.php');

$propertyId = isset($_GET['propertyId'])?$_GET['propertyId']:0;
$report_images = new Dynamo("report_images");

if($propertyId != 0) {
	$propertyObj = new Property();
	$propertyInfo = $propertyObj->getPropertyInfo($propertyId, false);
	
	$estimates_obj = new Dynamo("estimates");
	$estimatesArray = $estimates_obj->getAll("WHERE property_id = ".$propertyId." AND is_saved = 1");
	$estimatesArray = $estimatesArray[0];
	
	$reportObj = new Report();
	$estimatesObj = new Estimates();
	
	$usersObj = new Dynamo("users");
	$array_users = $usersObj->getAllWithId_default(false,"id");
	
	$subContractorsObj = new Dynamo("sub_contractors");
	$array_sub_contractors = $subContractorsObj->getAllWithId_default(false,"id");
	
	$companiesObj = new Dynamo("companies");
	$array_companies = $companiesObj->getAllWithId_default(false,"id");
	
	$dailyLogsObj = new Dynamo("daily_logs");
	
	/*$reportObj = new Report();
	$reports = $reportObj->getReportsSummary($propertyId, false,$array_users,$array_sub_contractors,$array_companies);**/
	$reports = $reportObj->getReportsSummary($propertyId, false,$array_users,$array_sub_contractors,$array_companies);
	$estimates = $estimatesObj->getEstimatesSummary($propertyId, false,$array_users,$array_companies);
	$daily_logs = $dailyLogsObj->getAll("WHERE property_id = ".$propertyId." ORDER BY timestamp DESC");
	
	$array_reports_estimates = array();
	for($i=0;$i<count($reports);$i++)
	{
		$reports[$i]['report'] = true;
		$array_reports_estimates[strtotime($reports[$i]['dateReported'])] = $reports[$i];
	}
	for($i=0;$i<count($estimates);$i++)
	{
		$array_reports_estimates[strtotime($estimates[$i]['dateReported'])] = $estimates[$i];
	}
	for($i=0;$i<count($daily_logs);$i++)
	{
		if($daily_logs[$i]['closed'] == 1)
			{
				$isClosed = 1;
				$isSaved = 0;
				$isSubmitted = 1;
			}
			else
			{
				$isClosed = 0;
				$isSaved = 1;
				$isSubmitted = 0;
			}
			
		$daily_logs_array = array('id'=>$daily_logs[$i]['id'],'firstName'=>$array_users[$daily_logs[$i]['user_id']]['first_name'],'lastName'=>$array_users[$daily_logs[$i]['user_id']]['last_name'],'dateReported'=>$daily_logs[$i]['timestamp'],'companyName'=>$daily_logs[$i]['company'],'isSubmitted'=>$isSubmitted,'isSaved'=>$isSaved,'isClosed'=>$isClosed,'status'=>'','dailyLogs'=>1);
		
		$array_reports_estimates[strtotime($daily_logs[$i]['timestamp'])] = $daily_logs_array;
	}
	
	krsort($array_reports_estimates);
	
	if(!$propertyInfo) {	
		echo "No property found.";
		return;
	}
?>
	<div class="pull-left"><h4>Editing <?php echo stripslashes($propertyInfo['name']); ?></h4></div>
	<div class="pull-right">
		<a href="archives.html" class="btn btn-warning"><i class="icon-chevron-left icon-white"></i> Archives List</a>
	</div>
	<div class="clearfix"></div>
	
	<div class="row-fluid">
		<div class="property-left">
			<form method="POST" class="form-horizontal" id="addPropertyForm">
			<input type="hidden" id="baseName" name="baseName" value="<?php echo __BASENAME__; ?>" />
			<input type="hidden" id="userId" name="userId" value="<?php echo $_SESSION['user_id']; ?>" />
			<input type="hidden" id="propertyId" name="propertyId" value="<?php echo $propertyId; ?>" />
			<input type="hidden" id="propertyStatus" name="propertyStatus" value="<?php echo $propertyInfo['status']; ?>" />
				
				<div id="status-message"></div>
			
				<div class="control-group">
					<label for="propertyName" class="control-label">Property Name</label>
					<div class="controls">
						<input type="text" name="propertyName" id="propertyName" class="form-control" placeholder="Property Name" value="<?php echo stripslashes($propertyInfo['name']); ?>" data-validation="required" data-validation-error-msg="Please enter a property name."  disabled/>
					</div>
				</div>
				<div class="control-group">
					<label for="address" class="control-label">Address</label>
					<div class="controls">
						<textarea type="text" name="address" id="address" class="form-control" placeholder="Address" data-validation="required" data-validation-error-msg="Address is a required field." disabled><?php echo $propertyInfo['address'];?></textarea>
					</div>
				</div>
				<div class="control-group">
					<label for="address" class="control-label">City St.</label>
					<div class="controls">
						<input type="text" name="city" id="city" class="form-control" placeholder="City" value="<?php echo $propertyInfo['city']; ?>" data-validation="required" data-validation-error-msg="City is a required field."  disabled/>
					</div>
				</div>
				<div class="control-group">
					<label for="address" class="control-label">Zip Code</label>
					<div class="controls">
						<input type="text" name="zip" id="zip" class="form-control" placeholder="Zip Code" value="<?php echo $propertyInfo['zip']; ?>" data-validation="required" data-validation-error-msg="Zip code is a required field."  disabled/>
					</div>
				</div>
				<div class="control-group">
					<label for="address" class="control-label">Google Map Link</label>
					<div class="controls">
						<input type="text" name="googleMapLink" id="googleMapLink" class="form-control" placeholder="Map URL" value="<?php echo $propertyInfo['map_link']; ?>" data-validation="required" data-validation-error-msg="Google Map link is a required field."  disabled/>
					</div>
				</div>
				<div class="control-group">
					<label for="status" class="control-label">Status</label>
					<div class="controls">
						<?php echo ($propertyInfo['status']==1?'Open':'Closed/Archived'); ?>
					</div>
				</div>
				<div class="control-group">
					<label for="status" class="control-label">&nbsp;</label>
					<div class="controls">
						<!--<button class="btn btn-primary b" type="submit">Submit Changes</button> &nbsp; <a href="properties.html" class="btn btn-default">Cancel</a>-->
					</div>
				</div>
			<form>
		</div>
		<div class="property-right">
			<?php 
			if(count($array_reports_estimates) > 0)
			{
				$count_submit  = 0;
				
				foreach($array_reports_estimates as $array_reports_estimate)
				{
					if($array_reports_estimate['dailyLogs'] == 1)
					{					
						?>
                        <div class="report-group">
							  <div class="report-thumbnail">&nbsp;</div>
							  <div class="report-details">
									<?php echo date('m/d/Y g:ia', strtotime($array_reports_estimate["dateReported"])); ?> Daily Logs by <strong><?php echo $array_reports_estimate["firstName"] . " " . $array_reports_estimate["lastName"]; ?></strong> with <strong><?php echo $array_reports_estimate["companyName"];?></strong> - 
									<?php
									if($array_reports_estimate['isSubmitted'] == 0) {
										$reportStatusClass = ($array_reports_estimate['isClosed']==0?'label-danger':'label-success');
										$reportStatusName = ($array_reports_estimate['isClosed']==0?'Open':'Closed');
										$estimateStatusName = ($estimate['isClosed']==0?'Open':'Closed');
									?>
										<span class="label <?php echo $reportStatusClass;?>"><?php echo $estimateStatusName; ?></span>
									<?php
									} else {
									?>
										<span class="label label-default">Saved & Closed</span>
									<?php
									}
									?>
							  </div>
							  
							  <?php 	
							  if($array_reports_estimate['isSubmitted'] == 1 && $array_reports_estimate['isSaved'] == 0) {
							  ?>
								<div class="report-action"><a href="view_daily_log_archive.html?propertyId=<?php echo $propertyId;?>&id=<?php print $array_reports_estimate["id"]; ?>" class="btn btn-default"><i class="icon-info-sign"></i> View</a></div>
							  <?php 
							  } else if($array_reports_estimate['isSubmitted'] == 0 && $array_reports_estimate['isSaved'] == 1) {
							  ?>
								<div class="report-action"><a href="edit_daily_log.html?propertyId=<?php echo $propertyId;?>&id=<?php echo $array_reports_estimate['id'];?>" class="btn btn-default"><i class="icon-pencil"></i> Edit</a></div>
							  <?php 
							  } 
							  ?>
							  <div class="clearfix"></div>
							  
							</div>
                        <?php
					}
					else
					{
						if(!$array_reports_estimate['report']) //estimates here
						{
							$count_submit += 1;
							// var_dump($report);	
						?>
							<div class="report-group">
							  <div class="report-thumbnail">&nbsp;</div>
							  <div class="report-details">
									<?php echo date('m/d/Y g:ia', strtotime($array_reports_estimate["dateReported"])); ?> Estimate by <strong><?php echo $array_reports_estimate["firstName"] . " " . $array_reports_estimate["lastName"]; ?></strong> with <strong><?php echo $array_reports_estimate["companyName"];?></strong> - 
									<?php
									if($array_reports_estimate['isSubmitted'] == 1) 
									{
										/*if($array_reports_estimate['isClosed'] == 0 && $count_submit == 1)
										{
											$estimateStatusName = "Accepted";
											$reportStatusClass = "label-success";
										}*/
										if($array_reports_estimate['isClosed'] == 0)
										{
											$estimateStatusName = "Submitted";
											$reportStatusClass = "label-blue";
										}
										if($array_reports_estimate['isClosed'] == 1)
										{
											$estimateStatusName = "Submitted";
											$reportStatusClass = "label-blue";
										}
										else if($array_reports_estimate['isClosed'] == 2)
										{
											$estimateStatusName = "Accepted";
											$reportStatusClass = "label-success";
										}
										//$estimateStatusName = ($estimate['isClosed']==0?'Open':'Closed');
									?>
										<span class="label <?php echo $reportStatusClass;?>"><?php echo $estimateStatusName; ?></span>
									<?php
									} else {
									?>
										<span class="label label-default">Saved</span>
									<?php
									}
									?>
							  </div>
							  
							  <?php 	
							  if($array_reports_estimate['isSubmitted'] == 1 && $array_reports_estimate['isSaved'] == 0) {
							  ?>
								<div class="report-action"><a href="view_archive_estimate.html?estimatesId=<?php echo $array_reports_estimate['id'];?>" class="btn btn-default"><i class="icon-info-sign"></i> View</a></div>
							  <?php 
							  } else if($array_reports_estimate['isSubmitted'] == 0 && $array_reports_estimate['isSaved'] == 1) {
							  ?>
								<div class="report-action"><a href="edit_estimate.html?propertyId=<?php echo $propertyId;?>&estimatesId=<?php echo $array_reports_estimate['id'];?>" class="btn btn-default"><i class="icon-pencil"></i> Edit</a></div>
							  <?php 
							  } 
							  ?>
							  <div class="clearfix"></div>
							  
							</div>
				<?php
						}
						else // reports here
						{
							?>
							<div class="report-group">
							  <div class="report-thumbnail">&nbsp;</div>
							  <div class="report-details">
									<?php echo date('m/d/Y g:ia', strtotime($array_reports_estimate["dateReported"])); ?> Report by <strong><?php echo $array_reports_estimate["firstName"] . " " . $array_reports_estimate["lastName"]; ?></strong> with <strong><?php echo $array_reports_estimate["companyName"];?></strong> - 
									<?php
									if($array_reports_estimate['isSubmitted'] == 1) {
										$reportStatusClass = ($array_reports_estimate['isClosed']==0?'label-danger':'label-success');
										$reportStatusName = ($array_reports_estimate['isClosed']==0?'Open':'Closed');
									?>
										<span class="label <?php echo $reportStatusClass;?>"><?php echo $reportStatusName; ?></span>
									<?php
									} else {
									?>
										<span class="label label-default">Saved</span>
									<?php
									}
									?>
							  </div>
							  
							  <?php 	
							  if($array_reports_estimate['isSubmitted'] == 1 && $array_reports_estimate['isSaved'] == 0) {
							  ?>
								<div class="report-action"><a href="view_report_archive.html?reportId=<?php echo $array_reports_estimate['id'];?>" class="btn btn-default"><i class="icon-info-sign"></i> View</a></div>
							  <?php 
							  } else if($array_reports_estimate['isSubmitted'] == 0 && $array_reports_estimate['isSaved'] == 1) {
							  ?>
								<div class="report-action"><a href="edit_report.html?propertyId=<?php echo $propertyId;?>&reportId=<?php echo $array_reports_estimate['id'];?>" class="btn btn-default"><i class="icon-pencil"></i> Edit</a></div>
							  <?php 
							  } 
							  ?>
							  <div class="clearfix"></div>
							  <?php
								$array_images = $report_images->getAll("WHERE report_id = ".$array_reports_estimate['id']);
								if(count($array_images) > 0)
								{
								?>
									<div class='image_row'>
								<?php	
									$count = 0;
									for($i=0;$i<count($array_images);$i++)
									{
										$count += 1;
										if($readonly)
										{
											print "<div class='property_images'><div class='set_main_image_container'><div class='set_main_image'>Set as main image</div></div>
										<div class='property_image_container'><a rel='light_box_2' href='images/report_uploads/".$array_images[$i]['image_name']."' title=''><img src='images/report_uploads/".$array_images[$i]['image_name']."' class='property_image' /></a></div><div class='image_id'>".$array_images[$i]['id']."</div>
										</div>";
										}
										else
										{	
											print "<div class='property_images'><div class='set_main_image_container'><div class='set_main_image'>Set as main image</div></div>
										<div class='property_image_container'><a rel='light_box' href='images/report_uploads/".$array_images[$i]['image_name']."' title=''><img src='images/report_uploads/".$array_images[$i]['image_name']."' class='property_image' /></a></div><div class='image_id'>".$array_images[$i]['id']."</div>
										</div>";
										}
										
										if($count == 5)
										{
											print "<div class='clearfix'></div>";
											$count = 0;
										}
									}
							  ?>
								<div class="clearfix property_image_bottom_line"></div>
								</div>
							  <?php
								}
							  ?>
							</div>
							<?php
						}
					}
				}
			}
			?>
		</div>
		<div class="clearfix"></div>
	</div>

	
	<script type="text/javascript">
	window.onload = function() {
		var statusMsg = document.getElementById('status-message');
		var formObj = document.forms['addPropertyForm'];
		
		var basePropertIdElem = formObj.propertyId;
		var baseNameElem = formObj.baseName;
		var propertyNameElem = formObj.propertyName;
		var propertyAddressElem = formObj.address;
		var propertyCityElem = formObj.city;
		var propertyZipElem = formObj.zip;
		var propertyMapLinkElem = formObj.googleMapLink;
		var userIdElem = formObj.propertyStatus;
		var statusVal;
		
		if(!baseNameElem) return;
		
		$.validate({	
			modules: 'security',
			onValidate:function() {
			},
			onError: function() {
				statusMsg.innerHTML = getAlert('error', 'Oops! something is wrong. Please check the values below.');
			},
			onSuccess : function() {
				statusMsg.innerHTML = "";
				
				statusMsg.innerHTML = getAlert('info', 'Please wait while we process your request...');
				
				$.ajax({
					url: baseNameElem.value + "/webservice/update_property.php",
					type: "POST",
					data: { id: basePropertIdElem.value
							,name: propertyNameElem.value
							,address: propertyAddressElem.value
							,city: propertyCityElem.value
							,zip: propertyZipElem.value 
							,link: propertyMapLinkElem.value
							,status: userIdElem.value
							,userId: userIdElem.value
						} 
				}).done(function( response ) {
					if(response.success) {
						statusMsg.innerHTML = getAlert('success', response.message);
					} else {
						if(!response.message || response.message == '' || !response) {
							statusMsg.innerHTML = getAlert('error', "Sorry, there has been a problem processing your request");
						} else {
							statusMsg.innerHTML = getAlert('error', response.message);
						}
					}
				});
			  
				return false;
			}
		});
	};
	
	</script>
<?php
} else {
?>
	Oops! something went wrong.
<?php
}
?>