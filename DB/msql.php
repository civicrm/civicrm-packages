<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * The PEAR DB driver for PHP's msql extension
 * for interacting with Mini SQL databases
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
 * @author     Sterling Hughes <sterling@php.net>
 * @author     Daniel Convissor <danielc@php.net>
 * @copyright  1997-2005 The PHP Group
 * @license    http://www.php.net/license/3_0.txt  PHP License 3.0
 * @version    CVS: $Id$
 * @link       http://pear.php.net/package/DB
 */

/**
 * Obtain the DB_common class so it can be extended from
 */
require_once 'DB/common.php';

/**
 * The methods PEAR DB uses to interact with PHP's msql extension
 * for interacting with Mini SQL databases
 *
 * These methods overload the ones declared in DB_common.
 *
 * @category   Database
 * @package    DB
 * @author     Sterling Hughes <sterling@php.net>
 * @author     Daniel Convissor <danielc@php.net>
 * @copyright  1997-2005 The PHP Group
 * @license    http://www.php.net/license/3_0.txt  PHP License 3.0
 * @version    Release: @package_version@
 * @link       http://pear.php.net/package/DB
 * @since      Class not fully functional until Release 1.7.0
 */
class DB_msql extends DB_common
{
    // {{{ properties

    /**
     * The DB driver type (mysql, oci8, odbc, etc.)
     * @var string
     */
    var $phptype = 'msql';

    /**
     * The database syntax variant to be used (db2, access, etc.), if any
     * @var string
     */
    var $dbsyntax = 'msql';

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
        'limit'         => 'emulate',
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


    // }}}
    // {{{ constructor

    /**
     * This constructor calls <kbd>$this->DB_common()</kbd>
     *
     * @return void
     */
    function DB_msql()
    {
        $this->DB_common();
    }

    // }}}
    // {{{ connect()

    /**
     * Connect to the database server, log in and open the database
     *
     * @param array $dsn         the data source name
     * @param bool  $persistent  should the connection be persistent?
     *
     * @return int  DB_OK on success. A DB_error object on failure.
     *
     * @see DB::connect(), DB::parseDSN()
     */
    function connect($dsn, $persistent = false)
    {
        if (!PEAR::loadExtension('msql')) {
            return $this->raiseError(DB_ERROR_EXTENSION_NOT_FOUND);
        }

        $this->dsn = $dsn;
        if ($dsn['dbsyntax']) {
            $this->dbsyntax = $dsn['dbsyntax'];
        }

        $dbhost = $dsn['hostspec'] ? $dsn['hostspec'] : 'localhost';

        $connect_function = $persistent ? 'msql_pconnect' : 'msql_connect';

        if ($dbhost && $dsn['username'] && $dsn['password']) {
            $this->connection = $connect_function($dbhost, $dsn['username'],
                                                  $dsn['password']);
        } elseif ($dbhost && $dsn['username']) {
            $this->connection = $connect_function($dbhost, $dsn['username']);
        } else {
            $this->connection = $connect_function($dbhost);
        }

        if (!$this->connection) {
            $this->raiseError(DB_ERROR_CONNECT_FAILED);
        }

        if (!@msql_select_db($dsn['database'], $this->connection)){
            return $this->msqlRaiseError();
        }
        return DB_OK;
    }

    // }}}
    // {{{ disconnect()

    function disconnect()
    {
        $ret = @msql_close($this->connection);
        $this->connection = null;
        return $ret;
    }

    // }}}
    // {{{ simpleQuery()

    function simpleQuery($query)
    {
        $this->last_query = $query;
        $query = $this->modifyQuery($query);
        $result = @msql_query($query, $this->connection);
        if (!$result) {
            return $this->msqlRaiseError();
        }
        // Determine which queries that should return data, and which
        // should return an error code only.
        return DB::isManip($query) ? DB_OK : $result;
    }


    // }}}
    // {{{ nextResult()

