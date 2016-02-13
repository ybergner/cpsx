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

?>
