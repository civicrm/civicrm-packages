<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 foldmethod=marker: */
// +----------------------------------------------------------------------+
// | PHP Version 4                                                        |
// +----------------------------------------------------------------------+
// | Copyright (c) 1997-2003 The PHP Group                                |
// +----------------------------------------------------------------------+
// | This source file is subject to version 2.02 of the PHP license,      |
// | that is bundled with this package in the file LICENSE, and is        |
// | available at through the world-wide-web at                           |
// | http://www.php.net/license/2_02.txt.                                 |
// | If you did not receive a copy of the PHP license and are unable to   |
// | obtain it through the world-wide-web, please send a note to          |
// | license@php.net so we can mail you a copy immediately.               |
// +----------------------------------------------------------------------+
// | Authors: Sterling Hughes <sterling@php.net>                          |
// |          Antônio Carlos Venâncio Júnior <floripa@php.net>            |
// +----------------------------------------------------------------------+
//
// $Id$


// TODO
//    - This driver may fail with multiple connections under the same
//      user/pass/host and different databases


require_once 'DB/common.php';

/**
 * Database independent query interface definition for PHP's Sybase
 * extension.
 *
 * @package  DB
 * @version  $Id$
 * @category Database
 * @author   Sterling Hughes <sterling@php.net>
 * @author   Antônio Carlos Venâncio Júnior <floripa@php.net>
 */

class DB_sybase extends DB_common
{
    // {{{ properties

    var $connection;
    var $phptype, $dbsyntax;
    var $prepare_tokens = array();
    var $prepare_types = array();
    var $transaction_opcount = 0;
    var $autocommit = true;

    // }}}
    // {{{ constructor

    /**
     * DB_sybase constructor.
     *
     * @access public
     */
    function DB_sybase()
    {
        $this->DB_common();
        $this->phptype = 'sybase';
        $this->dbsyntax = 'sybase';
        $this->features = array(
            'prepare' => false,
            'pconnect' => true,
            'transactions' => false,
            'limit' => 'emulate'
        );
        $this->errorcode_map = array(
        );
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
        if (!DB::assertExtension('sybase') && !DB::assertExtension('sybase_ct'))
            return $this->raiseError(DB_ERROR_EXTENSION_NOT_FOUND);

        $this->dsn = $dsninfo;
        $user = $dsninfo['username'];
        $pw   = $dsninfo['password'];

        $interface = $dsninfo['hostspec'] ? $dsninfo['hostspec'] : 'localhost';
        $connect_function = $persistent ? 'sybase_pconnect' : 'sybase_connect';

        if ($interface && $user && $pw) {
            $conn = @$connect_function($interface, $user, $pw);
        } else {
            $conn = FALSE;
        }

        if (!$conn) {
            return $this->raiseError(DB_ERROR_CONNECT_FAILED);
        }

        if ($dsninfo['database']) {
            if (!@sybase_select_db($dsninfo['database'], $conn)) {
                return $this->raiseError(DB_ERROR_NODBSELECTED, null,
                                         null, null, sybase_get_last_message());
            }
            $this->_db = $dsninfo['database'];
        }

        $this->connection = $conn;
        return DB_OK;
    }

    // }}}
    // {{{ disconnect()

    /**
     * Log out and disconnect from the database.
     *
     * @access public
     *
     * @return bool TRUE on success, FALSE if not connected.
     */
    function disconnect()
    {
        $ret = @sybase_close($this->connection);
        $this->connection = null;
        return $ret;
    }

    // }}}
    // {{{ errorNative()

    /**
     * Get the last server error messge (if any)
     *
     * @return string sybase last error message
     */
    function errorNative()
    {
        return sybase_get_last_message();
    }

    // }}}
    // {{{ errorCode()

