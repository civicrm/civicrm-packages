<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * The PEAR DB driver for PHP's mysqli extension
 * for interacting with MySQL databases
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
 * The methods PEAR DB uses to interact with PHP's mysqli extension
 * for interacting with MySQL databases
 *
 * This is for MySQL versions 4.1 and above.  Requires PHP 5.
 *
 * Note that persistent connections no longer exist.
 *
 * These methods overload the ones declared in DB_common.
 *
 * @author     Daniel Convissor <danielc@php.net>
 * @copyright  1997-2004 The PHP Group
 * @license    http://www.php.net/license/3_0.txt  PHP License
 * @version    Release: @package_version@
 * @link       http://pear.php.net/package/DB
 * @since      Class functional since Release 1.6.3
 */
class DB_mysqli extends DB_common
{
    // {{{ properties

    var $connection;
    var $phptype, $dbsyntax;
    var $prepare_tokens = array();
    var $prepare_types = array();
    var $transaction_opcount = 0;
    var $autocommit = true;
    var $fetchmode = DB_FETCHMODE_ORDERED; /* Default fetch mode */
    var $_db = false;

    /**
     * An enumerated array containing arrays with the column names
     * in results of executing prepared SELECT statements
     *
     * The keys are the statement's index number.
     *
     * @var    array
     * @access private
     */
    var $_stmt_col_names = array();

    /**
     * Array for converting MYSQLI_*_FLAG constants to text values
     * @var    array
     * @access public
     * @since  Property available since Release 1.6.5
     */
    var $mysqli_flags = array(
        MYSQLI_NOT_NULL_FLAG        => 'not_null',
        MYSQLI_PRI_KEY_FLAG         => 'primary_key',
        MYSQLI_UNIQUE_KEY_FLAG      => 'unique_key',
        MYSQLI_MULTIPLE_KEY_FLAG    => 'multiple_key',
        MYSQLI_BLOB_FLAG            => 'blob',
        MYSQLI_UNSIGNED_FLAG        => 'unsigned',
        MYSQLI_ZEROFILL_FLAG        => 'zerofill',
        MYSQLI_AUTO_INCREMENT_FLAG  => 'auto_increment',
        MYSQLI_TIMESTAMP_FLAG       => 'timestamp',
        MYSQLI_SET_FLAG             => 'set',
        // MYSQLI_NUM_FLAG             => 'numeric',  // unnecessary
        // MYSQLI_PART_KEY_FLAG        => 'multiple_key',  // duplicatvie
        MYSQLI_GROUP_FLAG           => 'group_by'
    );

    /**
     * Array for converting MYSQLI_TYPE_* constants to text values
     * @var    array
     * @access public
     * @since  Property available since Release 1.6.5
     */
    var $mysqli_types = array(
        MYSQLI_TYPE_DECIMAL     => 'decimal',
        MYSQLI_TYPE_TINY        => 'tinyint',
        MYSQLI_TYPE_SHORT       => 'int',
        MYSQLI_TYPE_LONG        => 'int',
        MYSQLI_TYPE_FLOAT       => 'float',
        MYSQLI_TYPE_DOUBLE      => 'double',
        // MYSQLI_TYPE_NULL        => 'DEFAULT NULL',  // let flags handle it
        MYSQLI_TYPE_TIMESTAMP   => 'timestamp',
        MYSQLI_TYPE_LONGLONG    => 'bigint',
        MYSQLI_TYPE_INT24       => 'mediumint',
        MYSQLI_TYPE_DATE        => 'date',
        MYSQLI_TYPE_TIME        => 'time',
        MYSQLI_TYPE_DATETIME    => 'datetime',
        MYSQLI_TYPE_YEAR        => 'year',
        MYSQLI_TYPE_NEWDATE     => 'date',
        MYSQLI_TYPE_ENUM        => 'enum',
        MYSQLI_TYPE_SET         => 'set',
        MYSQLI_TYPE_TINY_BLOB   => 'tinyblob',
        MYSQLI_TYPE_MEDIUM_BLOB => 'mediumblob',
        MYSQLI_TYPE_LONG_BLOB   => 'longblob',
        MYSQLI_TYPE_BLOB        => 'blob',
        MYSQLI_TYPE_VAR_STRING  => 'varchar',
        MYSQLI_TYPE_STRING      => 'char',
        MYSQLI_TYPE_GEOMETRY    => 'geometry',
    );

