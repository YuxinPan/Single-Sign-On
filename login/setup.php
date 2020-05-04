<?php


// Prevent direct access
function noDirectAccess(){
    if(count(get_included_files()) ==1) { 
        header("HTTP/1.0 404 Not Found");
        exit;
    }
}


//////////////////////////////////////////////////////////////////////


noDirectAccess();


include_once '../tokens.php'; // this should be your file contains tokens to various services, e.g. $loginSSLKey



// if no ssl_key to override, then default with HTTPS
if ((!(isset($_GET['ssl_key'])))||($_GET['ssl_key']!=$loginSSLKey)){
    if(!isset($_SERVER['HTTPS']) || $_SERVER['HTTPS'] == ""){
        $redirect = "https://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
        header("Location: $redirect");
        die();
    }
}

// Start session
if (!isset($_SESSION)) { // start session if not yet started
    session_start();
    date_default_timezone_set('UTC'); // Sync time
}

$config = array( // config array
    "SSOserverIP" => "YourIP",                      // identity provider server IP
    "SSOdomain" => "YourDomain",                    // identity provider domain // without HTTPS://
    "SSOrootPath" => "YourPath",                    // sso folder path
    "SSOauthPath" => "authServer.php",
    "SSOerrPath" => "err.php",
    "SERVICEserverIP" => array("YourServiceIP"),    // service provider server IP
    "SERVICErootPath" => "login",
    "loginSuccessRedirect" => "YourLoginPath",      // path redirect when logged in
    "SSL" => "authServerOn",                        // off, authServerOn, allOn # can turn off SSL for diagnosis purpose
    "preShareKey" => "YourPreShareKey"              // pre-share key/salt between identity provider and service provider
);


?>