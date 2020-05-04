<?php

include_once 'setup.php';

noDirectAccess();


// service provider login service
class authMaintainer{
    
    public function statusCheck(){
        
        if ((isset($_SESSION["loggedIn"]))&&($_SESSION["loggedIn"])){
            return True;
        }
        return False;
        
    }
    
    public function logIn(){

        $_SESSION["loggedIn"] = True;

    }
    
    public function logOut(){

        $_SESSION["loggedIn"] = False;
        if (isset($_SESSION["SSOusername"])){
            $_SESSION["SSOusername"] = "";
        }
        if (isset($_SESSION["SSOloggedIn"])){
            $_SESSION["SSOloggedIn"] = False;
        }

    }
    
}


// service provider authentication with identity provider
class SSOserviceServer{
    
    private $config;
    public function __construct($config) {
        $this->config = $config;
    }
    
    public function verifyUserToken(){

        $hashStr = hash('sha256', $this->config['preShareKey'].$_GET['token']); // this is to verify the service server identity

        $query = array(
            'act' => 'verify',
            'serverToken' => $hashStr,
            'userToken' => $_GET['token']
        );

        $ctx = stream_context_create(array('http'=>
            array(
                'timeout' => 3,  // in seconds
            )
        ));
        
        $feedback = file_get_contents("https://".join(DIRECTORY_SEPARATOR, array($this->config['SSOdomain'], $this->config['SSOrootPath'], $this->config['SSOauthPath']))."?".http_build_query($query), false, $ctx);

        $feedback = json_decode($feedback, true);

        // check if user IP is the same as in SSO database, prevent hijacking
        if ( (!(isset($feedback['result'])))||(!(isset($feedback['ip'])))||($feedback['result']!='True')||($feedback['ip']!=$_SERVER["REMOTE_ADDR"])) {
            return False;
        }
        
        $_SESSION["username"] = $feedback['username'];
        
        return True;
        
    }
    
}



?>
