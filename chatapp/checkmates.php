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
    $dbh = new PDO(PDO_DSN, DB_USER, DB_PASS, arr_pdo_attr());
} catch(PDOException $e) {
    echo "ERROR: " . $e->getMessage();
}


$debugme =0;
$form = get_form();

#miro si estoy ya en un team

$sdata = array($form["user"],$form["room"]);
$stmt = $dbh->prepare("select * from teams where user = ? and room = ? ");
$stmt->execute($sdata);
$rows = $stmt->fetch();

if($rows["team_seed"]){

#i'm on a team

	$misala = $rows["team_seed"];

}else{


	#miro si hay team en formacion para este room ?

	$sdata = array($form["room"]);
	$stmt = $dbh->prepare("select * from teams where room = ? and full = 0 ");
	$stmt->execute($sdata);
	$rows = $stmt->fetch();


	if(!$rows["team_seed"]){
	
	if($debugme == 1){print "debug: Start new team <br>";}

	#no team, make it

	$seedme = md5(time()+$form["room"]);
	$misala =  $seedme;
	$sdata = array($form["room"],$form["user"],$seedme);
	$stmt = $dbh->prepare("insert into teams (id,room,user,full,team_seed) values (DEFAULT,?,?,0,?) ");
	$stmt->execute($sdata);

	}else{

	if($debugme == 1){print "debug: Add to team ".$rows["team_seed"]."<br>";}

	$sdata = array($form["room"],$form["user"],$rows["team_seed"]);
        $stmt = $dbh->prepare("insert into teams (id,room,user,full,team_seed) values (DEFAULT,?,?,0,?) ");
        $stmt->execute($sdata);

	}



}


$jump = 0;


$sdata = array($misala);
$stmt = $dbh->prepare("select count(*) from teams where team_seed = ?");
$stmt->execute($sdata);
$rows = $stmt->fetch();


if($rows[0] == $form["queue"]){

$jump = 1;

	if($debugme == 1){print "debug: Team is full jump to chat <br>";}

	#mark as complete
        $sdata = array($misala);
        $stmt = $dbh->prepare("update teams set full = 1 where team_seed = ? ");
        $stmt->execute($sdata);


}


if($jump == 1){

print "<script>$('#clock').countdown('pause');$('#searching').val(0);$('#bot2').hide();$('#bot3').show();$('#chatkey').val('".$misala."');</script>";

}else{

print "<script>keepsearch();$('#chatkey').val('".$misala."');</script>";
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
