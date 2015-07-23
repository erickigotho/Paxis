<?php
require_once(dirname(dirname(dirname(__FILE__))) . '/modules/Dynamo.class.php');
require_once(dirname(dirname(dirname(__FILE__))) . '/modules/Tools.class.php');

$dailyLogsObject = new Dynamo("daily_logs");
$propertiesObject = new Dynamo("properties");
$daily_logs_today_progress_object = new Dynamo("daily_logs_today_progress");
$daily_logs_images_object = new Dynamo("daily_logs_images");
$tools = new Tools;

if(trim($_REQUEST['estimate']) == "true")
	$estimate = "&estimate=true";
else
	$estimate = "";
	
if(trim($_REQUEST["id"]) != '')
{
	$_REQUEST["daily_logs_id"] = $_REQUEST["id"];
	$daily_logs_array = $dailyLogsObject->getOne();
	
	if($daily_logs_array["closed"] == 1)
	{
		?>
        <script type="text/javascript">
		window.location.href = "view_daily_log.html?propertyId=<?php print $_REQUEST["propertyId"]; ?>&id=<?php print $_REQUEST["id"].$estimate; ?>";
		</script>
        <?php
	}
}
else
{
	header("Location: add_daily_log.html?propertyId=".$_REQUEST["propertyId"].$estimate);
	exit;
}

if(trim($_REQUEST["propertyId"]) != '')
{
	$_REQUEST['id'] = $_REQUEST["propertyId"];
	$propertyArray = $propertiesObject->getOne();
}
else
{
	print "You'll need property ID in order to proceed"	;
	exit;
}

if(trim($_GET['action']) != '' && trim($_GET['imageId']) != '')
{
	$query = "DELETE FROM daily_logs_images WHERE id = ".$_GET['imageId'];
	$daily_logs_images_object->customExecuteQuery($query);
}

if(trim($_POST["company"]) != '' && trim($_POST["report_no"]) != '' && trim($_POST["log_date"]) != '' && trim($_POST["project_community"]) != '' && 
trim($_POST["total_field_workers"]) != '' && trim($_POST["superintendent"]) != '' && trim($_POST["weather"]) != '')
{
	$log_date = strtotime($_REQUEST['log_date']);
	$_REQUEST['log_date'] = date("Y-m-d",$log_date);
	
	$dailyLogsObject = new Dynamo("daily_logs");
	$_REQUEST['id'] = $_REQUEST['daily_logs_id'];
	$_REQUEST['user_id'] = $_SESSION['user_id'];
	
	if($dailyLogsObject->edit())
	{
		$query = "DELETE FROM daily_logs_today_progress WHERE daily_logs_id = ".$_REQUEST['daily_logs_id'];
		$daily_logs_today_progress_object->customExecuteQuery($query);
		
		for($i=1;$i<30000;$i++)
		{
			if(trim($_REQUEST['notes_'.$i]) != '' || trim($_REQUEST['lot_'.$i]) != '')
			{
				$_REQUEST["lot"] = $_REQUEST["lot_".$i];
				$_REQUEST["job_status"] = $_REQUEST["job_status_".$i];
				$_REQUEST["notes"] = $_REQUEST["notes_".$i];
				if($_REQUEST["weather_delay_".$i])
					$_REQUEST["weather_delay"] = 1;
				else
					$_REQUEST["weather_delay"] = 0;
				
				if($_REQUEST["missed_inspection_".$i])
					$_REQUEST["missed_inspection"] = 1;
				else
					$_REQUEST["missed_inspection"] = 0;
				
				if($_REQUEST["vendor_no_show_".$i])
					$_REQUEST["vendor_no_show"] = 1;
				else
					$_REQUEST["vendor_no_show"] = 0;
					
				if($_REQUEST["vendor_did_not_finish_".$i])	
					$_REQUEST["vendor_did_not_finish"] = 1;
				else
					$_REQUEST["vendor_did_not_finish"] = 0;
					
				if($_REQUEST["site_issues_".$i])
					$_REQUEST["site_issues"] = 1;
				else
					$_REQUEST["site_issues"] = 0;
				
				if($_REQUEST["vendor_mistake_".$i])	
					$_REQUEST["vendor_mistake"] = 1;
				else
					$_REQUEST["vendor_mistake"] = 0;
				
				if($_REQUEST["other_".$i])
					$_REQUEST["other"] = 1;
				else
					$_REQUEST["other"] = 0;
				
				$daily_logs_today_progress_object->add();
			}
			else
				break;
		}
		
		if(count($_REQUEST) > 0)
		{
			foreach($_REQUEST as $key => $value)
			{
				if(stristr($key,"image_"))
				{
					if(trim($value) != '')
					{
						$query = "UPDATE daily_logs_images SET image_notes = '".addslashes($value)."' WHERE id = ".str_replace("image_","",$key);
						$daily_logs_images_object->customExecuteQuery($query);
					}
				}
			}
		}
		
		//image upload
		if(trim($_FILES['image_name']['name'][0]) != '')
		{
			for($i=0;$i<count($_FILES['image_name']['name']);$i++)
			{
				$filename = $_FILES['image_name']['name'][$i] = strtolower($_FILES['image_name']['name'][$i]);
				
				$file_ext = strrchr($filename, '.');
				
				$whitelist = array(".jpg",".jpeg",".gif",".png"); 
				
				if(!in_array($file_ext, $whitelist)) 
					die("Please upload either a jpeg, gif or png");
					
				$_FILES['image_name']['name'][$i] = $tools->replaceBadCharactersImage($_FILES['image_name']['name'][$i]);
				$arrayCheckImageExists = $daily_logs_images_object->getAll("WHERE image_name = \"".addslashes($_FILES['image_name']['name'][$i])."\"");	
				if(count($arrayCheckImageExists) > 0)
				{
					$_FILES['image_name']['name'][$i] = str_replace(".",time().".",$_FILES['image_name']['name'][$i]);
				}
				
				if(trim($_FILES['image_name']['name'][$i]) != '')
				{
					$_FILES['image_name']['name'][$i] = strtolower($_FILES['image_name']['name'][$i]);
				}
				
				if(move_uploaded_file($_FILES['image_name']['tmp_name'][$i],"images/daily_log_uploads/".$_FILES['image_name']['name'][$i]))
				{	
					$query = "INSERT INTO daily_logs_images (`daily_logs_id`,`image_name`,`timestamp`) VALUES(".$_REQUEST['daily_logs_id'].",\"".addslashes($_FILES['image_name']['name'][$i])."\",NOW())";
					$daily_logs_images_object->customExecuteQuery($query);	
				}
			}
		}
		?>
        <script type="text/javascript">
			window.onload = function() {
				var statusMsg = document.getElementById('status-message');
				statusMsg.innerHTML = getAlert('success', "You've successfully edited your Daily Log");
			};
			<?php
			if($_REQUEST['closed'] == 1)
			{
				if(trim($estimate) != '')
				{
			?>
				window.location.href = "edit_property_estimate.html?propertyId=<?php print $_REQUEST["propertyId"]; ?>";
			<?php		
				}
				else
				{
			?>
				window.location.href = "edit_property.html?propertyId=<?php print $_REQUEST["propertyId"]; ?>";
			<?php
				}
			}
			?>
		</script>
		<?php
	}
}

