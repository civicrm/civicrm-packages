--TEST--
DB_driver::simpleQuery test
--SKIPIF--
<?php include("skipif.inc"); ?>
--FILE--
<?php
include("mktable.inc");
include("../simplequery.inc");
?>
--EXPECT--
resource
