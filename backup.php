<?php
require_once("config/main.config.php");

$day = date("d",time());
$month = date("m",time());
$year = date("Y",time());
$dirname = "backups/backup_{$day}_{$month}_{$year}";

if(!file_exists($dirname))
	mkdir("{$dirname}");
	
if(file_exists($dirname))
{
	shell_exec("rsync -av --progress * {$dirname}/ --exclude backups --exclude images");
	shell_exec("mysqldump -u ".$config['database']['username']." --password='".$config['database']['password']."' --insert-ignore --skip-add-drop-table --complete-insert --opt ".$config['database']['name']." > {$dirname}/db.sql");
	print "Backup successful";
}
?>