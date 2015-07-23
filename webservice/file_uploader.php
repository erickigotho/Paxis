<?php
if(!isset($_SESSION)){
	session_start();
}

require_once(dirname(dirname(__FILE__)) . '/modules/Dynamo.class.php');
require_once(dirname(dirname(__FILE__)) . '/modules/FileUploader.class.php');

$report_images = new Dynamo("report_images");
$imageId = $report_images->getMaxId();

$uploader = new qqUploadedFileXhr;
$filename = $uploader->getName();

$array_file = explode(".",$filename);

$property_id = $_REQUEST['property_id'] = $_GET['propertyId'];

if(trim($_GET['reportId']) != '')
{
	$report_id = $_REQUEST['report_id'] = $_GET['reportId'];
	$image_filename = $report_id."_".$imageId.".".$array_file[count($array_file) - 1];
}
else if(trim($_GET['propertyId']) != '')
{
	$_REQUEST['report_id'] = '';
	$image_filename = $property_id."_".$imageId.".".$array_file[count($array_file) - 1];
}

$result = $uploader->save(dirname(dirname(__FILE__)) .'/images/report_uploads/'.$image_filename);

if($result)
{	
	$_REQUEST['image_name'] = $image_filename;
	$_REQUEST['date'] = date("Y-m-d H:i:s",time());
	$_REQUEST['user_id'] = $_SESSION['user_id'];
	
	
	$array_images_exist = array();
	$array_images_exist = $report_images->getAll("WHERE property_id = ".$property_id);
	
	if(count($array_images_exist) > 0)
		$_REQUEST['property_image'] = 0;
	else
		$_REQUEST['property_image'] = 1;
	
	$report_images->add();
?>
{error:"true",filename:"<?php print $_REQUEST['image_name']; ?>"}
<?php
}
else
{
?>
{error:"false"}
<?php
}
?>