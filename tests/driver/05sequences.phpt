--TEST--
DB_driver::sequences
--SKIPIF--
<?php
chdir(dirname(__FILE__));
require_once dirname(__FILE__) . '/skipif.inc';
$tableInfo = $dbh->dropSequence('ajkdslfajoijkadie');
if (DB::isError($tableInfo) && $tableInfo->code == DB_ERROR_NOT_CAPABLE) {
    die("skip $tableInfo->message");
}
?>
--FILE--
<?php
require_once dirname(__FILE__) . '/connect.inc';
require_once dirname(__FILE__) . '/../sequences.inc';
?>
--EXPECT--
an error is the proper response here
an error cought by the error handler is good
a=1
b=2
b-a=1
c=1
d=1
e=1