    // }}}
    // {{{ constructor

    /**
     * DB_mysql constructor.
     *
     * @access public
     */
    function DB_mysqli()
    {
        $this->DB_common();
        $this->phptype = 'mysqli';
        $this->dbsyntax = 'mysqli';
        $this->features = array(
            'prepare' => false,
            'ssl' => true,
            'transactions' => true,
            'limit' => 'alter'
        );
        $this->errorcode_map = array(
            1004 => DB_ERROR_CANNOT_CREATE,
            1005 => DB_ERROR_CANNOT_CREATE,
            1006 => DB_ERROR_CANNOT_CREATE,
            1007 => DB_ERROR_ALREADY_EXISTS,
            1008 => DB_ERROR_CANNOT_DROP,
            1022 => DB_ERROR_ALREADY_EXISTS,
            1046 => DB_ERROR_NODBSELECTED,
            1048 => DB_ERROR_CONSTRAINT,
            1050 => DB_ERROR_ALREADY_EXISTS,
            1051 => DB_ERROR_NOSUCHTABLE,
            1054 => DB_ERROR_NOSUCHFIELD,
            1061 => DB_ERROR_ALREADY_EXISTS,
            1062 => DB_ERROR_ALREADY_EXISTS,
            1064 => DB_ERROR_SYNTAX,
            1100 => DB_ERROR_NOT_LOCKED,
            1136 => DB_ERROR_VALUE_COUNT_ON_ROW,
            1146 => DB_ERROR_NOSUCHTABLE,
            1216 => DB_ERROR_CONSTRAINT,
            1217 => DB_ERROR_CONSTRAINT,
        );
    }

    // }}}
    // {{{ connect()

