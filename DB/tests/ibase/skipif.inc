<?php
// This script prints "skip" unless:
// * the ibase extension is built-in or loadable, AND
// * there is a database called "test" accessible
//   with no username/password, AND
if (!extension_loaded("interbase") && ini_get("enable_dl")) {
  $dlext = (substr(PHP_OS, 0, 3) == "WIN") ? ".dll" : ".so";
  @dl("interbase$dlext");
}
if (!extension_loaded("interbase")) {
    die("skip\n");
}
$conn = @ibase_connect("pear/DB/tests/ibase/test.gdb");
if (!is_resource($conn)) {
    die("skip\n");
}
?>
