--TEST--
DB_driver::numCols
--SKIPIF--
<?php require_once dirname(__FILE__) . '/skipif.inc'; ?>
--FILE--
<?php
require_once dirname(__FILE__) . '/mktable.inc';
require_once dirname(__FILE__) . '/../numcols.inc';
?>
--EXPECT--
1
2
3
4
