<?php

require_once './setup.inc';

error_reporting(E_ALL);

if (substr(DRIVER_DSN, 0, 5) == 'mssql') {
    ini_set('mssql.textlimit', 4096);
    ini_set('mssql.textsize', 4096);
}

require_once 'DB.php';

$options = array(
    //'optimize' => 'portability',
    'portability' => DB_PORTABILITY_ALL,
);

$dbh =& DB::connect(DRIVER_DSN, $options);
if (DB::isError($dbh)) {
    die("connect.inc: ".$dbh->toString());
}

function debug_die($o){
    die($o->toString());
}

?>
