<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * The PEAR DB driver for PHP's sqlite extension
 * for interacting with SQLite databases
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
 * @author     Urs Gehrig <urs@circle.ch>
 * @author     Mika Tuupola <tuupola@appelsiini.net>
 * @author     Daniel Convissor <danielc@php.net>
 * @copyright  1997-2005 The PHP Group
 * @license    http://www.php.net/license/3_0.txt  PHP License 3.0 3.0
 * @version    CVS: $Id$
 * @link       http://pear.php.net/package/DB
 */

/**
 * Obtain the DB_common class so it can be extended from
 */
require_once 'DB/common.php';

/**
 * The methods PEAR DB uses to interact with PHP's sqlite extension
 * for interacting with SQLite databases
 *
 * These methods overload the ones declared in DB_common.
 *
 * @category   Database
 * @package    DB
 * @author     Urs Gehrig <urs@circle.ch>
 * @author     Mika Tuupola <tuupola@appelsiini.net>
 * @author     Daniel Convissor <danielc@php.net>
 * @copyright  1997-2005 The PHP Group
 * @license    http://www.php.net/license/3_0.txt  PHP License 3.0 3.0
 * @version    Release: @package_version@
 * @link       http://pear.php.net/package/DB
 */
class DB_sqlite extends DB_common
{
    // {{{ properties

    /**
     * The DB driver type (mysql, oci8, odbc, etc.)
     * @var string
     */
    var $phptype = 'sqlite';

    /**
     * The database syntax variant to be used (db2, access, etc.), if any
     * @var string
     */
    var $dbsyntax = 'sqlite';

    /**
     * The capabilities of this DB implementation
     *
     * The 'new_link' element contains the PHP version that first provided
     * new_link support for this DBMS.  Contains false if it's unsupported.
     *
     * Meaning of the 'limit' element:
     *   + 'emulate' = emulate with fetch row by number
     *   + 'alter'   = alter the query
     *   + false     = skip rows
     *
     * @var array
     */
    var $features = array(
        'limit'         => 'alter',
        'new_link'      => false,
        'numrows'       => true,
        'pconnect'      => true,
        'prepare'       => false,
        'ssl'           => false,
        'transactions'  => false,
    );

    /**
     * A mapping of native error codes to DB error codes
     * @var array
     */
    var $errorcode_map = array(
    );

    /**
     * The raw database connection created by PHP
     * @var resource
     */
    var $connection;

    /**
     * The DSN information for connecting to a database
     * @var array
     */
    var $dsn = array();

    /**
     * SQLite data types
     *
     * @link http://www.sqlite.org/datatypes.html
     *
     * @var array
     */
    var $keywords = array (
        'BLOB'      => '',
        'BOOLEAN'   => '',
        'CHARACTER' => '',
        'CLOB'      => '',
        'FLOAT'     => '',
        'INTEGER'   => '',
        'KEY'       => '',
        'NATIONAL'  => '',
        'NUMERIC'   => '',
        'NVARCHAR'  => '',
        'PRIMARY'   => '',
        'TEXT'      => '',
        'TIMESTAMP' => '',
        'UNIQUE'    => '',
        'VARCHAR'   => '',
        'VARYING'   => '',
    );

    /**
     * The most recent error message from $php_errormsg
     * @var string
     * @access private
     */
    var $_lasterror = '';


    // }}}
    // {{{ constructor

    /**
     * Constructor for this class
     *
     * Error codes according to sqlite_exec.  Error Codes specification is
     * in the {@link http://sqlite.org/c_interface.html online manual}.
     *
     * This errorhandling based on sqlite_exec is not yet implemented.
     *
     * @return void
     */
    function DB_sqlite()
    {
        $this->DB_common();
    }

    // }}}
    // {{{ __wakeup()

    /**
     * Automatically reconnect to the database when PHP's unserialize()
     * function is called
     *
     * @return void
     *
     * @access private
     */
    function __wakeup() {
        DB_sqlite::connect($this->dsn, $this->options);
    }

    // }}}
    // {{{ connect()

