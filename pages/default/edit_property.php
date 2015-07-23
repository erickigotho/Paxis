<?php
require_once(dirname(dirname(dirname(__FILE__))) . '/modules/Property.class.php');
require_once(dirname(dirname(dirname(__FILE__))) . '/modules/Report.class.php');
require_once(dirname(dirname(dirname(__FILE__))) . '/modules/Estimates.class.php');
require_once(dirname(dirname(dirname(__FILE__))) . '/modules/Dynamo.class.php');

if($_SESSION['user_type'] == 5)
	$readonly = true;	
else
	$readonly = false;
		
$propertyId = isset($_GET['propertyId'])?$_GET['propertyId']:0;
$report_images = new Dynamo("report_images");

$complex_properties_obj = new Dynamo("complex_properties");
$complex_properties_array = $complex_properties_obj->getAll("ORDER BY id");

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
	
	$reports = $reportObj->getReportsSummary($propertyId, false,$array_users,$array_sub_contractors,$array_companies);
	$estimates = $estimatesObj->getEstimatesSummary($propertyId, false,$array_users,$array_companies);
	$daily_logs = $dailyLogsObj->getAll("WHERE property_id = ".$propertyId." ORDER BY timestamp DESC");
	
	$array_reports_estimates = array();
	for($i=0;$i<count($reports);$i++)
	{
		$reports[$i]['report'] = true;
		$array_reports_estimates[strtotime($reports[$i]['dateReported'])] = $reports[$i];
	}
	if($readonly == false)
	{
		for($i=0;$i<count($estimates);$i++)
		{
			$array_reports_estimates[strtotime($estimates[$i]['dateReported'])] = $estimates[$i];
		}
		
		$daily_logs_array = array();
		
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
	}
	krsort($array_reports_estimates);
	
	if(count($array_reports_estimates) > 0)
	{
		$count = 0;
		foreach($array_reports_estimates as $key => $value)
		{
			$count += 1;
			if($array_reports_estimates[$key]['report'] == 1)
			{
				if($array_reports_estimates[$key]['isSaved'] == 1  && $array_reports_estimates[$key]['isClosed'] == 0 && $count > 1)
				{
					$array_to_copy[$key] = $array_reports_estimates[$key];
					unset($array_reports_estimates[$key]);
					
					foreach($array_reports_estimates as $key1 => $value1)
					{
						$array_to_copy[$key1] = $value1;
					}
					
					$array_reports_estimates = $array_to_copy;
				}
				else
					break;
			}
			
		}
	}

	
	if(!isset($propertyInfo['name'])) {	
		echo '<div class="alert alert-error"><strong>Property not found.</strong></div>';
		return;
	}
	
