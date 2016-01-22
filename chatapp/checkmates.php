<?php
/* Modified 1/15/2016 by peterhalpin: uses edxapp database to assign cohorts
 to users in the same chatroom; see line 91
*/

define("DB_ENGINE", "mysql");
define("DB_HOST", "localhost");
define("DB_USER", "root");
define("DB_PASS", "");
define("DB_PORT", 3306 );
define("DB_NAME", "ajax_chat");
define("DB2_NAME", "edxapp");
define("DB_PREFIX", "");
define('ENCRYPTION_KEY', 'S9kv9034kLAU0338dh2rfSFW3');

define ("PDO_DSN", sprintf("mysql:host=%s;port=%d;dbname=%s", DB_HOST, DB_PORT, DB_NAME));
define ("PDO_DSN2", sprintf("mysql:host=%s;port=%d;dbname=%s", DB_HOST, DB_PORT, DB2_NAME));

// dbh2 is edxapp
try {
  $dbh = new PDO(PDO_DSN, DB_USER, DB_PASS, arr_pdo_attr());
  $dbh2 = new PDO(PDO_DSN2, DB_USER, DB_PASS, arr_pdo_attr());
} catch(PDOException $e) {
  echo "ERROR: " . $e->getMessage();
}

$debugme =0;
$form = get_form();

// miro si estoy ya en un team
// "i'm looking to see if i'm already on a team"
$sdata = array($form["user"],$form["room"]);
$stmt = $dbh->prepare("select * from teams where user = ? and room = ? ");
$stmt->execute($sdata);
$rows = $stmt->fetch();

if($rows["team_seed"]){

  // i'm on a team
  // mi sala = "my room" but this should probably be "my team"
  $misala = $rows["team_seed"];

}else{

  // miro si hay team en formacion para este room?
  // "is there an existing open team?"
  $sdata = array($form["room"]);
  $stmt = $dbh->prepare("select * from teams where room = ? and full = 0 ");
  $stmt->execute($sdata);
  $rows = $stmt->fetch();

  if(!$rows["team_seed"]){
    if($debugme == 1){print "debug: Start new team <br>";}

    // if no team, make one for me
    $seedme = md5(time()+$form["room"]);
    $misala =  $seedme;
    $sdata = array($form["room"],$form["user"],$seedme);
    $stmt = $dbh->prepare("insert into teams (id,room,user,full,team_seed) values (DEFAULT,?,?,0,?) ");
    $stmt->execute($sdata);

  }else{

    if($debugme == 1){print "debug: Add to team ".$rows["team_seed"]."<br>";}

    // or add me to an existing team
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

  // mark as complete
  $sdata = array($misala);
  $stmt = $dbh->prepare("update teams set full = 1 where team_seed = ? ");
  $stmt->execute($sdata);

  /* Set up cohorts. This assumes that only the values 1:n of
   course_user_group_id in the table course_groups_coursecohort are being used,
   where n = $form["queue"]. This is required because ajax_chat does not
   contain any info about course_id. If more than n cohorts exist on the edx
   instance, only the first n will be used, so these should be the "active"
   ones. If less than n cohorts exist, nothing is done.
  */

  $stmt = $dbh2->prepare("SELECT count(*) FROM course_groups_coursecohort");
  $stmt->execute();
  $n_cohorts = $stmt->fetch();

  if($n_cohorts[0] >= $form["queue"]){

    // First, get the edx user_id of the team members
    $stmt = $dbh->prepare("SELECT user FROM teams WHERE team_seed = ? ");
    $stmt->execute($sdata);

    // Loop over user_id because WHERE doesnt accept a vector ...
    for ($i = 0; $i < $form["queue"]; $i++){
    $temp = $stmt->fetch();
    $user_i = array($temp["user"]);

    $stmt2 = $dbh2->prepare("SELECT user_id FROM auth_userprofile WHERE name = ? ");
    $stmt2->execute($user_i);
    $user_id = $stmt2->fetch();

    $rdata = array($i+1, $user_id["user_id"]);
    $stmt3 = $dbh2->prepare("UPDATE course_groups_courseusergroup_users SET courseusergroup_id = ? WHERE user_id = ? ");
    $stmt3->execute($rdata);
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
  $sdata = array($misala);
  $stmt = $dbh->prepare("delete from teams where team_seed = ? ");
  $stmt->execute($sdata);
}

if($jump == 1){

print "<script>$('#clock').countdown('pause');$('#searching').val(0);$('#bot2').hide();$('#bot3').show();$('#chatkey').val('".$misala."');</script>";

}else{

print "<script>keepsearch();$('#chatkey').val('".$misala."');</script>";
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
