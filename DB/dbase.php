<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * The PEAR DB driver for PHP's dbase extension
 * for interacting with dBase databases
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
 * @author     Tomas V.V. Cox <cox@idecnet.com>
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
 * The methods PEAR DB uses to interact with PHP's dbase extension
 * for interacting with dBase databases
 *
 * These methods overload the ones declared in DB_common.
 *
 * @category   Database
 * @package    DB
 * @author     Tomas V.V. Cox <cox@idecnet.com>
 * @author     Daniel Convissor <danielc@php.net>
 * @copyright  1997-2005 The PHP Group
 * @license    http://www.php.net/license/3_0.txt  PHP License
 * @version    Release: @package_version@
 * @link       http://pear.php.net/package/DB
 */
class DB_dbase extends DB_common
{
    // {{{ properties

    var $connection;
    var $phptype, $dbsyntax;
    var $prepare_tokens = array();
    var $prepare_types = array();
    var $res_row = array();
    var $result = 0;

    // }}}
    // {{{ constructor

    /**
     * DB_mysql constructor.
     *
     * @access public
     */
    function DB_dbase()
    {
        $this->DB_common();
        $this->phptype = 'dbase';
        $this->dbsyntax = 'dbase';
        $this->features = array(
            'prepare'       => false,
            'pconnect'      => false,
            'transactions'  => false,
            'limit'         => false
        );
        $this->errorcode_map = array();
    }

    // }}}
    // {{{ connect()

    function connect($dsninfo, $persistent = false)
    {
        if (!DB::assertExtension('dbase')) {
            return $this->raiseError(DB_ERROR_EXTENSION_NOT_FOUND);
        }

        $this->dsn = $dsninfo;
        if ($dsninfo['dbsyntax']) {
            $this->dbsyntax = $dsninfo['dbsyntax'];
        }

        $ini = ini_get('track_errors');
        $php_errormsg = '';

        if ($ini) {
            $conn  = @dbase_open($dsninfo['database'], 0);
        } else {
            ini_set('track_errors', 1);
            $conn  = @dbase_open($dsninfo['database'], 0);
            ini_set('track_errors', $ini);
        }

        if (!$conn) {
            return $this->raiseError(DB_ERROR_CONNECT_FAILED, null,
                                     null, null, strip_tags($php_errormsg));
        }
        $this->connection = $conn;
        return DB_OK;
    }

    // }}}
    // {{{ disconnect()

    function disconnect()
    {
        $ret = @dbase_close($this->connection);
        $this->connection = null;
        return $ret;
    }

    // }}}
    // {{{ &query()

    function &query($query = null)
    {
        // emulate result resources
        $this->res_row[(int)$this->result] = 0;
        $tmp =& new DB_result($this, $this->result++);
        return $tmp;
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
        if ($rownum === null) {
            $rownum = $this->res_row[(int)$result]++;
        }
        if ($fetchmode & DB_FETCHMODE_ASSOC) {
            $arr = @dbase_get_record_with_names($this->connection, $rownum);
            if ($this->options['portability'] & DB_PORTABILITY_LOWERCASE && $arr) {
                $arr = array_change_key_case($arr, CASE_LOWER);
            }
        } else {
            $arr = @dbase_get_record($this->connection, $rownum);
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
    // {{{ numCols()

    function numCols($foo)
    {
        return @dbase_numfields($this->connection);
    }

    // }}}
    // {{{ numRows()

    function numRows($foo)
    {
        return @dbase_numrecords($this->connection);
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
     *               + boolean = <samp>T</samp> if true or
     *                 <samp>F</samp> if false.  Use the <kbd>Logical</kbd>
     *                 data type.
     *               + integer or double = the unquoted number
     *               + other (including strings and numeric strings) =
     *                 the data with single quotes escaped by preceeding
     *                 single quotes then the whole string is encapsulated
     *                 between single quotes
     *
     * @internal
     */
    function quoteSmart($in)
    {
        if (is_int($in) || is_double($in)) {
            return $in;
        } elseif (is_bool($in)) {
            return $in ? 'T' : 'F';
        } elseif (is_null($in)) {
            return 'NULL';
        } else {
            return "'" . $this->escapeSimple($in) . "'";
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
