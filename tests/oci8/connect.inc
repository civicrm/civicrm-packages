<?php

if (!defined('PATH_SEPARATOR')) {
    if (stristr(PHP_OS, 'WIN')) {
        define('PATH_SEPARATOR', ';');
    } else {
        define('PATH_SEPARATOR', ':');
    }
}
ini_set('include_path', realpath(dirname(__FILE__) . '/../..')
        . PATH_SEPARATOR . '.' . PATH_SEPARATOR
        . ini_get('include_path'));

require_once "DB.php";
$dbh = DB::connect("oci8://system:manager@test", array('debug' => 2));
if (PEAR::isError($dbh)) {
    die($dbh->tostring());
}

?>
