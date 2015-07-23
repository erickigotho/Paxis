<?php
	require_once('config/main.config.php');
	
	define('__BASENAME__', $config['baseName']);

	require_once('config/bootstrap.php');
	
	$template = new Template('layout/default.html');
	$template->title = 'Paxis Pro - Edit Report';
	$template->header = new Template('pages/default/sections/header.php');
	$template->content = new Template('pages/default/edit_report.php');
	$template->footer = new Template('pages/default/sections/footer.php');
	$template->render();
?>
