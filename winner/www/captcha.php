<?php
session_start();
$_SESSION = array();

include("bower_components/simple-php-captcha-master/simple-php-captcha.php");
$_SESSION['captcha'] = simple_php_captcha();

?>