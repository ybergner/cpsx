<?php
include "dbdefine.php";
include "formfunctions.php";

$debugme =0;
$form = get_form();

#miro si estoy ya en un team

$sdata = array($form["room"]);
$stmt = $dbhchat->prepare("select * from teams where team_seed = ? ");
$stmt->execute($sdata);
$rows = $stmt->fetchAll();


foreach($rows as $peer){
  $mates = $mates." ".$peer["user"].",";
}
print substr($mates,0,(strlen($mates)-1));

?>
