--TEST--
DB_mysql::simpleQuery test
--SKIPIF--
<?php include("skipif.inc"); ?>
--FILE--
<?php
require_once "DB.php";
include("mktable.inc");
include(dirname(__FILE__)."/../simplequery.inc");
?>
--EXPECT--
resource
