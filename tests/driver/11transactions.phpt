--TEST--
DB_driver::transaction test
--SKIPIF--
<?php
require_once dirname(__FILE__) . '/skipif.inc';
if (!$dbh->features['transactions']) {
    die('skip this driver does not support transactions');
}
?>
--FILE--
<?php
$needinnodb = true;
require_once dirname(__FILE__) . '/mktable.inc';
require_once dirname(__FILE__) . '/../transactions.inc';
?>
--EXPECT--
1) after autocommit: bing one.  ops=ok
2) before commit: bing one two three.  ops=ok
3) after commit: bing one two three.  ops=ok
4) before rollback: bing one two three four five.  ops=ok
5) after rollback: bing one two three.  ops=ok
6) before autocommit+rollback: bing one two three six seven.  ops=ok
7) after autocommit+rollback: bing one two three six seven.  ops=ok
8) testing that select doesn't disturbe opcount: ok
