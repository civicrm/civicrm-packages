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
 
c:\>sqlite.exe ./test.db

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
require_once 'XML/sql2xml.php';

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
    $result = $db->query("INSERT INTO $table VALUES ('dummy', '" . date('Y-m-j H:i:s') . "');");
}
 
echo "\n-------------------------------------------------------------\n";

// Get all ( getAll() à la DB/common.php )
$arr = $db->getAll("SELECT * FROM $table;", null, DB_FETCHMODE_ASSOC );
print_r($arr);

echo "\n-------------------------------------------------------------\n";
echo "Advantages of an SQLite PEAR implementation, e.g. for sql2xml:\n";

// Examples: http://php.chregu.tv/sql2xml/

$result = $db->getAll("SELECT * FROM $table;", null, DB_FETCHMODE_ASSOC );
$sql2xml = &new XML_sql2xml();
$xmlstring = $sql2xml->getXML($result );
print_r($xmlstring);

/*
// Expected output to be like:
<?xml version="1.0"?>
<root>
	<result>
		<row>
			<comment>dummy</comment>
			<datetime>2003-06-23 22:22:52</datetime>
		</row>
		<row>
			<comment>dummy</comment>
			<datetime>2003-06-23 22:22:53</datetime>
		</row>
		<row>
			<comment>dummy</comment>
			<datetime>2003-06-23 22:22:54</datetime>
		</row>
	</result>
</root>

*/

// Disconnect from the database
$db->disconnect();
?>
