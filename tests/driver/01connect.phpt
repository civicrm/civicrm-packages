--TEST--
DB_driver::connect
--INI--
error_reporting = 2047
--SKIPIF--
<?php chdir(dirname(__FILE__)); require_once './skipif.inc'; ?>
--FILE--
<?php
require_once './connect.inc';

if (is_object($dbh)) {
    print "\$dbh is an object\n";
}
switch ($dbh->phptype) {
    case 'mysqli':
        if (is_a($dbh->connection, 'mysqli')) {
            print "\$dbh is connected\n";
        } else {
            print "\$dbh NOT connected\n";
        }
        break;
    default:
        if (gettype($dbh->connection) == 'resource') {
            print "\$dbh is connected\n";
        } else {
            print "\$dbh NOT connected\n";
        }
}


$test_array_dsn = DB::parseDSN(DRIVER_DSN);

foreach ($test_array_dsn as $key => $value) {
    if ($value === false) {
        unset($test_array_dsn[$key]);
    }
}

$dbha =& DB::connect($test_array_dsn, $options);
if (DB::isError($dbha)) {
    die("connect.inc: ".$dbha->toString());
}
if (is_object($dbha)) {
    print "\$dbha is an object\n";
}
switch ($dbh->phptype) {
    case 'mysqli':
        if (is_a($dbha->connection, 'mysqli')) {
            print "\$dbha is connected\n";
        } else {
            print "\$dbha NOT connected\n";
        }
        break;
    default:
        if (gettype($dbha->connection) == 'resource') {
            print "\$dbha is connected\n";
        } else {
            print "\$dbha NOT connected\n";
        }
}

?>
--EXPECT--
$dbh is an object
$dbh is connected
$dbha is an object
$dbha is connected