    /**
     * Connect to the database server, log in and open the database
     *
     * PEAR DB's sqlite driver supports the following extra DSN options:
     *   + mode  The permissions for the database file, in four digit
     *            chmod octal format (eg "0600").
     *
     * @param array $dsn         the data source name
     * @param bool  $persistent  should the connection be persistent?
     *
     * @return int  DB_OK on success. A DB_error object on failure.
     *
     * @access private
     * @see DB::connect(), DB::parseDSN()
     */
    function connect($dsn, $persistent = false)
    {
        if (!PEAR::loadExtension('sqlite')) {
            return $this->raiseError(DB_ERROR_EXTENSION_NOT_FOUND);
        }

        $this->dsn = $dsn;
        if ($dsn['dbsyntax']) {
            $this->dbsyntax = $dsn['dbsyntax'];
        }

        if ($dsn['database']) {
            if (!file_exists($dsn['database'])) {
                if (!touch($dsn['database'])) {
                    return $this->sqliteRaiseError(DB_ERROR_NOT_FOUND);
                }
                if (!isset($dsn['mode']) ||
                    !is_numeric($dsn['mode']))
                {
                    $mode = 0644;
                } else {
                    $mode = octdec($dsn['mode']);
                }
                if (!chmod($dsn['database'], $mode)) {
                    return $this->sqliteRaiseError(DB_ERROR_NOT_FOUND);
                }
                if (!file_exists($dsn['database'])) {
                    return $this->sqliteRaiseError(DB_ERROR_NOT_FOUND);
                }
            }
            if (!is_file($dsn['database'])) {
                return $this->sqliteRaiseError(DB_ERROR_INVALID);
            }
            if (!is_readable($dsn['database'])) {
                return $this->sqliteRaiseError(DB_ERROR_ACCESS_VIOLATION);
            }
        } else {
            return $this->sqliteRaiseError(DB_ERROR_ACCESS_VIOLATION);
        }

        $connect_function = $persistent ? 'sqlite_popen' : 'sqlite_open';

        // track_errors must remain on for simpleQuery()
        ini_set('track_errors', 1);
        $php_errormsg = '';

        if (!$this->connection = @$connect_function($dsn['database'])) {
            if (empty($php_errormsg)) {
                return $this->sqliteRaiseError(DB_ERROR_NODBSELECTED);
            } else {
                return $this->raiseError(DB_ERROR_NODBSELECTED,
                                         null, null, null,
                                         $php_errormsg);
            }
        }
        return DB_OK;
    }

    // }}}
    // {{{ disconnect()

    /**
     * Log out and disconnect from the database
     *
     * @return bool true on success, false if not connected
     *
     * @todo fix return values
     */
    function disconnect()
    {
        $ret = @sqlite_close($this->connection);
        $this->connection = null;
        return $ret;
    }

    // }}}
    // {{{ simpleQuery()

    /**
     * Send a query to SQLite and returns the results as a SQLite resource
     * identifier
     *
     * @param string $query  the SQL query
     *
     * @return mixed  a valid SQLite result for successful SELECT queries,
     *                DB_OK for other successful queries. A DB error is
     *                returned on failure.
     */
    function simpleQuery($query)
    {
        $ismanip = DB::isManip($query);
        $this->last_query = $query;
        $query = $this->_modifyQuery($query);

        if (!ini_get('track_errors')) {
            // leave it on, since will need it on every time.
            ini_set('track_errors', 1);
        }
        $php_errormsg = '';

        $result = @sqlite_query($query, $this->connection);
        $this->_lasterror = $php_errormsg ? $php_errormsg : '';

        $this->result = $result;
        if (!$this->result) {
            return $this->sqliteRaiseError(null);
        }

        // sqlite_query() seems to allways return a resource
        // so cant use that. Using $ismanip instead
        if (!$ismanip) {
            $numRows = $this->numRows($result);
            if (is_object($numRows)) {
                // we've got PEAR_Error
                return $numRows;
            }
            return $result;
        }
        return DB_OK;
    }

    // }}}
    // {{{ nextResult()

    /**
     * Move the internal sqlite result pointer to the next available result
     *
     * @param resource $result  the valid sqlite result resource
     *
     * @return bool  true if a result is available otherwise return false
     */
    function nextResult($result)
    {
        return false;
    }

    // }}}
    // {{{ fetchInto()

