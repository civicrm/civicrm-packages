<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * The PEAR DB driver for PHP's pgsql extension
 * for interacting with PostgreSQL databases
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
 * @author     Rui Hirokawa <hirokawa@php.net>
 * @author     Stig Bakken <ssb@php.net>
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
 * The methods PEAR DB uses to interact with PHP's pgsql extension
 * for interacting with PostgreSQL databases
 *
 * These methods overload the ones declared in DB_common.
 *
 * @category   Database
 * @package    DB
 * @author     Rui Hirokawa <hirokawa@php.net>
 * @author     Stig Bakken <ssb@php.net>
 * @author     Daniel Convissor <danielc@php.net>
 * @copyright  1997-2005 The PHP Group
 * @license    http://www.php.net/license/3_0.txt  PHP License
 * @version    Release: @package_version@
 * @link       http://pear.php.net/package/DB
 */
class DB_pgsql extends DB_common
{
    // {{{ properties

    /**
     * The DB driver type (mysql, oci8, odbc, etc.)
     * @var string
     */
    var $phptype = 'pgsql';

    /**
     * The database syntax variant to be used (db2, access, etc.), if any
     * @var string
     */
    var $dbsyntax = 'pgsql';

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
        'ssl'           => true,
        'transactions'  => true,
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
     * Should data manipulation queries be committed automatically?
     * @var bool
     */
    var $autocommit = true;

    /**
     * The quantity of transactions begun
     * @var integer
     */
    var $transaction_opcount = 0;

    /**
     * The number of rows affected by a data manipulation query
     * @var integer
     */
    var $affected = 0;

    /**
     * The current row being looked at in fetchInto()
     * @var array
     */
    var $row = array();

    /**
     * The number of rows in a given result set
     * @var array
     */
    var $_num_rows = array();


    // }}}
    // {{{ constructor

    function DB_pgsql()
    {
        $this->DB_common();
    }

    // }}}
    // {{{ connect()

    /**
     * Connect to the database server, log in and open the database
     *
     * The pgsql driver supports several DSN options:
     *
     *   + connect_timeout  How many seconds to wait for a connection to
     *                       be established.  Available since PEAR DB 1.7.0.
     *   + new_link         Causes subsequent calls to connect() to return a
     *                       new connection link instead of the existing one.
     *                       WARNING: this is not portable to other DBMS's.
     *                       Available only if PHP is >= 4.3.0 and PEAR DB
     *                       is >= 1.7.0.
     *   + options          Command line options to be sent to the server.
     *                       Available since PEAR DB 1.6.4.
     *   + service          Specifies a service name in pg_service.conf that
     *                       holds additional connection parameters.
     *                       Available since PEAR DB 1.7.0.
     *   + sslmode          How should SSL be used when connecting?  Values:
     *                       disable, allow, prefer or require.
     *                       Available since PEAR DB 1.7.0.
     *   + tty              This was used to specify where to send server
     *                       debug output.  Available since PEAR DB 1.6.4.
     *
     * @param array $dsn         the data source name
     * @param bool  $persistent  should the connection should be persistent?
     *
     * @return int  DB_OK on success. A DB_error object on failure.
     *
     * @access private
     * @see DB::connect(), DB::parseDSN()
     * @link http://www.postgresql.org/docs/current/static/libpq.html#LIBPQ-CONNECT
     */
    function connect($dsninfo, $persistent = false)
    {
        if (!DB::assertExtension('pgsql')) {
            return $this->raiseError(DB_ERROR_EXTENSION_NOT_FOUND);
        }

        $this->dsn = $dsninfo;
        if ($dsninfo['dbsyntax']) {
            $this->dbsyntax = $dsninfo['dbsyntax'];
        }

        $protocol = $dsninfo['protocol'] ? $dsninfo['protocol'] : 'tcp';

        $params = array();
        $params[0] = '';
        if ($protocol == 'tcp') {
            if ($dsninfo['hostspec']) {
                $params[0] .= 'host=' . $dsninfo['hostspec'];
            }
            if ($dsninfo['port']) {
                $params[0] .= ' port=' . $dsninfo['port'];
            }
        } elseif ($protocol == 'unix') {
            // Allow for pg socket in non-standard locations.
            if ($dsninfo['socket']) {
                $params[0] .= 'host=' . $dsninfo['socket'];
            }
            if ($dsninfo['port']) {
                $params[0] .= ' port=' . $dsninfo['port'];
            }
        }
        if ($dsninfo['database']) {
            $params[0] .= ' dbname=\'' . addslashes($dsninfo['database']) . '\'';
        }
        if ($dsninfo['username']) {
            $params[0] .= ' user=\'' . addslashes($dsninfo['username']) . '\'';
        }
        if ($dsninfo['password']) {
            $params[0] .= ' password=\'' . addslashes($dsninfo['password']) . '\'';
        }
        if (!empty($dsninfo['options'])) {
            $params[0] .= ' options=' . $dsninfo['options'];
        }
        if (!empty($dsninfo['tty'])) {
            $params[0] .= ' tty=' . $dsninfo['tty'];
        }
        if (!empty($dsninfo['connect_timeout'])) {
            $params[0] .= ' connect_timeout=' . $dsninfo['connect_timeout'];
        }
        if (!empty($dsninfo['sslmode'])) {
            $params[0] .= ' sslmode=' . $dsninfo['sslmode'];
        }
        if (!empty($dsninfo['service'])) {
            $params[0] .= ' service=' . $dsninfo['service'];
        }

        if (version_compare(phpversion(), '4.3.0', '>=')) {
            if (isset($dsninfo['new_link'])
                && $dsninfo['new_link'] == 'true')
            {
                $params[] = PGSQL_CONNECT_FORCE_NEW;
            }
        }

        $connect_function = $persistent ? 'pg_pconnect' : 'pg_connect';

        $ini = ini_get('track_errors');
        $php_errormsg = '';
        if ($ini) {
            $conn = @call_user_func_array($connect_function, $params);
        } else {
            ini_set('track_errors', 1);
            $conn = @call_user_func_array($connect_function, $params);
            ini_set('track_errors', $ini);
        }
        if ($conn == false) {
            return $this->raiseError(DB_ERROR_CONNECT_FAILED, null,
                                     null, null, strip_tags($php_errormsg));
        }
        $this->connection = $conn;
        return DB_OK;
    }

