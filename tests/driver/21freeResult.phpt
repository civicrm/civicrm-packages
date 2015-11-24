--TEST--
DB_driver::freeResult
--SKIPIF--
<?php
require_once dirname(__FILE__) . '/skipif.inc';
?>
--FILE--
<?php
require_once dirname(__FILE__) . '/mktable.inc';

$res = $dbh->query('SELECT * FROM phptest');

if (DB::isError($res)) {
    echo "Result is a DB_Error.\n";
    drop_table($dbh, 'phptest');
    exit;
}

if ($dbh->freeResult($res->result)) {
    echo "Result was freed successfully.\n";
} else {
    echo "Error freeing result.\n";
}

drop_table($dbh, 'phptest');
?>
--EXPECT--
Result was freed successfully.
