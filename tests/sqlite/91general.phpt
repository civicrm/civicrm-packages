<?php
// +----------------------------------------------------------------------+
// | PHP Version 4                                                        |
// +----------------------------------------------------------------------+
// |                                                                      |
// +----------------------------------------------------------------------+
// | This source file is subject to version 2.0 of the PHP license,       |
// | that is bundled with this package in the file LICENSE, and is        |
// | available at through the world-wide-web at                           |
// | http://www.php.net/license/2_02.txt.                                 |
// | If you did not receive a copy of the PHP license and are unable to   |
// | obtain it through the world-wide-web, please send a note to          |
// | license@php.net so we can mail you a copy immediately.               |
// +----------------------------------------------------------------------+
// | Authors: Urs Gehrig <urs@circle.ch>                                  |
// +----------------------------------------------------------------------+
//
// $Id$

// http://www.google.com/search?q=site:www.sqlite.org+free+result

/*
Array
(
    [0] => sqlite_open
    [1] => sqlite_popen
    [2] => sqlite_close
    [3] => sqlite_query
    [4] => sqlite_fetch_array
    [5] => sqlite_libversion
    [6] => sqlite_libencoding
    [7] => sqlite_changes
    [8] => sqlite_last_insert_rowid
    [9] => sqlite_num_rows
    [10] => sqlite_num_fields
    [11] => sqlite_field_name
    [12] => sqlite_seek
    [13] => sqlite_escape_string
    [14] => sqlite_busy_timeout
    [15] => sqlite_last_error
    [16] => sqlite_error_string
    [17] => sqlite_unbuffered_query
    [18] => sqlite_create_aggregate
    [19] => sqlite_create_function
)
 
c:\>sqlite.exe ./var/testdb

sqlite> .dump
BEGIN TRANSACTION;
CREATE TABLE tbl_3ed32e (comment varchar(50), datetime varchar(50));
INSERT INTO tbl_3ed32e VALUES('Date and Time','2003-05-27 11:22:01');
INSERT INTO tbl_3ed32e VALUES('Date and Time','2003-05-27 11:22:01');
INSERT INTO tbl_3ed32e VALUES('Date and Time','2003-05-27 11:22:01');
INSERT INTO tbl_3ed32e VALUES('Date and Time','2003-05-27 11:22:02');
INSERT INTO tbl_3ed32e VALUES('Date and Time','2003-05-27 11:22:02');
COMMIT;
sqlite> .quit
 
c:\>echo '.dump' | sqlite ./var/testdb | gzip -c > testdb.dump.gz

*/

if (!extension_loaded('sqlite'))
{
    if (!dl(stristr(PHP_OS, "WIN") ? "php_sqlite.dll" : "sqlite.so"))
    {
        exit("Could not load extension SQLITE.\n");
    }
}
else
{
    echo "Extension loaded.\n";    
    echo "-------------------------------------------------------------\n";
    //print_r(get_extension_funcs("sqlite"));
}

function sprint_r($string="") {
    ob_start();
    print_r($string);
    $output = ob_get_contents();
    ob_end_clean();
    return $output;
}

// via PEAR:DB abstraction
require_once 'DB.php';
require_once 'DB/sqlite.php';

//echo $dsn = "sqlite://root:@localhost/u:/sqlite/var/testdb";
$dsn = array (
    'phptype'   => "sqlite",
    'database'  => getcwd() . DIRECTORY_SEPARATOR . "test.db",    
    'mode'      => 0644
);
$conf = array(
    'persistent'=> true 
);

// Create a DB instance
$db = &new DB_sqlite();

// Connect to the databse; if db not exists, a new one will be created
$db->connect($dsn, $conf );

// Give a new table name
$table = "tbl_" .  md5(uniqid(rand()));
$table = substr($table, 0, 10);

// Create a new table
$result = $db->query("CREATE TABLE $table (comment TEXT, datetime TEXT);");

// Insert new values into the table
for($i=0; $i<3; $i++) {
    sleep(1);
    $result = $db->query("INSERT INTO $table VALUES ('Date and Time', '" . date('Y-m-j H:i:s') . "');");
}

// Get the affected rows back
printf("affectedRows:\t\t%s\n", $db->affectedRows() );

// Get the last generated ID
printf("getLastInsertId:\t%s\n", $db->getLastInsertId() );

