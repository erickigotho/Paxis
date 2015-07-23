<?php
require_once(dirname(dirname(dirname(__FILE__))) . '/modules/Dynamo.class.php');
require_once(dirname(dirname(dirname(__FILE__))) . '/modules/Tools.class.php');

$tools = new Tools;
$daily_logs_today_progress_object = new Dynamo("daily_logs_today_progress");
$dailyLogsObject = new Dynamo("daily_logs");

if(trim($_REQUEST['estimate']) == "true")
	$estimate = "&estimate=true";
else
	$estimate = "";
		
if(trim($_REQUEST["propertyId"]) != '')
{
	$dailyLogsArray = $dailyLogsObject->getAll("WHERE DATE_FORMAT(timestamp,'%Y-%m-%d') = '".date("Y-m-d",time())."' AND property_id = ".$_REQUEST["propertyId"]);
	if(count($dailyLogsArray) > 0)
	{
		?>
        <script type="text/javascript">
		window.location.href = "edit_daily_log.html?propertyId=<?php print $_REQUEST["propertyId"]; ?>&id=<?php print $dailyLogsArray[0]["id"].$estimate; ?>";
		</script>
        <?php
		exit;
	}
	$_REQUEST['id'] = $_REQUEST["propertyId"];
	$propertiesObject = new Dynamo("properties");
	$propertyArray = $propertiesObject->getOne();
}
else
{
	print "You'll need property ID in order to proceed";
	exit;
}