    /**
     * Fetch a row and insert the data into an existing array
     *
     * Formating of the array and the data therein are configurable.
     *
     * @param resource $result    query result identifier
     * @param array    $arr       (reference) array where data from the row
     *                            should be placed
     * @param int      $fetchmode how the resulting array should be indexed
     * @param int      $rownum    the row number to fetch
     *
     * @return mixed DB_OK on success, null when end of result set is
     *               reached or on failure
     *
     * @see DB_result::fetchInto()
     */
    function fetchInto($result, &$arr, $fetchmode, $rownum = null)
    {
        if ($rownum !== null) {
            if (!@sqlite_seek($this->result, $rownum)) {
                return null;
            }
        }
        if ($fetchmode & DB_FETCHMODE_ASSOC) {
            $arr = @sqlite_fetch_array($result, SQLITE_ASSOC);
            if ($this->options['portability'] & DB_PORTABILITY_LOWERCASE && $arr) {
                $arr = array_change_key_case($arr, CASE_LOWER);
            }
        } else {
            $arr = @sqlite_fetch_array($result, SQLITE_NUM);
        }
        if (!$arr) {
            /* See: http://bugs.php.net/bug.php?id=22328 */
            return null;
        }
        if ($this->options['portability'] & DB_PORTABILITY_RTRIM) {
            /*
             * Even though this DBMS already trims output, we do this because
             * a field might have intentional whitespace at the end that
             * gets removed by DB_PORTABILITY_RTRIM under another driver.
             */
            $this->_rtrimArrayValues($arr);
        }
        if ($this->options['portability'] & DB_PORTABILITY_NULL_TO_EMPTY) {
            $this->_convertNullArrayValuesToEmpty($arr);
        }
        return DB_OK;
    }

    // }}}
    // {{{ freeResult()

    /**
     * Free the internal resources associated with $result
     *
     * @param resource $result  the SQLite result identifier
     *
     * @return bool  true on success, false if $result is invalid
     */
    function freeResult(&$result)
    {
        // XXX No native free?
        if (!is_resource($result)) {
            return false;
        }
        $result = null;
        return true;
    }

    // }}}
    // {{{ numCols()

    /**
     * Gets the number of columns in a result set
     *
     * @param resource $result  the SQLite result identifier
     *
     * @return int  the number of columns in a result set, or a DB_Error
     *              object on failure
     */
    function numCols($result)
    {
        $cols = @sqlite_num_fields($result);
        if (!$cols) {
            return $this->sqliteRaiseError();
        }
        return $cols;
    }

    // }}}
    // {{{ numRows()

    /**
     * Gets the number of rows affected by a query
     *
     * @param resource $result  the SQLite result identifier
     *
     * @return int  the number of rows affected by the last query, or a
     *              DB_Error object on failure
     */
    function numRows($result)
    {
        $rows = @sqlite_num_rows($result);
        if (!is_integer($rows)) {
            return $this->raiseError();
        }
        return $rows;
    }

    // }}}
    // {{{ affected()

    /**
     * Gets the number of rows affected by a data manipulation query
     *
     * @return int  the number of rows affected by the last query
     */
    function affectedRows()
    {
        return @sqlite_changes($this->connection);
    }

    // }}}
    // {{{ errorNative()

    /**
     * Get the native error string of the last error (if any) that
     * occured on the current connection
     *
     * This is used to retrieve more meaningfull error messages DB_pgsql
     * way since sqlite_last_error() does not provide adequate info.
     *
     * @return string  the native SQLite error message
     */
    function errorNative()
    {
        return($this->_lasterror);
    }

    // }}}
    // {{{ errorCode()

