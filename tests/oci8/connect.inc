<?php
$path = dirname(__FILE__) . "/../../..";
ini_set('include_path', realpath($path));
require_once "DB.php";
$dbh = DB::connect("oci8://system:manager@test", array('debug' => 2));
if (PEAR::isError($dbh)) {
    die($dbh->tostring());
}
?>