    // }}}
    // {{{ disconnect()

    /**
     * Log out and disconnect from the database.
     *
     * @return bool true on success, false if not connected.
     */
    function disconnect()
    {
        $ret = @pg_close($this->connection);
        $this->connection = null;
        return $ret;
    }

    // }}}
    // {{{ simpleQuery()

    /**
     * Send a query to PostgreSQL and return the results as a
     * PostgreSQL resource identifier.
     *
     * @param $query the SQL query
     *
     * @return int returns a valid PostgreSQL result for successful SELECT
     * queries, DB_OK for other successful queries.  A DB error code
     * is returned on failure.
     */
    function simpleQuery($query)
    {
        $ismanip = DB::isManip($query);
        $this->last_query = $query;
        $query = $this->modifyQuery($query);
        if (!$this->autocommit && $ismanip) {
            if ($this->transaction_opcount == 0) {
                $result = @pg_exec($this->connection, 'begin;');
                if (!$result) {
                    return $this->pgsqlRaiseError();
                }
            }
            $this->transaction_opcount++;
        }
        $result = @pg_exec($this->connection, $query);
        if (!$result) {
            return $this->pgsqlRaiseError();
        }
        // Determine which queries that should return data, and which
        // should return an error code only.
        if ($ismanip) {
            $this->affected = @pg_affected_rows($result);
            return DB_OK;
        } elseif (preg_match('/^\s*\(*\s*(SELECT|EXPLAIN|SHOW)\s/si', $query)) {
            /* PostgreSQL commands:
               ABORT, ALTER, BEGIN, CLOSE, CLUSTER, COMMIT, COPY,
               CREATE, DECLARE, DELETE, DROP TABLE, EXPLAIN, FETCH,
               GRANT, INSERT, LISTEN, LOAD, LOCK, MOVE, NOTIFY, RESET,
               REVOKE, ROLLBACK, SELECT, SELECT INTO, SET, SHOW,
               UNLISTEN, UPDATE, VACUUM
            */
            $this->row[(int)$result] = 0; // reset the row counter.
            $numrows = $this->numrows($result);
            if (is_object($numrows)) {
                return $numrows;
            }
            $this->_num_rows[(int)$result] = $numrows;
            $this->affected = 0;
            return $result;
        } else {
            $this->affected = 0;
            return DB_OK;
        }
    }

    // }}}
    // {{{ nextResult()

