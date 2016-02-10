<?php

error_reporting(E_ERROR | E_WARNING | E_PARSE);


        $sMd5Password = MD5("qwerty");

        $iCookieTime = time() + 24*60*60*30;
        setcookie("member_name", $_GET["user"], $iCookieTime, '/');
        $_COOKIE['member_name'] = $_GET["user"];
        setcookie("member_pass", $sMd5Password, $iCookieTime, '/');
        $_COOKIE['member_pass'] = $sMd5Password;

if(!$_GET["room"]) { $_GET["room"] = 1;  }

	$GLOBALS['myroom'] = $_GET["room"];

$db_server = "localhost";
$db_user = "ajaxchat";
$db_pass = "ajaxchat";

if(!@$conection = mysql_pconnect($db_server, $db_user, $db_pass)) { error("Error SQL 1"); exit;}
mysql_select_db("cpsx_chat") or die("error2");

#si no exoste lo creo

$query = "select * from s_members where name = '".mysql_escape_string($_GET["user"])."' limit 1";
$results = mysql_query($query);
$tipo = mysql_fetch_array($results);

if(!$tipo['id']){


$query = "insert into s_members (name,pass) values ('".mysql_escape_string($_GET["user"])."','d8578edf8458ce06fbc5bb76a58c5ca4')";
mysql_query($query);
}


if($_GET["MSG-EDX"]){

print "[ANSW: ".$_GET["MSG-EDX"]."]";

}

require_once('inc/db.inc.php');
require_once('inc/login.inc.php');
require_once('inc/ajx_chat.inc.php');


$GLOBALS['bLoggedIn'] = '1';

if ($_REQUEST['action'] == 'get_last_messages') {
    $sChatMessages = $GLOBALS['AjaxChat']->getMessages(true);
    require_once('inc/Services_JSON.php');
    $oJson = new Services_JSON();
    echo $oJson->encode(array('messages' => $sChatMessages));
    exit;
}


// draw login box
#echo $GLOBALS['oSimpleLoginSystem']->getLoginBox();

// draw exta necessary files
echo '<link type="text/css" rel="stylesheet" href="templates/css/styles.css" />';
echo '<script type="text/javascript" src="js/jquery-1.4.2.min.js"></script>';
echo '<script type="text/javascript" src="js/main.js"></script>';
#echo"<link href='http://fonts.googleapis.com/css?family=Open+Sans' rel='stylesheet' type='text/css'>";

// draw chat messages
$sChatMessages = $GLOBALS['AjaxChat']->getMessages();

// draw input form + accept inserted texts
if ($GLOBALS['bLoggedIn']) {
    $sChatInputForm = $GLOBALS['AjaxChat']->getInputForm();
    $GLOBALS['AjaxChat']->acceptMessages();
}

echo $sChatInputForm;
echo $sChatMessages;


require_once('templates/footer.html');



?>
