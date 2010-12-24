--TEST--
Bug 16617: MSSQL escape doesn't take into account trailing backslashes
--FILE--
<?php
include 'DB.php';
include 'DB/mssql.php';

$dbh = new DB_mssql();

$str = "C:\\\r\nX";
var_dump($str, $dbh->escapeSimple($str));
?>
--EXPECT--
string(6) "C:\
X"
string(9) "C:\\

X"
