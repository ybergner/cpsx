<?php


define("DB_ENGINE", "mysql");
define("DB_HOST", "localhost");
define("DB_USER", "root");
define("DB_PASS", "");
define("DB_PORT", 3306 );
define("DB_NAME", "CPSX");

define("DB_PREFIX", "");
define('ENCRYPTION_KEY', 'S9kv9034kLAU0338dh2rfSFW3');
define ("PDO_DSN", sprintf("mysql:host=%s;port=%d;dbname=%s", DB_HOST, DB_PORT, DB_NAME));

//
try {
    $dbh = new PDO(PDO_DSN, DB_USER, DB_PASS, arr_pdo_attr());
} catch(PDOException $e) {
    echo "ERROR: " . $e->getMessage();
}

$form = get_form();

print time()."<br>";
#print "<pre>";print_r($form);print "</pre>";

$sdata = array($form["user"],$form["room"]);
$stmt = $dbh->prepare("select count(*) from group_spool where user = ? and room = ? and room_key = '' ");
$stmt->execute($sdata);
$rows = $stmt->fetch();

$total_users = $rows[0];

if($total_users == 0){

$sdata = array($form["user"],$form["room"]);
$stmt = $dbh->prepare("insert into group_spool (user,room) values (?,?) ");
$stmt->execute($sdata);


print "addint to spool<br>";
}



$sdata = array($form["room"]);
$stmt = $dbh->prepare("select * from group_spool where room = ? and room_key = '' ");
$stmt->execute($sdata);
$rows = $stmt->fetchALL();
$c=1;
foreach($rows as $member){


print $c.":". $member["user"]."<br>";

$c++;
}


$key = md5(time()+$form["room"]);
print "Group size: ".($c-1)."/".$form["queue"]."<br>";


if(($c-1) == $form["queue"]){

print "<script>$('#searching').val(0);$('#bot1').hide();$('#bot2').hide();$('#bot3').show();$('#chatkey').val('".$key."')</script>";

}else{

print "<script>if($('#searching').val() == 1){setTimeout(chekme,12000);}</script>";
}



/**
 * Return PDO:MySQL attributes
 */
function arr_pdo_attr() {
    $arr_pdo_attrs = array (
        PDO::ATTR_AUTOCOMMIT => true,
        PDO::ATTR_EMULATE_PREPARES => true,
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_ORACLE_NULLS => true,
        PDO::ATTR_PERSISTENT => false,
        PDO::ATTR_PREFETCH => true,
        PDO::ATTR_TIMEOUT => 10,
        PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8',
    );
    return $arr_pdo_attrs;
}






/**
 * Common Get Data From Form
 * @return array $form
 */
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
