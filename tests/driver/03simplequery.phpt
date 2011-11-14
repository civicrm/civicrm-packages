--TEST--
DB_driver::simpleQuery
--SKIPIF--
<?php require_once dirname(__FILE__) . '/skipif.inc'; ?>
--FILE--
<?php
require_once dirname(__FILE__) . '/mktable.inc';
require_once dirname(__FILE__) . '/../simplequery.inc';
?>
--EXPECT--
passed