    /**
     * Determine PEAR::DB error code from the database's text error message.
     *
     * @param  string  $errormsg  error message returned from the database
     * @return integer  an error number from a DB error constant
     */
    function errorCode($errormsg)
    {
        static $error_regexps;
        if (empty($error_regexps)) {
            $error_regexps = array(
                '/Incorrect syntax near/'
                    => DB_ERROR_SYNTAX,
                '/^Unclosed quote before the character string [\"\'].*[\"\']\./'
                    => DB_ERROR_SYNTAX,
                '/Implicit conversion from datatype [\"\'].+[\"\'] to [\"\'].+[\"\'] is not allowed\./'
                    => DB_ERROR_INVALID_NUMBER,
                '/Cannot drop the table [\"\'].+[\"\'], because it doesn\'t exist in the system catalogs\./'
                    => DB_ERROR_NOSUCHTABLE,
                '/Only the owner of object [\"\'].+[\"\'] or a user with System Administrator \(SA\) role can run this command\./'
                    => DB_ERROR_ACCESS_VIOLATION,
                '/^.+ permission denied on object .+, database .+, owner .+/'
                    => DB_ERROR_ACCESS_VIOLATION,
                '/^.* permission denied, database .+, owner .+/'
                    => DB_ERROR_ACCESS_VIOLATION,
                '/[^.*] not found\./'
                    => DB_ERROR_NOSUCHTABLE,
                '/There is already an object named/'
                    => DB_ERROR_ALREADY_EXISTS,
                '/Invalid column name/'
                    => DB_ERROR_NOSUCHFIELD,
                '/Command has been aborted/'
                    => DB_ERROR_CONSTRAINT,
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
    // {{{ sybaseRaiseError()

    /**
     * Gather information about an error, then use that info to create a
     * DB error object and finally return that object.
     *
     * @param  integer  $errno  error number (often a DB constant) if
     *                          manually raising an error
     * @return object  DB error object
     * @see errorNative()
     * @see errorCode()
     * @see DB_common::raiseError()
     */
    function sybaseRaiseError($errno = null)
    {
        $native = $this->errorNative();
        if ($errno === null) {
            $errno = $this->errorCode($native);
        }
        return $this->raiseError($errno, null, null, null, $native);
    }

    // }}}
    // {{{ simpleQuery()

    /**
     * Send a query to Sybase and return the results as a Sybase resource
     * identifier.
     *
     * @param the SQL query
     *
     * @access public
     *
     * @return mixed returns a valid Sybase result for successful SELECT
     * queries, DB_OK for other successful queries.  A DB error is
     * returned on failure.
     */
    function simpleQuery($query)
    {
        $ismanip = DB::isManip($query);
        $this->last_query = $query;
        if (!@sybase_select_db($this->_db, $this->connection)) {
            return $this->sybaseRaiseError(DB_ERROR_NODBSELECTED);
        }
        $query = $this->modifyQuery($query);
        if (!$this->autocommit && $ismanip) {
            if ($this->transaction_opcount == 0) {
                $result = @sybase_query('BEGIN TRANSACTION', $this->connection);
                if (!$result) {
                    return $this->sybaseRaiseError();
                }
            }
            $this->transaction_opcount++;
        }
        $result = @sybase_query($query, $this->connection);
        if (!$result) {
            return $this->sybaseRaiseError();
        }
        if (is_resource($result)) {
            $numrows = $this->numRows($result);
            if (is_object($numrows)) {
                return $numrows;
            }
            $this->num_rows[(int)$result] = $numrows;
            return $result;
        }
        // Determine which queries that should return data, and which
        // should return an error code only.
        return $ismanip ? DB_OK : $result;
    }

    // }}}
    // {{{ nextResult()

    /**
     * Move the internal sybase result pointer to the next available result
     *
     * @param a valid sybase result resource
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
     * The array's keys will be converted to lower case if
     * <var>$options['optimize']</var> is set to <samp>portability</samp>
     * AND <var>$fetchmode</var> is set to <samp>DB_FETCHMODE_ASSOC</samp>.
     *
     * <var>$options['optimize']</var> can be set when instantiating the
     * DB class via DB::connect(), but can be changed using
     * DB_common::setOption.
     *
     * <var>$fetchmode</var> is usually set via DB_common::setFetchMode().
     *
     * @param $result    Sybase result identifier
     * @param $ar        (reference) array where data from the row is stored
     * @param $fetchmode how the resulting array should be indexed
     * @param $rownum    the row number to fetch
     *
     * @return mixed DB_OK on success, NULL when end of result set is
     *               reached or on failure
     *
     * @see DB::connect()
     * @see DB_common::setOption
     * @see DB_common::$options
     * @see DB_common::setFetchMode()
     * @access public
     */
    function fetchInto($result, &$ar, $fetchmode, $rownum=null)
    {
        if ($rownum !== null) {
            if (!@sybase_data_seek($result, $rownum)) {
                return null;
            }
        }
        if ($fetchmode & DB_FETCHMODE_ASSOC) {
            if (function_exists('sybase_fetch_assoc')) {
                $ar = @sybase_fetch_assoc($result);
            } else {
                if ($ar = @sybase_fetch_array($result)) {
                    foreach ($ar as $key => $value) {
                        if (is_int($key)) {
                            unset($ar[$key]);
                        }
                    }
                }
            }
            if ($this->options['optimize'] == 'portability' && $ar) {
                $ar = array_change_key_case($ar, CASE_LOWER);
            }
        } else {
            $ar = @sybase_fetch_row($result);
        }
        if (!$ar) {
            // reported not work as seems that sybase_get_last_message()
            // always return a message here
            //if ($errmsg = sybase_get_last_message()) {
            //    return $this->sybaseRaiseError($errmsg);
            //} else {
                return null;
            //}
        }
        return DB_OK;
    }

    // }}}
    // {{{ freeResult()

    /**
     * Free the internal resources associated with $result.
     *
     * @param $result Sybase result identifier
     *
     * @access public
     *
     * @return bool TRUE on success, FALSE if $result is invalid
     */
    function freeResult($result)
    {
        unset($this->num_rows[(int)$result]);
        return @sybase_free_result($result);
    }

    // }}}
    // {{{ quote()

    /**
     * Quote the given string so it can be safely used in a query.
     *
     * @param $str mixed data to be quoted
     *
     * @return mixed Submitted variable's type = returned value:
     *               + null = the string <samp>NULL</samp>
     *               + integer or double = the unquoted number
     *               + boolean = <samp>1</samp> if true or
     *                 <samp>0</samp> if false.  Use the TINYINT(1) data type.
     *                 Sybase doesn't have BOOLEAN and BIT can't be null.
     *               + other (including strings and numeric strings) =
     *                 the data with single quotes escaped by preceeding
     *                 single quotes then the whole string is encapsulated
     *                 between single quotes
     */
    function quote($str = null)
    {
        if (is_int($str) || is_double($str)) {
            return $str;
        } elseif (is_bool($str)) {
            return $str ? 1 : 0;
        } elseif (is_null($str)) {
            return 'NULL';
        } else {
            return "'" . str_replace("'", "''", $str) . "'";
        }
    }

    // }}}
    // {{{ numCols()

    /**
     * Get the number of columns in a result set.
     *
     * @param $result Sybase result identifier
     *
     * @access public
     *
     * @return int the number of columns per row in $result
     */
    function numCols($result)
    {
        $cols = @sybase_num_fields($result);
        if (!$cols) {
            return $this->sybaseRaiseError();
        }
        return $cols;
    }

    // }}}
    // {{{ numRows()

    /**
     * Get the number of rows in a result set.
     *
     * @param $result Sybase result identifier
     *
     * @access public
     *
     * @return int the number of rows in $result
     */
    function numRows($result)
    {
        $rows = @sybase_num_rows($result);
        if ($rows === false) {
            return $this->sybaseRaiseError();
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
            $result = @sybase_affected_rows($this->connection);
        } else {
            $result = 0;
        }
        return $result;
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
                $sql = "select name from sysobjects where type = 'U' order by name";
                break;
            case 'views':
                $sql = "select name from sysobjects where type = 'V'";
                break;
            default:
                return null;
        }
        return $sql;
    }

    // }}}
    // {{{ autoCommit()

    /**
     * Enable/disable automatic commits
     */
    function autoCommit($onoff = false)
    {
        // XXX if $this->transaction_opcount > 0, we should probably
        // issue a warning here.
        $this->autocommit = $onoff ? true : false;
        return DB_OK;
    }

    // }}}
    // {{{ commit()

    /**
     * Commit the current transaction.
     */
    function commit()
    {
        if ($this->transaction_opcount > 0) {
            if (!@sybase_select_db($this->_db, $this->connection)) {
                return $this->sybaseRaiseError(DB_ERROR_NODBSELECTED);
            }
            $result = @sybase_query('COMMIT', $this->connection);
            $this->transaction_opcount = 0;
            if (!$result) {
                return $this->sybaseRaiseError();
            }
        }
        return DB_OK;
    }

    // }}}
    // {{{ rollback()

    /**
     * Roll back (undo) the current transaction.
     */
    function rollback()
    {
        if ($this->transaction_opcount > 0) {
            if (!@sybase_select_db($this->_db, $this->connection)) {
                return $this->sybaseRaiseError(DB_ERROR_NODBSELECTED);
            }
            $result = @sybase_query('ROLLBACK', $this->connection);
            $this->transaction_opcount = 0;
            if (!$result) {
                return $this->sybaseRaiseError();
            }
        }
        return DB_OK;
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
