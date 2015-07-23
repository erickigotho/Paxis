<?php
require_once('config/main.config.php');
require_once('modules/Dynamo.class.php');
require_once('modules/attach_mailer_class.php');
require_once('mpdf/mpdf60/mpdf.php');

$subcontractor_emails_obj = new Dynamo("subcontractor_emails");
$array_emails = $subcontractor_emails_obj->getAll("WHERE sent = 0");

$users_obj = new Dynamo("users");
$companyAdminArray = $users_obj->getAll("WHERE user_type = 2 AND is_active = 1");

$email_message = '';
for($i=0;$i<count($array_emails);$i++)
{
	$email_message .= "<h2 style='color:#666;'>Subcontractor Report #".($i+1)."</h2>";
	$email_message .= $array_emails[$i]['emailBody'];
}

$companyAdminEmails = '';
for($i=0;$i<count($companyAdminArray);$i++)
{
	$companyAdminEmails .= $companyAdminArray[$i]['email'].",";
}

if(trim($email_message) != '' && trim($companyAdminEmails) != '')
{
	$companyAdminEmails = substr($companyAdminEmails,0,-1);
	
	$host_url = "http://".$_SERVER['HTTP_HOST'].rtrim(dirname(dirname($_SERVER['PHP_SELF'])), '/\\');
	$pdf_file = "mpdf/pdfs/sub_contractor_report.pdf";
	@unlink($pdf_file);
	@shell_exec("rm -rf ".$pdf_file);
	
	$emailBody_pdf = str_replace("<img src='$host_url","<img src='",str_replace("<img src='$host_url/","<img src='",str_replace("<img src=\"$host_url/","<img src=\"",$email_message)));
	
	preg_match_all("/<img src=[\"\'](.*?)[\"\']/i",$emailBody_pdf,$array);
	for($i=0;$i<count($array[1]);$i++)
	{
		if(!file_exists($array[1][$i]))
			$emailBody_pdf = str_replace($array[1][$i],"",$emailBody_pdf);
	}
	
	$mpdf=new mPDF('c'); 
	$mpdf->WriteHTML($emailBody_pdf);
	$mpdf->Output($pdf_file);
	
	$subject = "Subcontractor Reports for ".date("m/d/Y",time()-3600);
	
	$attachMailer = new attach_mailer("Paxis Group", "no-reply@Paxisgroup.com", $companyAdminEmails, $cc = "", $bcc="wendell.malpas@gmail.com" , $subject, $email_message);
			
	if(file_exists($pdf_file))
		$attachMailer->create_attachment_part($pdf_file,"attachment","application/pdf"); 
	
	$attachMailer->process_mail();
			
	//mail($companyAdminEmails,"Subcontractor Reports for ".date("m/d/Y",time()-3600),$email_message,$array_emails[$i]['headers']) or die("Error: There was an error sending mails");
	//@mail("ericap.enterprises@gmail.com","Subcontractor Reports for " . date("m/d/Y",time()-3600),$email_message,$array_emails[0]['headers']) or die("Error: There was an error sending mails");
}

$query = "UPDATE subcontractor_emails SET sent = 1";
$subcontractor_emails_obj->customExecuteQuery($query);

$query = "SELECT COUNT(*) AS count_emails FROM subcontractor_emails WHERE sent = 1";
$array_sub_contractor_emails = $subcontractor_emails_obj->customFetchQuery($query);
if($array_sub_contractor_emails[0]['count_emails'] > 3000)
{
	$query = "DELETE FROM subcontractor_emails WHERE sent = 1";
	$subcontractor_emails_obj->customExecuteQuery($query);
}

print "Emails successfully sent";
?>