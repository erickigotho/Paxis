<?php
if(!isset($_SESSION)){
	session_start();
}

define('__ROOT__', dirname(dirname(__FILE__)));
define('__BASENAME__', basename(__ROOT__));

require_once(__ROOT__ . '/modules/Dynamo.class.php');
require_once(__ROOT__ . '/modules/Estimates.class.php');
require_once(__ROOT__ . '/mpdf/mpdf60/mpdf.php');
require_once(__ROOT__ . '/modules/attach_mailer_class.php');

$result['success'] = false;
$result['message'] = '';

if(trim($_POST['propertyId']) != '' && trim($_POST['estimatesId']) != '')
{
	$estimatesId = $_POST['estimatesId'];
	$propertyId = $_POST['propertyId'];
	
	$estimates_obj = new Dynamo("estimates");
	$estimatesArray = $estimates_obj->getAll("WHERE id = ".$estimatesId);
	$estimatesArray = $estimatesArray[0];
		
	if(trim($estimatesArray["property_id"]) != '')
	{
		$properties_obj = new Dynamo("properties");
		$propertyArray = $properties_obj->getAll("WHERE id = ".$propertyId);
		$propertyArray = $propertyArray[0];
		
		$estimates_multiplier = $propertyArray['estimates_multiplier'];
		
		if(!isset($estimates_multiplier) || $estimates_multiplier == 0 || $estimates_multiplier < 0)
			$estimates_multiplier = 1;
			
		$estimate_room_items_units_obj = new Dynamo("estimate_room_items_units");
		$query = "SELECT eu.estimate_id,eu.units,er.room_template_id,eu.scope,re.item_name,(eu.price_per_unit*$estimates_multiplier) AS price_per_unit,re.unit_of_measure,eri.name,er.name AS estimate_room_name,(eu.units *  eu.price_per_unit * $estimates_multiplier) AS total_cost
		FROM estimate_room_items_units eu 
		INNER JOIN work_category_estimates re ON eu.work_category_estimates_id = re.id
		INNER JOIN estimate_room_items eri ON eri.id = eu.estimate_room_items_id
		INNER JOIN estimate_rooms er ON eri.room_id = er.id
		WHERE eu.estimate_id = ".$estimatesId . " AND eu.units != 0 ORDER BY er.room_template_id,eu.estimate_room_items_id,er.name";
		
		$estimate_room_items_units_array = $estimate_room_items_units_obj->customFetchQuery($query);
		
		$estimatesEmailBody = "<table border='1' bordercolor='#D0D7E5' style='border:1px solid #D0D7E5;' cellpadding='0' cellspacing='0'>";
		$email_sub_body = '';
		$cost = 0;
		$total_cost = 0;
		
		$unitsObj = new Dynamo("units");
		$unitsArray = $unitsObj->getAllWithId();
		
		for($i=0;$i<count($estimate_room_items_units_array);$i++)
		{
			if($unitsArray[$estimate_room_items_units_array[$i]['unit_of_measure']])
				$unit_of_measure = $unitsArray[$estimate_room_items_units_array[$i]['unit_of_measure']]['estimate_unit'];
				
			$email_sub_body .= "<tr>
				<td style='text-align:left;font:normal 12px Arial;color:#000'>&nbsp;&nbsp;".$estimate_room_items_units_array[$i]['name'] ." - <em>". $estimate_room_items_units_array[$i]['item_name']."</em>&nbsp;&nbsp;</td>
				<td style='text-align:right;font:normal 12px Arial;color:#000'>&nbsp;&nbsp;".$estimate_room_items_units_array[$i]['units']. " ".$unit_of_measure."&nbsp;&nbsp;</td>
				<td style='text-align:right;font:normal 12px Arial;color:#000'>&nbsp;&nbsp;@ $".number_format(number_format($estimate_room_items_units_array[$i]['price_per_unit'], 2, '.', ''),2)."&nbsp;&nbsp;</td>
				<td style='text-align:right;font:normal 12px Arial;color:#000'>&nbsp;&nbsp;$".number_format(number_format($estimate_room_items_units_array[$i]['total_cost'], 2, '.', ''),2)."&nbsp;&nbsp;</td>
			</tr>";
			
			if(trim($estimate_room_items_units_array[$i]['scope']) != '')
			{
				$email_sub_body .= "<tr>
				<td style='text-align:left;font:normal  Arial;color:#000;padding:5px;' colspan='4'><strong> - Scope:</strong> ".str_replace("\n","<br />",$estimate_room_items_units_array[$i]['scope'])."</td>
			</tr>";
			}
			
			$cost += $estimate_room_items_units_array[$i]['total_cost'];
			
			if($estimate_room_items_units_array[$i]['room_template_id'] != $estimate_room_items_units_array[$i+1]['room_template_id'] || $estimate_room_items_units_array[$i]['estimate_room_name'] != $estimate_room_items_units_array[$i+1]['estimate_room_name']  || !$estimate_room_items_units_array[$i+1]['room_template_id'])
			{
				$estimatesEmailBody .= "<tr>
					<td colspan='3' style='text-align:left;font:bold 14px Arial;color:#000;'>&nbsp;&nbsp;<strong>".$estimate_room_items_units_array[$i]['estimate_room_name']."<strong>&nbsp;&nbsp;</td>
					<td style='text-align:right;font:normal 14px Arial;color:#000;'>&nbsp;&nbsp;$".number_format(number_format($cost, 2, '.', ''),2)."&nbsp;&nbsp;</td>
				</tr>";
				$estimatesEmailBody .= $email_sub_body;
				$total_cost += $cost;
				if($i == (count($estimate_room_items_units_array) -1))
				{
					$estimatesEmailBody .= "<tr><td colspan='4'>&nbsp;</td></tr>";
					$estimatesEmailBody .= "<tr style='text-align:left;font:bold 14px Arial;color:#000'><td colspan='3'>&nbsp;&nbsp;<strong>Total</strong>&nbsp;&nbsp;</td><td style='text-align:right;font:normal 14px Arial;color:#000'>&nbsp;&nbsp;$".number_format(number_format($total_cost, 2, '.', ''),2)."&nbsp;&nbsp;</td></tr>";
				}
				else
					$estimatesEmailBody .= "<tr><td colspan='4'>&nbsp;</td></tr>";
				
				$email_sub_body = '';
				
				
				$cost = 0;
			}
		}
		
		$estimatesEmailBody .= "</table>";
		
		$searchArray = array('__PROPERTYCOMMUNITY__'
							, '__PROPERTYNAME__'
							, '__PROPERTYTYPE__'
							, '__PROPERTYJOBTYPE__'
							, '__ADDRESS__'
							, '__ESTIMATEDATE__'
							, '__ESTIMATEEDBY__'
							, '__ROOMS__');
							
		$replaceArray = array($propertyArray['community']
							, $propertyArray['name']
							, ($propertyArray['property_type']==1?'Commercial':'Residential')
							, ($propertyArray['job_type']==1?'Restoration':'New')
							, $propertyArray['address']
							, date('m/d/Y g:ia')
							, $_SESSION['user_firstname'] . ' ' . $_SESSION['user_lastname']
							, $estimatesEmailBody);
		
		$mailSubject = 'Estimate for ' . $propertyArray['community'] . ", " . $propertyArray['name'];
		
		$host_url = "http://".$_SERVER['HTTP_HOST'].rtrim(dirname(dirname($_SERVER['PHP_SELF'])), '/\\');
		$replace_title = "<a href='".$host_url."/view_estimate.html?estimatesId=".$estimatesId."'>".$mailSubject."</a>";
		
		$emailBody = file_get_contents(dirname(dirname(__FILE__)) . '/email_template/estimate.tpl');
		$emailBody = str_replace($searchArray, $replaceArray, $emailBody);
		$emailBody = str_replace($mailSubject,$replace_title,$emailBody);
		$emailBody = str_replace('__HOST__',$host_url,$emailBody);
		
		$pdf_file = "../mpdf/pdfs/estimate.pdf";
		@unlink($pdf_file);
		@shell_exec("rm -rf ".$pdf_file);
		
		$emailBody_pdf = str_replace("<img src='$host_url","<img src='..",str_replace("<img src=\"$host_url","<img src=\"..",$emailBody));
		
		preg_match_all("/<img src=[\"\'](.*?)[\"\']/i",$emailBody_pdf,$array);
		for($i=0;$i<count($array[1]);$i++)
		{
			if(!file_exists($array[1][$i]))
				$emailBody_pdf = str_replace($array[1][$i],"",$emailBody_pdf);
		}
		
		$mpdf=new mPDF('c'); 
		$mpdf->WriteHTML($emailBody_pdf);
		$mpdf->Output($pdf_file);
		
		$estimate_emails = str_replace("\n","",trim($propertyArray['estimates_emails']));
		
		if(trim($estimate_emails) != '')
		{
			$estimates = new Estimates;
			$arrayEmails = explode(",",$estimate_emails);
			
			if(count($arrayEmails) > 0)
			{
				$error = false;
				/*for($i=0;$i<count($arrayEmails);$i++)
				{
					if(!$estimates->validateEmail($arrayEmails[$i]))
						$error = true;
				}*/
				
				if($error == false)
				{
					$query = "UPDATE estimates SET is_submitted = 1,is_saved = 0 WHERE id = ".$estimatesId;
					$estimates_obj->customExecuteQuery($query);
					
					/*$mailHeaders = "From: Paxis Group <no-reply@Paxisgroup.com> \r\n";
					$mailHeaders .= "Reply-To: Paxis Group <no-reply@Paxisgroup.com>\r\n";
					$mailHeaders .= "Return-Path: Paxis Group <no-reply@Paxisgroup.com>\r\n";
					$mailHeaders .= "Bcc: Wendell Malpas <wendell.malpas@gmail.com>\r\n";
					$mailHeaders .= "X-Mailer: PHP v" .phpversion(). "\r\n";
					$mailHeaders .= "MIME-Version: 1.0\r\n";
					$mailHeaders .= "Content-Type: text/html; charset=utf-8";
					
					mail($estimate_emails,$mailSubject,$emailBody,$mailHeaders);*/
					
					$attachMailer = new attach_mailer("Paxis Group", "no-reply@Paxisgroup.com", $estimate_emails, $cc = "", $bcc="wendell.malpas@gmail.com" , $mailSubject, $emailBody);
			
					if(file_exists($pdf_file))
						$attachMailer->create_attachment_part($pdf_file,"attachment","application/pdf"); 
					
					$attachMailer->process_mail();
					
					$result['success'] = true;
					$result['message'] = "";
				}
				else
				{
					$result['success'] = false;
					$result['message'] = "Oops, there seems to be a problem with your Estimates email addresses.";
				}
			}
		}
	}
}

header('Content-type: application/json');
echo json_encode($result);
?>