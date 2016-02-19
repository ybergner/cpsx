<?php
include "dbdefine.php";

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

?>