    /**
     * Connect to a database and log in as the specified user.
     *
     * @param string $dsn the data source name (see DB::parseDSN for syntax)
     * @param boolean $persistent (optional) whether the connection should
     *                            be persistent
     * @return mixed DB_OK on success, a DB error on failure
     * @access public
     */
    function connect($dsninfo, $persistent = false)
    {
        if (!DB::assertExtension('mysqli')) {
            return $this->raiseError(DB_ERROR_EXTENSION_NOT_FOUND);
        }

        $this->dsn = $dsninfo;
        if ($dsninfo['dbsyntax']) {
            $this->dbsyntax = $dsninfo['dbsyntax'];
        }

        $conn = false;
        @ini_set('track_errors', true);

        if ($this->getOption('ssl') === true) {
            $init = mysqli_init();
            mysqli_ssl_set(
                $init,
                empty($dsninfo['key'])    ? null : $dsninfo['key'],
                empty($dsninfo['cert'])   ? null : $dsninfo['cert'],
                empty($dsninfo['ca'])     ? null : $dsninfo['ca'],
                empty($dsninfo['capath']) ? null : $dsninfo['capath'],
                empty($dsninfo['cipher']) ? null : $dsninfo['cipher']
            );
            if ($conn = @mysqli_real_connect($init,
                                             $dsninfo['hostspec'],
                                             $dsninfo['username'],
                                             $dsninfo['password'],
                                             $dsninfo['database'],
                                             $dsninfo['port'],
                                             $dsninfo['socket']))
            {
                $conn = $init;
            }
        } else {
            $conn = @mysqli_connect(
                $dsninfo['hostspec'],
                $dsninfo['username'],
                $dsninfo['password'],
                $dsninfo['database'],
                $dsninfo['port'],
                $dsninfo['socket']
            );
        }

        @ini_restore('track_errors');

        if (!$conn) {
            if (($err = @mysqli_connect_error()) != '') {
                return $this->raiseError(DB_ERROR_CONNECT_FAILED, null, null,
                                         null, $err);
            } elseif (empty($php_errormsg)) {
                return $this->raiseError(DB_ERROR_CONNECT_FAILED);
            } else {
                return $this->raiseError(DB_ERROR_CONNECT_FAILED, null, null,
                                         null, $php_errormsg);
            }
        }

        if ($dsninfo['database']) {
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
     * @return boolean true on success, false if not connected
     * @access public
     */
    function disconnect()
    {
        $ret = @mysqli_close($this->connection);
        $this->connection = null;
        return $ret;
    }

    // }}}
    // {{{ simpleQuery()

    /**
     * Send a query to MySQL and return the results as a MySQL resource
     * identifier.
     *
     * @param string $query the SQL query
     * @return mixed a valid MySQL result for successful SELECT
     *               queries, DB_OK for other successful queries.
     *               A DB error is returned on failure.
     * @access public
     */
    function simpleQuery($query)
    {
        $ismanip = DB::isManip($query);
        $this->last_query = $query;
        $query = $this->modifyQuery($query);
        if ($this->_db) {
            if (!@mysqli_select_db($this->connection, $this->_db)) {
                return $this->mysqliRaiseError(DB_ERROR_NODBSELECTED);
            }
        }
        if (!$this->autocommit && $ismanip) {
            if ($this->transaction_opcount == 0) {
                $result = @mysqli_query($this->connection, 'SET AUTOCOMMIT=0');
                $result = @mysqli_query($this->connection, 'BEGIN');
                if (!$result) {
                    return $this->mysqliRaiseError();
                }
            }
            $this->transaction_opcount++;
        }
        $result = @mysqli_query($this->connection, $query);
        if (!$result) {
            return $this->mysqliRaiseError();
        }
        if (is_object($result)) {
            return $result;
        }
        return DB_OK;
    }

    // }}}
    // {{{ prepare()

    /**
     * Prepares a query for multiple execution with execute().
     *
     * prepare() requires a generic query as string like <code>
     *    INSERT INTO numbers VALUES (?, ?, ?)
     * </code>.  The <kbd>?</kbd> characters are placeholders.
     *
     * Three types of placeholders can be used:
     *   + <kbd>?</kbd>  a quoted scalar value, i.e. strings, integers
     *   + <kbd>!</kbd>  value is inserted 'as is'
     *   + <kbd>&</kbd>  requires a file name.  The file's contents get
     *                     inserted into the query (i.e. saving binary
     *                     data in a db)
     *
     * Use backslashes to escape placeholder characters if you don't want
     * them to be interpreted as placeholders.  Example: <code>
     *    "UPDATE foo SET col=? WHERE col='over \& under'"
     * </code>
     *
     * @param string $query query to be prepared
     *
     * @return mixed DB statement resource on success. DB_Error on failure.
     *
     * @see DB_mysqli::execute()
     * @since Method available since Release 1.7.0
     */
    function prepare($query)
    {
        $tokens   = preg_split('/((?<!\\\)[&?!])/', $query, -1,
                               PREG_SPLIT_DELIM_CAPTURE);
        $binds    = count($tokens) - 1;
        $token    = 0;
        $types    = array();
        $newquery = '';

        foreach ($tokens as $key => $val) {
            switch ($val) {
                case '?':
                    $types[$token++] = DB_PARAM_SCALAR;
                    unset($tokens[$key]);
                    break;
                case '&':
                    $types[$token++] = DB_PARAM_OPAQUE;
                    unset($tokens[$key]);
                    break;
                case '!':
                    $types[$token++] = DB_PARAM_MISC;
                    unset($tokens[$key]);
                    break;
                default:
                    $tokens[$key] = preg_replace('/\\\([&?!])/', "\\1", $val);
                    if ($key != $binds) {
                        $newquery .= $tokens[$key] . '?';
                    } else {
                        $newquery .= $tokens[$key];
                    }
            }
        }

        $this->last_query = $query;
        $newquery = $this->modifyQuery($newquery);
        if (!$stmt = @mysqli_prepare($this->connection, $newquery)) {
            return $this->mysqliRaiseError();
        }

ob_start();
var_dump($stmt);
$id = md5(ob_get_contents());
ob_end_clean();

        $this->prepare_types[$id] = $types;
        $this->manip_query[$id] = DB::isManip($query);
        return $stmt;
    }

    // }}}
    // {{{ execute()

    /**
     * Executes a DB statement prepared with prepare().
     *
     * @param resource  $stmt  a DB statement resource returned from prepare()
     * @param mixed  $data  array, string or numeric data to be used in
     *                      execution of the statement.  Quantity of items
     *                      passed must match quantity of placeholders in
     *                      query:  meaning 1 for non-array items or the
     *                      quantity of elements in the array.
     *
     * @return mixed  returns a mysqli result resource for successful SELECT
     *                queries, DB_OK for other successful queries.
     *                A DB error object is returned on failure.
     *
     * @see DB_mysqli::prepare()
     * @since Method available since Release 1.7.0
     */
    function &execute($stmt, $data = array())
    {
        if (!is_array($data)) {
            $data = array($data);
        }

        $this->_data = $data;

ob_start();
var_dump($stmt);
$id = md5(ob_get_contents());
ob_end_clean();
        $types =& $this->prepare_types[$id];
        if (count($types) != count($data)) {
            $tmp =& $this->raiseError(DB_ERROR_MISMATCH);
            return $tmp;
        }

        $i      = 0;
        $params = array($stmt, '');
        foreach ($data as $key => $value) {
            if ($types[$i] == DB_PARAM_OPAQUE) {
                $fp = @fopen($data[$key], 'rb');
                if (!$fp) {
                    $tmp =& $this->raiseError(DB_ERROR_ACCESS_VIOLATION);
                    return $tmp;
                }
                $data[$key] = fread($fp, filesize($data[$key]));
                fclose($fp);
                $params[1] .= 'b';
            } else {
                if (is_string($data[$key])) {
                    $params[1] .= 's';
                }
                if (is_int($data[$key])) {
                    $params[1] .= 'i';
                }
                if (is_float($data[$key])) {
                    $params[1] .= 'd';
                }
                if (is_null($data[$key])) {
                    $params[1] .= 's';
                    $data[$key] = 'NULL';
                }
            }
            $params[] =& $data[$key];
            $i++;
        }

        $php_errormsg = '';
        if (!@call_user_func_array('mysqli_stmt_bind_param', $params)) {
            $tmp =& $this->raiseError(DB_ERROR, null, null, null,
                                      $php_errormsg);
            return $tmp;
        }

        if (!@mysqli_stmt_execute($stmt)) {
            $tmp =& $this->raiseError(DB_ERROR, null, null, null,
                                      @mysqli_stmt_errno($stmt) . ' ** ' .
                                      @mysqli_stmt_error($stmt));
            return $tmp;
        }

        $this->last_stmt = $stmt;
        if ($this->manip_query[$id]) {
            $tmp = DB_OK;
        } else {
            $tmp =& new DB_result($this, $stmt);
        }
        return $tmp;
    }

    // }}}
    // {{{ nextResult()

    /**
     * Move the internal mysql result pointer to the next available result.
     *
     * This method has not been implemented yet.
     *
     * @param resource $result a valid sql result resource
     * @return false
     * @access public
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
        if ($result instanceof mysqli_result) {
            // a result from a regular query

            if ($rownum !== null) {
                if (!@mysqli_data_seek($result, $rownum)) {
                    return null;
                }
            }
            if ($fetchmode & DB_FETCHMODE_ASSOC) {
                $arr = @mysqli_fetch_array($result, MYSQLI_ASSOC);
                if ($this->options['portability'] & DB_PORTABILITY_LOWERCASE && $arr) {
                    $arr = array_change_key_case($arr, CASE_LOWER);
                }
            } else {
                $arr = @mysqli_fetch_row($result);
            }
            if (!$arr) {
                $errno = @mysqli_errno($this->connection);
                if (!$errno) {
                    return null;
                }
                return $this->mysqliRaiseError($errno);
            }
        } else {
            // a result from the execution of a prepared statement

ob_start();
var_dump($stmt);
$id = md5(ob_get_contents());
ob_end_clean();

            if ($rownum !== null) {
                if (!@mysqli_stmt_data_seek($result, $rownum)) {
                    return null;
                }
            }

            if (!isset($this->_stmt_col_names[$id])) {
                $i = 0;
                $this->_stmt_col_names[$id] = array();
                $meta = mysqli_stmt_result_metadata($result);
                while ($info = mysqli_fetch_field($meta)) {
                    $this->_stmt_col_names[$id]['ordered'][$i++] = '';
                    $this->_stmt_col_names[$id]['assoc'][$info->name] = '';
                    $this->_stmt_col_names[$id]['lower'][strtolower($info->name)] = '';
                }
            }

            if ($fetchmode & DB_FETCHMODE_ASSOC) {
                if ($this->options['portability'] & DB_PORTABILITY_LOWERCASE) {
                    $arr = $this->_stmt_col_names[$id]['lower'];
                } else {
                    $arr = $this->_stmt_col_names[$id]['assoc'];
                }
            } else {
                $arr = $this->_stmt_col_names[$id]['ordered'];
            }

            $params = array($result);
            foreach ($arr as $key => $value) {
                $params[] =& $arr[$key];
            }
            call_user_func_array('mysqli_stmt_bind_result', $params);
            mysqli_stmt_fetch($result);
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
     * Free the internal resources associated with $result.
     *
     * @param resource $result MySQL result identifier
     * @return bool true on success, false if $result is invalid
     * @access public
     */
    function freeResult($result)
    {
        return @mysqli_free_result($result);
    }

    // }}}
    // {{{ freePrepared()

    /**
     * Free the internal resources associated with a prepared query.
     *
     * @param $stmt mysqli statement identifier
     *
     * @return bool true on success, false if $stmt is invalid
     *
     * @since Method available since Release 1.7.0
     */
    function freePrepared($stmt)
    {
ob_start();
var_dump($stmt);
$id = md5(ob_get_contents());
ob_end_clean();

        if (is_object($stmt) &&
            isset($this->prepare_types[$id]))
        {
            unset($this->prepare_types[$id]);
            unset($this->manip_query[$id]);
            unset($this->_stmt_col_names[$id]);
            @mysqli_stmt_close($stmt);
        } else {
            return false;
        }
        return true;
    }

    // }}}
    // {{{ numCols()

    /**
     * Get the number of columns in a result set.
     *
     * @param $result MySQL result identifier
     *
     * @access public
     *
     * @return int the number of columns per row in $result
     */
    function numCols($result)
    {
        $cols = @mysqli_num_fields($result);

        if (!$cols) {
            return $this->mysqliRaiseError();
        }

        return $cols;
    }

    // }}}
    // {{{ numRows()

    /**
     * Get the number of rows in a result set.
     *
     * @param resource $result MySQL result identifier
     * @return int the number of rows in $result
     * @access public
     */
    function numRows($result)
    {
        $rows = @mysqli_num_rows($result);
        if ($rows === null) {
            return $this->mysqliRaiseError();
        }
        return $rows;
    }

    // }}}
    // {{{ autoCommit()

    /**
     * Enable/disable automatic commits.
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
            if ($this->_db) {
                if (!@mysqli_select_db($this->connection, $this->_db)) {
                    return $this->mysqliRaiseError(DB_ERROR_NODBSELECTED);
                }
            }
            $result = @mysqli_query($this->connection, 'COMMIT');
            $result = @mysqli_query($this->connection, 'SET AUTOCOMMIT=1');
            $this->transaction_opcount = 0;
            if (!$result) {
                return $this->mysqliRaiseError();
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
            if ($this->_db) {
                if (!@mysqli_select_db($this->connection, $this->_db)) {
                    return $this->mysqliRaiseError(DB_ERROR_NODBSELECTED);
                }
            }
            $result = @mysqli_query($this->connection, 'ROLLBACK');
            $result = @mysqli_query($this->connection, 'SET AUTOCOMMIT=1');
            $this->transaction_opcount = 0;
            if (!$result) {
                return $this->mysqliRaiseError();
            }
        }
        return DB_OK;
    }

    // }}}
    // {{{ affectedRows()

    /**
     * Gets the number of rows affected by the data manipulation
     * query.  For other queries, this function returns 0.
     *
     * @return integer number of rows affected by the last query
     */
    function affectedRows()
    {
        if (DB::isManip($this->last_query)) {
            return @mysqli_affected_rows($this->connection);
        } else {
            return 0;
        }
     }

    // }}}
    // {{{ errorNative()

    /**
     * Get the native error code of the last error (if any) that
     * occured on the current connection.
     *
     * @return int native MySQL error code
     * @access public
     */
    function errorNative()
    {
        return @mysqli_errno($this->connection);
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
            $result = $this->query("UPDATE ${seqname} ".
                                   'SET id=LAST_INSERT_ID(id+1)');
            $this->popErrorHandling();
            if ($result === DB_OK) {
                // COMMON CASE
                $id = @mysqli_insert_id($this->connection);
                if ($id != 0) {
                    return $id;
                }
                // EMPTY SEQ TABLE
                // Sequence table must be empty for some reason, so fill it and return 1
                // Obtain a user-level lock
                $result = $this->getOne("SELECT GET_LOCK('${seqname}_lock',10)");
                if (DB::isError($result)) {
                    return $this->raiseError($result);
                }
                if ($result == 0) {
                    // Failed to get the lock, bail with a DB_ERROR_NOT_LOCKED error
                    return $this->mysqliRaiseError(DB_ERROR_NOT_LOCKED);
                }

                // add the default value
                $result = $this->query("REPLACE INTO ${seqname} (id) VALUES (0)");
                if (DB::isError($result)) {
                    return $this->raiseError($result);
                }

                // Release the lock
                $result = $this->getOne("SELECT RELEASE_LOCK('${seqname}_lock')");
                if (DB::isError($result)) {
                    return $this->raiseError($result);
                }
                // We know what the result will be, so no need to try again
                return 1;

            } elseif ($ondemand && DB::isError($result) &&
                $result->getCode() == DB_ERROR_NOSUCHTABLE)
            {
                // ONDEMAND TABLE CREATION
                $result = $this->createSequence($seq_name);
                // Since createSequence initializes the ID to be 1,
                // we do not need to retrieve the ID again (or we will get 2)
                if (DB::isError($result)) {
                    return $this->raiseError($result);
                } else {
                    // First ID of a newly created sequence is 1
                    return 1;
                }

            } elseif (DB::isError($result) &&
                      $result->getCode() == DB_ERROR_ALREADY_EXISTS)
            {
                // BACKWARDS COMPAT
                // see _BCsequence() comment
                $result = $this->_BCsequence($seqname);
                if (DB::isError($result)) {
                    return $this->raiseError($result);
                }
                $repeat = 1;
            }
        } while ($repeat);

        return $this->raiseError($result);
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
        $res = $this->query("CREATE TABLE ${seqname} ".
                            '(id INTEGER UNSIGNED AUTO_INCREMENT NOT NULL,'.
                            ' PRIMARY KEY(id))');
        if (DB::isError($res)) {
            return $res;
        }
        // insert yields value 1, nextId call will generate ID 2
        return $this->query("INSERT INTO ${seqname} (id) VALUES (0)");
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
        return $this->query('DROP TABLE ' . $this->getSequenceName($seq_name));
    }

    // }}}
    // {{{ _BCsequence()

    /**
     * Backwards compatibility with old sequence emulation implementation
     * (clean up the dupes).
     *
     * @param string $seqname The sequence name to clean up
     * @return mixed DB_Error or true
     */
    function _BCsequence($seqname)
    {
        // Obtain a user-level lock... this will release any previous
        // application locks, but unlike LOCK TABLES, it does not abort
        // the current transaction and is much less frequently used.
        $result = $this->getOne("SELECT GET_LOCK('${seqname}_lock',10)");
        if (DB::isError($result)) {
            return $result;
        }
        if ($result == 0) {
            // Failed to get the lock, can't do the conversion, bail
            // with a DB_ERROR_NOT_LOCKED error
            return $this->mysqliRaiseError(DB_ERROR_NOT_LOCKED);
        }

        $highest_id = $this->getOne("SELECT MAX(id) FROM ${seqname}");
        if (DB::isError($highest_id)) {
            return $highest_id;
        }
        // This should kill all rows except the highest
        // We should probably do something if $highest_id isn't
        // numeric, but I'm at a loss as how to handle that...
        $result = $this->query("DELETE FROM ${seqname} WHERE id <> $highest_id");
        if (DB::isError($result)) {
            return $result;
        }

        // If another thread has been waiting for this lock,
        // it will go thru the above procedure, but will have no
        // real effect
        $result = $this->getOne("SELECT RELEASE_LOCK('${seqname}_lock')");
        if (DB::isError($result)) {
            return $result;
        }
        return true;
    }

    // }}}
    // {{{ quoteIdentifier()

    /**
     * Quote a string so it can be safely used as a table or column name
     *
     * Quoting style depends on which database driver is being used.
     *
     * MySQL can't handle the backtick character (<kbd>`</kbd>) in
     * table or column names.
     *
     * @param string $str  identifier name to be quoted
     *
     * @return string  quoted identifier string
     *
     * @since 1.6.0
     * @access public
     * @internal
     */
    function quoteIdentifier($str)
    {
        return '`' . $str . '`';
    }

    // }}}
    // {{{ escapeSimple()

    /**
     * Escape a string according to the current DBMS's standards
     *
     * @param string $str  the string to be escaped
     *
     * @return string  the escaped string
     *
     * @internal
     */
    function escapeSimple($str) {
        return @mysqli_real_escape_string($this->connection, $str);
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
            return $query . " LIMIT $count";
        } else {
            return $query . " LIMIT $from, $count";
        }
    }

