<?php
require './setup.inc';
error_reporting(E_ALL);
require_once "DB.php";
$dbh = DB::connect(DRIVER_DSN, array('optimize' => 'portability'));
if (DB::isError($dbh)) {
    die("connect.inc: ".$dbh->toString());
}

function debug_die($o){
    die($o->toString());
}

$table = "phptest_" .  md5(uniqid(rand()));
$table = substr($table, 0, 15);

$table = "phptest";
?>
