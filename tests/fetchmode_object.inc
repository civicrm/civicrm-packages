<?php
error_reporting(E_ALL);

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
    drop_table($dbh, 'phptest');

    die($o->toString());
}


function print_obj(&$obj) {
    if (!is_object($obj)) {
        echo "ERROR: no object!\n";
    } else {
        echo strtolower(get_class($obj)) . ' -> ' . implode(' ', array_keys((array)$obj)) . "\n";
    }
}


$dbh->setErrorHandling(PEAR_ERROR_CALLBACK, 'pe');

echo "--- fetch with param DB_FETCHMODE_OBJECT ---\n";
$sth = $dbh->query("SELECT * FROM phptest");
$row = $sth->fetchRow(DB_FETCHMODE_OBJECT);
print_obj($row);
$sth->free();  // keep fbsql happy.

$sth = $dbh->query("SELECT * FROM phptest");
$sth->fetchInto($row, DB_FETCHMODE_OBJECT);
print_obj($row);
$sth->free();  // keep fbsql happy.

echo "--- fetch with default fetchmode DB_FETCHMODE_OBJECT ---\n";
$dbh->setFetchMode(DB_FETCHMODE_OBJECT);
$sth = $dbh->query("SELECT * FROM phptest");
$row = $sth->fetchRow();
print_obj($row);
$sth->free();  // keep fbsql happy.

$sth = $dbh->query("SELECT * FROM phptest");
$sth->fetchInto($row);
print_obj($row);
$sth->free();  // keep fbsql happy.

echo "--- fetch with default fetchmode DB_FETCHMODE_OBJECT and class DB_row ---\n";
$dbh->setFetchMode(DB_FETCHMODE_OBJECT, 'DB_row');
$sth = $dbh->query("SELECT * FROM phptest");
$row = $sth->fetchRow();
print_obj($row);
$sth->free();  // keep fbsql happy.

$sth = $dbh->query("SELECT * FROM phptest");
$sth->fetchInto($row);
print_obj($row);
$sth->free();  // keep fbsql happy.

echo "--- fetch with default fetchmode DB_FETCHMODE_OBJECT with no class then DB_row ---\n";
$dbh->setFetchMode(DB_FETCHMODE_OBJECT);
$sth = $dbh->query('SELECT * FROM phptest');
$row = $sth->fetchRow();
print_obj($row);
$sth->free();  // keep fbsql happy.

$dbh->setFetchMode(DB_FETCHMODE_OBJECT, 'DB_row');
$sth = $dbh->query('SELECT * FROM phptest');
$row = $sth->fetchRow();
print_obj($row);

$sth->free();  // keep fbsql happy.
               // keep ibase happy: can't drop tbl that has results open against it.

$dbh->setErrorHandling(PEAR_ERROR_RETURN);
drop_table($dbh, 'phptest');

?>
