<?php

define("DB_ENGINE", "mysql");
define("DB_HOST", "localhost");
define("DB_USER", "root");
define("DB_PASS", "");
define("DB_PORT", 3306 );
define("CHAT_DB", "ajax_chat");
define("EDXAPP_DB", "edxapp");
define("DB_PREFIX", "");
define('ENCRYPTION_KEY', 'S9kv9034kLAU0338dh2rfSFW3');

define ("PDO_CHAT", sprintf("mysql:host=%s;port=%d;dbname=%s", DB_HOST, DB_PORT, CHAT_DB));
define ("PDO_EDXAPP", sprintf("mysql:host=%s;port=%d;dbname=%s", DB_HOST, DB_PORT, EDXAPP_DB));

// dbhchatexapp is edxapp
try {
  $dbhchat = new PDO(PDO_CHAT, DB_USER, DB_PASS, arr_pdo_attr());
  $dbhchatexapp = new PDO(PDO_EDXAPP, DB_USER, DB_PASS, arr_pdo_attr());
} catch(PDOException $e) {
  echo "ERROR: " . $e->getMessage();
}

$debugme =0;
$form = get_form();

// "i'm looking to see if i'm already on a team"
$stmt = $dbhchat->prepare("select * from teams where user = ? and room = ? ");
$stmt->execute(array($form["user"],$form["room"]));
$rows = $stmt->fetch();

if($rows["team_seed"]){
  // i'm on a team
  $myteam = $rows["team_seed"];

}else{
  // "is there an existing open team?"
  $stmt = $dbhchat->prepare("select * from teams where room = ? and full = 0 ");
  $stmt->execute(array($form["room"]));
  $rows = $stmt->fetch();

  if(!$rows["team_seed"]){
    if($debugme == 1){print "debug: Start new team <br>";}

    // if no team, make one for me
    $seedme = md5(time()+$form["room"]);
    $myteam =  $seedme;
    $stmt = $dbhchat->prepare("insert into teams (id,room,user,full,team_seed) values (DEFAULT,?,?,0,?) ");
    $stmt->execute(array($form["room"],$form["user"],$seedme));

  }else{

    if($debugme == 1){print "debug: Add to team ".$rows["team_seed"]."<br>";}
    // or add me to an existing team
    $stmt = $dbhchat->prepare("insert into teams (id,room,user,full,team_seed) values (DEFAULT,?,?,0,?) ");
    $stmt->execute(array($form["room"],$form["user"],$rows["team_seed"]));
  }
}


$jump = 0;

$stmt = $dbhchat->prepare("select count(*) from teams where team_seed = ?");
$stmt->execute(array($myteam));
$rows = $stmt->fetch();

if($rows[0] == $form["queue"]){
  // the team is the required size
  $jump = 1;
  if($debugme == 1){print "debug: Team is full jump to chat <br>";}
  // mark as complete
  $stmt = $dbhchat->prepare("update teams set full = 1 where team_seed = ? ");
  $stmt->execute(array($myteam));

  /* Set up cohorts. This assumes that only the values 1:n of
   course_user_group_id in the table course_groups_coursecohort are being used,
   where n = $form["queue"]. This is required because ajax_chat does not
   contain any info about course_id. If more than n cohorts exist on the edx
   instance, only the first n will be used, so these should be the "active"
   ones. If less than n cohorts exist, nothing is done.
  */

  $stmt = $dbhchatexapp->prepare("SELECT count(*) FROM course_groups_coursecohort");
  $stmt->execute();
  $n_cohorts = $stmt->fetch();
  if($debugme == 1){
    print "Queue = ".$form["queue"]."<br>";
    print "Cohorts found = ".$n_cohorts[0]."<br>";
  }

  if($n_cohorts[0] >= $form["queue"]){

    // First, get the edx user_id of the team members
    $stmt = $dbhchat->prepare("SELECT user FROM teams WHERE team_seed = ? ");
    $stmt->execute(array($myteam));

    // Loop over user_id because WHERE doesnt accept a vector ...
    for ($i = 0; $i < $form["queue"]; $i++){
    $temp = $stmt->fetch();

    $stmt2 = $dbhchatexapp->prepare("SELECT user_id FROM auth_userprofile WHERE name = ? ");
    $stmt2->execute(array($temp["user"]));
    $user_id = $stmt2->fetch();

    $stmt3 = $dbhchatexapp->prepare("UPDATE course_groups_courseusergroup_users SET courseusergroup_id = ? WHERE user_id = ? ");
    $stmt3->execute(array($i+1, $user_id["user_id"]));
    }
  }
}

/* missing condition! sometimes the team queue becomes over full
 because of latency, if multiple people click start nearly at teh same time
 the simplest fix seems to be to delete the whole team queue
 because then it will just get regenerated...
*/

if($rows[0] > $form["queue"]){

  if($debugme == 1){print "debug: Team is over full! <br>";}
  // clear it!
  $stmt = $dbhchat->prepare("DELETE FROM teams WHERE team_seed = ? ");
  $stmt->execute(array($myteam));
}

if($jump == 1){

print "<script>$('#clock').countdown('pause');$('#searching').val(0);$('#bot2').hide();$('#bot3').show();$('#chatkey').val('".$myteam."');</script>";

}else{

print "<script>keepsearch();$('#chatkey').val('".$myteam."');</script>";
}

//-----------------------------------------------------------------------------
// Functions follow
//-----------------------------------------------------------------------------

// Return PDO:MySQL attributes
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

// Common get data from Form @return array $form
function get_form() {
  $form = array();

  if (getenv("REQUEST_METHOD") == "POST") {
    while (list($name,  $value)  =  each($_POST)) {
      $form[$name]  =  utf8_encode(strip_tags ($value));
    }
  }else{
    $query_string  =  getenv("QUERY_STRING");
    $query_array  =  split("&",  $query_string);
    while (list($key,  $val)  =  each($query_array)) {
      list($name,  $value)  =  split("=",  $val);
      $name  =  urldecode($name);
      $value  =  strip_tags (urldecode($value));
      $form[$name]  =  utf8_encode(htmlspecialchars($value));
    }
  }
  return $form;
}

?>