$_REQUEST["id"] = $_REQUEST["daily_logs_id"];
$daily_logs_array = $dailyLogsObject->getOne();

?>
	<form method="POST" class="form-horizontal" id="addDailyLog" name="addDailyLog" action="edit_daily_log.html?propertyId=<?php print $_REQUEST["propertyId"]; ?>&id=<?php print $_REQUEST["daily_logs_id"]; ?>" enctype="multipart/form-data">
    <?php
    if(trim($estimate) != '')
	{
	?>
    <input type="hidden" name="estimate" value="true" />
    <?php	
	}
	?>
	<input type="hidden" id="baseName" name="baseName" value="<?php echo __BASENAME__; ?>" />
	<input type="hidden" id="userId" name="userId" value="<?php echo $_SESSION['user_id']; ?>" />
		<div class="pull-left"><h4><?php if(count($daily_logs_array) > 0) print "Edit";else print "Add"; ?> Daily Log</h4></div>
		<div class="pull-right"><button class="btn btn-warning" type="submit">Submit Changes</button> &nbsp; <button onClick="saveandcloselog();" class="btn btn-danger" type="submit">Save & Close Log</button> &nbsp; <a href="<?php if(trim($estimate) != ''){?>edit_property_estimate.html?propertyId=<?php print $_REQUEST['propertyId']; }else{ ?>edit_property.html?propertyId=<?php print $_REQUEST['propertyId']; } ?>" class="btn btn-default">Cancel</a></div>
		<div class="clearfix"></div>
        <div id="status-message"></div>
		<div class="row-fluid">
        	<div class="property-left">
            	<div class="control-group">
                    <label for="address" class="control-label"><strong>&nbsp;</strong></label>
                    <div class="controls">
                        <strong>&nbsp;</strong>
                    </div>
                </div>
                <div class="control-group">
                    <label for="company" class="control-label">COMPANY</label>
                    <div class="controls">
                        <input type="text" name="company" id="company" class="form-control" placeholder="Company" value="<?php if(count($daily_logs_array) > 0) print $daily_logs_array["company"]; else print "Paxis"; ?>" data-validation="required" required="required"  data-validation-error-msg="Company is a required field" />
                    </div>
                </div>
                <div class="control-group">
                    <label for="report_no" class="control-label">REPORT #</label>
                    <div class="controls">
                        <input type="text" name="report_no" id="report_no" class="form-control" placeholder="Report #" value="<?php if(count($daily_logs_array) > 0) print $daily_logs_array["report_no"]; else print date("mdY",time()); ?>" data-validation="required" required="required"  data-validation-error-msg="Please enter a report number." readonly="readonly"  />
                    </div>
                </div>
                <div class="control-group">
                    <label for="log_date" class="control-label">DATE</label>
                    <div class="controls">
                        <input type="text" name="log_date" id="log_date" class="form-control" placeholder="Date" value="<?php if(count($daily_logs_array) > 0) print $daily_logs_array["log_date"]; else print date("m/d/Y",time()); ?>" data-validation="required" required="required"  data-validation-error-msg="Please enter a valid date." readonly="readonly"  />
                    </div>
                </div>
                <div class="control-group">
                    <label for="project_community" class="control-label">PROJECT/COMMUNITY</label>
                    <div class="controls">
                        <input type="text" name="project_community" id="project_community" class="form-control" placeholder="PROJECT/COMMUNITY" value="<?php print $propertyArray["community"]; ?>" data-validation="required" required="required"  data-validation-error-msg="Please enter a Project/Community."  />
                    </div>
                </div>
                <div class="control-group">
                    <label for="total_field_workers" class="control-label">TOTAL FIELD WORKERS</label>
                    <div class="controls">
                        <input type="text" name="total_field_workers" id="total_field_workers" class="form-control" placeholder="TOTAL FIELD WORKERS" value="<?php if(count($daily_logs_array) > 0) print $daily_logs_array["total_field_workers"]; else print "0"; ?>" data-validation-error-msg="Please enter a Project/Community." readonly="readonly"  />
                    </div>
                </div>
                <div class="control-group">
                    <label for="superintendent" class="control-label">SUPERINTENDENT</label>
                    <div class="controls">
                        <input type="text" name="superintendent" id="superintendent" class="form-control" placeholder="SUPERINTENDENT/PROJECT MANAGER" value="<?php if(count($daily_logs_array) > 0) print $daily_logs_array["superintendent"]; else print "PAXIS"; ?>" data-validation="required" required="required"  data-validation-error-msg="Please enter a Superintendent."  />
                    </div>
                </div>
                <div class="control-group">
                    <label for="weather" class="control-label">WEATHER</label>
                    <div class="controls">
                        <input type="text" name="weather" id="weather" class="form-control" placeholder="WEATHER" value="<?php if(count($daily_logs_array) > 0) print $daily_logs_array["weather"]; ?>" data-validation="required" required="required"  data-validation-error-msg="Please enter the weather"  />
                    </div>
                </div>
            </div>
            
            <!-- 2nd column -->
            <div class="property-left">
                <div class="control-group">
                    <label for="address" class="control-label"><strong>Sub</strong></label>
                    <div class="controls">
                        <strong>Count</strong>
                    </div>
                </div>
                <div class="control-group">
                    <label for="address" class="control-label">PAINTERS</label>
                    <div class="controls">
                        <input type="text" name="painters" id="painters" class="form-control workers_no" placeholder="PAINTERS" value="<?php if(count($daily_logs_array) > 0) print $daily_logs_array["painters"]; ?>"  />
                    </div>
                </div>
                <div class="control-group">
                    <label for="siding" class="control-label">SIDING</label>
                    <div class="controls">
                        <input type="text" name="siding" id="siding" class="form-control workers_no" placeholder="SIDING" value="<?php if(count($daily_logs_array) > 0) print $daily_logs_array["siding"]; ?>"  />
                    </div>
                </div>
                <div class="control-group">
                    <label for="plumbers" class="control-label">PLUMBERS</label>
                    <div class="controls">
                        <input type="text" name="plumbers" id="plumbers" class="form-control workers_no" placeholder="PLUMBERS" value="<?php if(count($daily_logs_array) > 0) print $daily_logs_array["plumbers"]; ?>"  />
                    </div>
                </div>
                <div class="control-group">
                    <label for="sheet_rock" class="control-label">SHEET ROCK</label>
                    <div class="controls">
                        <input type="text" name="sheet_rock" id="sheet_rock" class="form-control workers_no" placeholder="SHEET ROCK" value="<?php if(count($daily_logs_array) > 0) print $daily_logs_array["sheet_rock"]; ?>"  />
                    </div>
                </div>
                <div class="control-group">
                    <label for="framers" class="control-label">FRAMERS</label>
                    <div class="controls">
                        <input type="text" name="framers" id="framers" class="form-control workers_no" placeholder="FRAMERS" value="<?php if(count($daily_logs_array) > 0) print $daily_logs_array["framers"]; ?>"  />
                    </div>
                </div>
                <div class="control-group">
                    <label for="super" class="control-label">SUPER</label>
                    <div class="controls">
                        <input type="text" name="super" id="super" class="form-control workers_no" placeholder="SUPER" value="<?php if(count($daily_logs_array) > 0) print $daily_logs_array["super"]; ?>"  />
                    </div>
                </div>
                <div class="control-group">
                    <label for="other" class="control-label">OTHER</label>
                    <div class="controls">
                        <input type="text" name="other" id="other" class="form-control workers_no" placeholder="OTHER" value="<?php if(count($daily_logs_array) > 0) print $daily_logs_array["other"]; ?>"  />
                    </div>
                </div>
            </div>
            
            <!-- 3rd column -->
            <div class="property-left">
                <div class="control-group">
                    <label for="address" class="control-label"><strong>Sub</strong></label>
                    <div class="controls">
                        <strong>Count</strong>
                    </div>
                </div>
                <div class="control-group">
                    <label for="roofers" class="control-label">ROOFERS</label>
                    <div class="controls">
                        <input type="text" name="roofers" id="roofers" class="form-control workers_no" placeholder="ROOFERS" value="<?php if(count($daily_logs_array) > 0) print $daily_logs_array["roofers"]; ?>" />
                    </div>
                </div>
                <div class="control-group">
                    <label for="electricals" class="control-label">ELECTRICAL</label>
                    <div class="controls">
                        <input type="text" name="electricals" id="electricals" class="form-control workers_no" placeholder="ELECTRICAL" value="<?php if(count($daily_logs_array) > 0) print $daily_logs_array["electricals"]; ?>"  />
                    </div>
                </div>
                <div class="control-group">
                    <label for="graders" class="control-label">GRADERS</label>
                    <div class="controls">
                        <input type="text" name="graders" id="graders" class="form-control workers_no" placeholder="GRADERS" value="<?php if(count($daily_logs_array) > 0) print $daily_logs_array["graders"]; ?>" />
                    </div>
                </div>
                <div class="control-group">
                    <label for="hvac" class="control-label">HVAC</label>
                    <div class="controls">
                        <input type="text" name="hvac" id="hvac" class="form-control workers_no" placeholder="HVAC" value="<?php if(count($daily_logs_array) > 0) print $daily_logs_array["hvac"]; ?>" />
                    </div>
                </div>
                <div class="control-group">
                    <label for="concrete" class="control-label">CONCRETE</label>
                    <div class="controls">
                        <input type="text" name="concrete" id="concrete" class="form-control workers_no" placeholder="CONCRETE" value="<?php if(count($daily_logs_array) > 0) print $daily_logs_array["concrete"]; ?>" />
                    </div>
                </div>
                <div class="control-group">
                    <label for="concrete" class="control-label">LABORER</label>
                    <div class="controls">
                        <input type="text" name="laborer" id="laborer" class="form-control workers_no" placeholder="LABORER" value="<?php if(count($daily_logs_array) > 0) print $daily_logs_array["laborer"]; ?>" />
                    </div>
                </div>
            </div>
       </div>
       <div class="clear"></div>
       <p>&nbsp;</p>
       <div style="text-align:center;">
       DAILY HUDDLE (INCLUDE GENERAL/GLOBAL ISSUES CONCERNING PROJECT THIS DAY)<br />
       <p><textarea name="daily_huddle" id="daily_huddle" class="textarea_daily_log"><?php if(count($daily_logs_array) > 0) print $daily_logs_array["daily_huddle"]; ?></textarea></p>
       <p>&nbsp;</p>
       <h2>TODAY'S PROGRESS</h2>
       <?php
	   		$daily_logs_today_progress_array = $daily_logs_today_progress_object->getAll("WHERE daily_logs_id = ".$_REQUEST["daily_logs_id"]." ORDER BY id ASC");
	   ?>
       <div id="todays_progress">
       	<input type="hidden" name="number_reached" id="number_reached" value="<?php if(count($daily_logs_today_progress_array) > 0) print count($daily_logs_today_progress_array); else print 1; ?>" />
            
            	<?php
					if(count($daily_logs_today_progress_array) > 0)
					{
						for($i=0;$i<count($daily_logs_today_progress_array);$i++)
						{
							$index = $i+1;
						?>
                        <div class="todays_unit">
                        	<div class="span3 unit_today">
                                <strong>Lot</strong><br /><br />
                                <div class="inner_border"><input type="text" name="lot_<?php print $index; ?>" id="lot_<?php print $index; ?>" value="<?php print $daily_logs_today_progress_array[$i]["lot"]; ?>" class="span3" /></div>
                            </div>
                            <div class="span3 unit_today">
                                <strong>Job Status</strong><br /><br />
                                <div class="inner_border"><select name="job_status_<?php print $index; ?>" id="job_status_<?php print $index; ?>" class="span3">
                                <option name="GRADE"<?php if(trim($daily_logs_today_progress_array[$i]["job_status"]) == 'GRADE') print " selected='selected'"; ?>>GRADE</option>
                                <option name="CONCRETE"<?php if(trim($daily_logs_today_progress_array[$i]["job_status"]) == 'CONCRETE') print " selected='selected'"; ?>>CONCRETE</option>
                                <option name="FRAMING"<?php if(trim($daily_logs_today_progress_array[$i]["job_status"]) == 'FRAMING') print " selected='selected'"; ?>>FRAMING</option>
                                <option name="MECHANICALS"<?php if(trim($daily_logs_today_progress_array[$i]["job_status"]) == 'MECHANICALS') print " selected='selected'"; ?>>MECHANICALS</option>
                                <option name="SHEETROCK"<?php if(trim($daily_logs_today_progress_array[$i]["job_status"]) == 'SHEETROCK') print " selected='selected'"; ?>>SHEETROCK</option>
                                <option name="FINISHES"<?php if(trim($daily_logs_today_progress_array[$i]["job_status"]) == 'FINISHES') print " selected='selected'"; ?>>FINISHES</option>
                                <option name="C/O"<?php if(trim($daily_logs_today_progress_array[$i]["job_status"]) == 'C/O') print " selected='selected'"; ?>>C/O</option>
                            </select></div>
                            </div>
                            <div class="span4 unit_today">
                                Notes<br /><br />
                                <div class="inner_border"><textarea name="notes_<?php print $index; ?>" id="notes_<?php print $index; ?>" class="span4"><?php print $daily_logs_today_progress_array[$i]["notes"]; ?></textarea></div>
                            </div>
                            <div class="clear"></div>
                            <br />
                            
                            <div class="span3 unit_today">
                                Weather Delay<input type="checkbox" style="visibility:hidden;" name="weather_delay_<?php print $index; ?>" id="weather_delay_<?php print $index; ?>"<?php if($daily_logs_today_progress_array[$i]["weather_delay"] == 1) print " checked"; ?> /><button onclick="return changeStatus('weather_delay_<?php print $index; ?>','weather_delay_button_<?php print $index; ?>');" class="<?php if($daily_logs_today_progress_array[$i]["weather_delay"] == 1){ ?>glyphicon glyphicon-ok btn btn-warning<?php } else{ ?>btn btn-warning glyphicon glyphicon-warning-sign<?php } ?>" id="weather_delay_button_<?php print $index; ?>"></button>
                            </div>
                            <div class="span3 unit_today">
                                Missed Inspection<input type="checkbox" style="visibility:hidden;" name="missed_inspection_<?php print $index; ?>" id="missed_inspection_<?php print $index; ?>"<?php if($daily_logs_today_progress_array[$i]["missed_inspection"] == 1) print " checked"; ?> /><button onclick="return changeStatus('missed_inspection_<?php print $index; ?>','missed_inspection_button_<?php print $index; ?>');" class="<?php if($daily_logs_today_progress_array[$i]["missed_inspection"] == 1){ ?>glyphicon glyphicon-ok btn btn-warning<?php }else{ ?>btn btn-warning glyphicon glyphicon-warning-sign<?php } ?>" id="missed_inspection_button_<?php print $index; ?>"></button>
                            </div>
                            <div class="span3 unit_today">
                                Vendor No Show<input type="checkbox" style="visibility:hidden;" name="vendor_no_show_<?php print $index; ?>" id="vendor_no_show_<?php print $index; ?>"<?php if($daily_logs_today_progress_array[$i]["vendor_no_show"] == 1) print " checked"; ?> /><button onclick="return changeStatus('vendor_no_show_<?php print $index; ?>','vendor_no_show_button_<?php print $index; ?>');" class="<?php if($daily_logs_today_progress_array[$i]["vendor_no_show"] == 1){ ?>glyphicon glyphicon-ok btn btn-warning<?php }else{ ?>btn btn-warning glyphicon glyphicon-warning-sign<?php } ?>" id="vendor_no_show_button_<?php print $index; ?>"></button>
                             </div>
                             <div class="clear"></div>
                            <div class="span3 unit_today">
                                Vendor Did Not Finish<input type="checkbox" style="visibility:hidden;" name="vendor_did_not_finish_<?php print $index; ?>" id="vendor_did_not_finish_<?php print $index; ?>"<?php if($daily_logs_today_progress_array[$i]["vendor_did_not_finish"] == 1) print " checked"; ?> /><button onclick="return changeStatus('vendor_did_not_finish_<?php print $index; ?>','vendor_did_not_finish_button_<?php print $index; ?>');" class="<?php if($daily_logs_today_progress_array[$i]["vendor_did_not_finish"] == 1){ ?>glyphicon glyphicon-ok btn btn-warning<?php }else{ ?>btn btn-warning glyphicon glyphicon-warning-sign<?php } ?>" id="vendor_did_not_finish_button_<?php print $index; ?>"></button>
                             </div>
                            <div class="span3 unit_today">
                                Site Issues<input type="checkbox" style="visibility:hidden;" name="site_issues_<?php print $index; ?>" id="site_issues_<?php print $index; ?>"<?php if($daily_logs_today_progress_array[$i]["site_issues"] == 1) print " checked"; ?> /><button onclick="return changeStatus('site_issues_<?php print $index; ?>','site_issues_button_<?php print $index; ?>');" class="<?php if($daily_logs_today_progress_array[$i]["site_issues"] == 1){ ?>glyphicon glyphicon-ok btn btn-warning<?php } else{ ?>btn btn-warning glyphicon glyphicon-warning-sign<?php } ?>" id="site_issues_button_<?php print $index; ?>"></button>
                            </div>
                            <div class="span3 unit_today">
                                Vendor Mistake<input type="checkbox" style="visibility:hidden;" name="vendor_mistake_<?php print $index; ?>" id="vendor_mistake_<?php print $index; ?>"<?php if($daily_logs_today_progress_array[$i]["vendor_mistake"] == 1) print " checked"; ?> /><button onclick="return changeStatus('vendor_mistake_<?php print $index; ?>','vendor_mistake_button_<?php print $index; ?>');" class="<?php if($daily_logs_today_progress_array[$i]["vendor_mistake"] == 1){ ?>glyphicon glyphicon-ok btn btn-warning<?php } else{ ?>btn btn-warning glyphicon glyphicon-warning-sign<?php } ?>" id="vendor_mistake_button_<?php print $index; ?>"></button>
                             </div>
                              <div class="clear"></div>
                            <div class="span3 unit_today">
                                Other<input type="checkbox" style="visibility:hidden;" name="other_<?php print $index; ?>" id="other_<?php print $index; ?>"<?php if($daily_logs_today_progress_array[$i]["other"] == 1) print " checked"; ?> />C<button onclick="return changeStatus('other_<?php print $index; ?>','other_button_<?php print $index; ?>');" class="<?php if($daily_logs_today_progress_array[$i]["other"] == 1){ ?>glyphicon glyphicon-ok btn btn-warning<?php } else{ ?>btn btn-warning glyphicon glyphicon-warning-sign<?php } ?>" id="other_button_<?php print $index; ?>"></button>
                            </div>
                            <div class="clear"></div>
                            </div>
                            <p>&nbsp;</p>
                        <?php
						}
					}
					else
					{
				?>
	                <div class="todays_unit">
                        <div class="span3 unit_today">
                            <strong>Lot</strong><br /><br />
                            <div class="inner_border"><input type="text" name="lot_1" id="lot_1" class="span3" /></div>
                        </div>
                        <div class="span3 unit_today">
                            <strong>Job Status</strong><br /><br />
                            <div class="inner_border"><select name="job_status_1" id="job_status_1" class="span3">
                            <option name="GRADE">GRADE</option>
                            <option name="CONCRETE">CONCRETE</option>
                            <option name="FRAMING">FRAMING</option>
                            <option name="MECHANICALS">MECHANICALS</option>
                            <option name="SHEETROCK">SHEETROCK</option>
                            <option name="FINISHES">FINISHES</option>
                            <option name="C/O">C/O</option>
                        </select></div>
                        </div>
                        <div class="span4 unit_today">
                            Notes<br /><br />
                            <div class="inner_border"><textarea name="notes_1" id="notes_1" class="span4"></textarea></div>
                        </div>
                        <div class="clear"></div>
                        
                        <div class="span3 unit_today">
                            Weather Delay<input type="checkbox" style="visibility:hidden;" name="weather_delay_1" id="weather_delay_1" /><button onclick="return changeStatus('weather_delay_1','weather_delay_button_1');" class="btn btn-warning glyphicon glyphicon-warning-sign" id="weather_delay_button_1">
                        </div>
                        <div class="span3 unit_today">
                            Missed Inspection<input type="checkbox" style="visibility:hidden;" name="missed_inspection_1" id="missed_inspection_1" /><button onclick="return changeStatus('missed_inspection_1','missed_inspection_button_1');" class="btn btn-warning glyphicon glyphicon-warning-sign" id="missed_inspection_button_1">
                        </div>
                        <div class="span3 unit_today">
                            Vendor No Show<input type="checkbox" style="visibility:hidden;" name="vendor_no_show_1" id="vendor_no_show_1" /><button onclick="return changeStatus('vendor_no_show_1','vendor_no_show_button_1');" class="btn btn-warning glyphicon glyphicon-warning-sign" id="vendor_no_show_button_1"></button>
                         </div>
                         <div class="clear"></div>
                        <div class="span3 unit_today">
                            Vendor Did Not Finish<input type="checkbox" style="visibility:hidden;" name="vendor_did_not_finish_1" id="vendor_did_not_finish_1" /><button onclick="return changeStatus('vendor_did_not_finish_1','vendor_did_not_finish_button_1');" class="btn btn-warning glyphicon glyphicon-warning-sign" id="vendor_did_not_finish_button_1"></button>
                         </div>
                        <div class="span3 unit_today">
                            Site Issues<input type="checkbox" style="visibility:hidden;" name="site_issues_1" id="site_issues_1" /><button onclick="return changeStatus('site_issues_1','site_issues_button_1');" class="btn btn-warning glyphicon glyphicon-warning-sign" id="site_issues_button_1"></button>
                        </div>
                        <div class="span3 unit_today">
                            Vendor Mistake<input type="checkbox" style="visibility:hidden;" name="vendor_mistake_1" id="vendor_mistake_1" /><button onclick="return changeStatus('vendor_mistake_1','vendor_mistake_button_1');" class="btn btn-warning glyphicon glyphicon-warning-sign" id="vendor_mistake_button_1"></button>
                         </div>
                         <div class="clear"></div>
                        <div class="span3 unit_today">
                            Other<input type="checkbox" style="visibility:hidden;" name="other_1" id="other_1" /><button onclick="return changeStatus('other_1','other_button_1');" class="btn btn-warning glyphicon glyphicon-warning-sign" id="other_button_1"></button>
                        </div>
                        <div class="clear"></div>
                       </div>
                       <p>&nbsp;</p>
                <?php
					}
				?>
       </div>
       <p>&nbsp;</p>
       <p><a href=""><button class="btn btn-warning" onclick="return add_another_row();"><i class="icon-plus icon-white"></i> Add Another Row</button></a></p>
       <p>&nbsp;</p>
       <p>&nbsp;</p>
       <p>PROBLEMS DISCUSSIONS & DELAYS<br />
       <p><textarea name="problem_discussion_delays" id="problem_discussion_delays" class="textarea_daily_log"><?php if(count($daily_logs_array) > 0) print $daily_logs_array["problem_discussion_delays"]; ?></textarea></p>
       <p>RENTAL EQUIPMENT & MATERIAL DELIVERIES/RETURNS<br />
       <p><textarea name="rental_equipment_material_deliveries" id="rental_equipment_material_deliveries" class="textarea_daily_log"><?php if(count($daily_logs_array) > 0) print $daily_logs_array["rental_equipment_material_deliveries"]; ?></textarea></p>
       <p>GENERAL COMMENTS<br />
       <p><textarea name="general_comments" id="general_comments" class="textarea_daily_log"><?php if(count($daily_logs_array) > 0) print $daily_logs_array["general_comments"]; ?></textarea></p>
       <p>UPLOAD IMAGES</p>	
       <p><input type="file" name="image_name[]" multiple /></p>
           <div style="width:75%;margin:0 auto;text-align:left;">
           <a name="image_location"></a>
           <?php
                $daily_logs_images_array = $daily_logs_images_object->getAll("WHERE daily_logs_id = ".$_REQUEST["daily_logs_id"]." ORDER BY timestamp");
                for($i=0;$i<count($daily_logs_images_array);$i++)
                {
                    print "<div style='float:left;padding:10px;'><a href='images/daily_log_uploads/".$daily_logs_images_array[$i]["image_name"]."'><img src='images/daily_log_uploads/".$daily_logs_images_array[$i]["image_name"]."' style='height:100px;margin-right:5px;float:left;' /></a><a class='btn btn-danger' onclick='return confirm(\"Are you sure?\")' href='edit_daily_log.html?action=delete&imageId=".$daily_logs_images_array[$i]['id']."&propertyId=".$_REQUEST["propertyId"]."&id=".$_REQUEST["daily_logs_id"]."#image_location' title='Delete image' style='float:left;'><i class='icon-trash icon-white'></i></a>
					<div class='clear' style='margin-bottom:5px;'></div>
					<textarea name='image_".$daily_logs_images_array[$i]['id']."' placeholder='ADD IMAGE NOTE' style='width:150px;'>".$daily_logs_images_array[$i]["image_notes"]."</textarea> 
					</div>";
                }
				
				if(count($daily_logs_images_array) > 0)
				{
					print "<div class='clear'></div>";	
				}
           ?>
           </div>
       </div>
		<input type="hidden" name="closed" id="closed" value="0" />
        <input type="hidden" name="property_id" id="property_id" value="<?php print $_REQUEST["propertyId"]; ?>" />
        <input type="hidden" name="id" id="id" value="<?php print $_REQUEST["daily_logs_id"]; ?>" />
	<form>

	
	<script type="text/javascript">
		function add_another_row()
		{
			number_reached = parseInt($("#number_reached").val());
			number_reached += 1;
			
			html = '<p>&nbsp;</p><div class="todays_unit"><div class="span3 unit_today"><strong>Lot</strong><br /><br /><div class="inner_border"><input type="text" name="lot_'+number_reached+'" id="lot_'+number_reached+'" class="span3" /></div></div><div class="span3 unit_today"><strong>Job Status</strong><br /><br /><div class="inner_border"><select name="job_status_'+number_reached+'" id="job_status_'+number_reached+'" class="span3"><option name="GRADE">GRADE</option><option name="CONCRETE">CONCRETE</option><option name="FRAMING">FRAMING</option><option name="MECHANICALS">MECHANICALS</option><option name="SHEETROCK">SHEETROCK</option><option name="FINISHES">FINISHES</option><option name="C/O">C/O</option></select></div></div><div class="span4 unit_today">Notes<br /><br /><div class="inner_border"><textarea name="notes_'+number_reached+'" id="notes_'+number_reached+'" class="span4"></textarea></div></div><div class="clear"></div><div class="span3 unit_today">Weather Delay<input type="checkbox" style="visibility:hidden;" name="weather_delay_'+number_reached+'" id="weather_delay_'+number_reached+'" /><button onclick="return changeStatus(\'weather_delay_'+number_reached+'\',\'weather_delay_button_'+number_reached+'\');" class="btn btn-warning glyphicon glyphicon-warning-sign" id="weather_delay_button_'+number_reached+'"></div><div class="span3 unit_today">Missed Inspection<input type="checkbox" style="visibility:hidden;" name="missed_inspection_'+number_reached+'" id="missed_inspection_'+number_reached+'" /><button onclick="return changeStatus(\'missed_inspection_'+number_reached+'\',\'missed_inspection_button_'+number_reached+'\');" class="btn btn-warning glyphicon glyphicon-warning-sign" id="missed_inspection_button_'+number_reached+'"></div><div class="span3 unit_today">Vendor No Show<input type="checkbox" style="visibility:hidden;" name="vendor_no_show_'+number_reached+'" id="vendor_no_show_'+number_reached+'" /><button onclick="return changeStatus(\'vendor_no_show_'+number_reached+'\',\'vendor_no_show_button_'+number_reached+'\');" class="btn btn-warning glyphicon glyphicon-warning-sign" id="vendor_no_show_button_'+number_reached+'"></button></div><div class="clear"></div><div class="span3 unit_today">Vendor Did Not Finish<input type="checkbox" style="visibility:hidden;" name="vendor_did_not_finish_'+number_reached+'" id="vendor_did_not_finish_'+number_reached+'" /><button onclick="return changeStatus(\'vendor_did_not_finish_'+number_reached+'\',\'vendor_did_not_finish_button_'+number_reached+'\');" class="btn btn-warning glyphicon glyphicon-warning-sign" id="vendor_did_not_finish_button_'+number_reached+'"></button></div><div class="span3 unit_today">Site Issues<input type="checkbox" style="visibility:hidden;" name="site_issues_'+number_reached+'" id="site_issues_'+number_reached+'" /><button onclick="return changeStatus(\'site_issues_'+number_reached+'\',\'site_issues_button_'+number_reached+'\');" class="btn btn-warning glyphicon glyphicon-warning-sign" id="site_issues_button_'+number_reached+'"></button></div><div class="span3 unit_today">Vendor Mistake<input type="checkbox" style="visibility:hidden;" name="vendor_mistake_'+number_reached+'" id="vendor_mistake_'+number_reached+'" /><button onclick="return changeStatus(\'vendor_mistake_'+number_reached+'\',\'vendor_mistake_button_'+number_reached+'\');" class="btn btn-warning glyphicon glyphicon-warning-sign" id="vendor_mistake_button_'+number_reached+'"></button></div><div class="clear"></div><div class="span3 unit_today">Other<input type="checkbox" style="visibility:hidden;" name="other_'+number_reached+'" id="other_'+number_reached+'" /><button onclick="return changeStatus(\'other_'+number_reached+'\',\'other_button_'+number_reached+'\');" class="btn btn-warning glyphicon glyphicon-warning-sign" id="other_button_'+number_reached+'"></button></div><div class="clear"></div></div>';
			
			$("#todays_progress").append(html);
			$("#number_reached").val(number_reached);
			return false;	
		}
		
		function saveandcloselog()
		{
			$("#closed").val(1);
			return true;
		}
		
		$(".workers_no").keyup(function() {
			var workers_array = ["painters","siding","plumbers","sheet_rock","framers","super","other","roofers","electricals","graders","hvac","concrete","laborer"];
			
			total_workers = 0;
			
			for(i=0;i<workers_array.length;i++)
			{
				workers_num = parseInt($("#"+workers_array[i]).val());
				
				if(workers_num > 0)
					total_workers += workers_num;
			}
			
			$("#total_field_workers").val(total_workers);
		});
		
		<?php
		if(trim($_GET['success']) == 'true')
		{
		?>
			window.onload = function() {
				var statusMsg = document.getElementById('status-message');
				statusMsg.innerHTML = getAlert('success', "You've successfully added your Daily Log");
			};
		<?php
		}
		?>
		
		function changeStatus(checkbox_id,button_id)
		{
			if(document.getElementById(checkbox_id).checked == true)
			{
				$("#"+button_id).removeClass().addClass("btn btn-warning glyphicon glyphicon-warning-sign");
				document.getElementById(checkbox_id).checked = false;
			}
			else
			{	
				$("#"+button_id).removeClass().addClass("glyphicon glyphicon-ok btn btn-warning");
				document.getElementById(checkbox_id).checked = true;
			}
			
			return false;
		}
		
		function resetStatus(checkbox_id,button_id)
		{
			if($("#"+checkbox_id).prop('checked'))
			{
				$("#"+button_id).removeClass().addClass("glyphicon glyphicon-ok btn btn-warning");
			}
			else
			{	
				$("#"+button_id).removeClass().addClass("btn btn-warning glyphicon glyphicon-warning-sign");
			}
		}
		
		window.onload = function() {
			var checkboxes = new Array(); 
			checkboxes = document['addDailyLog'].getElementsByTagName('input');
			
			for (var i=0; i<checkboxes.length; i++)  
			{
				if(checkboxes[i].type == 'checkbox')   
				{
					var res = checkboxes[i].id.split("_");
					array_index = res[res.length-1];
				  	resetStatus(checkboxes[i].id,checkboxes[i].id.replace("_"+array_index,"_button_"+array_index));
				}
			}	
		};
</script>