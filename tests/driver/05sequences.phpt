--TEST--
DB_driver::sequences
--INI--
error_reporting = 2047
--SKIPIF--
<?php
error_reporting(E_ALL);
chdir(dirname(__FILE__));
require_once './skipif.inc';
$tableInfo = $db->dropSequence('ajkdslfajoijkadie');
if (DB::isError($tableInfo) && $tableInfo->code == DB_ERROR_NOT_CAPABLE) {
    die("skip $tableInfo->message");
}
?>
--FILE--
<?php
require_once './connect.inc';
require_once '../sequences.inc';
?>
--EXPECT--
DB Error: no such table
DB Error: no such table <- good error catched
a=1
b=2
b-a=1
c=1
d=1
e=1
