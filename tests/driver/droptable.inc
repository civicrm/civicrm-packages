<?php

/**
 * Drops a table.
 *
 * @package  DB
 * @version $Id$
 * @category Database
 * @internal
 */

function drop_table($dbh, $table) {
    switch ($dbh->phptype) {
        case 'fbsql':
            $res = $dbh->query("DROP TABLE $table CASCADE");
            break;
        default:
            $res = $dbh->query("DROP TABLE $table");
    }
    return $res;
}

?>
