--TEST--
DB_driver::numRows
--SKIPIF--
<?php chdir(dirname(__FILE__)); require_once './skipif.inc'; ?>
--FILE--
<?php
require_once './mktable.inc';
require_once '../numrows.inc';
?>
--EXPECT--
1 from first
2 from 0
3 from 1
4 from 2
5 from 3
6 from 4
5 from > 0
4 from < 4
2 from 5 and 6 not deleted
0 from < 0