if(trim($_POST["company"]) != '' && trim($_POST["report_no"]) != '' && trim($_POST["log_date"]) != '' && trim($_POST["project_community"]) != '' && 
trim($_POST["total_field_workers"]) != '' && trim($_POST["superintendent"]) != '' && trim($_POST["weather"]) != '')
{
	$log_date = strtotime($_REQUEST['log_date']);
	$_REQUEST['log_date'] = date("Y-m-d",$log_date);
	
	$_REQUEST['daily_logs_id'] = $maxId = $dailyLogsObject->getMaxId();
	$_REQUEST['user_id'] = $_SESSION['user_id'];
	
	if($dailyLogsObject->add())
	{
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
		
		$daily_logs_images_object = new Dynamo("daily_logs_images");
		
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
	}
	
	?>
    <script type="text/javascript">
		window.location.href = "edit_daily_log.html?propertyId=<?php print $_REQUEST['propertyId']; ?>&id=<?php print $_REQUEST['daily_logs_id']; ?>&success=true<?php print $estimate; ?>";
	</script>
    <?php
	exit;
}
?>
	<form method="POST" class="form-horizontal" id="addDailyLog" name="addDailyLog" enctype="multipart/form-data">
	<input type="hidden" id="baseName" name="baseName" value="<?php echo __BASENAME__; ?>" />
	<input type="hidden" id="userId" name="userId" value="<?php echo $_SESSION['user_id']; ?>" />
    <?php
    if(trim($estimate) != '')
	{
	?>
    <input type="hidden" name="estimate" value="true" />
    <?php	
	}
	?>
		<div class="pull-left"><h4>Add Daily Log</h4></div>
		<div class="pull-right"><button class="btn btn-warning" type="submit">Submit Changes</button> &nbsp; <a href="<?php if(trim($estimate) != ''){?>edit_property_estimate.html?propertyId=<?php print $_REQUEST['propertyId']; }else{ ?>edit_property.html?propertyId=<?php print $_REQUEST['propertyId']; } ?>" class="btn btn-default">Cancel</a></div>
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
                        <input type="text" name="company" id="company" class="form-control" placeholder="Company" value="Paxis" data-validation="required" required="required"  data-validation-error-msg="Company is a required field" />
                    </div>
                </div>
                <div class="control-group">
                    <label for="report_no" class="control-label">REPORT #</label>
                    <div class="controls">
                        <input type="text" name="report_no" id="report_no" class="form-control" placeholder="Report #" value="<?php print date("mdY",time()); ?>" data-validation="required" required="required"  data-validation-error-msg="Please enter a report number." readonly="readonly"  />
                    </div>
                </div>
                <div class="control-group">
                    <label for="log_date" class="control-label">DATE</label>
                    <div class="controls">
                        <input type="text" name="log_date" id="log_date" class="form-control" placeholder="Date" value="<?php print date("m/d/Y",time()); ?>" data-validation="required" required="required"  data-validation-error-msg="Please enter a valid date." readonly="readonly"  />
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
                        <input type="text" name="total_field_workers" id="total_field_workers" class="form-control" placeholder="TOTAL FIELD WORKERS" value="0" data-validation-error-msg="Please enter a Project/Community." readonly="readonly"  />
                    </div>
                </div>
                <div class="control-group">
                    <label for="superintendent" class="control-label">SUPERINTENDENT</label>
                    <div class="controls">
                        <input type="text" name="superintendent" id="superintendent" class="form-control" placeholder="SUPERINTENDENT/PROJECT MANAGER" value="PAXIS" data-validation="required" required="required"  data-validation-error-msg="Please enter a Superintendent."  />
                    </div>
                </div>
                <div class="control-group">
                    <label for="weather" class="control-label">WEATHER</label>
                    <div class="controls">
                        <input type="text" name="weather" id="weather" class="form-control" placeholder="WEATHER" value="" data-validation="required" required="required"  data-validation-error-msg="Please enter the weather"  />
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
                        <input type="text" name="painters" id="painters" class="form-control workers_no" placeholder="PAINTERS" value=""  />
                    </div>
                </div>
                <div class="control-group">
                    <label for="siding" class="control-label">SIDING</label>
                    <div class="controls">
                        <input type="text" name="siding" id="siding" class="form-control workers_no" placeholder="SIDING" value=""  />
                    </div>
                </div>
                <div class="control-group">
                    <label for="plumbers" class="control-label">PLUMBERS</label>
                    <div class="controls">
                        <input type="text" name="plumbers" id="plumbers" class="form-control workers_no" placeholder="PLUMBERS" value=""  />
                    </div>
                </div>
                <div class="control-group">
                    <label for="sheet_rock" class="control-label">SHEET ROCK</label>
                    <div class="controls">
                        <input type="text" name="sheet_rock" id="sheet_rock" class="form-control workers_no" placeholder="SHEET ROCK" value=""  />
                    </div>
                </div>
                <div class="control-group">
                    <label for="framers" class="control-label">FRAMERS</label>
                    <div class="controls">
                        <input type="text" name="framers" id="framers" class="form-control workers_no" placeholder="FRAMERS" value=""  />
                    </div>
                </div>
                <div class="control-group">
                    <label for="super" class="control-label">SUPER</label>
                    <div class="controls">
                        <input type="text" name="super" id="super" class="form-control workers_no" placeholder="SUPER" value=""  />
                    </div>
                </div>
                <div class="control-group">
                    <label for="other" class="control-label">OTHER</label>
                    <div class="controls">
                        <input type="text" name="other" id="other" class="form-control workers_no" placeholder="OTHER" value=""  />
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
                        <input type="text" name="roofers" id="roofers" class="form-control workers_no" placeholder="ROOFERS" value="" />
                    </div>
                </div>
                <div class="control-group">
                    <label for="electricals" class="control-label">ELECTRICAL</label>
                    <div class="controls">
                        <input type="text" name="electricals" id="electricals" class="form-control workers_no" placeholder="ELECTRICAL" value=""  />
                    </div>
                </div>
                <div class="control-group">
                    <label for="graders" class="control-label">GRADERS</label>
                    <div class="controls">
                        <input type="text" name="graders" id="graders" class="form-control workers_no" placeholder="GRADERS" value="" />
                    </div>
                </div>
                <div class="control-group">
                    <label for="hvac" class="control-label">HVAC</label>
                    <div class="controls">
                        <input type="text" name="hvac" id="hvac" class="form-control workers_no" placeholder="HVAC" value="" />
                    </div>
                </div>
                <div class="control-group">
                    <label for="concrete" class="control-label">CONCRETE</label>
                    <div class="controls">
                        <input type="text" name="concrete" id="concrete" class="form-control workers_no" placeholder="CONCRETE" value="" />
                    </div>
                </div>
                <div class="control-group">
                    <label for="concrete" class="control-label">LABORER</label>
                    <div class="controls">
                        <input type="text" name="laborer" id="laborer" class="form-control workers_no" placeholder="LABORER" value="" />
                    </div>
                </div>
            </div>
       </div>
       <div class="clear"></div>
       <p>&nbsp;</p>
       <div style="text-align:center;">
       DAILY HUDDLE (INCLUDE GENERAL/GLOBAL ISSUES CONCERNING PROJECT THIS DAY)<br />
       <p><textarea name="daily_huddle" id="daily_huddle" class="textarea_daily_log"></textarea></p>
       <p>&nbsp;</p>
       <h2>TODAY'S PROGRESS</h2>
       <div id="todays_progress">
       	<input type="hidden" name="number_reached" id="number_reached" value="1" />
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
                <br />
                <div class="span3 unit_today">
                	Weather Delay<input type="checkbox" name="weather_delay_1" id="weather_delay_1" style="visibility:hidden;" /><button onclick="return changeStatus('weather_delay_1','weather_delay_button_1');" class="btn btn-warning glyphicon glyphicon-warning-sign" id="weather_delay_button_1"></button>
                </div>
                <div class="span3 unit_today">
                	Missed Inspection<input type="checkbox" name="missed_inspection_1" id="missed_inspection_1" style="visibility:hidden;" /><button onclick="return changeStatus('missed_inspection_1','missed_inspection_button_1');" class="btn btn-warning glyphicon glyphicon-warning-sign" id="missed_inspection_button_1"></button>
                </div>
                <div class="span3 unit_today">
                	Vendor No Show<input type="checkbox" name="vendor_no_show_1" id="vendor_no_show_1" style="visibility:hidden;" /><button onclick="return changeStatus('vendor_no_show_1','vendor_no_show_button_1');" class="btn btn-warning glyphicon glyphicon-warning-sign" id="vendor_no_show_button_1"></button>
                 </div>
                 <div class="clear"></div>
                <div class="span3 unit_today">
                	Vendor Did Not Finish<input type="checkbox" name="vendor_did_not_finish_1" id="vendor_did_not_finish_1" style="visibility:hidden;" /><button onclick="return changeStatus('vendor_did_not_finish_1','vendor_did_not_finish_button_1');" class="btn btn-warning glyphicon glyphicon-warning-sign" id="vendor_did_not_finish_button_1"></button>
                 </div>
                <div class="span3 unit_today">
                	Site Issues<input type="checkbox" name="site_issues_1" id="site_issues_1" style="visibility:hidden;" /><button onclick="return changeStatus('site_issues_1','site_issues_button_1');" class="btn btn-warning glyphicon glyphicon-warning-sign" id="site_issues_button_1"></button>
                </div>
                
                <div class="span3 unit_today">
                	Vendor Mistake<input type="checkbox" name="vendor_mistake_1" id="vendor_mistake_1" style="visibility:hidden;" /><button onclick="return changeStatus('vendor_mistake_1','vendor_mistake_button_1');" class="btn btn-warning glyphicon glyphicon-warning-sign" id="vendor_mistake_button_1"></button>
                 </div>
                 <div class="clear"></div>
                <div class="span3 unit_today">
                	Other<input type="checkbox" name="other_1" id="other_1" style="visibility:hidden;" /><button onclick="return changeStatus('other_1','other_button_1');" class="btn btn-warning glyphicon glyphicon-warning-sign" id="other_button_1"></button>
                </div>
                <div class="clear"></div>
            </div>
       </div>
       <p>&nbsp;</p>
       <p><a href=""><button class="btn btn-warning" onclick="return add_another_row();"><i class="icon-plus icon-white"></i> Add Another Row</button></a></p>
       <p>&nbsp;</p>
       <p>&nbsp;</p>
       <p>PROBLEMS DISCUSSIONS & DELAYS<br />
       <p><textarea name="problem_discussion_delays" id="problem_discussion_delays" class="textarea_daily_log"></textarea></p>
       <p>RENTAL EQUIPMENT & MATERIAL DELIVERIES/RETURNS<br />
       <p><textarea name="rental_equipment_material_deliveries" id="rental_equipment_material_deliveries" class="textarea_daily_log"></textarea></p>
       <p>GENERAL COMMENTS<br />
       <p><textarea name="general_comments" id="general_comments" class="textarea_daily_log"></textarea></p>
       <p>UPLOAD IMAGES</p>	
       <p><input type="file" name="image_name[]" multiple /></p>
       </div>
		<input type="hidden" name="closed" id="closed" value="0" />
        <input type="hidden" name="property_id" id="property_id" value="<?php print $_REQUEST["propertyId"]; ?>" />
	<form>

	
	<script type="text/javascript">
		window.onload = function() {
			var checkboxes = new Array(); 
			checkboxes = document['addDailyLog'].getElementsByTagName('input');
			
			for (var i=0; i<checkboxes.length; i++)  
			{
				if (checkboxes[i].type == 'checkbox')   
				{
				  checkboxes[i].checked = false;
				}
			}	
		};
		
		function add_another_row()
		{
			number_reached = parseInt($("#number_reached").val());
			number_reached += 1;
			
			html = '<p>&nbsp;</p><div class="todays_unit"><div class="span3 unit_today"><strong>Lot</strong><br /><br /><div class="inner_border"><input type="text" name="lot_'+number_reached+'" id="lot_'+number_reached+'" class="span3" /></div></div><div class="span3 unit_today"><strong>Job Status</strong><br /><br /><div class="inner_border"><select name="job_status_'+number_reached+'" id="job_status_'+number_reached+'" class="span3"><option name="GRADE">GRADE</option><option name="CONCRETE">CONCRETE</option><option name="FRAMING">FRAMING</option><option name="MECHANICALS">MECHANICALS</option><option name="SHEETROCK">SHEETROCK</option><option name="FINISHES">FINISHES</option><option name="C/O">C/O</option></select></div></div><div class="span4 unit_today">Notes<br /><br /><div class="inner_border"><textarea name="notes_'+number_reached+'" id="notes_'+number_reached+'" class="span4"></textarea></div></div><div class="clear"></div><br /><div class="span3 unit_today">Weather Delay<input type="checkbox" name="weather_delay_'+number_reached+'" id="weather_delay_'+number_reached+'" style="visibility:hidden;" /><button onclick="return changeStatus(\'weather_delay_'+number_reached+'\',\'weather_delay_button_'+number_reached+'\');" class="btn btn-warning glyphicon glyphicon-warning-sign" id="weather_delay_button_'+number_reached+'"></button></div><div class="span3 unit_today">Missed Inspection<input type="checkbox" name="missed_inspection_'+number_reached+'" id="missed_inspection_'+number_reached+'" style="visibility:hidden;" /><button onclick="return changeStatus(\'missed_inspection_'+number_reached+'\',\'missed_inspection_button_'+number_reached+'\');" class="btn btn-warning glyphicon glyphicon-warning-sign" id="missed_inspection_button_'+number_reached+'"></button></div><div class="span3 unit_today">Vendor No Show<input type="checkbox" name="vendor_no_show_'+number_reached+'" id="vendor_no_show_'+number_reached+'" style="visibility:hidden;" /><button onclick="return changeStatus(\'vendor_no_show_'+number_reached+'\',\'vendor_no_show_button_'+number_reached+'\');" class="btn btn-warning glyphicon glyphicon-warning-sign" id="vendor_no_show_button_'+number_reached+'"></button></div><div class="clear"></div><div class="span3 unit_today">Vendor Did Not Finish<input type="checkbox" name="vendor_did_not_finish_'+number_reached+'" id="vendor_did_not_finish_'+number_reached+'" style="visibility:hidden;" /><button onclick="return changeStatus(\'vendor_did_not_finish_'+number_reached+'\',\'vendor_did_not_finish_button_'+number_reached+'\');" class="btn btn-warning glyphicon glyphicon-warning-sign" id="vendor_did_not_finish_button_'+number_reached+'"></button></div><div class="span3 unit_today">Site Issues<input type="checkbox" name="site_issues_'+number_reached+'" id="site_issues_'+number_reached+'" style="visibility:hidden;" /><button onclick="return changeStatus(\'site_issues_'+number_reached+'\',\'site_issues_button_'+number_reached+'\');" class="btn btn-warning glyphicon glyphicon-warning-sign" id="site_issues_button_'+number_reached+'"></button></div><div class="span3 unit_today">Vendor Mistake<input type="checkbox" name="vendor_mistake_'+number_reached+'" id="vendor_mistake_'+number_reached+'" style="visibility:hidden;" /><button onclick="return changeStatus(\'vendor_mistake_'+number_reached+'\',\'vendor_mistake_button_'+number_reached+'\');" class="btn btn-warning glyphicon glyphicon-warning-sign" id="vendor_mistake_button_'+number_reached+'"></button></div><div class="clear"></div><div class="span3 unit_today">Other<input type="checkbox" name="other_'+number_reached+'" id="other_'+number_reached+'" style="visibility:hidden;" /><button onclick="return changeStatus(\'other_'+number_reached+'\',\'other_button_'+number_reached+'\');" class="btn btn-warning glyphicon glyphicon-warning-sign" id="other_button_'+number_reached+'"></button></div><div class="clear"></div></div>';
			
			$("#todays_progress").append(html);
			$("#number_reached").val(number_reached);
			return false;	
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
	</script>