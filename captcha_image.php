<?php
require_once('helpers/Captcha.class.php');

$image = new Securimage();

$image->image_width = 250;
$image->image_height = 80;
$image->perturbation = 0.85;
$image->image_bg_color = new Securimage_Color("#999");
$image->use_transparent_text = true;
$image->num_lines = 3;
$image->line_color = new Securimage_Color("#ccc");

$image->show('images/captcha/bg1.jpg');