--TEST--
DB_driver::simpleQuery test
--SKIPIF--
<?php chdir(dirname(__FILE__)); require_once 'skipif.inc'; ?>
--FILE--
<?php
require_once 'mktable.inc';
require_once '../simplequery.inc';
?>
--EXPECT--
resource
