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
 * @license    http://www.php.net/license/3_0.txt  PHP License 3.0
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
 * @license    http://www.php.net/license/3_0.txt  PHP License 3.0
 * @version    Release: @package_version@
 * @link       http://pear.php.net/package/DB
 */
class DB_dbase extends DB_common
{
    // {{{ properties

    /**
     * The DB driver type (mysql, oci8, odbc, etc.)
     * @var string
     */
    var $phptype = 'dbase';

    /**
     * The database syntax variant to be used (db2, access, etc.), if any
     * @var string
     */
    var $dbsyntax = 'dbase';

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
        'limit'         => false,
        'new_link'      => false,
        'pconnect'      => false,
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
     * A means of emulating result resources
     * @var array
     */
    var $res_row = array();

    /**
     * The quantity of results so far
     *
     * For emulating result resources.
     *
     * @var integer
     */
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
        DB_dbase::connect($this->dsn, $this->options);
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
     * @access private
     * @see DB::connect(), DB::parseDSN()
     */
    function connect($dsn, $persistent = false)
    {
        if (!PEAR::loadExtension('dbase')) {
            return $this->raiseError(DB_ERROR_EXTENSION_NOT_FOUND);
        }

        $this->dsn = $dsn;
        if ($dsn['dbsyntax']) {
            $this->dbsyntax = $dsn['dbsyntax'];
        }

        $ini = ini_get('track_errors');
        $php_errormsg = '';
        if ($ini) {
            $this->connection = @dbase_open($dsn['database'], 0);
        } else {
            ini_set('track_errors', 1);
            $this->connection = @dbase_open($dsn['database'], 0);
            ini_set('track_errors', $ini);
        }

        if (!$this->connection) {
            return $this->raiseError(DB_ERROR_CONNECT_FAILED,
                                     null, null, null,
                                     strip_tags($php_errormsg));
        }
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
