<?php


$config = array();

$config['basePath'] = dirname(dirname(__FILE__));

$baseName = basename(dirname(dirname(__FILE__)));
$baseName = (empty($baseName) || ($baseName == 'htdocs') || ($baseName == 'public_html')) ? '' : '/' . $baseName;

$config['baseName'] = $baseName;

$config['encryptionKey'] = 'wEn231d3lLn@tAl13NaTh@nAMeL1@$3lEn31wa863@%^nN$';

$config['database'] = array(
							'host'=>'localhost',
							'username'=>'root',
							'password'=>'',
							'name'=>'paxispro'
						);


/*$config['database'] = array(
							'host'=>'localhost',
							'username'=>'plp4eder_eric',
							'password'=>'Comp0$tYre54)',
							'name'=>'plp4eder_puchlistpro'
						);*/
						
define('DB_HOST', $config['database']['host']);
define('DB_USER', $config['database']['username']);
define('DB_PASSWORD', $config['database']['password']);
define('DB_NAME', $config['database']['name']);
define('ENCRYPTION_KEY', $config['encryptionKey']);
define('EMAIL_PATTERN', '/^([a-zA-Z0-9_\-\.]+)@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.)|(([a-zA-Z0-9\-]+\.)+))([a-zA-Z]{2,4}|[0-9]{1,3})(\]?)$/');

?>