// Select all from the table
$db->freeResult($result );
$result = $db->query("SELECT * FROM $table;" );

// Fetch the results and print an array
$arr = $db->fetchRow($result, DB_FETCHMODE_ASSOC );
print_r($arr);

// Get the schema of the table
$db->freeResult($result );
$result = $db->query( $db->getSpecialQuery("schema_x", array("table" => $table ) ) );
$arr = $db->fetchRow($result );
print_r($arr);

// Get a dump of all tables
$db->freeResult($result );
$result = $db->query( $db->getSpecialQuery("schema") );
$arr = $db->fetchRow($result );
print_r($arr);

// Get the DB file size
echo "-------------------------------------------------------------\n";
echo "DB file size: \n\t";
print_r($db->getDbFileStats("size" ));

// ALTER TABLE is not supported in SQLite, so this demonstrates the helper query
echo "\n-------------------------------------------------------------\n";
echo "\nAltering the table (I): $table: \n";
$args = array(
    'table' => $table, 
    'rows'  => "id, titel, content, datetime",
    'save'  => "'', '', comment, datetime"
);
// Executes ALTER TABLE and retruns SELECT statement
$query = $db->getSpecialQuery("alter", $args );
$db->query($query );

$db->query("INSERT INTO $table VALUES ('23', 'John', 'Doe', '" . date('Y-m-j H:i:s') . "');" );
$db->query("INSERT INTO $table VALUES ('87', 'Aretha', 'Smith', '" . date('Y-m-j H:i:s') . "');" );
$result = $db->query("SELECT * FROM $table;" );
$arr = $db->fetchRow($result, DB_FETCHMODE_ASSOC );
print_r($arr);

/**
* The dump looks something like this now:
sqlite> .dump
BEGIN TRANSACTION;
CREATE TABLE tbl_fda533 (id, titel, content, datetime);
INSERT INTO tbl_fda533 VALUES('','','Date and Time','2003-05-30 12:29:32');
INSERT INTO tbl_fda533 VALUES('','','Date and Time','2003-05-30 12:29:33');
INSERT INTO tbl_fda533 VALUES('','','Date and Time','2003-05-30 12:29:34');
INSERT INTO tbl_fda533 VALUES(23,'John','Doe','2003-05-30 12:29:35');
INSERT INTO tbl_fda533 VALUES(52,'Aretha','Smith','2003-05-30 12:29:35');
COMMIT;
sqlite>
*/

// ALTER TABLE is not supported in SQLite, so this demonstrates the helper query
echo "\n-------------------------------------------------------------";
echo "\nAltering the table (II): $table: \n";
$args = array(
    'table' => $table, 
    'rows'  => "id INTEGER PRIMARY KEY, firstname TEXT, surname TEXT, datetime TEXT",
    'save'  => "NULL, titel, content, datetime"
);
// Executes ALTER TABLE and returns SELECT statement
$query = $db->getSpecialQuery("alter", $args );
$db->query($query );

$db->query("INSERT INTO $table VALUES (NULL, 'Yannick', 'Gets', '" . date('Y-m-j H:i:s') . "');" );
$db->query("INSERT INTO $table VALUES (NULL, 'Rosa', 'Bennet', '" . date('Y-m-j H:i:s') . "');" );
$result = $db->query("SELECT * FROM $table;" );

echo "\n-------------------------------------------------------------\n";
echo "\nAn example using fetchRow() \n";

// Get each row of data on each iteration until
// there is no more rows
while($row = $db->fetchRow($result, DB_FETCHMODE_ASSOC ) ) {
    echo $row['id'] . ", ";
}

echo "\n-------------------------------------------------------------\n";
echo "\nAn example using fetchInto() \n";
$result = $db->query("SELECT * FROM $table;" );
// An example using fetchInto()
while ($db->fetchInto($result, $row, DB_FETCHMODE_ASSOC )) {
    echo $row['id'] . ", ";
}

/*
foreach($arr AS $key => $val ) {
    echo "Surname ($key): " . trim($val['surname'] ) . "\n";
}
*/
echo "\n-------------------------------------------------------------\n";

// Get all 
$arr = $db->getAll("SELECT * FROM $table;", null, DB_FETCHMODE_ASSOC );
print_r($arr);

// Disconnect from the database
$db->disconnect();
?>
