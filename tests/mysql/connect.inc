<?php

include_once dirname(dirname(dirname(__FILE__)))."/DB.php";
$dbh = DB::connect("mysql://localhost/test");
if (DB::isError($dbh)) {
    die("connect.inc: ".$dbh->getMessage());
}

?>
