--TEST--
DB_driver::bug22328
--SKIPIF--
<?php chdir(dirname(__FILE__)); require_once 'skipif.inc'; ?>
--FILE--
<?php
require_once 'connect.inc';
$dbh->setErrorHandling(PEAR_ERROR_RETURN);
$res = $dbh->query('select * from php_limit');
$error = 0;
while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
	if (DB::isError($row) && $error) {
		die('bug');
	}
	$res2 = $dbh->query("FAKE QUERY");
	if (!DB::isError($res2)) {
		die('bug');
	}
	$error = true;
}
?>
--EXPECT--
