<?php
include "dbdefine.php";

$sdata = array($_GET["user"],$_GET["room"]);
$stmt = $dbhchat->prepare("delete from teams where user = ? and room = ? ");
$stmt->execute($sdata);

?>
