<?php

function check_error($e, $expected_db_code, $should_be_error = true) {
    if ($should_be_error) {
        if (DB::isError($e)) {
            if ($e->getCode() == $expected_db_code) {
                print "matches expected outcome\n";
            } else {
                print "UNEXPECTED OUTCOME...\n";
                print '    PEAR::DB errorcode: ' . $e->getCode() . "\n";
                print '    ' . $e->getUserInfo() . "\n";
            }
        } else {
            print "UNEXPECTED OUTCOME... expected error but it wasn't\n";
        }
    } else {
        if (DB::isError($e)) {
            print "UNEXPECTED OUTCOME... didn't expect error but it was\n";
            print '    PEAR::DB errorcode: ' . $e->getCode() . "\n";
            print '    ' . $e->getUserInfo() . "\n";
        } else {
            print "matches expected outcome\n";
        }
    }
}



$dbh->setErrorHandling(PEAR_ERROR_RETURN);


print 'Trying to provoke DB_ERROR_NOSUCHTABLE:  ';
$res = $dbh->query('SELECT * FROM tableThatsBogus');
check_error($res, DB_ERROR_NOSUCHTABLE);


print 'Trying to provoke DB_ERROR_ALREADY_EXISTS for create table:  ';
$res = $dbh->query($test_mktable_query);
check_error($res, DB_ERROR_ALREADY_EXISTS);


print 'Trying to provoke DB_ERROR_NOSUCHTABLE:  ';
$res = drop_table($dbh, 'tableThatsBogus');
check_error($res, DB_ERROR_NOSUCHTABLE);


print 'Trying to provoke DB_ERROR_CONSTRAINT for primary key insert duplicate:  ';
$res = drop_table($dbh, 'a');
$res = $dbh->query('CREATE TABLE a (a INTEGER NOT NULL, PRIMARY KEY (a))');
$res = $dbh->query('INSERT INTO a VALUES (1)');
$res = $dbh->query('INSERT INTO a VALUES (1)');
check_error($res, DB_ERROR_CONSTRAINT);


print 'Trying to provoke DB_ERROR_CONSTRAINT for primary key update duplicate:  ';
$res = $dbh->query('INSERT INTO a VALUES (2)');
$res = $dbh->query('UPDATE a SET a=1 WHERE a=2');
check_error($res, DB_ERROR_CONSTRAINT);


print 'Trying to provoke DB_ERROR_CONSTRAINT for unique key insert duplicate:  ';
$res = drop_table($dbh, 'a');
$res = $dbh->query('CREATE TABLE a (a INTEGER NOT NULL, UNIQUE (a))');
$res = $dbh->query('INSERT INTO a VALUES (1)');
$res = $dbh->query('INSERT INTO a VALUES (1)');
check_error($res, DB_ERROR_CONSTRAINT);


print 'Trying to provoke DB_ERROR_CONSTRAINT for unique key update duplicate:  ';
$res = $dbh->query('INSERT INTO a VALUES (2)');
$res = $dbh->query('UPDATE a SET a=1 WHERE a=2');
check_error($res, DB_ERROR_CONSTRAINT);


print 'Trying to provoke DB_ERROR_CONSTRAINT for foreign key on insert:  ';
$res = drop_table($dbh, 'b');
$res = drop_table($dbh, 'a');
switch ($dbh->phptype) {
    case 'mysql':
    case 'mysqli':
        $res = $dbh->query('CREATE TABLE a (a INT NOT NULL, '
                    . 'PRIMARY KEY (a)) '
                    . 'TYPE=INNODB');
        $res = $dbh->query('CREATE TABLE b (b INT, '
                    . 'INDEX par_ind (b), '
                    . 'FOREIGN KEY (b) REFERENCES a (a)) '
                    . 'TYPE=INNODB');
        break;
    default:
        $res = $dbh->query('CREATE TABLE a (a INTEGER NOT NULL, PRIMARY KEY (a))');
        $res = $dbh->query('CREATE TABLE b (b INTEGER REFERENCES a (a))');
}
$res = $dbh->query('INSERT INTO a (a) values (1)');
$res = $dbh->query('INSERT INTO b (b) values (2)');
switch ($dbh->phptype) {
    case 'sqlite':
        check_error($res, DB_ERROR_CONSTRAINT, false);
        break;
    default:
        check_error($res, DB_ERROR_CONSTRAINT);
}


print 'Trying to provoke DB_ERROR_CONSTRAINT for foreign key on delete:  ';
$res = $dbh->query('INSERT INTO b (b) values (1)');
$res = $dbh->query('DELETE FROM a WHERE a = 1');
switch ($dbh->phptype) {
    case 'sqlite':
        check_error($res, DB_ERROR_CONSTRAINT, false);
        break;
    default:
        check_error($res, DB_ERROR_CONSTRAINT);
}


print 'Trying to provoke DB_ERROR_CONSTRAINT_NOT_NULL on insert:  ';
$res = drop_table($dbh, 'peartestnull');
$res = $dbh->query('CREATE TABLE peartestnull (a CHAR(3) NOT NULL)');
$res = $dbh->query('INSERT INTO peartestnull VALUES (NULL)');
check_error($res, DB_ERROR_CONSTRAINT_NOT_NULL);


print 'Trying to provoke DB_ERROR_CONSTRAINT_NOT_NULL on update:  ';
$res = $dbh->query("INSERT INTO peartestnull VALUES ('one')");
$res = $dbh->query("UPDATE peartestnull SET a = NULL WHERE a = 'one'");
switch ($dbh->phptype) {
    case 'mysql':
    case 'mysqli':
        check_error($res, DB_ERROR_CONSTRAINT_NOT_NULL, false);
        break;
    default:
        check_error($res, DB_ERROR_CONSTRAINT_NOT_NULL);
}


print 'Trying to provoke DB_ERROR_DIVZERO:  ';
// Interbase detects the error on fetching
$res = $dbh->getAll('SELECT 0/0 FROM phptest');
switch ($dbh->phptype) {
    case 'odbc':
        switch ($dbh->dbsyntax) {
            case 'access':
                check_error($res, DB_ERROR_DIVZERO, false);
                break;
            case 'db2':
                check_error($res, DB_ERROR_DIVZERO);
                break;
        }
        break;
    case 'ifx':
    case 'fbsql':
    case 'mssql':
    case 'mysql':
    case 'mysqli':
    case 'sqlite':
    case 'sybase':
        check_error($res, DB_ERROR_DIVZERO, false);
        break;
    default:
        check_error($res, DB_ERROR_DIVZERO);
}


print 'Trying to provoke DB_ERROR_INVALID_NUMBER:  ';
$res = $dbh->query("UPDATE phptest SET a = 'abc' WHERE a = 42");
switch ($dbh->phptype) {
    case 'mysql':
    case 'mysqli':
    case 'sqlite':
        check_error($res, DB_ERROR_INVALID_NUMBER, false);
        break;
    default:
        check_error($res, DB_ERROR_INVALID_NUMBER);
}


print 'Trying to provoke DB_ERROR_NOSUCHFIELD:  ';
$res = $dbh->query('SELECT e FROM phptest');
check_error($res, DB_ERROR_NOSUCHFIELD);


print 'Trying to provoke DB_ERROR_SYNTAX:  ';
$res = $dbh->query('CREATE');
check_error($res, DB_ERROR_SYNTAX);


drop_table($dbh, 'phptest');
drop_table($dbh, 'b');
drop_table($dbh, 'a');
drop_table($dbh, 'peartestnull');

?>
