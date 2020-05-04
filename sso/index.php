<?php

// user login interface // CSS style to be applied

include_once 'setup.php';
include_once 'SSOclass.php';

$con = mysqli_connect("localhost",$mySQLusername,$mySQLKey);
mysqli_select_db($con,"my_db");

$user = new auth();

if (!($user->sessionValid($con))){
    if (!($user->createSessionUser($con))){
        echo "Single Sign-on Server Error";
        die();
    }
}


if ((isset($_GET['service'])) && (in_array($_GET['service'], $domainForSSO))){
    $_SESSION['SSOservice'] = $_GET['service'];
}
else {
    $_SESSION['SSOservice'] = $domainForSSO[1];
}

// if already logged in with session
if ($_SESSION["SSOloggedIn"]){

    // redirect to auth server
    header("Location: ".$config['SSOauthPage']);
    die();
}


$captcha = True;


?><!DOCTYPE HTML><html><head><title>Single Sign-on - Yuxin Pan</title><link rel="shortcut icon" href="/favicon.ico"/><meta name="keywords" content="Yuxin Pan,login,Single Sign-on,SSO"><meta name="description" content="Single Sign-on page for Yuxin Pan."><meta name="author" content="Yuxin Pan"><meta http-equiv="Content-Type" content="text/html; charset=UTF-8" /><meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1"><?php 
    

    if ($captcha){
        echo "<script src='https://www.google.com/recaptcha/api.js'></script>";
    }


?><script>function GetFocus(){ document.all.<?php 


    echo "Username";


?>.focus();}</script></head><body onload="GetFocus()"><div class="message warning"><div class="inset"><div class="login-head"><h1>Log in to <?php 


    echo $_SESSION['SSOservice'];


?></h1></div><form action="auth.php" method="post"><input type="text" name="Username" class="text" placeholder="<?php


    echo 'Username"';


?> onfocus="this.value = '';" onblur="if (this.value == '') {this.value = 'Username';}"><a  class="icon user"></a><div class="clear"></div><input type="password" name="Password" placeholder="Password " onfocus="this.value = '';" onblur="if (this.value == '') {this.value = 'Password';}"><a  class="icon lock"></a><div class="clear"></div>
<?php 


    if ($captcha){
        echo "<div class=\"g-recaptcha\" data-sitekey=\"YourRecaptchaKey\"></div>";
    } 


?>
<div class="submit"><input type="submit" name="submit" value="Log In"><div class="clear"></div></div></form></div></div><div class="clear"></div>
<?php


    echo "<div class=\"footer\"><p>Single Sign-On Yuxin Pan. Copyright © ".date("Y").". All Rights Reserved.</p></div>";


?></body></html>