<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * The PEAR DB driver for PHP's odbc extension
 * for interacting with databases via ODBC connections
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
 * @author     Stig Bakken <ssb@php.net>
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
 * The methods PEAR DB uses to interact with PHP's odbc extension
 * for interacting with databases via ODBC connections
 *
 * These methods overload the ones declared in DB_common.
 *
 * More info on ODBC errors could be found here:
 * http://msdn.microsoft.com/library/default.asp?url=/library/en-us/trblsql/tr_err_odbc_5stz.asp
 *
 * @category   Database
 * @package    DB
 * @author     Stig Bakken <ssb@php.net>
 * @author     Daniel Convissor <danielc@php.net>
 * @copyright  1997-2005 The PHP Group
 * @license    http://www.php.net/license/3_0.txt  PHP License 3.0
 * @version    Release: @package_version@
 * @link       http://pear.php.net/package/DB
 */
class DB_odbc extends DB_common
{
    // {{{ properties

    /**
     * The DB driver type (mysql, oci8, odbc, etc.)
     * @var string
     */
    var $phptype = 'odbc';

    /**
     * The database syntax variant to be used (db2, access, etc.), if any
     * @var string
     */
    var $dbsyntax = 'sql92';

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
     * NOTE: The feature set of the following drivers are different than
     * the default:
     *   + solid: 'transactions' = true
     *   + navision: 'limit' = false
     *
     * @var array
     */
    var $features = array(
        'limit'         => 'emulate',
        'new_link'      => false,
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
        '01004' => DB_ERROR_TRUNCATED,
        '07001' => DB_ERROR_MISMATCH,
        '21S01' => DB_ERROR_VALUE_COUNT_ON_ROW,
        '21S02' => DB_ERROR_MISMATCH,
        '22003' => DB_ERROR_INVALID_NUMBER,
        '22005' => DB_ERROR_INVALID_NUMBER,
        '22008' => DB_ERROR_INVALID_DATE,
        '22012' => DB_ERROR_DIVZERO,
        '23000' => DB_ERROR_CONSTRAINT,
        '23502' => DB_ERROR_CONSTRAINT_NOT_NULL,
        '23503' => DB_ERROR_CONSTRAINT,
        '23504' => DB_ERROR_CONSTRAINT,
        '23505' => DB_ERROR_CONSTRAINT,
        '24000' => DB_ERROR_INVALID,
        '34000' => DB_ERROR_INVALID,
        '37000' => DB_ERROR_SYNTAX,
        '42000' => DB_ERROR_SYNTAX,
        '42601' => DB_ERROR_SYNTAX,
        'IM001' => DB_ERROR_UNSUPPORTED,
        'S0000' => DB_ERROR_NOSUCHTABLE,
        'S0001' => DB_ERROR_ALREADY_EXISTS,
        'S0002' => DB_ERROR_NOSUCHTABLE,
        'S0011' => DB_ERROR_ALREADY_EXISTS,
        'S0012' => DB_ERROR_NOT_FOUND,
        'S0021' => DB_ERROR_ALREADY_EXISTS,
        'S0022' => DB_ERROR_NOSUCHFIELD,
        'S1009' => DB_ERROR_INVALID,
        'S1090' => DB_ERROR_INVALID,
        'S1C00' => DB_ERROR_NOT_CAPABLE,
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
     * The number of rows affected by a data manipulation query
     * @var integer
     */
    var $affected = 0;


    // }}}
    // {{{ constructor

    function DB_odbc()
    {
        $this->DB_common();
    }

    // }}}
    // {{{ connect()

    /**
     * Connect to the database server, log in and open the database
     *
     * PEAR DB's odbc driver supports the following extra DSN options:
     *   + cursor  The type of cursor to be used for this connection.
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
        if (!PEAR::loadExtension('odbc')) {
            return $this->raiseError(DB_ERROR_EXTENSION_NOT_FOUND);
        }

        $this->dsn = $dsn;
        if ($dsn['dbsyntax']) {
            $this->dbsyntax = $dsn['dbsyntax'];
        }
        switch ($this->dbsyntax) {
            case 'solid':
                $this->features['transactions'] = true;
                break;
            case 'navision':
                $this->features['limit'] = false;
        }

        /*
         * This is hear for backwards compatibility. Should have been using
         * 'database' all along, but prior to 1.6.0RC3 'hostspec' was used.
         */
        if ($dsn['database']) {
            $odbcdsn = $dsn['database'];
        } elseif ($dsn['hostspec']) {
            $odbcdsn = $dsn['hostspec'];
        } else {
            $odbcdsn = 'localhost';
        }

        $connect_function = $persistent ? 'odbc_pconnect' : 'odbc_connect';

        if (empty($dsn['cursor'])) {
            $this->connection = @$connect_function($odbcdsn, $dsn['username'],
                                                   $dsn['password']);
        } else {
            $this->connection = @$connect_function($odbcdsn, $dsn['username'],
                                                   $dsn['password'],
                                                   $dsn['cursor']);
        }

        if (!is_resource($this->connection)) {
            return $this->raiseError(DB_ERROR_CONNECT_FAILED,
                                     null, null, null,
                                     $this->errorNative());
        }
        return DB_OK;
    }

