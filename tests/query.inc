<?php

/**
 * Calls the query() method in various ways against any DBMS.
 *
 * @see      17query.phpt
 * @see      DB_common::query()
 * 
 * @package  DB
 * @version  $Id$
 * @category Database
 * @author   Daniel Convissor <danielc@analysisandsolutions.com>
 * @internal
 */


$dbh->setErrorHandling(PEAR_ERROR_DIE);
$dbh->setFetchMode(DB_FETCHMODE_ASSOC);


$res =& $dbh->query('DELETE FROM phptest WHERE a = 17');
print 'delete: ' . ($res == DB_OK ? 'okay' : 'error') . "\n";

$res =& $dbh->query("INSERT INTO phptest (a, b) VALUES (17, 'one')");
print 'insert: ' . ($res == DB_OK ? 'okay' : 'error') . "\n";

$res =& $dbh->query('INSERT INTO phptest (a, b) VALUES (?, ?)', array(17, 'two'));
print 'insert: ' . ($res == DB_OK ? 'okay' : 'error') . "\n";


$res =& $dbh->query('SELECT a, b FROM phptest WHERE a = 17');
$row = $res->fetchRow();
print "a = {$row['a']}, b = {$row['b']}\n";

$res =& $dbh->query('SELECT a, b FROM phptest WHERE b = ?', array('two'));
$row = $res->fetchRow();
print "a = {$row['a']}, b = {$row['b']}\n";


$res =& $dbh->query('DELETE FROM phptest WHERE a = ?', array(17));
print 'delete: ' . ($res == DB_OK ? 'okay' : 'error') . "\n";

?>
