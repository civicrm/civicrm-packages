--TEST--
DB_oci8::tableInfo test
--SKIPIF--
<?php include("skipif.inc"); ?>
--FILE--
<?php
include dirname(__FILE__)."/mktable.inc";
print "Printing output for dbh->tableInfo('phptest')\n";
print_r($dbh->tableInfo('phptest'));
print "Printing output for res->tableInfo()\n";
$res = $dbh->query('SELECT * FROM phptest');
print_r($res->tableInfo());
?>
--EXPECT--
Printing output for dbh->tableInfo('phptest')
Array
(
    [0] => Array
        (
            [table] => PHPTEST
            [name] => A
            [type] => NUMBER
            [len] => 22
            [format] =>
            [nullable] => 1
            [default] =>
        )

    [1] => Array
        (
            [table] => PHPTEST
            [name] => B
            [type] => VARCHAR2
            [len] => 40
            [format] =>
            [nullable] => 1
            [default] =>
        )

    [2] => Array
        (
            [table] => PHPTEST
            [name] => C
            [type] => LONG
            [len] => 0
            [format] =>
            [nullable] => 1
            [default] =>
        )

    [3] => Array
        (
            [table] => PHPTEST
            [name] => D
            [type] => VARCHAR2
            [len] => 20
            [format] =>
            [nullable] => 1
            [default] =>
        )

    [num_fields] => 4
)
Printing output for res->tableInfo()
Array
(
    [0] => Array
        (
            [name] => A
            [type] => NUMBER
            [len] => 22
            [table] => A
            [format] =>
            [nullable] =>
            [default] =>
        )

    [1] => Array
        (
            [name] => B
            [type] => VARCHAR
            [len] => 40
            [table] => B
            [format] =>
            [nullable] => 1
            [default] =>
        )

    [2] => Array
        (
            [name] => C
            [type] => LONG
            [len] => 2147483647
            [table] => PHPTEST
            [format] =>
            [nullable] => 1
            [default] =>
        )

    [3] => Array
        (
            [name] => D
            [type] => VARCHAR
            [len] => 20
            [table] => PHPTEST
            [format] =>
            [nullable] => 1
            [default] =>
        )

    [num_fields] => 4
)
