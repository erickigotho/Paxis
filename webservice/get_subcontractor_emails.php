<?php
require_once(dirname(dirname(__FILE__)) . '/modules/Dynamo.class.php');
require_once(dirname(dirname(__FILE__)) . '/modules/Tools.class.php');

$result['html'] = '';


if($_SESSION['user_type'] != 5)
{
	$tools = new Tools;
	$array_sub_contractors = $tools->getSubContractorToEmail_faster($_POST["reportId"],$_POST["propertyId"],new Dynamo("subcontractors_assign"));
	if(count($array_sub_contractors) > 0)
	{
		ob_start();
		print "and to the following sub contractors:
		<ul>";
		for($i=0;$i<count($array_sub_contractors);$i++)
		{
			print "<li>".$array_sub_contractors[$i]."</li>";
		}
		print "</ul>";
		$html = ob_get_clean();
		$result['html'] = $html;
	}
}

header('Content-type: application/json');
echo json_encode($result);
?>