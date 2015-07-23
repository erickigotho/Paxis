<?php
if(!isset($_SESSION)){
	session_start();
}

require_once(dirname(dirname(__FILE__)) . '/modules/Dynamo.class.php');
if(trim($_REQUEST['image_name']) != '')
{
	if(stristr($_REQUEST['image_name'],"/"))
	{
		$array_url = explode("/",$_REQUEST['image_name']);
		$image_name = $array_url[count($array_url) - 1];
	}
	else
		$image_name = $_REQUEST['image_name'];	
	
	$report_images = new Dynamo("report_images");
	
	$array_image = $report_images->getAll("WHERE image_name = \"{$image_name}\"");
	$array_image = $array_image[0];
	
	if(count($array_image) > 0)
	{
		$report_images->customExecuteQuery("UPDATE report_images SET property_image = 0 WHERE property_id = ".$array_image["property_id"]);
		$report_images->customExecuteQuery("UPDATE report_images SET property_image = 1 WHERE id = ".$array_image["id"]);
	}
}
?>