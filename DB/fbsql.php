<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * The PEAR DB driver for PHP's fbsql extension
 * for interacting with FrontBase databases
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
 * @author     Frank M. Kromann <frank@frontbase.com>
 * @author     Daniel Convissor <danielc@php.net>
 * @copyright  1997-2005 The PHP Group
 * @license    http://www.php.net/license/3_0.txt  PHP License
 * @version    CVS: $Id$
 * @link       http://pear.php.net/package/DB
 */

/**
 * Obtain the DB_common class so it can be extended from
 */
require_once 'DB/common.php';

/**
 * The methods PEAR DB uses to interact with PHP's fbsql extension
 * for interacting with FrontBase databases
 *
 * These methods overload the ones declared in DB_common.
 *
 * @category   Database
 * @package    DB
 * @author     Frank M. Kromann <frank@frontbase.com>
 * @author     Daniel Convissor <danielc@php.net>
 * @copyright  1997-2005 The PHP Group
 * @license    http://www.php.net/license/3_0.txt  PHP License
 * @version    Release: @package_version@
 * @link       http://pear.php.net/package/DB
 * @since      Class functional since Release 1.7.0
 */
class DB_fbsql extends DB_common
{
    // {{{ properties

    /**
     * The DB driver type (mysql, oci8, odbc, etc.)
     * @var string
     */
    var $phptype = 'fbsql';

    /**
     * The database syntax variant to be used (db2, access, etc.), if any
     * @var string
     */
    var $dbsyntax = 'fbsql';

