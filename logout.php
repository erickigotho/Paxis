<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo (isset($title) ? $title : ''); ?></title>
	<meta name="author" content="Wendell Malpas">
	<link href="images/favicon.ico" type="image/x-icon" rel="icon" />
	<link href="images/favicon.ico" type="image/x-icon" rel="shortcut icon" />
	
    <link rel="stylesheet" type="text/css" href="../css/bootstrap.min.css" />
    <link rel="stylesheet" type="text/css" href="../css/admin.css" />
</head>
<body>
<?php
	if(!isset($_SESSION)){
		session_start();
	}
	
	if(isset($_SESSION['user_email'])) {
		unset($_SESSION['user_email']);
		$_SESSION = array();
		session_destroy();
		
		header( 'Location: login.html' );
	} else {
		?>
			<h1>Error</h1>
			You are not currently logged in, logout failed. 
			Please <a href="index.html">click here</a> to go back to the main page.
		<?php
	}
?>
</body>
</html>