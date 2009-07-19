--TEST--
DB_driver::simpleQuery
--INI--
error_reporting = 2047
--SKIPIF--
<?php require_once dirname(__FILE__) . '/skipif.inc'; ?>
--FILE--
<?php
require_once dirname(__FILE__) . '/mktable.inc';
require_once dirname(__FILE__) . '/../simplequery.inc';
?>
--EXPECT--
passed
