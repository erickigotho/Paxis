<?php
if(!isset($_SESSION)){
	session_start();
}

require_once(dirname(dirname(__FILE__)) . '/modules/Dynamo.class.php');
if(trim($_REQUEST['image_url']) != '')
{
	if(stristr($_REQUEST['image_url'],"http://"))
	{
		$array_url = explode("images",$_REQUEST['image_url']);
		$_REQUEST['image_url'] = "images".$array_url[1];
	}
	
	$image_url = "../".$_REQUEST['image_url'];
	
	if(trim($_REQUEST['rotate'] == 'left') && file_exists($image_url))
	{
		$image = imagecreatefromstring(file_get_contents($image_url));	
		$image = imagerotate($image,90,0);
		imagejpeg($image,$image_url);
	}
	
	if(trim($_REQUEST['rotate'] == 'right') && file_exists($image_url))
	{
		$image = imagecreatefromstring(file_get_contents($image_url));	
		$image = imagerotate($image,-90,0);
		imagejpeg($image,$image_url);
	}
}
?>