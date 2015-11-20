--TEST--
DB_driver::freeResult
--SKIPIF--
<?php
require_once dirname(__FILE__) . '/skipif.inc';
die("skip for everyone, note that phptype = $dbh->phptype");
if ($dbh->phptype == 'mysqli') die ('skip mysqli returns result objects rather than resources');
?>
--FILE--
<?php
require_once dirname(__FILE__) . '/mktable.inc';

$res = $dbh->query('SELECT * FROM phptest');

if (DB::isError($res)) {
    echo "Result is a DB_Error.\n";
}

if (is_resource($res->result)) {
    echo "Result includes resource.\n";
} else {
    echo "Result does not include a resource!\n";
    print_r($res->result);
}

if ($dbh->freeResult($res->result)) {
    echo "Resource was freed successfully.\n";
} else {
    echo "Error freeing result.\n";
}

drop_table($dbh, 'phptest');
?>
--EXPECT--
Result includes resource.
Resource was freed successfully.
