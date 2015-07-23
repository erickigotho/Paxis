<?php
if(!isset($_SESSION)){
	session_start();
}

require_once(dirname(dirname(__FILE__)) . '/modules/Company.class.php');

$companyId = !isset($_POST['companyId']) ? "": $_POST['companyId'];
$companyName = !isset($_POST['companyName']) ? "": $_POST['companyName'];

$companyObj = new Company();
$companyObj->updateCompanyInfo($companyId, $companyName);
?>