    // }}}
    // {{{ disconnect()

    function disconnect()
    {
        $err = @odbc_close($this->connection);
        $this->connection = null;
        return $err;
    }

    // }}}
    // {{{ simpleQuery()

    /**
     * Send a query to ODBC and return the results as a ODBC resource
     * identifier.
     *
     * @param $query the SQL query
     *
     * @return int returns a valid ODBC result for successful SELECT
     * queries, DB_OK for other successful queries.  A DB error code
     * is returned on failure.
     */
    function simpleQuery($query)
    {
        $this->last_query = $query;
        $query = $this->modifyQuery($query);
        $result = @odbc_exec($this->connection, $query);
        if (!$result) {
            return $this->odbcRaiseError(); // XXX ERRORMSG
        }
        // Determine which queries that should return data, and which
        // should return an error code only.
        if (DB::isManip($query)) {
            $this->affected = $result; // For affectedRows()
            return DB_OK;
        }
        $this->affected = 0;
        return $result;
    }

    // }}}
    // {{{ nextResult()

    /**
     * Move the internal odbc result pointer to the next available result
     *
     * @param a valid fbsql result resource
     *
     * @access public
     *
     * @return true if a result is available otherwise return false
     */
    function nextResult($result)
    {
        return @odbc_next_result($result);
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
        $arr = array();
        if ($rownum !== null) {
            $rownum++; // ODBC first row is 1
            if (version_compare(phpversion(), '4.2.0', 'ge')) {
                $cols = @odbc_fetch_into($result, $arr, $rownum);
            } else {
                $cols = @odbc_fetch_into($result, $rownum, $arr);
            }
        } else {
            $cols = @odbc_fetch_into($result, $arr);
        }

        if (!$cols) {
            /* XXX FIXME: doesn't work with unixODBC and easysoft
                          (get corrupted $errno values)
            if ($errno = @odbc_error($this->connection)) {
                return $this->RaiseError($errno);
            }*/
            return null;
        }
        if ($fetchmode !== DB_FETCHMODE_ORDERED) {
            for ($i = 0; $i < count($arr); $i++) {
                $colName = @odbc_field_name($result, $i+1);
                $a[$colName] = $arr[$i];
            }
            if ($this->options['portability'] & DB_PORTABILITY_LOWERCASE) {
                $a = array_change_key_case($a, CASE_LOWER);
            }
            $arr = $a;
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
        return @odbc_free_result($result);
    }

    // }}}
    // {{{ numCols()

    function numCols($result)
    {
        $cols = @odbc_num_fields($result);
        if (!$cols) {
            return $this->odbcRaiseError();
        }
        return $cols;
    }

    // }}}
    // {{{ affectedRows()

    /**
     * Returns the number of rows affected by a manipulative query
     * (INSERT, DELETE, UPDATE)
     * @return mixed int affected rows, 0 when non manip queries or
     *               DB error on error
     */
    function affectedRows()
    {
        if (empty($this->affected)) {  // In case of SELECT stms
            return 0;
        }
        $nrows = @odbc_num_rows($this->affected);
        if ($nrows == -1) {
            return $this->odbcRaiseError();
        }
        return $nrows;
    }

    // }}}
    // {{{ numRows()

    /**
     * ODBC may or may not support counting rows in the result set of
     * SELECTs.
     *
     * @param $result the odbc result resource
     * @return the number of rows, or 0
     */
    function numRows($result)
    {
        $nrows = @odbc_num_rows($result);
        if ($nrows == -1) {
            return $this->odbcRaiseError(DB_ERROR_UNSUPPORTED);
        }
        return $nrows;
    }

    // }}}
    // {{{ quoteIdentifier()

    /**
     * Quote a string so it can be safely used as a table / column name
     *
     * Quoting style depends on which dbsyntax was passed in the DSN.
     *
     * Use 'mssql' as the dbsyntax in the DB DSN only if you've unchecked
     * "Use ANSI quoted identifiers" when setting up the ODBC data source.
     *
     * @param string $str  identifier name to be quoted
     *
     * @return string  quoted identifier string
     *
     * @since 1.6.0
     * @access public
     */
    function quoteIdentifier($str)
    {
        switch ($this->dsn['dbsyntax']) {
            case 'access':
                return '[' . $str . ']';
            case 'mssql':
            case 'sybase':
                return '[' . str_replace(']', ']]', $str) . ']';
            case 'mysql':
            case 'mysqli':
                return '`' . $str . '`';
            default:
                return '"' . str_replace('"', '""', $str) . '"';
        }
    }

    // }}}
    // {{{ quote()

    /**
     * @deprecated  Deprecated in release 1.6.0
     * @internal
     */
    function quote($str) {
        return $this->quoteSmart($str);
    }

    // }}}
    // {{{ errorNative()

    /**
     * Get the native error code of the last error (if any) that
     * occured on the current connection.
     *
     * @access public
     *
     * @return int ODBC error code
     */
    function errorNative()
    {
        if (!isset($this->connection) || !is_resource($this->connection)) {
            return @odbc_error() . ' ' . @odbc_errormsg();
        }
        return @odbc_error($this->connection) . ' ' . @odbc_errormsg($this->connection);
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
        $repeat = 0;
        do {
            $this->pushErrorHandling(PEAR_ERROR_RETURN);
            $result = $this->query("update ${seqname} set id = id + 1");
            $this->popErrorHandling();
            if ($ondemand && DB::isError($result) &&
                $result->getCode() == DB_ERROR_NOSUCHTABLE) {
                $repeat = 1;
                $this->pushErrorHandling(PEAR_ERROR_RETURN);
                $result = $this->createSequence($seq_name);
                $this->popErrorHandling();
                if (DB::isError($result)) {
                    return $this->raiseError($result);
                }
                $result = $this->query("insert into ${seqname} (id) values(0)");
            } else {
                $repeat = 0;
            }
        } while ($repeat);

        if (DB::isError($result)) {
            return $this->raiseError($result);
        }

        $result = $this->query("select id from ${seqname}");
        if (DB::isError($result)) {
            return $result;
        }

        $row = $result->fetchRow(DB_FETCHMODE_ORDERED);
        if (DB::isError($row || !$row)) {
            return $row;
        }

        return $row[0];
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
        return $this->query("CREATE TABLE ${seqname} ".
                            '(id integer NOT NULL,'.
                            ' PRIMARY KEY(id))');
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
        return $this->query("DROP TABLE ${seqname}");
    }

    // }}}
    // {{{ autoCommit()

    function autoCommit($onoff = false)
    {
        if (!@odbc_autocommit($this->connection, $onoff)) {
            return $this->odbcRaiseError();
        }
        return DB_OK;
    }

    // }}}
    // {{{ commit()

    function commit()
    {
        if (!@odbc_commit($this->connection)) {
            return $this->odbcRaiseError();
        }
        return DB_OK;
    }

    // }}}
    // {{{ rollback()

    function rollback()
    {
        if (!@odbc_rollback($this->connection)) {
            return $this->odbcRaiseError();
        }
        return DB_OK;
    }

    // }}}
    // {{{ odbcRaiseError()

    /**
     * Gather information about an error, then use that info to create a
     * DB error object and finally return that object.
     *
     * @param  integer  $errno  PEAR error number (usually a DB constant) if
     *                          manually raising an error
     * @return object  DB error object
     * @see errorNative()
     * @see DB_common::errorCode()
     * @see DB_common::raiseError()
     */
    function odbcRaiseError($errno = null)
    {
        if ($errno === null) {
            switch ($this->dbsyntax) {
                case 'access':
                    if ($this->options['portability'] & DB_PORTABILITY_ERRORS) {
                        $this->errorcode_map['07001'] = DB_ERROR_NOSUCHFIELD;
                    } else {
                        // Doing this in case mode changes during runtime.
                        $this->errorcode_map['07001'] = DB_ERROR_MISMATCH;
                    }

                    $native_code = odbc_error($this->connection);

                    // S1000 is for "General Error."  Let's be more specific.
                    if ($native_code == 'S1000') {
                        $errormsg = odbc_errormsg($this->connection);
                        static $error_regexps;
                        if (!isset($error_regexps)) {
                            $error_regexps = array(
                                '/includes related records.$/i'  => DB_ERROR_CONSTRAINT,
                                '/cannot contain a Null value/i' => DB_ERROR_CONSTRAINT_NOT_NULL,
                            );
                        }
                        foreach ($error_regexps as $regexp => $code) {
                            if (preg_match($regexp, $errormsg)) {
                                return $this->raiseError($code,
                                        null, null, null,
                                        $native_code . ' ' . $errormsg);
                            }
                        }
                        $errno = DB_ERROR;
                    } else {
                        $errno = $this->errorCode($native_code);
                    }
                    break;
                default:
                    $errno = $this->errorCode(odbc_error($this->connection));
            }
        }
        return $this->raiseError($errno, null, null, null,
                                 $this->errorNative());
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
