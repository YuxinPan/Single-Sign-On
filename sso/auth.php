<?php

// user authentication

include_once 'setup.php';
include_once 'SSOclass.php';

$con = mysqli_connect("localhost",$mySQLusername,$mySQLKey);
mysqli_select_db($con,"YourDB");

$user = new auth();

if (!($user->sessionValid($con))){ // if user not come from index.php with session started
    header("Location: err.php");
    die();
}

if (!($user->addLoginAttempt($con))){ // add login attempts count
    echo "Auth Server Error";
    die();
}

// if already logged in with session
if ($_SESSION["SSOloggedIn"]){
    $SSOtoken = $user->addSuccessLogin($con,$_SESSION["SSOusername"]);
    
    $query = array(
        'act' => 'auth',
        'token' => $SSOtoken
    );
    
    // redirect to service server
    header("Location: https://".join(DIRECTORY_SEPARATOR, array($_SESSION['SSOservice'], $config['SERVICErootPath']))."?".http_build_query($query));
    die();
}

// Captcha service check
if (!($user->captchaCheck())) {
    if (!($user->addFailedAttempt($con))){ // add failed login attempts count
        echo "Auth Server Error"; // if adding not successful
        die();
    }
    $_SESSION['message'] = "Error in the verification process.";
    header("Location: err.php");
    die();
}
    
// if incomplete credentials
if ( (!isset($_POST["Username"])) || (empty($_POST["Username"])) || (!isset($_POST["Password"])) || (empty($_POST["Password"]))) {
    if (!($user->addFailedAttempt($con))){ // add failed login attempts count
        echo "Auth Server Error"; // if adding not successful
        die();
    }

    $_SESSION['message'] = "Incorrect login credentials.";
    header("Location: err.php");
    die();
}

// credential check
if (!($user->credentialsCheck($con,$_POST["Username"],$_POST["Password"]))){ // check usrname and password
    if (!($user->addFailedAttempt($con))){ // add failed login attempts count
        echo "Auth Server Error"; // if adding not successful
        die();
    }

    $_SESSION['message'] = "Incorrect login credentials.";
    header("Location: err.php");
    die();
}
else { // if successful
    $SSOtoken = $user->addSuccessLogin($con,$_POST["Username"]);
    
    $query = array(
        'act' => 'auth',
        'token' => $SSOtoken
    );
    
    // redirect to service server
    header("Location: https://".join(DIRECTORY_SEPARATOR, array($_SESSION['SSOservice'], $config['SERVICErootPath']))."?".http_build_query($query));
    die();
}

?>
