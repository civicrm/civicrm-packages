--TEST--
DB_driver::quote
--SKIPIF--
<?php chdir(dirname(__FILE__)); require_once './skipif.inc'; ?>
--FILE--
<?php
require_once './connect.inc';


/**
 * Local error callback handler.
 *
 * Drops the phptest table, prints out an error message and kills the
 * process.
 *
 * @param object  $o  PEAR error object automatically passed to this method
 * @return void
 * @see PEAR::setErrorHandling()
 */
function pe($o) {
    global $dbh;

    $dbh->setErrorHandling(PEAR_ERROR_RETURN);
    $dbh->query('DROP TABLE pearquote');

    die($o->toString());
}


$dbh->setErrorHandling(PEAR_ERROR_RETURN);
$dbh->query('DROP TABLE pearquote');
$dbh->query("CREATE TABLE pearquote (n DECIMAL(3,1), s VARCHAR(8))");

$dbh->setErrorHandling(PEAR_ERROR_CALLBACK, 'pe');

$strings = array(
    "'",
    "\"",
    "\\",
    "%",
    "_",
    "''",
    "\"\"",
    "\\\\",
    "\\'\\'",
    "\\\"\\\""
);
$nums = array(
    12.3,
    15,
);

echo "String escape test: ";
foreach ($strings as $s) {
    $quoted = $dbh->quoteSmart($s);
    $dbh->query("INSERT INTO pearquote VALUES (1, $quoted)");
}
$diff = array_diff($strings, $res = $dbh->getCol("SELECT s FROM pearquote"));
if (count($diff) > 0) {
    echo "FAIL";
    print_r($strings);
    print_r($res);
} else {
    echo "OK";
}

$dbh->query("DELETE FROM pearquote");

echo "\nNumber escape test: ";
foreach ($nums as $n) {
    $quoted = $dbh->quoteSmart($n);
    $dbh->query("INSERT INTO pearquote VALUES ($quoted, 'foo')");
}

$diff = array();
$res =& $dbh->getCol('SELECT n FROM pearquote ORDER BY n');
foreach ($nums as $key => $val) {
    if ($val != $res[$key]) {
        $diff[] = "$val != {$res[$key]}";
    }
}

if (count($diff) > 0) {
    echo "FAIL";
    print_r($nums);
    print_r($res);
} else {
    echo "OK\n";
}


$dbh->setErrorHandling(PEAR_ERROR_RETURN);
$dbh->query('DROP TABLE pearquote');

?>
--EXPECT--
String escape test: OK
Number escape test: OK
