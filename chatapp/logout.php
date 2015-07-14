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
    $dbh = new PDO(PDO_DSN, DB_USER, DB_PASS);
} catch(PDOException $e) {
    echo "ERROR: " . $e->getMessage();
}




#miro si estoy ya en un team

$sdata = array($_GET["user"],$_GET["seed"]);
$stmt = $dbh->prepare("delete from teams where user = ? and team_seed = ? ");
$stmt->execute($sdata);


print "Logged Out";


?>
