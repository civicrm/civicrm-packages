--TEST--
DB_driver::sequences
--SKIPIF--
<?php chdir(dirname(__FILE__)); require_once 'skipif.inc'; ?>
--FILE--
<?php
require_once 'connect.inc';
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
