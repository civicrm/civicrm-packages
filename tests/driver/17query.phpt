--TEST--
DB_driver::query
--SKIPIF--
<?php

/**
 * Tests the query() method for any DBMS.
 *
 * @see      query.inc
 * 
 * @package  DB
 * @version  $Id$
 * @category Database
 * @author   Daniel Convissor <danielc@analysisandsolutions.com>
 * @internal
 */

chdir(dirname(__FILE__));
require_once './skipif.inc';

?>
--FILE--
<?php
require_once './mktable.inc';
require_once '../query.inc';
?>
--EXPECT--
delete: okay
insert: okay
insert: okay
a = 17, b = one
a = 17, b = two
delete: okay
