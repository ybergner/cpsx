<?php
include "dbdefine.php"; // mysql details

try {
  $dbhchat = new PDO(PDO_CHAT, DB_USER, DB_PASS, arr_pdo_attr());
  $dbhedxapp = new PDO(PDO_EDXAPP, DB_USER, DB_PASS, arr_pdo_attr());
  echo "so far so good";
} catch(PDOException $e) {
  echo "ERROR: " . $e->getMessage();
}

$stmt = $dbhchat->prepare("select * from teams where
                            user = ? and room = ? and course = ?
                            and full = 1 and team_seed != '' ");
$stmt->execute(array($_GET["user"],$_GET["room"],$_GET["course"]));
$rows = $stmt->fetch();


?>
