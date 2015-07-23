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
$unitsObj = new Dynamo("units");
$units_array = $unitsObj->getAll();
if(trim($_POST['workCategoryId']) != '')
{
	$array_work_category_estimates = $work_category_estimates_obj->getAll("WHERE work_category_id = ".$_POST['workCategoryId'] ." ORDER BY item_name");

	if(count($array_work_category_estimates) > 0)
	{
		$result['success'] = true;
		//$result['message'] = $array_work_category_estimates;
		
		$result['message'] = '<div id="accordion" class="panel-group">';
		for($i=0;$i<count($array_work_category_estimates);$i++)
		{
			$result['message'] .= '<div class="panel panel-default">
				<div class="panel-heading">
					 <h4 class="panel-title">
						<a href="#panel'.($i+1).'" data-parent="#accordion" data-toggle="collapse" class="accordion-toggle collapsed"><i class="glyphicon glyphicon-plus"></i>'.$array_work_category_estimates[$i]['item_name'].'</a> 
					</h4>
		
				</div>
				<div class="panel-collapse collapse" id="panel'.($i+1).'" style="height: 0px;">
					<div class="panel-body">
						 <div class="estimatestemplate_row">			
							<div class="estimatestemplate_left">
								<input type="text" name="item_name_'.$i.'_e" id="item_name_'.$i.'_e" class="item_name" value="'.$array_work_category_estimates[$i]['item_name'].'" />
							</div>
							<div class="estimatestemplate_middle">
								$ <input type="text" name="price_per_unit_'.$i.'_e" id="price_per_unit_'.$i.'_e" class="price_per_unit" value="'.$array_work_category_estimates[$i]['price_per_unit'].'" />
							</div>
							<div class="estimatestemplate_right">
								<select name="unit_of_measure_'.$i.'_e" id="unit_of_measure_'.$i.'_e" class="unit_of_measure">';
								
								for($j=0;$j<count($units_array);$j++)
								{
									if($array_work_category_estimates[$i]['unit_of_measure'] == $units_array[$j]['id'])
									{
										$result['message'] .= '<option value="'.$units_array[$j]['id'].'" selected="selected">'.$units_array[$j]['estimate_unit'].'</option>';
									}
									else
									{
										$result['message'] .= '<option value="'.$units_array[$j]['id'].'">'.$units_array[$j]['estimate_unit'].'</option>';
									}
								}
									
						$result['message'] .= '</select>
							</div>		
							<div class="clearfix"></div>	
						</div>
					</div>
				</div>
			</div>';
		}
		
		$result['message'] .= '</div>
		<script type="text/javascript">//&lt;![CDATA[ 
			
			var selectIds = $(".panel-collapse");
			$(function ($) {
				selectIds.on("show.bs.collapse hidden.bs.collapse", function () {
					$(this).prev().find(".glyphicon").toggleClass("glyphicon-plus glyphicon-minus");
				})
			});  
			
		</script>
		';
	}
}

header('Content-type: application/json');
echo json_encode($result);	
?>