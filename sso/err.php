<?php

// error page

include_once 'setup.php';

?>
<!DOCTYPE HTML>
    
<html>
    <head>
        <title>Error - Yuxin Pan</title>
        <link href="css/style2.css" rel="stylesheet" type="text/css" media="all" />
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    </head>
    <body>
        <div class="message warning">
            <div class="inset"><div class="login-head"><h1>Error</h1></div>
                <form action="./" method="post">
                    <strong><?php 
    
if (!isset($_SESSION['message'])) {
    $_SESSION['message']="";
}

if ($_SESSION['message']=="") {
    echo "An error occurred.";
}
else {
    echo $_SESSION['message'];
    $_SESSION['message']=""; // clear cache in session
}

?></strong>
                    <div class="clear"></div>
                    <div class="clear"> </div>
                    <div class="submit"><input type="submit" value="Go back"><div class="clear"></div></div>
                </form>
            </div>
        </div>
        <div class="clear"></div><?php 
//if ($version!="mobile_tier1"){
//    echo "<div class=\"footer\"><p>Coded by Yuxin Pan. Copyright © ".date("Y").". All Rights Reserved.</p></div>";} 
?>

    </body>
</html>