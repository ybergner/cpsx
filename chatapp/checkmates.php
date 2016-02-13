<?php

define("DB_ENGINE", "mysql");
define("DB_HOST", "localhost");
define("DB_USER", "root");
define("DB_PASS", "");
define("DB_PORT", 3306 );
define("CHAT_DB", "cpsx_chat");
define("EDXAPP_DB", "edxapp");
define("DB_PREFIX", "");
define('ENCRYPTION_KEY', 'S9kv9034kLAU0338dh2rfSFW3');

define ("PDO_CHAT",
  sprintf("mysql:host=%s;port=%d;dbname=%s", DB_HOST, DB_PORT, CHAT_DB));
define ("PDO_EDXAPP",
  sprintf("mysql:host=%s;port=%d;dbname=%s", DB_HOST, DB_PORT, EDXAPP_DB));

try {
  $dbhchat = new PDO(PDO_CHAT, DB_USER, DB_PASS, arr_pdo_attr());
  $dbhedxapp = new PDO(PDO_EDXAPP, DB_USER, DB_PASS, arr_pdo_attr());
} catch(PDOException $e) {
  echo "ERROR: " . $e->getMessage();
}

$debugme =0;
$form = get_form();

// plus signs used in edX course_ids encode spaces for PHP,
// so we need to do this
$form["course"] =  str_replace(" ", "+", $form["course"]);

// check if i'm already on a team
$stmt = $dbhchat->prepare("select * from teams where user = ? and room = ? and course = ? ");
$stmt->execute(array($form["user"],$form["room"],$form["course"]));
$rows = $stmt->fetch();

if($rows["team_seed"]){
  // already on a team
  $myteam = $rows["team_seed"];

}else{ // need to assign to existing team or create one
  // "is there an existing open team?"
  $stmt = $dbhchat->prepare("select * from teams where room = ? and course = ? and full = 0 ");
  $stmt->execute(array($form["room"],$form["course"]));
  $rows = $stmt->fetch();

  if(!$rows["team_seed"]){
    if($debugme == 1){print "debug: Start new team <br>";}

    // if no team, make one for me
    $seedme = md5(time()+$form["room"]);
    $myteam =  $seedme;
    $stmt = $dbhchat->prepare("insert into teams
    (id,room,user,course,full,team_seed) values (DEFAULT,?,?,?,0,?) ");
    $stmt->execute(array($form["room"],$form["user"],$form["course"],$seedme));

  }else{

    if($debugme == 1){print "debug: Add to team ".$rows["team_seed"]."<br>";}
    // or add me to an existing team
    $stmt = $dbhchat->prepare("insert into teams
    (id,room,user,course,full,team_seed) values (DEFAULT,?,?,?,0,?) ");
    $stmt->execute(
    array($form["room"],$form["user"],$form["course"],$rows["team_seed"])
  );
}
}


$fullroom = 0;

$stmt = $dbhchat->prepare("select count(*) from teams where team_seed = ?");
$stmt->execute(array($myteam));
$rows = $stmt->fetch();

if($rows[0] == $form["queue"]){// the team is the required size
  $fullroom = 1;
  if($debugme == 1){print "debug: Team is full jump to chat <br>";}
  // mark as complete
  $stmt = $dbhchat->prepare("update teams set full = 1 where team_seed = ? ");
  $stmt->execute(array($myteam));

  // Now assign cohorts, if applicable
  // first check if course is actually cohorted
  $checkforcohorts = $dbhedxapp->prepare("SELECT is_cohorted
    FROM course_groups_coursecohortssettings
    WHERE course_id = ?");
  $checkforcohorts->execute(array($form["course"]));
  $is_cohorted = $checkforcohorts->fetch();

  if ($is_cohorted[0]==1) {
    // echo $form["course"]." is cohorted!"."<br>";
    $getcohortnames = $dbhedxapp->prepare("SELECT name, id
      FROM course_groups_courseusergroup
      WHERE course_id = ? ");
    $getcohortnames->execute(array($form["course"]));
    // this is the awesome way:
    $cohort_names = $getcohortnames->fetchAll(PDO::FETCH_KEY_PAIR);
    // print_r($cohort_names);
    $assign_order = array($cohort_names['Group_A'],
    $cohort_names['Group_B'],
    $cohort_names['Default Group']);

    $stmt = $dbhchat->prepare("SELECT user FROM teams WHERE team_seed = ? ");
    $stmt->execute(array($myteam));
    $teammates = $stmt->fetchAll();
    // print_r($teammates);

    for ($i = 0; $i < $form["queue"]; $i++){
      $stmt2 = $dbhedxapp->prepare("SELECT user_id FROM auth_userprofile
        WHERE name = ? ");
      $stmt2->execute(array($teammates[i]["user"]));
      $user_id = $stmt2->fetch();

      $stmt3 = $dbhedxapp->prepare("UPDATE
          course_groups_courseusergroup_users
          SET courseusergroup_id = ? WHERE user_id = ? ");
      $stmt3->execute(array($assign_order[i], $user_id["user_id"]));
    }
  }
  else if ($is_cohorted[0]==0) {
    // echo $form["course"]." is not cohorted!"."<br>";
  }

  //
  // $stmt = $dbhedxapp->prepare("SELECT count(*) FROM
  //                                 course_groups_coursecohort");
  // $stmt->execute();
  // $n_cohorts = $stmt->fetch();
  // if($debugme == 1){
  //   print "Queue = ".$form["queue"]."<br>";
  //   print "Cohorts found = ".$n_cohorts[0]."<br>";
  // }

  // if($n_cohorts[0] >= $form["queue"]){
  //
  //   // First, get the edx user_id of the team members
  //   $stmt = $dbhchat->prepare("SELECT user FROM teams WHERE team_seed = ? ");
  //   $stmt->execute(array($myteam));
  //
  //   // Loop over user_id because WHERE doesnt accept a vector ...
  //   for ($i = 0; $i < $form["queue"]; $i++){
  //     $temp = $stmt->fetch();
  //
  //     $stmt2 = $dbhedxapp->prepare("SELECT user_id FROM auth_userprofile WHERE name = ? ");
  //     $stmt2->execute(array($temp["user"]));
  //     $user_id = $stmt2->fetch();
  //
  //     $stmt3 = $dbhedxapp->prepare("UPDATE course_groups_courseusergroup_users SET courseusergroup_id = ? WHERE user_id = ? ");
  //     $stmt3->execute(array($i+1, $user_id["user_id"]));
  //   }
  // }
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

if($fullroom == 1){
  print "<script>$('#clock').countdown('pause');$('#searching').val(0);$('#bot2').hide();$('#bot3').show();$('#chatkey').val('".$myteam."');</script>";

}else{
  print "<script>keepsearch();$('#chatkey').val('".$myteam."');</script>";
}

//-----------------------------------------------------------------------
// Functions follow
//-----------------------------------------------------------------------

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