    /**
     * Determine PEAR::DB error code from the database's text error message
     *
     * @param string $errormsg  the error message returned from the database
     *
     * @return integer  the DB error number
     */
    function errorCode($errormsg)
    {
        static $error_regexps;
        if (!isset($error_regexps)) {
            $error_regexps = array(
                '/^no such table:/' => DB_ERROR_NOSUCHTABLE,
                '/^no such index:/' => DB_ERROR_NOT_FOUND,
                '/^(table|index) .* already exists$/' => DB_ERROR_ALREADY_EXISTS,
                '/PRIMARY KEY must be unique/i' => DB_ERROR_CONSTRAINT,
                '/is not unique/' => DB_ERROR_CONSTRAINT,
                '/uniqueness constraint failed/' => DB_ERROR_CONSTRAINT,
                '/may not be NULL/' => DB_ERROR_CONSTRAINT_NOT_NULL,
                '/^no such column:/' => DB_ERROR_NOSUCHFIELD,
                '/^near ".*": syntax error$/' => DB_ERROR_SYNTAX,
                '/[0-9]+ values for [0-9]+ columns/i' => DB_ERROR_VALUE_COUNT_ON_ROW,
            );
        }
        foreach ($error_regexps as $regexp => $code) {
            if (preg_match($regexp, $errormsg)) {
                return $code;
            }
        }
        // Fall back to DB_ERROR if there was no mapping.
        return DB_ERROR;
    }

    // }}}
    // {{{ dropSequence()

    /**
     * Deletes a sequence
     *
     * @param string $seq_name  name of the sequence to be deleted
     *
     * @return int  DB_OK on success.  DB_Error if problems.
     *
     * @see DB_common::dropSequence()
     */
    function dropSequence($seq_name)
    {
        $seqname = $this->getSequenceName($seq_name);
        return $this->query("DROP TABLE $seqname");
    }

    /**
     * Creates a new sequence
     *
     * @param string $seq_name  name of the new sequence
     *
     * @return int  DB_OK on success.  A DB_Error object is returned if
     *              problems arise.
     *
     * @see DB_common::createSequence()
     */
    function createSequence($seq_name)
    {
        $seqname = $this->getSequenceName($seq_name);
        $query   = 'CREATE TABLE ' . $seqname .
                   ' (id INTEGER UNSIGNED PRIMARY KEY) ';
        $result  = $this->query($query);
        if (DB::isError($result)) {
            return($result);
        }
        $query   = "CREATE TRIGGER ${seqname}_cleanup AFTER INSERT ON $seqname
                    BEGIN
                        DELETE FROM $seqname WHERE id<LAST_INSERT_ROWID();
                    END ";
        $result  = $this->query($query);
        if (DB::isError($result)) {
            return($result);
        }
    }

    // }}}
    // {{{ nextId()

    /**
     * Returns the next free id in a sequence
     *
     * @param string  $seq_name  name of the sequence
     * @param boolean $ondemand  when true, the seqence is automatically
     *                           created if it does not exist
     *
     * @return int  the next id number in the sequence.  DB_Error if problem.
     *
     * @see DB_common::nextID()
     */
    function nextId($seq_name, $ondemand = true)
    {
        $seqname = $this->getSequenceName($seq_name);

        do {
            $repeat = 0;
            $this->pushErrorHandling(PEAR_ERROR_RETURN);
            $result = $this->query("INSERT INTO $seqname (id) VALUES (NULL)");
            $this->popErrorHandling();
            if ($result === DB_OK) {
                $id = @sqlite_last_insert_rowid($this->connection);
                if ($id != 0) {
                    return $id;
                }
            } elseif ($ondemand && DB::isError($result) &&
                      $result->getCode() == DB_ERROR_NOSUCHTABLE)
            {
                $result = $this->createSequence($seq_name);
                if (DB::isError($result)) {
                    return $this->raiseError($result);
                } else {
                    $repeat = 1;
                }
            }
        } while ($repeat);

        return $this->raiseError($result);
    }

    // }}}
    // {{{ tableInfo()

