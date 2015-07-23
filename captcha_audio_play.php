<?php
require_once('helpers/Captcha.class.php');

$image = new Securimage();
$image->outputAudioFile();