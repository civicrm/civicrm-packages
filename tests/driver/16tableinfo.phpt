--TEST--
DB_driver::tableInfo
--SKIPIF--
<?php

/**
 * Generic test for the tableInfo() methods found in the various drivers.
 *
 * @see      tableinfo_driver.inc
 * 
 * @package  DB
 * @version  $Id$
 * @category Database
 * @author   Daniel Convissor <danielc@analysisandsolutions.com>
 * @internal
 */

chdir(dirname(__FILE__));
require_once './skipif.inc';
$tableInfo = $db->tableInfo('ajkdslfajoijkadie');
if (DB::isError($tableInfo) && $tableInfo->code == DB_ERROR_NOT_CAPABLE) {
    die("skip $tableInfo->message");
}

?>
--FILE--
<?php
//  $Id$

require_once './mktable.inc';
require_once '../tableinfo_driver.inc';
?>
--EXPECT--
==========================================
Passing result OBJECT to method in DB_<type>.
Output = default.
------------------------------------------

first field:
table ... matched expected value
name => a
type ... matched expected value
len ... matched expected value
flags ... matched expected value

eight field:
table ... matched expected value
name => d
type ... matched expected value
len ... matched expected value
flags ... matched expected value

==========================================
Passing result ID to method in DB_<type>.
Output = DB_TABLEINFO_ORDER.
------------------------------------------

first field:
table ... matched expected value
name => a
type ... matched expected value
len ... matched expected value
flags ... matched expected value

fourth field:
table ... matched expected value
name => d
type ... matched expected value
len ... matched expected value
flags ... matched expected value

num_fields:
8

order:
a => 4
b => 5
c => 6
d => 7
fk => 1

==========================================
Passing DB_TABLEINFO_ORDERTABLE to method in DB_result.
Output = DB_TABLEINFO_ORDERTABLE.
------------------------------------------

first field:
table ... matched expected value
name => a
type ... matched expected value
len ... matched expected value
flags ... matched expected value

fourth field:
table ... matched expected value
name => d
type ... matched expected value
len ... matched expected value
flags ... matched expected value

num_fields:
8

ordertable[phptest]:
matched expected values

ordertable[phptest_fk]:
matched expected values

==========================================
Passing TABLE NAME 'phptest_fk' to method in DB_<driver>.
Output = default.
------------------------------------------

first field:
table ... matched expected value
name => a
type ... matched expected value
len ... matched expected value
flags ... matched expected value

second field:
table ... matched expected value
name => fk
type ... matched expected value
len ... matched expected value
flags ... matched expected value

third field:
table ... matched expected value
name => c
type ... matched expected value
len ... matched expected value
flags ... matched expected value

fourth field:
table ... matched expected value
name => d
type ... matched expected value
len ... matched expected value
flags ... matched expected value

==========================================
Passing TABLE NAME 'phptest_fk' to method in DB_<driver>.
Output = DB_TABLEINFO_FULL.
------------------------------------------

first field:
table ... matched expected value
name => a
type ... matched expected value
len ... matched expected value
flags ... matched expected value

order:
a => 0
fk => 1
c => 2
d => 3

ordertable[phptest_fk]:
a => 0
fk => 1
c => 2
d => 3

==========================================
Passing TABLE NAME 'phptest_fk' to method in DB_<driver> AGAIN.
Output = DB_TABLEINFO_FULL.
------------------------------------------

first field:
table ... matched expected value
name => a
type ... matched expected value
len ... matched expected value
flags ... matched expected value