    // }}}
    // {{{ mysqliRaiseError()

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
    function mysqliRaiseError($errno = null)
    {
        if ($errno === null) {
            if ($this->options['portability'] & DB_PORTABILITY_ERRORS) {
                $this->errorcode_map[1022] = DB_ERROR_CONSTRAINT;
                $this->errorcode_map[1048] = DB_ERROR_CONSTRAINT_NOT_NULL;
                $this->errorcode_map[1062] = DB_ERROR_CONSTRAINT;
            } else {
                // Doing this in case mode changes during runtime.
                $this->errorcode_map[1022] = DB_ERROR_ALREADY_EXISTS;
                $this->errorcode_map[1048] = DB_ERROR_CONSTRAINT;
                $this->errorcode_map[1062] = DB_ERROR_ALREADY_EXISTS;
            }
            $errno = $this->errorCode(mysqli_errno($this->connection));
        }
        return $this->raiseError($errno, null, null, null,
                                 @mysqli_errno($this->connection) . ' ** ' .
                                 @mysqli_error($this->connection));
    }

    // }}}
    // {{{ tableInfo()

    /**
     * Returns information about a table or a result set.
     *
     * WARNING: this method will probably not work because the mysqli_*()
     * functions it relies upon may not exist.
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
            $id = @mysqli_query($this->connection,
                                "SELECT * FROM $result LIMIT 0");
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

        if (!is_a($id, 'mysqli_result')) {
            return $this->mysqliRaiseError(DB_ERROR_NEED_MORE_DATA);
        }

        if ($this->options['portability'] & DB_PORTABILITY_LOWERCASE) {
            $case_func = 'strtolower';
        } else {
            $case_func = 'strval';
        }

        $count = @mysqli_num_fields($id);

        // made this IF due to performance (one if is faster than $count if's)
        if (!$mode) {
            for ($i=0; $i<$count; $i++) {
                $tmp = @mysqli_fetch_field($id);
                $res[$i]['table'] = $case_func($tmp->table);
                $res[$i]['name']  = $case_func($tmp->name);
                $res[$i]['type']  = isset($this->mysqli_types[$tmp->type]) ?
                                          $this->mysqli_types[$tmp->type] :
                                          'unknown';
                $res[$i]['len']   = $tmp->max_length;

                $res[$i]['flags'] = '';
                foreach ($this->mysqli_flags as $const => $means) {
                    if ($tmp->flags & $const) {
                        $res[$i]['flags'] .= $means . ' ';
                    }
                }
                if ($tmp->def) {
                    $res[$i]['flags'] .= 'default_' . rawurlencode($tmp->def);
                }
                $res[$i]['flags'] = trim($res[$i]['flags']);
            }
        } else { // full
            $res['num_fields']= $count;

            for ($i=0; $i<$count; $i++) {
                $tmp = @mysqli_fetch_field($id);
                $res[$i]['table'] = $case_func($tmp->table);
                $res[$i]['name']  = $case_func($tmp->name);
                $res[$i]['type']  = isset($this->mysqli_types[$tmp->type]) ?
                                          $this->mysqli_types[$tmp->type] :
                                          'unknown';
                $res[$i]['len']   = $tmp->max_length;

                $res[$i]['flags'] = '';
                foreach ($this->mysqli_flags as $const => $means) {
                    if ($tmp->flags & $const) {
                        $res[$i]['flags'] .= $means . ' ';
                    }
                }
                if ($tmp->def) {
                    $res[$i]['flags'] .= 'default_' . rawurlencode($tmp->def);
                }
                $res[$i]['flags'] = trim($res[$i]['flags']);

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
            @mysqli_free_result($id);
        }
        return $res;
    }

    // }}}
    // {{{ getSpecialQuery()

    /**
     * Returns the query needed to get some backend info.
     *
     * @param string $type What kind of info you want to retrieve
     * @return string The SQL query string
     */
    function getSpecialQuery($type)
    {
        switch ($type) {
            case 'tables':
                return 'SHOW TABLES';
            case 'views':
                return DB_ERROR_NOT_CAPABLE;
            case 'users':
                $sql = 'select distinct User from user';
                if ($this->dsn['database'] != 'mysql') {
                    $dsn = $this->dsn;
                    $dsn['database'] = 'mysql';
                    if (DB::isError($db = DB::connect($dsn))) {
                        return $db;
                    }
                    $sql = $db->getCol($sql);
                    $db->disconnect();
                    // XXX Fixme the mysql driver should take care of this
                    if (!@mysqli_select_db($this->connection, $this->dsn['database'])) {
                        return $this->mysqliRaiseError(DB_ERROR_NODBSELECTED);
                    }
                }
                return $sql;
            case 'databases':
                return 'SHOW DATABASES';
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
