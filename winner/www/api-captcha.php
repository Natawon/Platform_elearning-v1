<?php
session_start();
include("bower_components/simple-php-captcha-master/simple-php-captcha.php");
$_SESSION['captcha'] = simple_php_captcha();
exit($_SESSION['captcha']['image_src']);