    /**
     * Returns information about a table
     *
     * @param string         $result  a string containing the name of a table
     * @param int            $mode    a valid tableInfo mode
     *
     * @return array  an associative array with the information requested.
     *                 A DB_Error object on failure.
     *
     * @see DB_common::tableInfo()
     * @since Method available since Release 1.7.0
     */
    function tableInfo($result, $mode = null)
    {
        if (is_string($result)) {
            /*
             * Probably received a table name.
             * Create a result resource identifier.
             */
            $id = @sqlite_array_query($this->connection,
                                      "PRAGMA table_info('$result');",
                                      SQLITE_ASSOC);
            $got_string = true;
        } else {
            $this->last_query = '';
            return $this->raiseError(DB_ERROR_NOT_CAPABLE, null, null, null,
                                     'This DBMS can not obtain tableInfo' .
                                     ' from result sets');
        }

        if ($this->options['portability'] & DB_PORTABILITY_LOWERCASE) {
            $case_func = 'strtolower';
        } else {
            $case_func = 'strval';
        }

        $count = count($id);
        $res   = array();

        if ($mode) {
            $res['num_fields'] = $count;
        }

        for ($i = 0; $i < $count; $i++) {
            if (strpos($id[$i]['type'], '(') !== false) {
                $bits = explode('(', $id[$i]['type']);
                $type = $bits[0];
                $len  = rtrim($bits[1],')');
            } else {
                $type = $id[$i]['type'];
                $len  = 0;
            }

            $flags = '';
            if ($id[$i]['pk']) {
                $flags .= 'primary_key ';
            }
            if ($id[$i]['notnull']) {
                $flags .= 'not_null ';
            }
            if ($id[$i]['dflt_value'] !== null) {
                $flags .= 'default_' . rawurlencode($id[$i]['dflt_value']);
            }
            $flags = trim($flags);

            $res[$i] = array(
                'table' => $case_func($result),
                'name'  => $case_func($id[$i]['name']),
                'type'  => $type,
                'len'   => $len,
                'flags' => $flags,
            );

            if ($mode & DB_TABLEINFO_ORDER) {
                $res['order'][$res[$i]['name']] = $i;
            }
            if ($mode & DB_TABLEINFO_ORDERTABLE) {
                $res['ordertable'][$res[$i]['table']][$res[$i]['name']] = $i;
            }
        }

        return $res;
    }

    // }}}
    // {{{ getSpecialQuery()

    /**
     * Obtain the query string needed for listing a given type of objects
     *
     * @param string $type  the kind of objects you want to retrieve
     * @param array  $args  SQLITE DRIVER ONLY: a private array of arguments
     *                       used by the getSpecialQuery().  Do not use
     *                       this directly.
     *
     * @return string  the SQL query string or null if the driver doesn't
     *                  support the object type requested
     *
     * @access private
     * @see DB_common::getListOf()
     */
    function getSpecialQuery($type, $args = array())
    {
        if (!is_array($args)) {
            return $this->raiseError('no key specified', null, null, null,
                                     'Argument has to be an array.');
        }

        switch ($type) {
            case 'master':
                return 'SELECT * FROM sqlite_master;';
            case 'tables':
                return "SELECT name FROM sqlite_master WHERE type='table' "
                       . 'UNION ALL SELECT name FROM sqlite_temp_master '
                       . "WHERE type='table' ORDER BY name;";
            case 'schema':
                return 'SELECT sql FROM (SELECT * FROM sqlite_master '
                       . 'UNION ALL SELECT * FROM sqlite_temp_master) '
                       . "WHERE type!='meta' "
                       . 'ORDER BY tbl_name, type DESC, name;';
            case 'schemax':
            case 'schema_x':
                /*
                 * Use like:
                 * $res = $db->query($db->getSpecialQuery('schema_x',
                 *                   array('table' => 'table3')));
                 */
                return 'SELECT sql FROM (SELECT * FROM sqlite_master '
                       . 'UNION ALL SELECT * FROM sqlite_temp_master) '
                       . "WHERE tbl_name LIKE '{$args['table']}' "
                       . "AND type!='meta' "
                       . 'ORDER BY type DESC, name;';
            case 'alter':
                /*
                 * SQLite does not support ALTER TABLE; this is a helper query
                 * to handle this. 'table' represents the table name, 'rows'
                 * the news rows to create, 'save' the row(s) to keep _with_
                 * the data.
                 *
                 * Use like:
                 * $args = array(
                 *     'table' => $table,
                 *     'rows'  => "id INTEGER PRIMARY KEY, firstname TEXT, surname TEXT, datetime TEXT",
                 *     'save'  => "NULL, titel, content, datetime"
                 * );
                 * $res = $db->query( $db->getSpecialQuery('alter', $args));
                 */
                $rows = strtr($args['rows'], $this->keywords);

                $q = array(
                    'BEGIN TRANSACTION',
                    "CREATE TEMPORARY TABLE {$args['table']}_backup ({$args['rows']})",
                    "INSERT INTO {$args['table']}_backup SELECT {$args['save']} FROM {$args['table']}",
                    "DROP TABLE {$args['table']}",
                    "CREATE TABLE {$args['table']} ({$args['rows']})",
                    "INSERT INTO {$args['table']} SELECT {$rows} FROM {$args['table']}_backup",
                    "DROP TABLE {$args['table']}_backup",
                    'COMMIT',
                );

                /*
                 * This is a dirty hack, since the above query will not get
                 * executed with a single query call so here the query method
                 * will be called directly and return a select instead.
                 */
                foreach ($q as $query) {
                    $this->query($query);
                }
                return "SELECT * FROM {$args['table']};";
            default:
                return null;
        }
    }

