<?php

/**
 * Tests the drivers' numRows() method
 *
 * Executed by driver/09numrows.phpt
 *
 * PHP versions 4 and 5
 *
 * LICENSE: This source file is subject to version 3.0 of the PHP license
 * that is available through the world-wide-web at the following URI:
 * http://www.php.net/license/3_0.txt.  If you did not receive a copy of
 * the PHP License and are unable to obtain it through the web, please
 * send a note to license@php.net so we can mail you a copy immediately.
 *
 * @category   Database
 * @package    DB
 * @author     Daniel Convissor <danielc@php.net>
 * @copyright  1997-2005 The PHP Group
 * @license    http://www.php.net/license/3_0.txt  PHP License 3.0
 * @version    $Id$
 * @link       http://pear.php.net/package/DB
 */

/**
 * Local error callback handler
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

    print "\n------------\n";
    if ($o->getCode() == DB_ERROR_UNSUPPORTED) {
        print "This DBMS does not support numRows().";
    } elseif ($o->getCode() == DB_ERROR_MISMATCH) {
        print "Mismatch between the number of placeholders and parameters.\n";
        foreach ($o->backtrace as $item => $detail) {
            if ($detail['function'] == 'query') {
                echo 'QUERY: ' . $detail['args'][0] . "\n";
                echo "PARAMETERS:\n";
                print_r($detail['args'][1]);
            }
        }
    } else {
        print $o->getDebugInfo() . "\n";
    }
    exit;
}

$dbh->setErrorHandling(PEAR_ERROR_CALLBACK, 'pe');


$sth = $dbh->query("SELECT a FROM phptest");
switch ($dbh->phptype) {
    case 'ibase':
        /*
         * Interbase doesn't allow manipulating tables that have result
         * sets still open.
         */
        $dbh->freeResult($sth->result);
        break;
}
if (!DB::isError($rows = $sth->numRows())) {
    print "$rows from first\n";
} else {
    print "\n";
}
for ($i = 0; $i < 5; $i++) {
    $sth = $dbh->query("INSERT INTO phptest (a) VALUES ($i)");
    $sth = $dbh->query("SELECT a FROM phptest");
    if (!DB::isError($rows = $sth->numRows())) {
        print "$rows from $i\n";
    } else {
        print "\n";
    }
}

$sth = $dbh->query('SELECT a FROM phptest WHERE a > ?', 0);
if (!DB::isError($rows = $sth->numRows())) {
    print "$rows from > 0\n";
} else {
    print "\n";
}

$sth = $dbh->prepare('SELECT a FROM phptest WHERE a < ?');
$res = $dbh->execute($sth, array(4));
if (!DB::isError($rows = $res->numRows())) {
    print "$rows from < 4\n";
} else {
    print "\n";
}

$dbh->query("DELETE FROM phptest WHERE a < 4");
$sth = $dbh->query("SELECT a FROM phptest");
if (!DB::isError($rows = $sth->numRows())) {
    print "$rows from 5 and 6 not deleted\n";
} else {
    print "\n";
}
$sth = $dbh->query("SELECT a FROM phptest where a < 0");
if (!DB::isError($rows = $sth->numRows())) {
    print "$rows from < 0\n";
} else {
    print "\n";
}


switch ($dbh->phptype) {
    case 'ibase':
        /*
         * Interbase doesn't allow dropping tables that have result
         * sets still open.
         */
        $dbh->freeResult($sth->result);
        break;
}
$dbh->setErrorHandling(PEAR_ERROR_RETURN);
drop_table($dbh, 'phptest');
