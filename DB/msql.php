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
 * @copyright  1997-2004 The PHP Group
 * @license    http://www.php.net/license/3_0.txt  PHP License
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
 * @author     Sterling Hughes <sterling@php.net>
 * @author     Daniel Convissor <danielc@php.net>
 * @copyright  1997-2004 The PHP Group
 * @license    http://www.php.net/license/3_0.txt  PHP License
 * @version    Release: @package_version@
 * @link       http://pear.php.net/package/DB
 */
class DB_msql extends DB_common
{
    // {{{ properties

    var $connection;
    var $phptype, $dbsyntax;
    var $prepare_tokens = array();
    var $prepare_types = array();

    // }}}
    // {{{ constructor

    function DB_msql()
    {
        $this->DB_common();
        $this->phptype = 'msql';
        $this->dbsyntax = 'msql';
        $this->features = array(
            'prepare' => false,
            'pconnect' => true,
            'transactions' => false,
            'limit' => 'emulate'
        );
    }

    // }}}
    // {{{ connect()

    function connect($dsninfo, $persistent = false)
    {
        if (!DB::assertExtension('msql')) {
            return $this->raiseError(DB_ERROR_EXTENSION_NOT_FOUND);
        }

        $this->dsn = $dsninfo;
        if ($dsninfo['dbsyntax']) {
            $this->dbsyntax = $dsninfo['dbsyntax'];
        }

        $dbhost = $dsninfo['hostspec'] ? $dsninfo['hostspec'] : 'localhost';

        $connect_function = $persistent ? 'msql_pconnect' : 'msql_connect';

        if ($dbhost && $dsninfo['username'] && $dsninfo['password']) {
            $conn = $connect_function($dbhost, $dsninfo['username'],
                                      $dsninfo['password']);
        } elseif ($dbhost && $dsninfo['username']) {
            $conn = $connect_function($dbhost, $dsninfo['username']);
        } else {
            $conn = $connect_function($dbhost);
        }
        if (!$conn) {
            $this->raiseError(DB_ERROR_CONNECT_FAILED);
        }
        if (!@msql_select_db($dsninfo['database'], $conn)){
            return $this->raiseError(DB_ERROR_NODBSELECTED);
        }
        $this->connection = $conn;
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
            return $this->raiseError();
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
            if ($error = @msql_error()) {
                return $this->raiseError($error);
            } else {
                return null;
            }
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
            return $this->raiseError();
        }
        return $cols;
    }

    // }}}
    // {{{ numRows()

    function numRows($result)
    {
        $rows = @msql_num_rows($result);
        if (!$rows) {
            return $this->raiseError();
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

    // }}}

}

/*
 * Local variables:
 * tab-width: 4
 * c-basic-offset: 4
 * End:
 */

?>
