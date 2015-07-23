<?php
require_once(dirname(dirname(dirname(__FILE__))) . '/modules/Dynamo.class.php');

$dailyLogsObject = new Dynamo("daily_logs");
$propertiesObject = new Dynamo("properties");
$daily_logs_today_progress_object = new Dynamo("daily_logs_today_progress");
$daily_logs_images_object = new Dynamo("daily_logs_images");

if(trim($_REQUEST['estimate']) == "true")
	$estimate = "&estimate=true";
else
	$estimate = "";
	
if(trim($_REQUEST["id"]) != '')
{
	$_REQUEST["daily_logs_id"] = $_REQUEST["id"];
	$daily_logs_array = $dailyLogsObject->getOne();
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
?>
	<form method="POST" class="form-horizontal" id="addDailyLog">
	<input type="hidden" id="baseName" name="baseName" value="<?php echo __BASENAME__; ?>" />
	<input type="hidden" id="userId" name="userId" value="<?php echo $_SESSION['user_id']; ?>" />
    
		<div class="pull-left"><h4>View Daily Log</h4></div>
		<div class="pull-right"><a href="<?php if(trim($estimate) != ''){?>edit_property_estimate.html?propertyId=<?php print $_REQUEST['propertyId']; }else{ ?>edit_property.html?propertyId=<?php print $_REQUEST['propertyId']; } ?>" class="btn btn-default">Cancel</a></div>
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
                        <input type="text" name="company" id="company" class="form-control" placeholder="Company" value="<?php if(count($daily_logs_array) > 0) print $daily_logs_array["company"]; else print "Paxis"; ?>" data-validation="required" disabled  data-validation-error-msg="Company is a required field" readonly="readonly" />
                    </div>
                </div>
                <div class="control-group">
                    <label for="report_no" class="control-label">REPORT #</label>
                    <div class="controls">
                        <input type="text" name="report_no" id="report_no" class="form-control" placeholder="Report #" value="<?php if(count($daily_logs_array) > 0) print $daily_logs_array["report_no"]; else print date("mdY",time()); ?>" data-validation="required" disabled  data-validation-error-msg="Please enter a report number." readonly="readonly"  />
                    </div>
                </div>
                <div class="control-group">
                    <label for="log_date" class="control-label">DATE</label>
                    <div class="controls">
                        <input type="text" name="log_date" id="log_date" class="form-control" placeholder="Date" value="<?php if(count($daily_logs_array) > 0) print $daily_logs_array["log_date"]; else print date("m/d/Y",time()); ?>" data-validation="required" disabled  data-validation-error-msg="Please enter a valid date." readonly="readonly"  />
                    </div>
                </div>
                <div class="control-group">
                    <label for="project_community" class="control-label">PROJECT/COMMUNITY</label>
                    <div class="controls">
                        <input type="text" name="project_community" id="project_community" class="form-control" placeholder="PROJECT/COMMUNITY" value="<?php print $propertyArray["community"]; ?>" data-validation="required" disabled  data-validation-error-msg="Please enter a Project/Community." readonly="readonly" />
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
                        <input type="text" name="superintendent" id="superintendent" class="form-control" placeholder="SUPERINTENDENT/PROJECT MANAGER" value="<?php if(count($daily_logs_array) > 0) print $daily_logs_array["superintendent"]; else print "PAXIS"; ?>" data-validation="required" disabled  data-validation-error-msg="Please enter a Superintendent." readonly="readonly"  />
                    </div>
                </div>
                <div class="control-group">
                    <label for="weather" class="control-label">WEATHER</label>
                    <div class="controls">
                        <input type="text" name="weather" id="weather" class="form-control" placeholder="WEATHER" value="<?php if(count($daily_logs_array) > 0) print $daily_logs_array["weather"]; ?>" data-validation="required" disabled  data-validation-error-msg="Please enter the weather" readonly="readonly" />
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
                        <input type="text" name="painters" id="painters" class="form-control workers_no" placeholder="PAINTERS" value="<?php if(count($daily_logs_array) > 0) print $daily_logs_array["painters"]; ?>" readonly="readonly"  />
                    </div>
                </div>
                <div class="control-group">
                    <label for="siding" class="control-label">SIDING</label>
                    <div class="controls">
                        <input type="text" name="siding" id="siding" class="form-control workers_no" placeholder="SIDING" value="<?php if(count($daily_logs_array) > 0) print $daily_logs_array["siding"]; ?>" readonly="readonly"  />
                    </div>
                </div>
                <div class="control-group">
                    <label for="plumbers" class="control-label">PLUMBERS</label>
                    <div class="controls">
                        <input type="text" name="plumbers" id="plumbers" class="form-control workers_no" placeholder="PLUMBERS" value="<?php if(count($daily_logs_array) > 0) print $daily_logs_array["plumbers"]; ?>" readonly="readonly"  />
                    </div>
                </div>
                <div class="control-group">
                    <label for="sheet_rock" class="control-label">SHEET ROCK</label>
                    <div class="controls">
                        <input type="text" name="sheet_rock" id="sheet_rock" class="form-control workers_no" placeholder="SHEET ROCK" value="<?php if(count($daily_logs_array) > 0) print $daily_logs_array["sheet_rock"]; ?>" readonly="readonly"  />
                    </div>
                </div>
                <div class="control-group">
                    <label for="framers" class="control-label">FRAMERS</label>
                    <div class="controls">
                        <input type="text" name="framers" id="framers" class="form-control workers_no" placeholder="FRAMERS" value="<?php if(count($daily_logs_array) > 0) print $daily_logs_array["framers"]; ?>" readonly="readonly" />
                    </div>
                </div>
                <div class="control-group">
                    <label for="super" class="control-label">SUPER</label>
                    <div class="controls">
                        <input type="text" name="super" id="super" class="form-control workers_no" placeholder="SUPER" value="<?php if(count($daily_logs_array) > 0) print $daily_logs_array["super"]; ?>" readonly="readonly"  />
                    </div>
                </div>
                <div class="control-group">
                    <label for="other" class="control-label">OTHER</label>
                    <div class="controls">
                        <input type="text" name="other" id="other" class="form-control workers_no" placeholder="OTHER" value="<?php if(count($daily_logs_array) > 0) print $daily_logs_array["other"]; ?>" readonly="readonly"  />
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
                        <input type="text" name="roofers" id="roofers" class="form-control workers_no" placeholder="ROOFERS" value="<?php if(count($daily_logs_array) > 0) print $daily_logs_array["roofers"]; ?>" readonly="readonly" />
                    </div>
                </div>
                <div class="control-group">
                    <label for="electrical" class="control-label">ELECTRICAL</label>
                    <div class="controls">
                        <input type="text" name="electrical" id="electrical" class="form-control workers_no" placeholder="ELECTRICAL" value="<?php if(count($daily_logs_array) > 0) print $daily_logs_array["electrical"]; ?>" readonly="readonly"  />
                    </div>
                </div>
                <div class="control-group">
                    <label for="graders" class="control-label">GRADERS</label>
                    <div class="controls">
                        <input type="text" name="graders" id="graders" class="form-control workers_no" placeholder="GRADERS" value="<?php if(count($daily_logs_array) > 0) print $daily_logs_array["graders"]; ?>" readonly="readonly" />
                    </div>
                </div>
                <div class="control-group">
                    <label for="hvac" class="control-label">HVAC</label>
                    <div class="controls">
                        <input type="text" name="hvac" id="hvac" class="form-control workers_no" placeholder="HVAC" value="<?php if(count($daily_logs_array) > 0) print $daily_logs_array["hvac"]; ?>" readonly="readonly" />
                    </div>
                </div>
                <div class="control-group">
                    <label for="concrete" class="control-label">CONCRETE</label>
                    <div class="controls">
                        <input type="text" name="concrete" id="concrete" class="form-control workers_no" placeholder="CONCRETE" value="<?php if(count($daily_logs_array) > 0) print $daily_logs_array["concrete"]; ?>" readonly="readonly" />
                    </div>
                </div>
                <div class="control-group">
                    <label for="concrete" class="control-label">LABORER</label>
                    <div class="controls">
                        <input type="text" name="laborer" id="laborer" class="form-control workers_no" placeholder="LABORER" value="<?php if(count($daily_logs_array) > 0) print $daily_logs_array["laborer"]; ?>" readonly="readonly" />
                    </div>
                </div>
            </div>
       </div>
       <div class="clear"></div>
       <p>&nbsp;</p>
       <div style="text-align:center;">
       DAILY HUDDLE (INCLUDE GENERAL/GLOBAL ISSUES CONCERNING PROJECT THIS DAY)<br />
       <p><textarea name="daily_huddle" id="daily_huddle" class="textarea_daily_log" readonly="readonly"><?php if(count($daily_logs_array) > 0) print $daily_logs_array["daily_huddle"]; ?></textarea></p>
       <p>&nbsp;</p>
       <h2>TODAY'S PROGRESS</h2>
       <?php
	   		$daily_logs_today_progress_array = $daily_logs_today_progress_object->getAll("WHERE daily_logs_id = ".$_REQUEST["daily_logs_id"]." ORDER BY id ASC");
	   ?>
       <div id="todays_progress">
       	<input type="hidden" name="number_reached" id="number_reached" value="<?php if(count($daily_logs_today_progress_array) > 0) print count($daily_logs_today_progress_array); else print 1; ?>"   />
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
                                <div class="inner_border"><input type="text" name="lot_<?php print $index; ?>" id="lot_<?php print $index; ?>" value="<?php print $daily_logs_today_progress_array[$i]["lot"]; ?>" disabled class="span3" /></div>
                            </div>
                            <div class="span3 unit_today">
                                <strong>Job Status***</strong><br /><br />
                                <div class="inner_border"><select name="job_status_<?php print $index; ?>" id="job_status_<?php print $index; ?>" disabled class="span3">
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
                                <div class="inner_border"><textarea name="notes_<?php print $index; ?>" id="notes_<?php print $index; ?>" readonly="readonly" class="span4"><?php print $daily_logs_today_progress_array[$i]["notes"]; ?></textarea></div>
                            </div>
                            <div class="clear"></div>
                            <br />
                            <div class="span3 unit_today">
                                Weather Delay<input type="checkbox" style="visibility:hidden;" name="weather_delay_<?php print $index; ?>" id="weather_delay_<?php print $index; ?>"<?php if($daily_logs_today_progress_array[$i]["weather_delay"] == 1) print " checked"; ?> disabled /><button onclick="return false;" class="<?php if($daily_logs_today_progress_array[$i]["weather_delay"] == 1){ ?>glyphicon glyphicon-ok btn btn-warning<?php } else{ ?>btn btn-warning glyphicon glyphicon-warning-sign<?php } ?>" id="weather_delay_button_<?php print $index; ?>"></button>
                            </div>
                            <div class="span3 unit_today">
                                Missed Inspection<input type="checkbox" style="visibility:hidden;" name="missed_inspection_<?php print $index; ?>" id="missed_inspection_<?php print $index; ?>"<?php if($daily_logs_today_progress_array[$i]["missed_inspection"] == 1) print " checked"; ?> disabled /><button onclick="return false;" class="<?php if($daily_logs_today_progress_array[$i]["missed_inspection"] == 1){ ?>glyphicon glyphicon-ok btn btn-warning<?php }else{ ?>btn btn-warning glyphicon glyphicon-warning-sign<?php } ?>" id="missed_inspection_button_<?php print $index; ?>"></button>
                            </div>
                            <div class="span3 unit_today">
                                Vendor No Show<input type="checkbox" style="visibility:hidden;" name="vendor_no_show_<?php print $index; ?>" id="vendor_no_show_<?php print $index; ?>"<?php if($daily_logs_today_progress_array[$i]["vendor_no_show"] == 1) print " checked"; ?> disabled /><button onclick="return false;" class="<?php if($daily_logs_today_progress_array[$i]["vendor_no_show"] == 1){ ?>glyphicon glyphicon-ok btn btn-warning<?php }else{ ?>btn btn-warning glyphicon glyphicon-warning-sign<?php } ?>" id="vendor_no_show_button_<?php print $index; ?>"></button>
                             </div>
                             <div class="clear"></div>
                            <div class="span3 unit_today">
                                Vendor Did Not Finish<input type="checkbox" style="visibility:hidden;" name="vendor_did_not_finish_<?php print $index; ?>" id="vendor_did_not_finish_<?php print $index; ?>"<?php if($daily_logs_today_progress_array[$i]["vendor_did_not_finish"] == 1) print " checked"; ?> disabled /><button onclick="return false;" class="<?php if($daily_logs_today_progress_array[$i]["vendor_did_not_finish"] == 1){ ?>glyphicon glyphicon-ok btn btn-warning<?php }else{ ?>btn btn-warning glyphicon glyphicon-warning-sign<?php } ?>" id="vendor_did_not_finish_button_<?php print $index; ?>"></button>
                             </div>
                            <div class="span3 unit_today">
                                Site Issues<input type="checkbox" style="visibility:hidden;" name="site_issues_<?php print $index; ?>" id="site_issues_<?php print $index; ?>"<?php if($daily_logs_today_progress_array[$i]["site_issues"] == 1) print " checked"; ?> disabled /><button onclick="return false;" class="<?php if($daily_logs_today_progress_array[$i]["site_issues"] == 1){ ?>glyphicon glyphicon-ok btn btn-warning<?php } else{ ?>btn btn-warning glyphicon glyphicon-warning-sign<?php } ?>" id="site_issues_button_<?php print $index; ?>"></button>
                            </div>
                            <div class="span3 unit_today">
                                Vendor Mistake<input type="checkbox" style="visibility:hidden;" name="vendor_mistake_<?php print $index; ?>" id="vendor_mistake_<?php print $index; ?>"<?php if($daily_logs_today_progress_array[$i]["vendor_mistake"] == 1) print " checked"; ?> disabled /><button onclick="return false;" class="<?php if($daily_logs_today_progress_array[$i]["vendor_mistake"] == 1){ ?>glyphicon glyphicon-ok btn btn-warning<?php } else{ ?>btn btn-warning glyphicon glyphicon-warning-sign<?php } ?>" id="vendor_mistake_button_<?php print $index; ?>"></button>
                             </div>
                             <div class="clear"></div>
                            <div class="span3 unit_today">
                                Other<input type="checkbox" style="visibility:hidden;" name="other_<?php print $index; ?>" id="other_<?php print $index; ?>"<?php if($daily_logs_today_progress_array[$i]["other"] == 1) print " checked"; ?> disabled /><button onclick="return false;" class="<?php if($daily_logs_today_progress_array[$i]["other"] == 1){ ?>glyphicon glyphicon-ok btn btn-warning<?php } else{ ?>btn btn-warning glyphicon glyphicon-warning-sign<?php } ?>" id="other_button_<?php print $index; ?>"></button>
                            </div>
                            <div class="clear"></div>
                            <br />
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
                            <div class="inner_border"><input type="text" name="lot_1" id="lot_1" disabled /></div>
                        </div>
                        <div class="span3 unit_today">
                            <strong>Job Status***</strong><br /><br />
                            <div class="inner_border"><select name="job_status_1" id="job_status_1" disabled>
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
                            <div class="inner_border"><textarea name="notes_1" id="notes_1" disabled></textarea></div>
                        </div>
                        <div class="clear"></div>
                        <br />
                        
                        <div class="span2 unit_today">
                            Weather Delay
                            <div class="inner_border"><input type="checkbox" style="visibility:hidden;" name="weather_delay_1" id="weather_delay_1" disabled /></div>
                        </div>
                        <div class="span2 unit_today">
                            Missed Inspection
                            <div class="inner_border"><input type="checkbox" style="visibility:hidden;" name="missed_inspection_1" id="missed_inspection_1" disabled /></div>
                        </div>
                        <div class="span2 unit_today">
                            Vendor No Show
                            <div class="inner_border"><input type="checkbox" style="visibility:hidden;" name="vendor_no_show_1" id="vendor_no_show_1" disabled /></div>
                         </div>
                        <div class="span2 unit_today">
                            Vendor Did Not Finish
                            <div class="inner_border"><input type="checkbox" style="visibility:hidden;" name="vendor_did_not_finish_1" id="vendor_did_not_finish_1" disabled /></div>
                         </div>
                        <div class="span2 unit_today">
                            Site Issues
                            <div class="inner_border"><input type="checkbox" style="visibility:hidden;" name="site_issues_1" id="site_issues_1" disabled /></div>
                        </div>
                        <div class="clear"></div>
                        
                        <div class="span5 unit_today">
                            Vendor Mistake
                            <div class="inner_border"><input type="checkbox" style="visibility:hidden;" name="vendor_mistake_1" id="vendor_mistake_1" disabled /></div>
                         </div>
                        <div class="span5 unit_today">
                            Other
                            <div class="inner_border"><input type="checkbox" style="visibility:hidden;" name="other_1" id="other_1" disabled /></div>
                        </div>
                        <div class="clear"></div>
                    </div>
                    <p>&nbsp;</p>
                <?php
					}
				?>
       </div>
       <p>&nbsp;</p>
       <p>&nbsp;</p>
       <p>PROBLEMS DISCUSSIONS & DELAYS<br />
       <p><textarea name="problem_discussion_delays" id="problem_discussion_delays" class="textarea_daily_log" disabled><?php if(count($daily_logs_array) > 0) print $daily_logs_array["problem_discussion_delays"]; ?></textarea></p>
       <p>RENTAL EQUIPMENT & MATERIAL DELIVERIES/RETURNS<br />
       <p><textarea name="rental_equipment_material_deliveries" id="rental_equipment_material_deliveries" class="textarea_daily_log" disabled><?php if(count($daily_logs_array) > 0) print $daily_logs_array["rental_equipment_material_deliveries"]; ?></textarea></p>
       <p>GENERAL COMMENTS<br />
       <p><textarea name="general_comments" id="general_comments" class="textarea_daily_log" disabled><?php if(count($daily_logs_array) > 0) print $daily_logs_array["general_comments"]; ?></textarea></p>	
       <p>
       <div style="width:75%;margin:0 auto;text-align:left;">
           <?php
                $daily_logs_images_array = $daily_logs_images_object->getAll("WHERE daily_logs_id = ".$_REQUEST["daily_logs_id"]." ORDER BY timestamp");
                for($i=0;$i<count($daily_logs_images_array);$i++)
                {
                    print "<div style='float:left;padding:10px;'><a href='images/daily_log_uploads/".$daily_logs_images_array[$i]["image_name"]."'><img src='images/daily_log_uploads/".$daily_logs_images_array[$i]["image_name"]."' style='height:100px;margin-right:5px;margin-bottom:5px;' /></a>
					<textarea name='image_".$daily_logs_images_array[$i]['id']."' placeholder='ADD IMAGE NOTE' style='width:150px;' disabled='disabled'>".$daily_logs_images_array[$i]["image_notes"]."</textarea> 
					</div>";
                }
				
				if(count($daily_logs_images_array) > 0)
				{
					print "<div class='clear'></div>";	
				}
           ?>
           </div>
       </p>
       </div>
	<form>