    /**
     * Move the internal pgsql result pointer to the next available result
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
        if (!isset($error_regexps)) {
            $error_regexps = array(
                '/(([Rr]elation|[Ss]equence|[Tt]able)( [\"\'].*[\"\'])? does not exist|[Cc]lass ".+" not found)$/' => DB_ERROR_NOSUCHTABLE,
                '/index .* does not exist/' => DB_ERROR_NOT_FOUND,
                '/[Cc]olumn [\"\'].*[\"\'] .*does not exist/' => DB_ERROR_NOSUCHFIELD,
                '/[Rr]elation [\"\'].*[\"\'] already exists|[Cc]annot insert a duplicate key into (a )?unique index.*/' => DB_ERROR_ALREADY_EXISTS,
                '/(divide|division) by zero$/'          => DB_ERROR_DIVZERO,
                '/pg_atoi: error in .*: can\'t parse /' => DB_ERROR_INVALID_NUMBER,
                '/invalid input syntax for integer/'    => DB_ERROR_INVALID_NUMBER,
                '/ttribute [\"\'].*[\"\'] not found$|[Rr]elation [\"\'].*[\"\'] does not have attribute [\"\'].*[\"\']/' => DB_ERROR_NOSUCHFIELD,
                '/parser: parse error at or near \"/'   => DB_ERROR_SYNTAX,
                '/syntax error at/'                     => DB_ERROR_SYNTAX,
                '/permission denied/'                   => DB_ERROR_ACCESS_VIOLATION,
                '/violates not-null constraint/'        => DB_ERROR_CONSTRAINT_NOT_NULL,
                '/violates [\w ]+ constraint/'          => DB_ERROR_CONSTRAINT,
                '/referential integrity violation/'     => DB_ERROR_CONSTRAINT,
                '/more expressions than target columns/i' => DB_ERROR_VALUE_COUNT_ON_ROW,
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
        $result_int = (int)$result;
        $rownum = ($rownum !== null) ? $rownum : $this->row[$result_int];
        if ($rownum >= $this->_num_rows[$result_int]) {
            return null;
        }
        if ($fetchmode & DB_FETCHMODE_ASSOC) {
            $arr = @pg_fetch_array($result, $rownum, PGSQL_ASSOC);
            if ($this->options['portability'] & DB_PORTABILITY_LOWERCASE && $arr) {
                $arr = array_change_key_case($arr, CASE_LOWER);
            }
        } else {
            $arr = @pg_fetch_row($result, $rownum);
        }
        if (!$arr) {
            $err = pg_errormessage($this->connection);
            if (!$err) {
                return null;
            }
            return $this->pgsqlRaiseError();
        }
        if ($this->options['portability'] & DB_PORTABILITY_RTRIM) {
            $this->_rtrimArrayValues($arr);
        }
        if ($this->options['portability'] & DB_PORTABILITY_NULL_TO_EMPTY) {
            $this->_convertNullArrayValuesToEmpty($arr);
        }
        $this->row[$result_int] = ++$rownum;
        return DB_OK;
    }

    // }}}
    // {{{ freeResult()

    /**
     * Free the internal resources associated with $result.
     *
     * @param $result int PostgreSQL result identifier
     *
     * @return bool true on success, false if $result is invalid
     */
    function freeResult($result)
    {
        if (is_resource($result)) {
            unset($this->row[(int)$result]);
            unset($this->_num_rows[(int)$result]);
            $this->affected = 0;
            return @pg_freeresult($result);
        }
        return false;
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
    // {{{ escapeSimple()

    /**
     * Escape a string according to the current DBMS's standards
     *
     * PostgreSQL treats a backslash as an escape character, so they are
     * removed.
     *
     * Not using pg_escape_string() yet because it requires PostgreSQL
     * to be at version 7.2 or greater.
     *
     * @param string $str  the string to be escaped
     *
     * @return string  the escaped string
     *
     * @internal
     */
    function escapeSimple($str) {
        return str_replace("'", "''", str_replace('\\', '\\\\', $str));
    }

    // }}}
    // {{{ numCols()

    /**
     * Get the number of columns in a result set.
     *
     * @param $result resource PostgreSQL result identifier
     *
     * @return int the number of columns per row in $result
     */
    function numCols($result)
    {
        $cols = @pg_numfields($result);
        if (!$cols) {
            return $this->pgsqlRaiseError();
        }
        return $cols;
    }

    // }}}
    // {{{ numRows()

    /**
     * Get the number of rows in a result set.
     *
     * @param $result resource PostgreSQL result identifier
     *
     * @return int the number of rows in $result
     */
    function numRows($result)
    {
        $rows = @pg_numrows($result);
        if ($rows === null) {
            return $this->pgsqlRaiseError();
        }
        return $rows;
    }

    // }}}
    // {{{ errorNative()

    /**
     * Get the native error code of the last error (if any) that
     * occured on the current connection.
     *
     * @return int native PostgreSQL error code
     */
    function errorNative()
    {
        return pg_errormessage($this->connection);
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
            // (disabled) hack to shut up error messages from libpq.a
            //@fclose(@fopen("php://stderr", "w"));
            $result = @pg_exec($this->connection, 'end;');
            $this->transaction_opcount = 0;
            if (!$result) {
                return $this->pgsqlRaiseError();
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
            $result = @pg_exec($this->connection, 'abort;');
            $this->transaction_opcount = 0;
            if (!$result) {
                return $this->pgsqlRaiseError();
            }
        }
        return DB_OK;
    }

    // }}}
    // {{{ affectedRows()

    /**
     * Gets the number of rows affected by the last query.
     * if the last query was a select, returns 0.
     *
     * @return int number of rows affected by the last query or DB_ERROR
     */
    function affectedRows()
    {
        return $this->affected;
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
        $repeat = false;
        do {
            $this->pushErrorHandling(PEAR_ERROR_RETURN);
            $result =& $this->query("SELECT NEXTVAL('${seqname}')");
            $this->popErrorHandling();
            if ($ondemand && DB::isError($result) &&
                $result->getCode() == DB_ERROR_NOSUCHTABLE) {
                $repeat = true;
                $this->pushErrorHandling(PEAR_ERROR_RETURN);
                $result = $this->createSequence($seq_name);
                $this->popErrorHandling();
                if (DB::isError($result)) {
                    return $this->raiseError($result);
                }
            } else {
                $repeat = false;
            }
        } while ($repeat);
        if (DB::isError($result)) {
            return $this->raiseError($result);
        }
        $arr = $result->fetchRow(DB_FETCHMODE_ORDERED);
        $result->free();
        return $arr[0];
    }

    // }}}
    // {{{ createSequence()

    /**
     * Create the sequence
     *
     * @param string $seq_name the name of the sequence
     * @return mixed DB_OK on success or DB error on error
     * @access public
     */
    function createSequence($seq_name)
    {
        $seqname = $this->getSequenceName($seq_name);
        $result = $this->query("CREATE SEQUENCE ${seqname}");
        return $result;
    }

    // }}}
    // {{{ dropSequence()

    /**
     * Drop a sequence
     *
     * @param string $seq_name the name of the sequence
     * @return mixed DB_OK on success or DB error on error
     * @access public
     */
    function dropSequence($seq_name)
    {
        $seqname = $this->getSequenceName($seq_name);
        return $this->query("DROP SEQUENCE ${seqname}");
    }

    // }}}
    // {{{ modifyLimitQuery()

    function modifyLimitQuery($query, $from, $count, $params = array())
    {
        $query = $query . " LIMIT $count OFFSET $from";
        return $query;
    }

    // }}}
    // {{{ pgsqlRaiseError()

    /**
     * Gather information about an error, then use that info to create a
     * DB error object and finally return that object.
     *
     * @param  integer  $errno  PEAR error number (usually a DB constant) if
     *                          manually raising an error
     * @return object  DB error object
     * @see errorNative()
     * @see errorCode()
     * @see DB_common::raiseError()
     */
    function pgsqlRaiseError($errno = null)
    {
        $native = $this->errorNative();
        if ($errno === null) {
            $err = $this->errorCode($native);
        } else {
            $err = $errno;
        }
        return $this->raiseError($err, null, null, null, $native);
    }

    // }}}
    // {{{ _pgFieldFlags()

    /**
     * Flags of a Field
     *
     * @param int $resource PostgreSQL result identifier
     * @param int $num_field the field number
     *
     * @return string The flags of the field ("not_null", "default_value",
     *                "primary_key", "unique_key" and "multiple_key"
     *                are supported).  The default value is passed
     *                through rawurlencode() in case there are spaces in it.
     * @access private
     */
    function _pgFieldFlags($resource, $num_field, $table_name)
    {
        $field_name = @pg_fieldname($resource, $num_field);

        $result = @pg_exec($this->connection, "SELECT f.attnotnull, f.atthasdef
                                FROM pg_attribute f, pg_class tab, pg_type typ
                                WHERE tab.relname = typ.typname
                                AND typ.typrelid = f.attrelid
                                AND f.attname = '$field_name'
                                AND tab.relname = '$table_name'");
        if (@pg_numrows($result) > 0) {
            $row = @pg_fetch_row($result, 0);
            $flags  = ($row[0] == 't') ? 'not_null ' : '';

            if ($row[1] == 't') {
                $result = @pg_exec($this->connection, "SELECT a.adsrc
                                    FROM pg_attribute f, pg_class tab, pg_type typ, pg_attrdef a
                                    WHERE tab.relname = typ.typname AND typ.typrelid = f.attrelid
                                    AND f.attrelid = a.adrelid AND f.attname = '$field_name'
                                    AND tab.relname = '$table_name' AND f.attnum = a.adnum");
                $row = @pg_fetch_row($result, 0);
                $num = preg_replace("/'(.*)'::\w+/", "\\1", $row[0]);
                $flags .= 'default_' . rawurlencode($num) . ' ';
            }
        } else {
            $flags = '';
        }
        $result = @pg_exec($this->connection, "SELECT i.indisunique, i.indisprimary, i.indkey
                                FROM pg_attribute f, pg_class tab, pg_type typ, pg_index i
                                WHERE tab.relname = typ.typname
                                AND typ.typrelid = f.attrelid
                                AND f.attrelid = i.indrelid
                                AND f.attname = '$field_name'
                                AND tab.relname = '$table_name'");
        $count = @pg_numrows($result);

        for ($i = 0; $i < $count ; $i++) {
            $row = @pg_fetch_row($result, $i);
            $keys = explode(' ', $row[2]);

            if (in_array($num_field + 1, $keys)) {
                $flags .= ($row[0] == 't' && $row[1] == 'f') ? 'unique_key ' : '';
                $flags .= ($row[1] == 't') ? 'primary_key ' : '';
                if (count($keys) > 1)
                    $flags .= 'multiple_key ';
            }
        }

        return trim($flags);
    }

    // }}}
    // {{{ tableInfo()

    /**
     * Returns information about a table or a result set.
     *
     * NOTE: only supports 'table' and 'flags' if <var>$result</var>
     * is a table name.
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
    function tableInfo($result, $mode = null)
    {
        if (is_string($result)) {
            /*
             * Probably received a table name.
             * Create a result resource identifier.
             */
            $id = @pg_exec($this->connection, "SELECT * FROM $result LIMIT 0");
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
            return $this->pgsqlRaiseError(DB_ERROR_NEED_MORE_DATA);
        }

        if ($this->options['portability'] & DB_PORTABILITY_LOWERCASE) {
            $case_func = 'strtolower';
        } else {
            $case_func = 'strval';
        }

        $count = @pg_numfields($id);

        // made this IF due to performance (one if is faster than $count if's)
        if (!$mode) {

            for ($i=0; $i<$count; $i++) {
                $res[$i]['table'] = $got_string ? $case_func($result) : '';
                $res[$i]['name']  = $case_func(@pg_fieldname($id, $i));
                $res[$i]['type']  = @pg_fieldtype($id, $i);
                $res[$i]['len']   = @pg_fieldsize($id, $i);
                $res[$i]['flags'] = $got_string ? $this->_pgFieldflags($id, $i, $result) : '';
            }

        } else { // full
            $res['num_fields']= $count;

            for ($i=0; $i<$count; $i++) {
                $res[$i]['table'] = $got_string ? $case_func($result) : '';
                $res[$i]['name']  = $case_func(@pg_fieldname($id, $i));
                $res[$i]['type']  = @pg_fieldtype($id, $i);
                $res[$i]['len']   = @pg_fieldsize($id, $i);
                $res[$i]['flags'] = $got_string ? $this->_pgFieldFlags($id, $i, $result) : '';

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
            @pg_freeresult($id);
        }
        return $res;
    }

    // }}}
    // {{{ getTablesQuery()

    /**
     * Returns the query needed to get some backend info
     * @param string $type What kind of info you want to retrieve
     * @return string The SQL query string
     */
    function getSpecialQuery($type)
    {
        switch ($type) {
            case 'tables':
                return "SELECT c.relname as \"Name\"
                        FROM pg_class c, pg_user u
                        WHERE c.relowner = u.usesysid AND c.relkind = 'r'
                        AND not exists (select 1 from pg_views where viewname = c.relname)
                        AND c.relname !~ '^(pg_|sql_)'
                        UNION
                        SELECT c.relname as \"Name\"
                        FROM pg_class c
                        WHERE c.relkind = 'r'
                        AND not exists (select 1 from pg_views where viewname = c.relname)
                        AND not exists (select 1 from pg_user where usesysid = c.relowner)
                        AND c.relname !~ '^pg_'";
            case 'views':
                // Table cols: viewname | viewowner | definition
                return 'SELECT viewname FROM pg_views';
            case 'users':
                // cols: usename |usesysid|usecreatedb|usetrace|usesuper|usecatupd|passwd  |valuntil
                return 'SELECT usename FROM pg_user';
            case 'databases':
                return 'SELECT datname FROM pg_database';
            case 'functions':
                return 'SELECT proname FROM pg_proc';
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
