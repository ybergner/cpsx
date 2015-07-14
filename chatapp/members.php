<?php


define("DB_ENGINE", "mysql");
define("DB_HOST", "localhost");
define("DB_USER", "root");
define("DB_PASS", "");
define("DB_PORT", 3306 );
define("DB_NAME", "ajax_chat");

define("DB_PREFIX", "");
define('ENCRYPTION_KEY', 'S9kv9034kLAU0338dh2rfSFW3');
define ("PDO_DSN", sprintf("mysql:host=%s;port=%d;dbname=%s", DB_HOST, DB_PORT, DB_NAME));

//
try {
    $dbh = new PDO(PDO_DSN, DB_USER, DB_PASS);
} catch(PDOException $e) {
    echo "ERROR: " . $e->getMessage();
}


$debugme =0;
$form = get_form();

#miro si estoy ya en un team

$sdata = array($form["room"]);
$stmt = $dbh->prepare("select * from teams where team_seed = ? ");
$stmt->execute($sdata);
$rows = $stmt->fetchAll();


foreach($rows as $peer){

$mates = $mates." ".$peer["user"].",";

}

print substr($mates,0,(strlen($mates)-1));

function get_form() {

    $form = array();

    if (getenv("REQUEST_METHOD") == "POST") {
        while  (list($name,  $value)  =  each($_POST)) {
            $form[$name]  =  utf8_encode(strip_tags ($value));
        }
    }
    else {
        $query_string  =  getenv("QUERY_STRING");
        $query_array  =  split("&",  $query_string);
        while  (list($key,  $val)  =  each($query_array)) {
            list($name,  $value)  =  split("=",  $val);
            $name  =  urldecode($name);
            $value  =  strip_tags (urldecode($value));
            $form[$name]  =  utf8_encode(htmlspecialchars($value));
        }
    }
    return $form;
}
?>
