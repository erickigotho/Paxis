<?php
if(!isset($_SESSION)){
	session_start();
}

require_once(dirname(dirname(__FILE__)) . '/modules/Company.class.php');

$companyName = !isset($_POST['companyName']) ? "": $_POST['companyName'];

$companyObj = new Company();
$companyObj->addCompany($companyName);
?>