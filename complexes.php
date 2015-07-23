<?php
	require_once('config/main.config.php');
	
	define('__BASENAME__', $config['baseName']);

	require_once('config/bootstrap.php');
	
	if(!isset($_SESSION["user_email"]) || empty($_SESSION["user_email"])) {
		header("location: " . __BASENAME__ . "/login.html");
	} else {
		$template = new Template('layout/default.html');
		$template->title = 'Paxis Pro - Complexes List';
		$template->header = new Template('pages/default/sections/header.php');
		$template->content = new Template('pages/default/complexes.php');
		$template->footer = new Template('pages/default/sections/footer.php');
		$template->render();
	}
?>
