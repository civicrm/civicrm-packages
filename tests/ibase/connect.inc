<?php
require_once "DB.php";
$dbh = DB::connect("ibase://localhost/" . dirname(__FILE__). "/test.gdb?dialect=3", array('optimize'=>'portability'));
ibase_timefmt("%Y-%m-%d", IBASE_DATE);
?>
