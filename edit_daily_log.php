<?php
	require_once('config/main.config.php');
	
	define('__BASENAME__', $config['baseName']);

	require_once('config/bootstrap.php');
	
	$template = new Template('layout/default.html');
	$template->title = 'Paxis Pro - Edit Daily Log';
	$template->header = new Template('pages/default/sections/header.php');
	$template->content = new Template('pages/default/edit_daily_log.php');
	$template->footer = new Template('pages/default/sections/footer.php');
	$template->render();
?>
