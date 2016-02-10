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

define ("PDO_CHAT", sprintf("mysql:host=%s;port=%d;dbname=%s", DB_HOST, DB_PORT, CHAT_DB));
define ("PDO_EDXAPP", sprintf("mysql:host=%s;port=%d;dbname=%s", DB_HOST, DB_PORT, EDXAPP_DB));

try {
  $dbhchat = new PDO(PDO_CHAT, DB_USER, DB_PASS, arr_pdo_attr());
  $dbhedxapp = new PDO(PDO_EDXAPP, DB_USER, DB_PASS, arr_pdo_attr());
} catch(PDOException $e) {
  echo "ERROR: " . $e->getMessage();
}

$stmt = $dbhchat->prepare("delete from teams where user = ? and team_seed = ? ");
$stmt->execute(array($_GET["user"],$_GET["seed"]));
print "Logged Out";

// Upon logout, the cohort should also be re-assigned to the DEFAULT
// for now we can identify this as the (first) random assignment cohort
// as the other ones are manually assigned
//
// THIS STILL DOESNT WORK; NOT SURE WHY
//
// $stmt4 = $dbhedxapp ->prepare("SELECT course_user_group_id FROM course_groups_coursecohort WHERE assignment_type = 'random' ");
// $stmt4->execute()
// $rand_group = $stmt4->fetch();
// print $rand_group[0]
//
// $stmt2 = $dbhedxapp->prepare("SELECT user_id FROM auth_userprofile WHERE name = ? ");
// $stmt2->execute(array($_GET["user"]));
// $user_id = $stmt2->fetch();
//
// $stmt3 = $dbhedxapp->prepare("UPDATE course_groups_courseusergroup_users SET courseusergroup_id = ? WHERE user_id = ? ");
// $stmt3->execute(array($rand_group["course_user_group_id"], $user_id["user_id"]));

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


?>
