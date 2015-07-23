<?php
if(!isset($_SESSION)){
	session_start();
}

define('__ROOT__', dirname(dirname(__FILE__)));
define('__BASENAME__', basename(__ROOT__));

require_once(__ROOT__ . '/modules/Dynamo.class.php');

$result['success'] = false;
$result['message'] = '';

$work_category_estimates_obj = new Dynamo("work_category_estimates");
$estimate_room_items_units_obj = new Dynamo("estimate_room_items_units");
$unitsObj = new Dynamo("units");
$room_template_items_obj = new Dynamo("room_template_items");
$sub_contractors_obj = new Dynamo("sub_contractors");
$subcontractors_assign_obj = new Dynamo("subcontractors_assign");

$unitsArray = $unitsObj->getAllWithId();

if(trim($_POST['edit_off']) != '')
	$edit_off = true;
	
if(trim($_POST['roomTemplateItemsId']) != '' && trim($_POST['estimate_id']) != '' && trim($_POST['room_id']) != '' && trim($_POST['work_category_id']) != '')
{
	$array_work_category_estimates = $work_category_estimates_obj->getAll("WHERE work_category_id = ".$_POST['work_category_id'] ." ORDER BY item_name");
	
	$array_estimate_room_items_units = $estimate_room_items_units_obj->getAllWithId_default("WHERE estimate_id = ".$_POST['estimate_id']." AND room_id = ".$_POST['room_id'],"work_category_estimates_id");
	
	//get subcontractors
	$room_template_items_array = $room_template_items_obj->getAll("WHERE id = ".$_POST['roomTemplateItemsId']);
	
	
	if(count($room_template_items_array) > 0)
	{
		$query = "SELECT sub_contractor_work_category.sub_contractor_id,sub_contractors.first_name,sub_contractors.last_name FROM sub_contractor_work_category INNER JOIN sub_contractors ON sub_contractors.id = sub_contractor_work_category.sub_contractor_id WHERE sub_contractor_work_category.work_category_id = ".$room_template_items_array[0]['work_category_id'];
		
		$subcontractor_array = 	$sub_contractors_obj->customFetchQuery($query);
	}
	
	if(count($array_work_category_estimates) > 0)
	{
		$result['success'] = true;
		//$result['message'] = $array_work_category_estimates;
		
		$result['message'] = '<div id="accordion" class="panel-group">';
		
		for($i=0;$i<count($array_work_category_estimates);$i++)
		{
			$units_of_measure = $unitsArray[$array_work_category_estimates[$i]['unit_of_measure']]["estimate_unit"];
			
			$result['message'] .= '<div class="panel panel-default">
				<div class="panel-heading">
					 <h4 class="panel-title">
						<a href="#panel'.($i+1).'" data-parent="#accordion" data-toggle="collapse" class="accordion-toggle"><i class="glyphicon glyphicon-minus"></i>'.$array_work_category_estimates[$i]['item_name'].'</a> 
					</h4>
		
				</div>
				<div class="panel-collapse collapse in" id="panel'.($i+1).'">
					<div class="panel-body">
						 	<div class="panel-body-padding">';
								if(isset($edit_off))
								{
									$result['message'] .= $array_estimate_room_items_units[$array_work_category_estimates[$i]['id']]['units']. " ".$units_of_measure;
								}
								else
								{
								$result['message'] .= '<table border="0">
								<tr>
									<td style="padding-right:20px;">Please fill in the '.$units_of_measure.' for this unit</td>
									<td>Price Per Unit ($)</td>
								</tr>
								<tr>
								<td style="padding-right:20px;">
						 			<input type="text" name="units_'.($array_estimate_room_items_units[$array_work_category_estimates[$i]['id']]['id']?$array_estimate_room_items_units[$array_work_category_estimates[$i]['id']]['id']:0).'_'.$array_work_category_estimates[$i]['id'].'" id="units_'.($array_estimate_room_items_units[$array_work_category_estimates[$i]['id']]['id']?$array_estimate_room_items_units[$array_work_category_estimates[$i]['id']]['id']:0).'_'.$array_work_category_estimates[$i]['id'].'" class="units" value="'.$array_estimate_room_items_units[$array_work_category_estimates[$i]['id']]['units'].'" />
								</td>
								<td>
									<input type="text" name="price_per_unit_'.($array_estimate_room_items_units[$array_work_category_estimates[$i]['id']]['id']?$array_estimate_room_items_units[$array_work_category_estimates[$i]['id']]['id']:0).'_'.$array_work_category_estimates[$i]['id'].'" id="price_per_unit_'.($array_estimate_room_items_units[$array_work_category_estimates[$i]['id']]['id']?$array_estimate_room_items_units[$array_work_category_estimates[$i]['id']]['id']:0).'_'.$array_work_category_estimates[$i]['id'].'" class="price_per_unit" value="'.($array_estimate_room_items_units[$array_work_category_estimates[$i]['id']]['price_per_unit']?$array_estimate_room_items_units[$array_work_category_estimates[$i]['id']]['price_per_unit']:$array_work_category_estimates[$i]['price_per_unit']).'" />
								</td>
								</tr>
								<tr>
									<td colspan="2">
										Scope<br />
										<textarea style="width:100%;" name="scope_'.($array_estimate_room_items_units[$array_work_category_estimates[$i]['id']]['id']?$array_estimate_room_items_units[$array_work_category_estimates[$i]['id']]['id']:0).'_'.$array_work_category_estimates[$i]['id'].'" id="scope_'.($array_estimate_room_items_units[$array_work_category_estimates[$i]['id']]['id']?$array_estimate_room_items_units[$array_work_category_estimates[$i]['id']]['id']:0).'_'.$array_work_category_estimates[$i]['id'].'" class="scope">'.$array_estimate_room_items_units[$array_work_category_estimates[$i]['id']]['scope'].'</textarea>
									</td>
								</tr>';
								
								if(trim($array_estimate_room_items_units[$array_work_category_estimates[$i]['id']]['units']) != '' || trim($array_estimate_room_items_units[$array_work_category_estimates[$i]['id']]['scope']) != '')
								{
									$result['message'] .= '<tr>
										<td colspan="2">
											<div id="result_div"></div>
											<a href="" id="'.$array_estimate_room_items_units[$array_work_category_estimates[$i]['id']]['id'].'" class="remove_estimate" style="text-decoration:underline;color:blue;" onclick="remove_estimate('.$array_estimate_room_items_units[$array_work_category_estimates[$i]['id']]['id'].');return false;">Remove Estimate</a>
											<script type="text/javascript">
												var statusMsg = document.getElementById("status-message");
												function remove_estimate(id)
												{
													if(confirm("Are you sure?"))
													{
														$.ajax({
															url: "webservice/delete_estimate.php",
															type: "POST",
															data: { 
																	 unit_id:id
																} 
														}).done(function( response ) {
															if(response.success) {
																statusMsg.innerHTML = getAlert("success", "Successfully removed estimate");
																tmpRoomIndex = document.getElementById("tmpRoomIndex").value;
																roomItemIndex = document.getElementById("roomItemIndex").value;
																number_of_estimates = $("#roomItemCommentContainer_"+tmpRoomIndex+"_"+roomItemIndex).find(".badge-small").html();
																number_of_estimates = parseInt(number_of_estimates) - 1;
																if(number_of_estimates <= 0)
																{
																	$("#roomItemCommentContainer_"+tmpRoomIndex+"_"+roomItemIndex).html("");
																}
																else
																{
																	$("#roomItemCommentContainer_"+tmpRoomIndex+"_"+roomItemIndex).find(".badge-small").html(number_of_estimates);
																}
																
															}
															else
															{
																statusMsg.innerHTML = getAlert("error", "Estimate has not been removed");
															}
															$("#estimates").modal("hide");
														});
													}
												}
											</script>
										</td>
									</tr>';
								}
								
								$result['message'] .= '</table>';
								
								}
								
							$result['message'] .= '</div>
						</div>
					</div>
				</div>';
		}
		
		$result['message'] .= '</div>';
		
		//subcontractors
		if(count($subcontractor_array) > 0)
		{
			$subcontractors_assign_array_copy = $subcontractors_assign_obj->getAll("WHERE property_id = ".$_REQUEST['propertyId']." AND work_category_id = ".$room_template_items_array[0]['work_category_id']);
			$subcontractors_assign_array = array();
			if(count($subcontractors_assign_array_copy) > 0)
			{
				
				for($i=0;$i<count($subcontractors_assign_array_copy);$i++)
				{
					$subcontractors_assign_array[$subcontractors_assign_array_copy[$i]['sub_contractor_id']] = $subcontractors_assign_array_copy[$i];
				}
			}
			
			$result['message'] .= '<table><tr><td colspan="2"><br />';
			$result['message'] .= '<strong style="font-size:15px;color:#353535;">Please select the subcontractors you\'d like to use:</strong><br />';
			$result['message'] .= '<input type="hidden" name="work_category_id" id="work_category_id" value="'.$room_template_items_array[0]['work_category_id'].'" />';
			for($i=0;$i<count($subcontractor_array);$i++)
			{
				$result['message'] .= '<label><input type="checkbox" name="subcontractor_id" class="subcontractor_id" value="'.$subcontractor_array[$i]['sub_contractor_id'].'"'.($subcontractors_assign_array[$subcontractor_array[$i]['sub_contractor_id']]?" checked=\"checked\"":"").' /> '.$subcontractor_array[$i]['first_name']." ".$subcontractor_array[$i]['last_name']."</label>";
			}
			
			$result['message'] .= '<br /></td></tr></table>';
		}
		
		$result['message'] .= '<script type="text/javascript">
			var selectIds = $(".panel-collapse");
			$(function ($) {
				selectIds.on("hidden.bs.collapse show.bs.collapse", function () {
					$(this).prev().find(".glyphicon").toggleClass("glyphicon-minus glyphicon-plus");
				})
			});
		</script>
		';
		
		/*$result['message'] = '
		<div id="accordion" class="panel-group">
			<div class="panel panel-default">
				<div class="panel-heading">
					 <h4 class="panel-title"><a href="#panel1" data-parent="#accordion" data-toggle="collapse" 
					 class="accordion-toggle collapsed"><i class="glyphicon glyphicon-plus"></i>this</a></h4>
				</div>
				<div class="panel-collapse collapse" id="panel1">
					<div class="panel-body">
						 	<div class="panel-body-padding">here</div>
					</div>
				</div>
			</div>
		</div>
		<script type="text/javascript">
			
			var selectIds = $(".panel-collapse");
			$(function ($) {
				selectIds.on("show.bs.collapse hidden.bs.collapse", function () {
					$(this).prev().find(".glyphicon").toggleClass("glyphicon-plus glyphicon-minus");
				})
			});  
			
		</script>
		';*/
	}
}

header('Content-type: application/json');
echo json_encode($result);	
?>