?>
	<div class="pull-left"><h4>Editing <?php echo stripslashes($propertyInfo['name']); ?></h4></div>
	<div class="pull-right">
		<?php
		if($readonly == false)
		{
			?>
            <a href="add_daily_log.html?propertyId=<?php echo $propertyId; ?>"><button class="btn btn-warning"><i class="icon-plus icon-white"></i> Add Daily Log</button></a>
            <?php	
		}
		
		if($readonly == false)
		{	
			if($propertyInfo['in_estimates'] != 3)
			{
				if($estimatesArray['is_saved'] <= 0 && $propertyInfo['status']==1 && $propertyInfo['isSaved'] <= 0) {
				?>
					<a href="add_estimate.html?propertyId=<?php echo $propertyId; ?>" class="btn btn-warning"><i class="icon-plus icon-white"></i> Add New Estimate</a>
				<?php
				} else {
				?>
					<button href="add_estimate.html?propertyId=<?php echo $propertyId; ?>" class="btn btn-warning" title="Oops! you may have incomplete estimate or this property is already closed." disabled><i class="icon-plus icon-white"></i> Add New Estimate</button>
				<?php
				}
			}
		}
		?>
		
		<?php
		if($propertyInfo['isSaved'] <= 0 && $propertyInfo['status']==1 && $estimatesArray['is_saved'] <= 0) {
		?>
			<a href="add_report.html?propertyId=<?php echo $propertyId; ?>" class="btn btn-warning"><i class="icon-plus icon-white"></i> Add New Report</a>
		<?php
		} else {
		?>
			<button href="add_report.html?propertyId=<?php echo $propertyId; ?>" class="btn btn-warning" title="Oops! you may have incomplete report or this property is already closed." disabled><i class="icon-plus icon-white"></i> Add New Report</button>
		<?php
		}
		?>
	</div>
	<div class="clearfix"></div>
	
	<div class="row-fluid">
		<div class="property-left">
			<form method="POST" class="form-horizontal" id="addPropertyForm" name="addPropertyForm">
			<input type="hidden" id="baseName" name="baseName" value="<?php echo __BASENAME__; ?>" />
			<input type="hidden" id="userId" name="userId" value="<?php echo $_SESSION['user_id']; ?>" />
			<input type="hidden" id="propertyId" name="propertyId" value="<?php echo $propertyId; ?>" />
			<input type="hidden" id="propertyStatus" name="propertyStatus" value="<?php echo $propertyInfo['status']; ?>" />
				
				<div id="status-message"></div>
				<div class="control-group">
					<label for="community" class="control-label">Community</label>
					<div class="controls">
						<?php if(!$readonly){?><input type="text" name="community" id="community" class="form-control" placeholder="Community" value="<?php } echo $propertyInfo['community']; if(!$readonly){ ?>" data-validation="required" data-validation-error-msg="Community is a required field" <?php echo ($propertyInfo['status']==0?'disabled':'');?> /><?php } ?>
					</div>
				</div>
				<div class="control-group">
					<label for="propertyName" class="control-label">Property Name</label>
					<div class="controls">
						<?php if(!$readonly){ ?><input type="text" name="propertyName" id="propertyName" class="form-control" placeholder="Property Name" value="<?php } echo stripslashes($propertyInfo['name']); if(!$readonly){ ?>" data-validation="required" data-validation-error-msg="Please enter a property name." <?php echo ($propertyInfo['status']==0?'disabled':'');?> /><?php } ?> 
					</div>
				</div>
				<div class="control-group">
					<label for="jobType" class="control-label">Job Type</label>
					<div class="controls">
                    <?php 
						if($readonly)
						{ 
							if($propertyInfo['job_type']==0)
								print "New";
							else if($propertyInfo['job_type']==1)
								print "Restoration";
						}
						else
						{
					?>
						<select id="jobType" name="jobType" data-validation="required" data-validation-error-msg="Job type is a required field" <?php echo ($propertyInfo['status']==0?'disabled':'');?>>
						<option value="">-- Choose job type --</option>
						<option value="0" <?php echo ($propertyInfo['job_type']==0?'selected':'');?>>New</option>
						<option value="1" <?php echo ($propertyInfo['job_type']==1?'selected':'');?>>Restoration</option>
						</select>
                        <?php
						}
						?>
					</div>
				</div>
				<div class="control-group">
					<label for="propertyType" class="control-label">Property Type</label>
					<div class="controls">
                     <?php 
						if($readonly)
						{ 
							if($propertyInfo['property_type']==0)
								print "Residential";
							else if($propertyInfo['property_type']==1)
								print "Commercial";
							for($i=0;$i<count($complex_properties_array);$i++)
							{
								if($propertyInfo['property_type']==($i+2))
									print $complex_properties_array[$i]["community"];
							}
						}
						else
						{
					?>
						<select id="propertyType" name="propertyType" data-validation="required" data-validation-error-msg="Property type is a required field" <?php echo ($propertyInfo['status']==0?'disabled':'');?><?php if($readonly) print ' disabled="disabled"'; ?>>
						<option value="">-- Choose property type --</option>
						<option value="0" <?php echo ($propertyInfo['property_type']==0?'selected':'');?>>Residential</option>
						<option value="1" <?php echo ($propertyInfo['property_type']==1?'selected':'');?>>Commercial</option>
                        <?php
							for($i=0;$i<count($complex_properties_array);$i++)
							{
								$selected = '';
								if($propertyInfo['property_type']==($i+2))
									$selected = ' selected';
								
								print "<option value=\"".($i+2)."\" rel='".$complex_properties_array[$i]["id"]."'".$selected.">".$complex_properties_array[$i]["community"]."</option>";	
							}
						?>
                        </select>
                       <?php
						}
					   ?>
					</div>
				</div>
				<div class="control-group">
					<label for="address" class="control-label">Address</label>
					<div class="controls">
						<?php if(!$readonly){ ?><textarea name="address" id="address" class="form-control" placeholder="Address" data-validation="required" data-validation-error-msg="Address is a required field." <?php echo ($propertyInfo['status']==0?'disabled':'');?>><?php } echo $propertyInfo['address']; if(!$readonly){ ?></textarea><?php } ?>
					</div>
				</div>
				<div class="control-group">
					<label for="address" class="control-label">City St.</label>
					<div class="controls">
						<?php if(!$readonly){ ?><input type="text" name="city" id="city" class="form-control" placeholder="City" value="<?php } echo $propertyInfo['city']; if(!$readonly){ ?>" data-validation="required" data-validation-error-msg="City is a required field."  <?php echo ($propertyInfo['status']==0?'disabled':''); ?> /> <?php } ?>
					</div>
				</div>
				<div class="control-group">
					<label for="address" class="control-label">Zip Code</label>
					<div class="controls">
						<?php if(!$readonly){ ?><input type="text" name="zip" id="zip" class="form-control" placeholder="Zip Code" value="<?php } echo $propertyInfo['zip']; if(!$readonly){ ?>" data-validation="required" data-validation-error-msg="Zip code is a required field." <?php echo ($propertyInfo['status']==0?'disabled':'');?> /><?php } ?>
					</div>
				</div>
				<div class="control-group">
					<label for="address" class="control-label">Google Map Link</label>
					<div class="controls">
						<?php if(!$readonly){ ?><input type="text" name="googleMapLink" id="googleMapLink" class="form-control" placeholder="Map URL" value="<?php } echo $propertyInfo['map_link']; if(!$readonly){ ?>"  <?php echo ($propertyInfo['status']==0?'disabled':'');?> /><?php } ?>
					</div>
				</div>
                <div class="control-group">
					<label for="estimates_multiplier" class="control-label">Estimates Multiplier</label>
					<div class="controls">
						<?php if(!$readonly){ ?><input type="text" name="estimatesMultiplier" id="estimatesMultiplier" class="form-control" placeholder="Estimates Multipler" value="<?php } echo $propertyInfo['estimates_multiplier']; if(!$readonly){ ?>"  <?php echo ($propertyInfo['status']==0?'disabled':'');?> /><?php } ?>
					</div>
				</div>
				<div class="control-group">
					<label for="emailList" class="control-label"<?php if($readonly){?> style="visibility:hidden;"<?php }?>>
						<strong>Email Address</strong>
						<br/>
						<small>Email reports will be sent to:</small>
                        
					</label>
					
					<div class="controls">
						<textarea name="emailList" id="emailList"<?php if($readonly){?> style="visibility:hidden"<?php } ?> rows="3" placeholder="email_1@abc.com, email_2@abc.com, email_3@abc.com" data-validation="required" data-validation-error-msg="Enter email address's in comma-separated format."  <?php echo ($propertyInfo['status']==0?'disabled':'');?><?php if($readonly) print ' disabled="disabled"'; ?>><?php echo empty($propertyInfo['emails'])?'':$propertyInfo['emails']; ?></textarea>
					</div>
				</div>
                <div class="control-group">
					<label for="estimatesEmailList" class="control-label"<?php if($readonly){?> style="visibility:hidden;"<?php }?>>
						<strong>Estimates Email Address</strong>
						<br/>
						<small>Email reports will be sent to:</small>
					</label>
					
					<div class="controls">
						<textarea name="estimatesEmailList" id="estimatesEmailList"<?php if($readonly){?> style="visibility:hidden"<?php } ?> rows="3" placeholder="email_1@abc.com, email_2@abc.com, email_3@abc.com" data-validation-error-msg="Enter email address's in comma-separated format."  <?php echo ($propertyInfo['status']==0?'disabled':'');?><?php if($readonly) print ' disabled="disabled"'; ?>><?php echo empty($propertyInfo['estimates_emails'])?'':$propertyInfo['estimates_emails']; ?></textarea>
					</div>
				</div>
				<div class="control-group">
					<label for="status" class="control-label">Status</label>
					<div class="controls">
						<?php echo ($propertyInfo['status']==1?'Open':'Closed/Archived'); ?>
					</div>
				</div>
                <?php 
					if($readonly == false)
					{ 
				?>
				<div class="control-group">
					<label for="status" class="control-label">&nbsp;</label>
					<div class="controls">
						<?php if($propertyInfo['status']==1): ?>
						<button class="btn btn-warning" type="submit">Submit Changes</button> &nbsp; <a href="properties.html" class="btn btn-default">Cancel</a>
						<?php endif; ?>
                        <br /><br />
						<a class="btn btn-warning" href="#editSubcontractorsModal" data-toggle="modal">Edit Subcontractors</a>
					</div>
				</div>
                <?php
					}
				?>
			</form>
		</div>
        <?php
			$subcontractors_assign_object = new Dynamo("subcontractors_assign");
			$subcontractors_assign_array = $subcontractors_assign_object->getAll("WHERE property_id = ".$_REQUEST['propertyId']);
			
			$subcontractors_ids_assign_array = array();
			for($i=0;$i<count($subcontractors_assign_array);$i++)
			{
				$subcontractors_ids_assign_array[$subcontractors_assign_array[$i]['work_category_id']][] = $subcontractors_assign_array[$i]['sub_contractor_id'];
			}
			
			//$subcontractors_assign_array = $subcontractors_assign_object->getAllWithId_default("WHERE property_id = ".$_REQUEST['propertyId'],"work_category_id");
			
			$work_categories_object = new Dynamo("work_categories");
			$work_categories_array = $work_categories_object->getAll("ORDER BY id");
			
			$sub_contractors_object = new Dynamo("sub_contractors");
			$sub_contractors_array = $sub_contractors_object->getAll("INNER JOIN sub_contractor_work_category ON sub_contractors.id = sub_contractor_work_category.sub_contractor_id ORDER BY sub_contractors.first_name");
			
			$work_sub_contractors_array = array();
			for($i=0;$i<count($sub_contractors_array);$i++)
			{
				//if($subcontractors_assign_array[$sub_contractors_array[$i]['work_category_id']]['sub_contractor_id'] ==  $sub_contractors_array[$i]['id'])
				/*if(!is_array($subcontractors_ids_assign_array[$sub_contractors_array[$i]['work_category_id']]))
					$subcontractors_ids_assign_array[$sub_contractors_array[$i]['work_category_id']] = array();
				*/
				
				if(!is_array($subcontractors_ids_assign_array[$sub_contractors_array[$i]['work_category_id']]))
					$subcontractors_ids_assign_array[$sub_contractors_array[$i]['work_category_id']] = array();
					
				if(in_array($sub_contractors_array[$i]['id'],$subcontractors_ids_assign_array[$sub_contractors_array[$i]['work_category_id']]))
				{
					$selected = " checked='checked'";
				}
				else
					$selected = "";
				
				/*if(trim($work_sub_contractors_array[$sub_contractors_array[$i]['work_category_id']]) == '')
					$work_sub_contractors_array[$sub_contractors_array[$i]['work_category_id']] .= "<option value=''>-- Choose Subcontractor --</option>";*/
					
					$work_sub_contractors_array[$sub_contractors_array[$i]['work_category_id']] .= "<label style='float:left;width:150px;padding-right:20px;white-space:nowrap'><input type=\"checkbox\" class=\"sub_contractor_id_sub\" name='' value='".$sub_contractors_array[$i]['id']."'".$selected." /> ".$sub_contractors_array[$i]['first_name']." ".$sub_contractors_array[$i]['last_name']."</label>";
					
					
					
					//$work_sub_contractors_array[$sub_contractors_array[$i]['work_category_id']] .= "<option value='".$sub_contractors_array[$i]['id']."'".$selected.">".$sub_contractors_array[$i]['first_name']." ".$sub_contractors_array[$i]['last_name']."</option>";
			}
		?>
        <div id="editSubcontractorsModal" class="modal hide fade" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                <h3>Edit Subcontractors</h3>
            </div>
            <form id="editSubContractorForm" name="editSubContractorForm" method="post">
                <input type="hidden" id="property_id" name="property_id" value="<?php echo $_REQUEST['propertyId'] ?>" />
                <div class="modal-body">
                    <div id="status-message"></div>
                    
                    <?php
                    for($i=0;$i<count($work_categories_array);$i++)
                    {
                        
                    ?>
                    <div class="control-group">
                        <label for="roomName" class="control-label"><strong><?php print $work_categories_array[$i]['name']; ?></strong>:<?php if(trim($work_sub_contractors_array[$work_categories_array[$i]['id']]) == ''){print " No subcontractor available for this category";} ?></label>
                        <?php
                        if(trim($work_sub_contractors_array[$work_categories_array[$i]['id']]) != '')
                        {
                        ?>
                        <input type="hidden" name="work_category_id_sub_<?php print $i+1; ?>" id="work_category_id_sub_<?php print $i+1; ?>" class="work_category_id_sub" value="<?php print $work_categories_array[$i]['id']; ?>" />
                        <!--<select name="sub_contractor_id_sub_<?php print $i+1; ?>" class="sub_contractor_id_sub" multiple> -->
                            <?php
                                print str_replace("name=''","name='sub_contractor_id_sub_".$work_categories_array[$i]['id']."'",$work_sub_contractors_array[$work_categories_array[$i]['id']]);
                            ?>
							<div style="clear:both;"></div>
                        <!--</select> -->
                        <?php
                        }
                        ?>
                    </div>
                    <?php
                    }
                    ?>
                    
                </div>
                <div class="modal-footer-custom">
                    <button class="btn btn-primary">Save Sub Contractors</button>
                    <button class="btn" data-dismiss="modal" aria-hidden="true">Close</button>
                </div>
            </form>
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
								<div class="report-action"><a href="view_daily_log.html?propertyId=<?php echo $propertyId;?>&id=<?php print $array_reports_estimate["id"]; ?>" class="btn btn-default"><i class="icon-info-sign"></i> View</a></div>
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
								<div class="report-action"><a href="view_property_estimate.html?estimatesId=<?php echo $array_reports_estimate['id'];?>" class="btn btn-default"><i class="icon-info-sign"></i> View</a></div>
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
								<div class="report-action"><a href="view_report.html?reportId=<?php echo $array_reports_estimate['id'];?>" class="btn btn-default"><i class="icon-info-sign"></i> View</a></div>
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
		var estimatesMultiplier = formObj.estimatesMultiplier;
		var userIdElem = formObj.propertyStatus;
		var emailAddressElem = formObj.emailList;
		var estimatesEmailListElem = formObj.estimatesEmailList;
		var propertyTypeElem = formObj.propertyType;
		var jobTypeElem = formObj.jobType;
		var communityElem = formObj.community;
		var statusVal;
		
		if(!baseNameElem) return;
		
		$.validate({
			form: '#addPropertyForm',	
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
							,estimatesMultiplier: estimatesMultiplier.value
							,status: userIdElem.value
							,userId: userIdElem.value
							,emails: emailAddressElem.value
							,propertyType: propertyTypeElem.options[propertyTypeElem.selectedIndex].value
							,jobType: jobTypeElem.options[jobTypeElem.selectedIndex].value
							,estimatesEmailList: estimatesEmailListElem.value
							,community: communityElem.value
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
		
		var formObjSub = document.forms['editSubContractorForm'];
		
		$.validate({	
			form: '#editSubContractorForm',
			modules: 'security',
			onValidate:function() {
			},
			onError: function() {
				statusMsg.innerHTML = getAlert('error', 'Oops! something is wrong. Please check the values below.');
			},
			onSuccess : function() {
				statusMsg.innerHTML = "";
				statusMsg.innerHTML = getAlert('info', 'Please wait while we process your request...');
				
				var sub_contractor_assign = [];
				
				sub_contractor_id_sub = $(".sub_contractor_id_sub");
				//work_category_id_sub = $(".work_category_id_sub");
				
				for(i=0;i<sub_contractor_id_sub.length;i++)
				{
					subcontractor_name = sub_contractor_id_sub.eq(i).attr("name");
					subcontractor_work_category_id = subcontractor_name.replace("sub_contractor_id_sub_","")
					
					//for(j=0;j<sub_contractor_id_sub[i].length;j++)
					{
						if(sub_contractor_id_sub[i].checked)
						{
							sub_contractor_assign.push({sub_contractor_id:sub_contractor_id_sub[i].value,work_category_id:subcontractor_work_category_id});
						}
					}
				}
				
				$.ajax({
					url: baseNameElem.value + "/webservice/edit_sub_contractor_main.php",
					type: "POST",
					data: { property_id: formObjSub.property_id.value
							,data: JSON.stringify(sub_contractor_assign)
						} 
				}).done(function( response ) {
					if(response.success) {
						statusMsg.innerHTML = getAlert('success', response.message);
						$("#editSubcontractorsModal").modal('hide');
					} else {
						if(!response.message || response.message == '' || !response) {
							statusMsg.innerHTML = getAlert('error', "Sorry, there has been a problem processing your request");
							$("#editSubcontractorsModal").modal('hide');
						} else {
							statusMsg.innerHTML = getAlert('error', response.message);
							$("#editSubcontractorsModal").modal('hide');
						}
					}
				});
			  
				return false;
			}
		});
		
		/*$(".property_images").mouseenter(function() {
			$(this).find(".set_main_image").fadeIn();
		});
		$(".property_images").mouseleave(function() {
			$(this).find(".set_main_image").fadeOut();
		});
		
		$(".property_images").click(function() {
			 image_id = $(this).find(".image_id").html();
			 statusMsg.innerHTML = getAlert('error', "Processing requst...");
			 $.ajax({url:"webservice/set_main_image.php?image_id="+image_id,success:function(result){
				statusMsg.innerHTML = getAlert('success', "Image successfully set as main image");
			  }});
		});*/
	};
	
	function setMainImage(imageUrl)
	{
		var statusMsg = document.getElementById('status-message');
		statusMsg.innerHTML = getAlert('error', "Processing requst...");
		 $.ajax({url:"webservice/set_main_image.php?image_name="+imageUrl,success:function(result){
			statusMsg.innerHTML = getAlert('success', "Image successfully set as main image");
		  }});
	}
	</script>
	
<?php
} else {
?>
	Oops! something went wrong.
<?php
}
?>