    /**
     * The capabilities of this DB implementation
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
        'pconnect'      => true,
        'prepare'       => false,
        'ssl'           => false,
        'transactions'  => true,
    );

    /**
     * A mapping of native error codes to DB error codes
     * @var array
     */
    var $errorcode_map = array(
         22 => DB_ERROR_SYNTAX,
         85 => DB_ERROR_ALREADY_EXISTS,
        108 => DB_ERROR_SYNTAX,
        116 => DB_ERROR_NOSUCHTABLE,
        217 => DB_ERROR_INVALID_NUMBER,
        226 => DB_ERROR_NOSUCHFIELD,
        231 => DB_ERROR_INVALID,
        251 => DB_ERROR_SYNTAX,
        266 => DB_ERROR_NOT_FOUND,
        357 => DB_ERROR_CONSTRAINT_NOT_NULL,
        358 => DB_ERROR_CONSTRAINT,
        360 => DB_ERROR_CONSTRAINT,
        361 => DB_ERROR_CONSTRAINT,
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


    // }}}
    // {{{ constructor

    /**
     * DB_fbsql constructor.
     *
     * @access public
     */
    function DB_fbsql()
    {
        $this->DB_common();
    }

    // }}}
    // {{{ connect()

    /**
     * Connect to a database and log in as the specified user.
     *
     * @param $dsn the data source name (see DB::parseDSN for syntax)
     * @param $persistent (optional) whether the connection should
     *        be persistent
     * @access public
     * @return int DB_OK on success, a DB error on failure
     */
    function connect($dsninfo, $persistent = false)
    {
        if (!DB::assertExtension('fbsql')) {
            return $this->raiseError(DB_ERROR_EXTENSION_NOT_FOUND);
        }

        $this->dsn = $dsninfo;
        if ($dsninfo['dbsyntax']) {
            $this->dbsyntax = $dsninfo['dbsyntax'];
        }

        $dbhost = $dsninfo['hostspec'] ? $dsninfo['hostspec'] : 'localhost';

        $ini = ini_get('track_errors');
        ini_set('track_errors', 1);
        $php_errormsg = '';

        $connect_function = $persistent ? 'fbsql_pconnect' : 'fbsql_connect';

        if ($dbhost && $dsninfo['username'] && $dsninfo['password']) {
            $conn = @$connect_function($dbhost, $dsninfo['username'],
                                       $dsninfo['password']);
        } elseif ($dbhost && $dsninfo['username']) {
            $conn = @$connect_function($dbhost, $dsninfo['username']);
        } elseif ($dbhost) {
            $conn = @$connect_function($dbhost);
        } else {
            $conn = false;
        }

        ini_set('track_errors', $ini);

        if (!$conn) {
            if (empty($php_errormsg)) {
                return $this->raiseError(DB_ERROR_CONNECT_FAILED);
            } else {
                return $this->raiseError(DB_ERROR_CONNECT_FAILED, null, null,
                                         null, $php_errormsg);
            }
        }

        $this->connection = $conn;

        if ($dsninfo['database']) {
            if (!fbsql_select_db($dsninfo['database'], $conn)) {
                return $this->fbsqlRaiseError();
            }
        }

        return DB_OK;
    }

    // }}}
    // {{{ disconnect()

    /**
     * Log out and disconnect from the database.
     *
     * @access public
     *
     * @return bool true on success, false if not connected.
     */
    function disconnect()
    {
        $ret = @fbsql_close($this->connection);
        $this->connection = null;
        return $ret;
    }

    // }}}
    // {{{ simpleQuery()

    /**
     * Send a query to fbsql and return the results as a fbsql resource
     * identifier.
     *
     * @param the SQL query
     *
     * @access public
     *
     * @return mixed returns a valid fbsql result for successful SELECT
     * queries, DB_OK for other successful queries.  A DB error is
     * returned on failure.
     */
    function simpleQuery($query)
    {
        $this->last_query = $query;
        $query = $this->modifyQuery($query);
        $result = @fbsql_query("$query;", $this->connection);
        if (!$result) {
            return $this->fbsqlRaiseError();
        }
        // Determine which queries that should return data, and which
        // should return an error code only.
        if (DB::isManip($query)) {
            return DB_OK;
        }
        return $result;
    }

    // }}}
    // {{{ nextResult()

    /**
     * Move the internal fbsql result pointer to the next available result
     *
     * @param a valid fbsql result resource
     *
     * @access public
     *
     * @return true if a result is available otherwise return false
     */
    function nextResult($result)
    {
        return @fbsql_next_result($result);
    }

    // }}}
    // {{{ fetchInto()

    /**
     * Fetch a row and insert the data into an existing array.
     *
     * Formating of the array and the data therein are configurable.
     * See DB_result::fetchInto() for more information.
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
     * @access private
     */
    function fetchInto($result, &$arr, $fetchmode, $rownum=null)
    {
        if ($rownum !== null) {
            if (!@fbsql_data_seek($result, $rownum)) {
                return null;
            }
        }
        if ($fetchmode & DB_FETCHMODE_ASSOC) {
            $arr = @fbsql_fetch_array($result, FBSQL_ASSOC);
            if ($this->options['portability'] & DB_PORTABILITY_LOWERCASE && $arr) {
                $arr = array_change_key_case($arr, CASE_LOWER);
            }
        } else {
            $arr = @fbsql_fetch_row($result);
        }
        if (!$arr) {
            $errno = @fbsql_errno($this->connection);
            if (!$errno) {
                return null;
            }
            return $this->fbsqlRaiseError($errno);
        }
        if ($this->options['portability'] & DB_PORTABILITY_RTRIM) {
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
     * Free the internal resources associated with $result.
     *
     * @param $result fbsql result identifier
     *
     * @access public
     *
     * @return bool true on success, false if $result is invalid
     */
    function freeResult($result)
    {
        return @fbsql_free_result($result);
    }

    // }}}
    // {{{ autoCommit()

    function autoCommit($onoff=false)
    {
        if ($onoff) {
            $this->query("SET COMMIT TRUE");
        } else {
            $this->query("SET COMMIT FALSE");
        }
    }

    // }}}
    // {{{ commit()

    function commit()
    {
        @fbsql_commit();
    }

    // }}}
    // {{{ rollback()

    function rollback()
    {
        @fbsql_rollback();
    }

    // }}}
    // {{{ numCols()

    /**
     * Get the number of columns in a result set.
     *
     * @param $result fbsql result identifier
     *
     * @access public
     *
     * @return int the number of columns per row in $result
     */
    function numCols($result)
    {
        $cols = @fbsql_num_fields($result);

        if (!$cols) {
            return $this->fbsqlRaiseError();
        }

        return $cols;
    }

    // }}}
    // {{{ numRows()

    /**
     * Get the number of rows in a result set.
     *
     * @param $result fbsql result identifier
     *
     * @access public
     *
     * @return int the number of rows in $result
     */
    function numRows($result)
    {
        $rows = @fbsql_num_rows($result);
        if ($rows === null) {
            return $this->fbsqlRaiseError();
        }
        return $rows;
    }

    // }}}
    // {{{ affectedRows()

    /**
     * Gets the number of rows affected by the data manipulation
     * query.  For other queries, this function returns 0.
     *
     * @return number of rows affected by the last query
     */
    function affectedRows()
    {
        if (DB::isManip($this->last_query)) {
            $result = @fbsql_affected_rows($this->connection);
        } else {
            $result = 0;
        }
        return $result;
     }

    // }}}
    // {{{ errorNative()

    /**
     * Get the native error code of the last error (if any) that
     * occured on the current connection.
     *
     * @access public
     *
     * @return int native fbsql error code
     */
    function errorNative()
    {
        return @fbsql_errno($this->connection);
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
     * @internal
     * @see DB_common::nextID()
     * @access public
     */
    function nextId($seq_name, $ondemand = true)
    {
        $seqname = $this->getSequenceName($seq_name);
        do {
            $repeat = 0;
            $this->pushErrorHandling(PEAR_ERROR_RETURN);
            $result = $this->getOne('SELECT UNIQUE FROM ' . $seqname);
            $this->popErrorHandling();
            if ($ondemand && DB::isError($result) &&
                $result->getCode() == DB_ERROR_NOSUCHTABLE) {
                $repeat = 1;
                $result = $this->createSequence($seq_name);
                if (DB::isError($result)) {
                    return $result;
                }
            } else {
                $repeat = 0;
            }
        } while ($repeat);
        if (DB::isError($result)) {
            $this->raiseError($result);
            return $result;
        }
        return $result;
    }

    /**
     * Creates a new sequence
     *
     * @param string $seq_name  name of the new sequence
     *
     * @return int  DB_OK on success.  A DB_Error object is returned if
     *              problems arise.
     *
     * @internal
     * @see DB_common::createSequence()
     * @access public
     */
    function createSequence($seq_name)
    {
        $seqname = $this->getSequenceName($seq_name);
        $res = $this->query('CREATE TABLE ' . $seqname
                            . ' (id INTEGER NOT NULL,'
                            . ' PRIMARY KEY(id))');
        if ($res) {
            $res = $this->query('SET UNIQUE = 0 FOR ' . $seqname);
        }
        return $res;
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
     * @internal
     * @see DB_common::dropSequence()
     * @access public
     */
    function dropSequence($seq_name)
    {
        $seqname = $this->getSequenceName($seq_name);
        return $this->query("DROP TABLE ${seqname} RESTRICT");
    }

    // }}}
    // {{{ modifyQuery()

    function modifyQuery($query)
    {
        return $query;
    }

    // }}}
    // {{{ modifyLimitQuery()

    function modifyLimitQuery($query, $from, $count, $params = array())
    {
        if (DB::isManip($query)) {
            return str_ireplace("SELECT", "SELECT TOP($count)", $query);
        } else {
            return str_ireplace("SELECT", "SELECT TOP($from, $count)", $query);
        }
    }

    // }}}
    // {{{ quoteSmart()

    /**
     * Format input so it can be safely used in a query
     *
     * @param mixed $in  data to be quoted
     *
     * @return mixed Submitted variable's type = returned value:
     *               + null = the string <samp>NULL</samp>
     *               + boolean = string <samp>TRUE</samp> or <samp>FALSE</samp>
     *               + integer or double = the unquoted number
     *               + other (including strings and numeric strings) =
     *                 the data escaped according to MySQL's settings
     *                 then encapsulated between single quotes
     *
     * @internal
     */
    function quoteSmart($in)
    {
        if (is_int($in) || is_double($in)) {
            return $in;
        } elseif (is_bool($in)) {
            return $in ? 'TRUE' : 'FALSE';
        } elseif (is_null($in)) {
            return 'NULL';
        } else {
            return "'" . $this->escapeSimple($in) . "'";
        }
    }

    // }}}
    // {{{ fbsqlRaiseError()

    /**
     * Gather information about an error, then use that info to create a
     * DB error object and finally return that object.
     *
     * @param  integer  $errno  PEAR error number (usually a DB constant) if
     *                          manually raising an error
     * @return object  DB error object
     * @see DB_common::errorCode()
     * @see DB_common::raiseError()
     */
    function fbsqlRaiseError($errno = null)
    {
        if ($errno === null) {
            $errno = $this->errorCode(fbsql_errno($this->connection));
        }
        return $this->raiseError($errno, null, null, null,
                        @fbsql_error($this->connection));
    }

    // }}}
    // {{{ tableInfo()

    /**
     * Returns information about a table or a result set.
     *
     * @param object|string  $result  DB_result object from a query or a
     *                                string containing the name of a table
     * @param int            $mode    a valid tableInfo mode
     * @return array  an associative array with the information requested
     *                or an error object if something is wrong
     * @access public
     * @internal
     * @see DB_common::tableInfo()
     */
    function tableInfo($result, $mode = null) {
        if (isset($result->result)) {
            /*
             * Probably received a result object.
             * Extract the result resource identifier.
             */
            $id = $result->result;
            $got_string = false;
        } elseif (is_string($result)) {
            /*
             * Probably received a table name.
             * Create a result resource identifier.
             */
            $id = @fbsql_list_fields($this->dsn['database'],
                                     $result, $this->connection);
            $got_string = true;
        } else {
            /*
             * Probably received a result resource identifier.
             * Copy it.
             * Deprecated.  Here for compatibility only.
             */
            $id = $result;
            $got_string = false;
        }

        if (!is_resource($id)) {
            return $this->fbsqlRaiseError(DB_ERROR_NEED_MORE_DATA);
        }

        if ($this->options['portability'] & DB_PORTABILITY_LOWERCASE) {
            $case_func = 'strtolower';
        } else {
            $case_func = 'strval';
        }

        $count = @fbsql_num_fields($id);

        // made this IF due to performance (one if is faster than $count if's)
        if (!$mode) {
            for ($i=0; $i<$count; $i++) {
                $res[$i]['table'] = $case_func(@fbsql_field_table($id, $i));
                $res[$i]['name']  = $case_func(@fbsql_field_name($id, $i));
                $res[$i]['type']  = @fbsql_field_type($id, $i);
                $res[$i]['len']   = @fbsql_field_len($id, $i);
                $res[$i]['flags'] = @fbsql_field_flags($id, $i);
            }
        } else { // full
            $res["num_fields"]= $count;

            for ($i=0; $i<$count; $i++) {
                $res[$i]['table'] = $case_func(@fbsql_field_table($id, $i));
                $res[$i]['name']  = $case_func(@fbsql_field_name($id, $i));
                $res[$i]['type']  = @fbsql_field_type($id, $i);
                $res[$i]['len']   = @fbsql_field_len($id, $i);
                $res[$i]['flags'] = @fbsql_field_flags($id, $i);

                if ($mode & DB_TABLEINFO_ORDER) {
                    $res['order'][$res[$i]['name']] = $i;
                }
                if ($mode & DB_TABLEINFO_ORDERTABLE) {
                    $res['ordertable'][$res[$i]['table']][$res[$i]['name']] = $i;
                }
            }
        }

        // free the result only if we were called on a table
        if ($got_string) {
            @fbsql_free_result($id);
        }
        return $res;
    }

    // }}}
    // {{{ getSpecialQuery()

    /**
     * Returns the query needed to get some backend info
     * @param string $type What kind of info you want to retrieve
     * @return string The SQL query string
     */
    function getSpecialQuery($type)
    {
        switch ($type) {
            case 'tables':
                return 'SELECT "table_name" FROM information_schema.tables'
                       . ' t0, information_schema.schemata t1'
                       . ' WHERE t0.schema_pk=t1.schema_pk AND'
                       . ' "table_type" = \'BASE TABLE\''
                       . ' AND "schema_name" = current_schema';
            case 'views':
                return 'SELECT "table_name" FROM information_schema.tables'
                       . ' t0, information_schema.schemata t1'
                       . ' WHERE t0.schema_pk=t1.schema_pk AND'
                       . ' "table_type" = \'VIEW\''
                       . ' AND "schema_name" = current_schema';
            case 'users':
                return 'SELECT "user_name" from information_schema.users'; 
            case 'functions':
                return 'SELECT "table_name" FROM information_schema.psm_routines'
                       . ' t0, information_schema.schemata t1'
                       . ' WHERE t0.schema_pk=t1.schema_pk AND'
                       . ' AND "routine_kind"=\'FUNCTION\''
                       . ' AND "schema_name" = current_schema';
            case 'procedures':
                return 'SELECT "table_name" FROM information_schema.psm_routines'
                       . ' t0, information_schema.schemata t1'
                       . ' WHERE t0.schema_pk=t1.schema_pk AND'
                       . ' AND "routine_kind"=\'PROCEDURE\''
                       . ' AND "schema_name" = current_schema';
            default:
                return null;
        }
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