    // }}}
    // {{{ getDbFileStats()

    /**
     * Get the file stats for the current database
     *
     * Possible arguments are dev, ino, mode, nlink, uid, gid, rdev, size,
     * atime, mtime, ctime, blksize, blocks or a numeric key between
     * 0 and 12.
     *
     * @param string $arg  the array key for stats()
     *
     * @return mixed  an array on an unspecified key, integer on a passed
     *                arg and false at a stats error
     */
    function getDbFileStats($arg = '')
    {
        $stats = stat($this->dsn['database']);
        if ($stats == false) {
            return false;
        }
        if (is_array($stats)) {
            if (is_numeric($arg)) {
                if (((int)$arg <= 12) & ((int)$arg >= 0)) {
                    return false;
                }
                return $stats[$arg ];
            }
            if (array_key_exists(trim($arg), $stats)) {
                return $stats[$arg ];
            }
        }
        return $stats;
    }

    // }}}
    // {{{ escapeSimple()

    /**
     * Escape a string according to the current DBMS's standards
     *
     * In SQLite, this makes things safe for inserts/updates, but may
     * cause problems when performing text comparisons against columns
     * containing binary data. See the
     * {@link http://php.net/sqlite_escape_string PHP manual} for more info.
     *
     * @param string $str  the string to be escaped
     *
     * @return string  the escaped string
     *
     * @since Method available since Release 1.6.1
     * @see DB_common::escapeSimple()
     */
    function escapeSimple($str) {
        return @sqlite_escape_string($str);
    }

    // }}}
    // {{{ modifyLimitQuery()

    function modifyLimitQuery($query, $from, $count, $params = array())
    {
        $query = $query . " LIMIT $count OFFSET $from";
        return $query;
    }

    // }}}
    // {{{ modifyQuery()

    /**
     * "DELETE FROM table" gives 0 affected rows in SQLite
     *
     * This little hack lets you know how many rows were deleted.
     *
     * @param string $query  the SQL query string
     *
     * @return string  the SQL query string
     *
     * @access private
     */
    function _modifyQuery($query)
    {
        if ($this->options['portability'] & DB_PORTABILITY_DELETE_COUNT) {
            if (preg_match('/^\s*DELETE\s+FROM\s+(\S+)\s*$/i', $query)) {
                $query = preg_replace('/^\s*DELETE\s+FROM\s+(\S+)\s*$/',
                                      'DELETE FROM \1 WHERE 1=1', $query);
            }
        }
        return $query;
    }

    // }}}
    // {{{ sqliteRaiseError()

    /**
     * Gather information about an error, then use that info to create a
     * DB error object and finally return that object
     *
     * @param integer $errno  the PEAR error number (usually a DB constant) if
     *                         manually raising an error
     *
     * @return object  DB_error object
     *
     * @see errorNative(), errorCode(), DB_common::raiseError()
     */
    function sqliteRaiseError($errno = null)
    {
        $native = $this->errorNative();
        if ($errno === null) {
            $errno = $this->errorCode($native);
        }

        $errorcode = @sqlite_last_error($this->connection);
        $userinfo = "$errorcode ** $this->last_query";

        return $this->raiseError($errno, null, null, $userinfo, $native);
    }

    // }}}
}

/*
 * Local variables:
 * tab-width: 4
 * c-basic-offset: 4
 * End:
 */

?>
