<?php

include_once 'setup.php';
include_once 'serviceServer.php';


// if redirected back from identity provider
if ((isset($_GET['token']))&&(isset($_GET['act']))&&($_GET['act']=='auth')){
    
    $server = new SSOserviceServer($config);
    $user = new authMaintainer();
    if ($server->verifyUserToken()){ // if token verified
        $user->logIn();
        header("Location: ../".$config['loginSuccessRedirect']);
    }
    else { // redirect to error page
        header("Location: https://".join(DIRECTORY_SEPARATOR, array($config['SSOdomain'], $config['SSOrootPath'], $config['SSOerrPath']))); 
    }
    die();
    
}

// new visit redirect to login page
$query = array( // no need to configure this for new SSO service server
    'service' => $_SERVER['HTTP_HOST']
);

header("Location: https://".join(DIRECTORY_SEPARATOR, array($config['SSOdomain'], $config['SSOrootPath']))."?".http_build_query($query));

die();

?>