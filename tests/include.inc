<?php

error_reporting(E_ALL);

if (!defined('PATH_SEPARATOR')) {
    if (stristr(PHP_OS, 'WIN')) {
        define('PATH_SEPARATOR', ';');
    } else {
        define('PATH_SEPARATOR', ':');
    }
}

/*
 * If the path to your PEAR installation is found in the left hand
 * portion of the if() expression below, that means this file has
 * come from the PEAR installer.  Therefore, let's use the
 * installed version of DB, which should be found via the
 * computer's default include_path.  Add '.' to the include_path
 * to ensure '.' is in there.
 * 
 * If the path has not been substituted in the if() expression,
 * this file has likely come from a CVS checkout or a .tar file.
 * Therefore, we'll assume the tests should use the version of
 * DB that has come from there as well.
 */
if ('@include_path@' != '@'.'include_path'.'@') {
    ini_set('include_path', ini_get('include_path')
            . PATH_SEPARATOR . '.'
    );
} else {
    ini_set('include_path', realpath(dirname(__FILE__) . '/..')
            . PATH_SEPARATOR . '.' . PATH_SEPARATOR
            . ini_get('include_path')
    );
}

?>
