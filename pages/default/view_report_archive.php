<?php
require_once(dirname(dirname(dirname(__FILE__))) . '/modules/Report.class.php');
require_once(dirname(dirname(dirname(__FILE__))) . '/modules/Dynamo.class.php');

$reportId = isset($_GET['reportId'])?$_GET['reportId']:0;

if($reportId != 0) {
	$reportObj = new Report();
	
	$usersObj = new Dynamo("users");
	$array_users = $usersObj->getAllWithId_default(false,"id");
	$subContractorsObj = new Dynamo("sub_contractors");
	$array_sub_contractors = $subContractorsObj->getAllWithId_default(false,"id");
	
	$reportInfo = $reportObj->getReportDetails($reportId, false,$array_users,$array_sub_contractors);
	
	$report_images = new Dynamo("report_images");
	
	$array_report_images_count = $report_images->getAll("WHERE property_id = ".$reportInfo['propertyId']);
	
	$array_report_images = $report_images->getAllWithId_default("WHERE property_id = ".$reportInfo['propertyId'],'room_item_id');
	
	for($i=0;$i<count($array_report_images_count);$i++)
	{
		$array_report_images[$array_report_images_count[$i]['room_item_id']]['count'] += 1;
		
		if($array_report_images_count[$i]['image_name'] != $array_report_images[$array_report_images_count[$i]['room_item_id']]['image_name'])
		{
			$array_report_images[$array_report_images_count[$i]['room_item_id']]['extra_images'] .= $array_report_images_count[$i]['image_name'].",";
		}
	}
	
	$main_image_array = $report_images->getAll("WHERE property_image = 1 AND property_id = ".$reportInfo['propertyId']);
	
	if(count($main_image_array) > 0)
		$main_image_array = $main_image_array[0];
	?>
	
	<form method="POST" class="form-horizontal" id="addReportForm" onsubmit="return false">
	<input type="hidden" id="_BASENAME" name="baseName" value="<?php echo __BASENAME__; ?>" />
	<input type="hidden" id="_USERID" name="userId" value="<?php echo $_SESSION['user_id']; ?>" />
	<input type="hidden" id="_ROOMS" name="rooms" value='<?php echo str_replace("'","&#39;",json_encode($reportInfo['rooms'])); ?>' />
	
	<div class="pull-left"><h4>Report for <?php echo stripslashes($reportInfo['propertyCommunity']); ?>, <?php echo stripslashes($reportInfo['propertyName']); ?></h4></div>
	<div class="pull-right"><a href="edit_archive_property.html?propertyId=<?php echo $reportInfo['propertyId']; ?>" class="btn btn-default b">Cancel</a></div>
	<div class="clearfix"></div>
	
	<div class="report-header-wrapper">
		<div class="property-summary">
			<div class="propertydetail-group">
				<div class="property-label">Community</div>
				<div class="property-value"><?php echo $reportInfo['propertyCommunity'];?></div>
			</div>
			<div class="propertydetail-group">
				<div class="property-label">Property Name</div>
				<div class="property-value"><?php echo $reportInfo['propertyName'];?></div>
			</div>
			<div class="propertydetail-group">
				<div class="property-label">Job Type</div>
				<div class="property-value"><?php echo ($reportInfo['propertyJobType']==0?"New":"Restoration");?></div>
			</div>
			<div class="propertydetail-group">
				<div class="property-label">Property Type</div>
				<div class="property-value"><?php echo ($reportInfo['propertyType']==0?"Residential":"Commercial");?></div>
			</div>
			<div class="propertydetail-group">
				<div class="property-label">Address</div>
				<div class="property-value"><?php echo $reportInfo['propertyAddress'];?>, <?php echo $reportInfo['propertyCity'];?> <?php echo $reportInfo['propertyState'];?> - <a href="javascript: void(0)" onclick="window.open('<?php echo $reportInfo['propertyMapLink'];?>')">View Map</a></div>
			</div>
			<div class="propertydetail-group">
				<div class="property-label">Report Date</div>
				<div class="property-value"><?php echo date('m/d/Y g:ia', strtotime($reportInfo['firstReportDate']));?></div>
			</div>
			<div class="propertydetail-group">
				<div class="property-label">Status</div>
				<div class="property-value"><?php echo ($reportInfo['reportStatusId']==0)?'Open':'Closed';?></div>
			</div>
		</div>
		<div class="report-summary">
			<div class="report-summary-details">
				<div class="report-label">This report was completed by</div>
				<div class="report-value"><strong><?php echo $reportInfo['firstName'] . " " . $reportInfo['lastName'];?></strong></div>
				<br/>
				<div class="report-label">Copies of this report were sent to:</div>
				<div class="report-value">
					<ul>
					<?php 
						$emails = explode(',', $reportInfo['propertyEmails']);
						
						foreach($emails as $email):
						?>
							<li><?php echo $email; ?></li>
						<?php
						endforeach;
					?>
					</ul>
				</div>
			</div>
			<div class="report-summary-photo<?php if(count($main_image_array) > 0){ ?> property-photo-none<?php } ?>">
				<?php
					if(count($main_image_array) > 0)
						print "<img src='images/report_uploads/".$main_image_array["image_name"]."' class='archive-property-image' />";
					else
					{
				?>
				&nbsp;
				<?php
					}
				?>
			</div>
		</div>
		<div class="clearfix"></div>
	</div>
	<br/>
	<div class="row-fluid" id="rooms-wrapper">
		<?php
			// echo json_encode($reportInfo['rooms']);
			// var_dump($reportInfo['rooms']);
			
			$tmpRoomIndex = 0;

			foreach($reportInfo['rooms'] as $room):
				// var_dump($room);
			?>
				<div class="accordion-group" id="room_<?php echo $tmpRoomIndex;?>">
					<div class="accordion-heading">
						<div class="row-fluid">
							<div class="room-name">
								<a href="#collapse_<?php echo $tmpRoomIndex;?>" data-parent="#rooms-wrapper" data-toggle="collapse" class="accordion-toggle"><?php echo $room['roomName'];?></a>
							</div>
							<div class="room-name-action">
								<span class="label label-warning" id="room_status_<?php echo $tmpRoomIndex; ?>">Pending Review</span>
							</div>
							<div class="clearfix"></div>
						</div>
						
					</div>
					<div class="accordion-body collapse" id="collapse_<?php echo $tmpRoomIndex;?>">
						<div class="accordion-inner report-room-items">
							<?php 
							if(isset($room['items'])) {
							?>
								<?php
									$roomItemIndex = 0;
									foreach($room['items'] as $item):
										// var_dump($item);
									?>
									<div class="roomitem-group<?php if($item['isEstimate'] == 1) print ' roomitem-estimate'; ?>">
										<div class="roomitem-desc"><?php echo $item['itemName']; ?></div>
										<div class="roomitem-action">
											<?php
											if($item['isEstimate'] == 0 && count($item['arrayRoomItemsUnits']) > 0)
											{
												if($item['arrayRoomItemsUnits'][0]['item_name'] != $room['items'][$roomItemIndex+1]['itemName'])
												{
													$itemClassName = '';
												
													switch($item['statusId']) {
														case '1':
														case '4':
															$itemClassName = 'label-success';
															break;
														case '3':
															$itemClassName = 'label-danger';
															break;
														default:
															$itemClassName = 'label-warning';
															break;
													}
													?>
													<span class="label <?php echo $itemClassName;?>"><?php echo (empty($item['statusName'])?'Pending Review':$item['statusName']); ?></span>
                                           <?php	
														
												}
											}
											else
											{
												$itemClassName = '';
												
												switch($item['statusId']) {
													case '1':
													case '4':
														$itemClassName = 'label-success';
														break;
													case '3':
														$itemClassName = 'label-danger';
														break;
													default:
														$itemClassName = 'label-warning';
														break;
												}
												?>
												<span class="label <?php echo $itemClassName;?>"><?php echo (empty($item['statusName'])?'Pending Review':$item['statusName']); ?></span>
                                           <?php
											}
										   ?>
										</div>
										<div class="roomitem-comment">
										<?php
										
										$greater_count = 0;
										if(count($item['comments']) > count($item['images']))
											$greater_count = count($item['comments']);
										else
											$greater_count = count($item['images']);
										
										if($greater_count > 0)
										 {
											 for($i=0;$i<$greater_count;$i++)
											 {
												echo '<div id="comment_order_'.$tmpRoomIndex.'_'.$roomItemIndex.'_'.$i.'" style="height:30px;">';
												
												if($item['comments'][$i])
												{
													echo '<span class="icon-comment"></span> '. $item['comments'][$i]['comment'];
												}
												
												
												echo "</div>"; 
											 }
										 }
										?>
										<?php //echo (isset($item['comments'][0]['comment'])? '<span class="icon-comment"></span> '. $item['comments'][0]['comment']:'&nbsp;'); ?> </div>
										<div class="roomitem-image-upload">
										<?php
										if($greater_count > 0)
										{
											for($i=0;$i<$greater_count;$i++)
											{
												echo '<div id="image_order_'.$tmpRoomIndex.'_'.$roomItemIndex.'_'.$i.'" style="position:relative;padding-bottom:10px;">';
												if($item['images'][$i])
												{
											?>
											<?php echo '<a rel="light_box_'.$tmpRoomIndex.'_'.$roomItemIndex.'_'.$i.'" href="images/report_uploads/'.$item['images'][$i][0]['image_name'].'" title="'.$item['itemName'].'"><span class="icon-image-upload"></span></a>'.(count($item['images'][$i]) > 1? '<span class="badge">'.count($item['images'][$i]).'</span></a>': ''); ?>
													<?php
														if(count($item['images'][$i]) > 1)
														{
															for($j=1;$j<count($item['images'][$i]);$j++)
															{
																print "<a rel='light_box_".$tmpRoomIndex.'_'.$roomItemIndex.'_'.$i."' href='images/report_uploads/".$item['images'][$i][$j]['image_name']."' title=\"".$item['itemName']."\"><img src='' style='display:none;' /></a>";	
															}
														}
													?>
											<?php
												}
												else
												{
													print "&nbsp;";	
												}
												echo '</div>';
												?>
												<script type="text/javascript">
													$jq1(document).ready(function() {
														$jq1("a[rel=light_box_<?php print $tmpRoomIndex."_".$roomItemIndex."_".$i; ?>]").fancybox({
															'transitionIn'		: 'none',
															'transitionOut'		: 'none',
															'titlePosition' 	: 'inside',
															'titleFormat'		: function(title, currentArray, currentIndex, currentOpts) {
																return '<span id="fancybox-title-over">Image ' + (currentIndex + 1) + ' / ' + currentArray.length + (title.length ? ' &nbsp; ' + title : '') + '<?php if(trim($item['comments'][$i]['comment']) != '') { ?> <div style="float:right;"><span class="icon-comment" style="color:#fff;"></span> <?php print $item['comments'][$i]['comment']; ?> </div><div class="clear"></div> <?php } ?> <br /><a href="" onclick="rotate_left(\''+currentArray[currentIndex].href+'\');return false;"><img src="images/rotate_left.png" title="Rotate Left" width="40" /></a> &nbsp;  &nbsp; <a href="" onclick="rotate_right(\''+currentArray[currentIndex].href+'\');return false;"><img src="images/rotate_right.png" title="Rotate Right" width="40" /></a></span>';
																//$("#fancybox-img").attr("title",title);
																//return '<span id="fancybox-title-over">' + title + ' </span>';
															}
														});	
													});
												</script>
												<?php
											}
										}
										?>
										<?php //echo (isset($array_report_images[$item['itemId']])? '<a rel="light_box_'.$tmpRoomIndex.'_'.$roomItemIndex.'" href="images/report_uploads/'.$array_report_images[$item['itemId']]['image_name'].'" title="'.$item['itemName'].'"><span class="icon-image-upload"></span></a>'.($array_report_images[$item['itemId']]['count'] > 1? '<span class="badge">'.$array_report_images[$item['itemId']]['count'].'</span></a>': '') :''); ?> </div>
                                        <?php
											/*if(trim($array_report_images[$item['itemId']]['extra_images']) != '')
											{
												$array_report_images[$item['itemId']]['extra_images'] = substr($array_report_images[$item['itemId']]['extra_images'],0,-1);
												$extraImagesArray = explode(",",$array_report_images[$item['itemId']]['extra_images']);
												
												for($i=0;$i<count($extraImagesArray);$i++)
												{
													print "<a rel='light_box_2' href='images/report_uploads/".$extraImagesArray[$i]."' title='".$item['itemName']."'><img src='' style='display:none;' /></a>";
												}
											}*/
										?>
										<div class="clearfix"></div>
									</div>
									<?php
										$roomItemIndex++;
									endforeach;
								?>
							<?php 
							}
							?>
						</div>
					</div>
				</div>
			<?php
				$tmpRoomIndex++;
			endforeach;
		?>
	</div>
	
	<br/><br/>
	<strong>General Comments:</strong>
	<div class="existingComments">
		<table class="table" id="reportExistingComments">
		<?php
		if(isset($reportInfo['reportComments'])) {
			foreach($reportInfo['reportComments'] as $reportComment):
				$comment =  !isset($reportComment['comment'])?'':$reportComment['comment'];
				
				if($reportComment['isSubmitted'] == 1) {
					$commentDate = (!isset($reportComment['date'])?"": $reportComment['date']);
					$commentedBy = !isset($reportComment['user'])?'':$reportComment['user'];
			?>
				<tr>
					<td><?php echo $commentDate;?></td>
					<td><?php echo $comment;?></td>
					<td>by: <?php echo $commentedBy;?></td>
				</tr>
			<?php
				} 
			endforeach;
		}
		?>
		</table>
	</div>
	
	</form>
	
	<script type="text/javascript">
	var m_roomIndex = 0;
	var m_listRooms = [];
	
	window.onload = function() {
		var roomsElem = document.getElementById("_ROOMS");
		var existingRooms = JSON.parse(roomsElem.value);
		
		//[Start] Process existing rooms
		for(var i=0; i<existingRooms.length; i++) {
			var arrRoomItems = [];
			for(var j=0; j < existingRooms[i].items.length; j++) {
				arrRoomItems.push({id: existingRooms[i].items[j].itemId, name: existingRooms[i].items[j].itemName, statusId: existingRooms[i].items[j].statusId});
			}
			
			m_listRooms.push({roomId: i, roomName: existingRooms[i].roomName, roomItems: arrRoomItems});
			
			//SET ROOM INDICATOR
			var roomStatusElem = document.getElementById("room_status_" + i);
			
			setRoomsStatusIndicator(roomStatusElem, existingRooms[i].items);
			
			m_roomIndex = i;
		}
		
		if(m_listRooms.length) {
			$('#saveBtn').prop('disabled', false);
		}
		//[End] Process existing rooms
	}
	
	function setRoomsStatusIndicator(statusElem, punchList) {
		var isIncomplete = false;
		
		for(var i=0; i<punchList.length; i++) {
			if(punchList[i].statusId == 3) {
				statusElem.setAttribute("class", "label label-danger");
				statusElem.innerHTML = "Incomplete";
				isIncomplete = true;
				break;
			} else if(punchList[i].statusId == 2) {
				statusElem.setAttribute("class", "label label-warning");
				statusElem.innerHTML = "Pending Review";
				isIncomplete = true;
				break;
			}
		}
		
		if(!isIncomplete) {
			statusElem.setAttribute("class", "label label-success");
			statusElem.innerHTML = "Completed";
		}
	}
	</script>
<?php 
} else {
?>
	Oops! something went wrong.
<?php
}
?>