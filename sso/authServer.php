<?php

// this is auth server responding to service server verification request
    
include_once 'setup.php';
include_once 'SSOclass.php';

if (!(in_array($_SERVER['REMOTE_ADDR'], $config["SERVICEserverIP"]))){ // if request made from service server
    die(json_encode(array('result' => "False")));
}

if (!((isset($_GET['serverToken']))&&(isset($_GET['userToken']))&&(isset($_GET['act']))&&($_GET['act']=='verify'))){
    die(json_encode(array('result' => "False")));
}

$con = mysqli_connect("localhost",$mySQLusername,$mySQLKey);
mysqli_select_db($con,"YourDB");

$verifyRequest = new verify();

if ($verifyRequest->TokenCheck($con,$_GET['serverToken'],$_GET['userToken'],$config['preShareKey'])){

    echo $verifyRequest->generateFeedback($con,$_GET['userToken']);
    
}

die();

?>
