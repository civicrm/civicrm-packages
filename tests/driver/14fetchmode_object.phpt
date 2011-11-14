--TEST--
DB_driver::fetchmode object
--SKIPIF--
<?php require_once dirname(__FILE__) . '/skipif.inc'; ?>
--FILE--
<?php
require_once dirname(__FILE__) . '/mktable.inc';
require_once dirname(__FILE__) . '/../fetchmode_object.inc';
?>
--EXPECT--
--- fetch with param DB_FETCHMODE_OBJECT ---
stdclass -> a b cc d
stdclass -> a b cc d
--- fetch with default fetchmode DB_FETCHMODE_OBJECT ---
stdclass -> a b cc d
stdclass -> a b cc d
--- fetch with default fetchmode DB_FETCHMODE_OBJECT and class DB_row ---
db_row -> a b cc d
db_row -> a b cc d
--- fetch with default fetchmode DB_FETCHMODE_OBJECT with no class then DB_row ---
stdclass -> a b cc d
db_row -> a b cc d