    /**
     * Move the internal msql result pointer to the next available result
     *
     * @param a valid fbsql result resource
     *
     * @access public
     *
     * @return true if a result is available otherwise return false
     */
    function nextResult($result)
    {
        return false;
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
            if (!@msql_data_seek($result, $rownum)) {
                return null;
            }
        }
        if ($fetchmode & DB_FETCHMODE_ASSOC) {
            $arr = @msql_fetch_array($result, MSQL_ASSOC);
            if ($this->options['portability'] & DB_PORTABILITY_LOWERCASE && $arr) {
                $arr = array_change_key_case($arr, CASE_LOWER);
            }
        } else {
            $arr = @msql_fetch_row($result);
        }
        if (!$arr) {
            return null;
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

    function freeResult($result)
    {
        return @msql_free_result($result);
    }

    // }}}
    // {{{ numCols()

    function numCols($result)
    {
        $cols = @msql_num_fields($result);
        if (!$cols) {
            return $this->msqlRaiseError();
        }
        return $cols;
    }

    // }}}
    // {{{ numRows()

    function numRows($result)
    {
        $rows = @msql_num_rows($result);
        if (!$rows) {
            return $this->msqlRaiseError();
        }
        return $rows;
    }

    // }}}
    // {{{ affected()

    /**
     * Gets the number of rows affected by a query.
     *
     * @return number of rows affected by the last query
     */
    function affectedRows()
    {
        return @msql_affected_rows($this->connection);
    }

    /**
     * Get the last server error messge (if any)
     *
     * @return string  the last error message
     */
    function errorNative()
    {
        return @msql_error($this->connection);
    }

    // }}}
    // {{{ errorCode()

    /**
     * Determine PEAR::DB error code from the database's text error message
     *
     * @param string $errormsg  the error message returned from the database
     *
     * @return integer  the error number from a DB_ERROR* constant
     */
    function errorCode($errormsg)
    {
        static $error_regexps;
        if (!isset($error_regexps)) {
            $error_regexps = array(
            );
        }

        foreach ($error_regexps as $regexp => $code) {
            if (preg_match($regexp, $errormsg)) {
                return $code;
            }
        }
        return DB_ERROR;
    }

    // }}}
    // {{{ msqlRaiseError()

    /**
     * Gather information about an error and return it as a DB_Error object
     *
     * @param  integer  $errno  PEAR error number (usually a DB constant) if
     *                          manually raising an error
     *
     * @return object  a DB_Error object
     *
     * @see DB_msql::errorNative(), DB_msql::errorCode(),
     *      DB_common::raiseError()
     */
    function msqlRaiseError($errno = null)
    {
        $native = $this->errorNative();
        if ($errno === null) {
            $errno = $this->errorCode($native);
        }
        return $this->raiseError($errno, null, null, null, $native);
    }

    // }}}
    // {{{ tableInfo()

    /**
     * Returns information about a table or a result set
     *
     * @param object|string  $result  DB_result object from a query or a
     *                                 string containing the name of a table.
     *                                 While this also accepts a query result
     *                                 resource identifier, this behavior is
     *                                 deprecated.
     * @param int            $mode    a valid tableInfo mode
     *
     * @return array  an associative array with the information requested.
     *                 A DB_Error object on failure.
     *
     * @see DB_common::setOption()
     */
    function tableInfo($result, $mode = null)
    {
        if (is_string($result)) {
            /*
             * Probably received a table name.
             * Create a result resource identifier.
             */
            $id = @msql_query("SELECT * FROM $result WHERE 1=0",
                              $this->connection);
            $got_string = true;
        } elseif (isset($result->result)) {
            /*
             * Probably received a result object.
             * Extract the result resource identifier.
             */
            $id = $result->result;
            $got_string = false;
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
            return $this->raiseError(DB_ERROR_NEED_MORE_DATA);
        }

        if ($this->options['portability'] & DB_PORTABILITY_LOWERCASE) {
            $case_func = 'strtolower';
        } else {
            $case_func = 'strval';
        }

        $count = @msql_num_fields($id);
        $res   = array();

        if ($mode) {
            $res['num_fields'] = $count;
        }

        for ($i = 0; $i < $count; $i++) {
            $tmp = @msql_fetch_field($id);

            $flags = '';
            if ($tmp->primary_key) {
                $flags .= 'primary_key ';
            }
            if ($tmp->not_null) {
                $flags .= 'not_null ';
            }
            if ($tmp->unique) {
                $flags .= 'unique_key ';
            }
            $flags = trim($flags);

            $res[$i] = array(
                'table' => $case_func($tmp->table),
                'name'  => $case_func($tmp->name),
                'type'  => $tmp->type,
                'len'   => msql_field_len($id, $i),
                'flags' => $flags,
            );

            if ($mode & DB_TABLEINFO_ORDER) {
                $res['order'][$res[$i]['name']] = $i;
            }
            if ($mode & DB_TABLEINFO_ORDERTABLE) {
                $res['ordertable'][$res[$i]['table']][$res[$i]['name']] = $i;
            }
        }

        // free the result only if we were called on a table
        if ($got_string) {
            @msql_free_result($id);
        }
        return $res;
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
