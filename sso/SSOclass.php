<?php

include_once 'setup.php';

noDirectAccess();


// user login authentication
class auth{
    
    private $passSalt;

    public function __construct() {
        
        $this->passSalt[0]="YourSalt1";
        $this->passSalt[1]="YourSalt2";

    }
    
    public function sessionValid($con){
        
        if (array_key_exists("ip_address",$_SESSION)){
            if ($_SERVER["REMOTE_ADDR"] == $_SESSION["ip_address"]) { // check if session hijack
                $result = mysqli_query($con,"SELECT * FROM SSO
                                        WHERE sessionID='".session_id()."'");
                $row = mysqli_fetch_array($result);
                if  (!empty($row)){
                    return True;
                }
            }
        }
        return False;
    }
    
    public function createSessionUser($con){
        
        $_SESSION["ip_address"] = $_SERVER["REMOTE_ADDR"];
        $_SESSION["SSOloggedIn"] = False;
        if (!(mysqli_query($con,"INSERT INTO SSO 
        (sessionID, createTime, Token, loginAttempts,failedAttempts, IPaddress, lastAttemptTime, serverQueryCounts,attemptUsername) 
        VALUES ('".session_id()."','".time()."','','0','0','".$_SERVER["REMOTE_ADDR"]."','','0','')"))){
            echo "MySQL Error.";
            return False;
        }
        return True;
    }
    
    public function addLoginAttempt($con){

        $result = mysqli_query($con,"SELECT loginAttempts FROM SSO WHERE sessionID ='".session_id()."' LIMIT 1");

        $row = mysqli_fetch_assoc($result);
        $updated = (int)$row['loginAttempts'] + 1;
 
        if (!(mysqli_query($con,"UPDATE SSO SET loginAttempts='".(string)$updated."'  WHERE sessionID ='".session_id()."'"))){
            echo "MySQL Error.";
            return False;
        }
        if (!(mysqli_query($con,"UPDATE SSO SET lastAttemptTime='".time()."'  WHERE sessionID ='".session_id()."'"))){
            echo "MySQL Error.";
            return False;
        }
        return True;
    }
    
    public function addFailedAttempt($con){

        $result = mysqli_query($con,"SELECT failedAttempts FROM SSO WHERE sessionID ='".session_id()."' LIMIT 1");

        $row = mysqli_fetch_assoc($result);
        $updated = (int)$row['failedAttempts'] + 1;

        if (!(mysqli_query($con,"UPDATE SSO SET failedAttempts='".(string)$updated."'  WHERE sessionID ='".session_id()."'"))){
            echo "MySQL Error.";
            return False;
        }

        return True;
    }
    
    public function credentialsCheck($con,$username,$password){

        $EnteredUsername = htmlspecialchars(strtolower(mysqli_real_escape_string($con,$username)));
        
        // update attempts username
        if (!(mysqli_query($con,"UPDATE SSO SET attemptUsername='".$EnteredUsername."'  WHERE sessionID ='".session_id()."'"))){
            echo "MySQL Error.";
            return False;
        }
        
        // check Accounts table 
        $result = mysqli_query($con,"SELECT * FROM Accounts
                                        WHERE Username='".$EnteredUsername."' LIMIT 1");
        $row = mysqli_fetch_array($result);
        
        if (empty($row)) {
            return False;
        }
        
        $SHA256Password = $this->passSalt[0].htmlspecialchars(mysqli_real_escape_string($con,$password)).$this->passSalt[1];
        $SHA256Password = hash('sha256', $SHA256Password);
        
        if ($SHA256Password == $row['Password']) {
                        
            return True;
        }

        return False;
    }
    
    public function addSuccessLogin($con,$username){

        $SSOtoken = generateRandomString(128); // SSO token to be taken to service server
        $COOKIEtoken = generateRandomString(128); // Cookie token to be stored in user browser
        
        // log IP
        if (!(mysqli_query($con,"UPDATE Accounts SET P1 = '".$_SERVER['REMOTE_ADDR']."' WHERE Username = '".$username."'"))){
            echo "MySQL Error to Update.";
            die();
        }
        
        // log SSO token
        if (!(mysqli_query($con,"UPDATE Accounts SET P2 = '".$SSOtoken."' WHERE Username = '".$username."'"))){
            echo "MySQL Error to Update.";
            die();
        }
        
        // log user agent
        if (!(mysqli_query($con,"UPDATE Accounts SET P3 = '".substr($_SERVER['HTTP_USER_AGENT'], 0, 255)."' WHERE Username = '".$username."'"))){ // substr to trim the user agent if it gets longer than 255
            echo "MySQL Error to Update.";
            die();
        }
        
        // log user cookie token
        if (!(mysqli_query($con,"UPDATE Accounts SET P4 = '".$COOKIEtoken."' WHERE Username = '".$username."'"))){
            echo "MySQL Error to Update.";
            die();
        }
        
        $_SESSION["SSOusername"] = $username;
        $_SESSION["SSOloggedIn"] = True;
        
        return $SSOtoken;
        
    }
    
    public function SSOlogOut(){ // not being used

        $_SESSION["SSOusername"] = "";
        $_SESSION["SSOloggedIn"] = False;
        
    }
    
    public function captchaCheck(){

        if(!(isset($_POST['g-recaptcha-response']))){
            return False;
        }
        
        $captcha = htmlspecialchars($_POST['g-recaptcha-response']);

        $response=json_decode(file_get_contents("https://www.google.com/recaptcha/api/siteverify?secret=YourSecret&response=".$captcha."&remoteip=".$_SERVER['REMOTE_ADDR']), true);

        if($response['success'] == false){
            return False;
        }
        
        return True;
     
    }
}


// service provider query response
class verify{
    
    public function TokenCheck($con,$serverToken,$userToken,$preShareKey){
        
        // check Accounts table to find matching $userToken
        $result = mysqli_query($con,"SELECT * FROM Accounts
                                        WHERE P2='$userToken' LIMIT 1");
        $row = mysqli_fetch_array($result);

        if (empty($row)) {
            return False;
        }
 
        // extra check on server identity by hash the pre shared key
        if ($serverToken != hash('sha256', $preShareKey.$row['P2'])){
            return False; // mismatch, failed
        }

        return True;
        
    }
    
    public function generateFeedback($con,$userToken){

        $feedback = array('result' => "False", 
                          'ip' => "", 
                          'username' => "",
                          //'timestamp' => time()
                         );

        // check Accounts table to find matching $userToken
        $result = mysqli_query($con,"SELECT * FROM Accounts
                                        WHERE P2='".$userToken."' LIMIT 1");
        $row = mysqli_fetch_array($result);
        
        if (empty($row)) {
            return json_encode($feedback);
        }
        
        // erase one-time token
        if (!(mysqli_query($con,"UPDATE Accounts SET P2 = '' WHERE P2='".$userToken."'"))){
            die(json_encode(array('result' => "False",'error'=>"MySQL")));
        }
        
        $feedback['result'] = "True";
        $feedback['ip'] = $row['P1'];
        $feedback['username'] = $row['Username'];
        
        return json_encode($feedback);
        
    }
}
?>
