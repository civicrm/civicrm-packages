<?php

if (!defined('PATH_SEPARATOR')) {
    if (stristr(PHP_OS, 'WIN')) {
        define('PATH_SEPARATOR', ';');
    } else {
        define('PATH_SEPARATOR', ':');
    }
}
ini_set('include_path', realpath(dirname(__FILE__) . '/../..')
        . PATH_SEPARATOR . '.' . PATH_SEPARATOR
        . ini_get('include_path'));

//define('DRIVER_DSN', '');
define('DRIVER_DSN', 'pgsql://Shmigegie:176z.-ak&&U@localhost/stp');
//define('DRIVER_DSN', 'mysql://Shmigegie:176z.-ak&&U@/stp');
//define('DRIVER_DSN', 'oci8://Shmigegie:176z.-ak&&U@/stp');
//define('DRIVER_DSN', 'ibase://Shmigegie:176z.-ak&&U@/d:/interbase/stp.gdb');
//define('DRIVER_DSN', 'mssql://Shmigegie:176z.-ak&&U@/stp');
//define('DRIVER_DSN', 'sybase://Shmigegie:176z.-ak&&U@base2k/stp');
//define('DRIVER_DSN', 'sqlite://Shmigegie:176z.-ak&&U@base2k/d:/sqlite/stp?mode=0666');

?>
