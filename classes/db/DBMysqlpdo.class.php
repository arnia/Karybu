<?php
require_once('DBMysql.class.php');

use Psr\Log\LoggerInterface;
use Karybu\Exception\DBConnectionFailedException;

/**
 * Class to use MySQLi DBMS as mysqli_*
 * mysql handling class
 *
 * Does not use prepared statements, since mysql driver does not support them
 *
 * @author Arnia (developers@xpressengine.com)
 * @package /classes/db
 * @version 0.1
 **/
class DBMysqlpdo extends DBMysql
{

    /**
     * Variables for using PDO
     **/
    var $bind_idx = 0;
    var $bind_vars = array();
    var $param = array();

    /**
     * Constructor
     * @return void
     **/
    function DBMysqlpdo(LoggerInterface $logger = null)
    {
        $this->logger = $logger;
        $this->_setDBInfo();
        $connected = $this->_connect();
        if (!$connected) {
            throw new DBConnectionFailedException ("Connection to MySQL failed!");
        }
    }

    /**
     * Return if installed
     **/
    function isSupported()
    {
        return class_exists('PDO');
    }

    /**
     * create an instance of this class
     */
    function create(LoggerInterface $logger = null)
    {
        return new DBMysqlpdo($logger);
    }

    /**
     * DB Connect
     * this method is private
     * @param array $connection connection's value is db_hostname, db_port, db_database, db_userid, db_password
     * @return resource
     */
    function __connect($connection)
    {
        // Attempt to connect
        try {
            // PDO is only supported with PHP5,
            // so it is allowed to use try~catch statment in this class.
            $result = new PDO('mysql:=' . $connection['db_hostname'] . ';port=' . $connection[db_port] . ';dbname=' . $connection['db_database'] . ';', $connection['db_userid'], $connection['db_password'], array(
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
            ));
            // Make sure the default behaviour is to always auto-commit; this will be set to false on each $db->begin() call (begin transaction)
            $result->setAttribute(PDO::ATTR_AUTOCOMMIT, true);
        } catch (PDOException $e) {
            error_log($e->getMessage());
            $this->setError(-1, 'Connection failed: ' . $e->getMessage());
            $this->is_connected = false;
            return;
        }

        return $result;
    }

    /**
     * disconnect to DB
     **/
    function _close(PDO &$connection)
    {
        if ($this->transaction_started) {
            /** @var $connection PDO */
            $connection->commit();
            $this->transaction_started = false;
        }
    }

    /**
     * DB transaction start
     * this method is private
     * @return boolean
     */
    function _begin()
    {
        /** @var $connection PDO */
        $connection = & $this->_getConnection('master');
        $connection->beginTransaction(); // Turns off auto-commit
        return true;
    }

    /**
     * Commit
     **/
    function _commit()
    {
        /** @var $connection PDO */
        $connection = & $this->_getConnection('master');
        try {
            $connection->commit();
            return true;
        } catch (PDOException $e) {
            // There was no transaction started, so just continue.
            error_log($e->getMessage());
            return false;
        }
    }

    /**
     * DB transaction rollback
     * this method is private
     * @return boolean
     */
    function _rollback()
    {
        /** @var $connection PDO */
        $connection = & $this->_getConnection('master');
        $connection->rollBack();
        return true;
    }

    /**
     * Add or change quotes to the query string variables
     **/
    function addQuotes($string)
    {
        if (version_compare(PHP_VERSION, "5.9.0", "<") && get_magic_quotes_gpc()) {
            $string = stripslashes(
                str_replace("\\", "\\\\", $string)
            );
        }
        if (!is_numeric($string)) {
            $string = str_replace("'", "''", $string);
        }
        return $string;
    }

    /**
     * Execute the query
     * this method is private
     * @param string $query
     * @param resource $connection
     * @return resource
     */
    function __query($query, PDO &$connection)
    {
        if ($this->use_prepared_statements == 'Y') {
            // 1. Prepare query
            /** @var $stmt PDOStatement */
            $stmt = $connection->prepare($query);
            if ($stmt) {
                //$types = '';
                $params = array();
                $this->_prepareQueryParameters($params);

                if (!empty($params)) {
                    try {
                        foreach ($params as $key => $param) {
                            $stmt->bindParam($key + 1, $param->value, $param->type);
                        }
                    } catch (PDOException $e) {
                        error_log($e->getMessage());
                        $this->setError(-1, $e->getMessage());
                    }
                }

                try {
                    $stmt->execute();
                } catch (PDOException $e) {
                    error_log($e->getMessage());
                    $this->setError(-1, $e->getMessage());
                }

                // Return stmt for other processing - like retrieving resultset (_fetch)
                return $stmt;
            }

        }
        // Run the query statement
        try {
            $stmt = $connection->query($query);
        } catch (PDOException $e) {
            error_log($e->getMessage());
            $this->setError(-1, $e->getMessage());
        }

        return $stmt;
    }

    /**
     * Before execute query, prepare statement
     * this method is private
     * @param string $types
     * @param array $params
     * @return void
     */
    function _prepareQueryParameters(&$params)
    {
        $params = array();
        if (!$this->param) {
            return;
        }

        foreach ($this->param as $k => $o) {
            $value = $o->getUnescapedValue();
            $type = $o->getType();

            // Skip column names -> this should be concatenated to query string
            if ($o->isColumnName()) {
                continue;
            }

            switch ($type) {
                case 'number' :
                    $type = PDO::PARAM_INT;
                    break;
                case 'varchar' :
                    $type = PDO::PARAM_STR;
                    break;
                case 'bignumber' :
                    $type = PDO::PARAM_INT;
                    break;
                case 'char' :
                    $type = PDO::PARAM_STR;
                    break;
                case 'text' :
                    $type = PDO::PARAM_STR;
                    break;
                case 'bigtext' :
                    $type = PDO::PARAM_STR;
                    break;
                case 'date' :
                    $type = PDO::PARAM_STR;
                    break;
                case 'float' :
                    $type = PDO::PARAM_STR;
                    break;
                default:
                    $type = PDO::PARAM_STR;

            }
            $param = new stdClass();
            if (is_array($value)) {
                foreach ($value as $v) {
                    $param->value = $v;
                    $param->type = $type;
                    $params[] = $param;
                }
            } else {
                $param->value = $value;
                $param->type = $type;
                $params[] = $param;
            }


        }
    }

    /**
     * Fetch the result
     * @param resource $result
     * @param int|NULL $arrayIndexEndValue
     * @return array
     */
    function _fetch(PDOStatement &$result, $arrayIndexEndValue = null)
    {
        if ($this->use_prepared_statements != 'Y') {
            return parent::_fetch($result, $arrayIndexEndValue);
        }
        $output = array();
        if (!$this->isConnected() || $this->isError() || !$result) {
            return $output;
        }
        while ($tmp = $result->fetch((PDO::FETCH_OBJ))) {
            if ($arrayIndexEndValue) {
                $output[$arrayIndexEndValue--] = $tmp;
            }
            else {
                $output[] = $tmp;
            }
        }
        if (count($output) == 1) {
            if (isset($arrayIndexEndValue)) {
                return $output;
            }
            else {
                return $output[0];
            }
        }
        $result->closeCursor();
        return $output;
    }

    /**
     * Handles insertAct
     * @param Object $queryObject
     * @param boolean $with_values
     * @return resource
     */
    function _executeInsertAct($queryObject, $with_values = false)
    {
        if ($this->use_prepared_statements != 'Y') {
            return parent::_executeInsertAct($queryObject);
        }
        $this->param = $queryObject->getArguments();
        $result = parent::_executeInsertAct($queryObject, $with_values);
        unset($this->param);
        return $result;
    }

    /**
     * Handles updateAct
     * @param Object $queryObject
     * @param boolean $with_values
     * @return resource
     */
    function _executeUpdateAct($queryObject, $with_values = false)
    {
        if ($this->use_prepared_statements != 'Y') {
            return parent::_executeUpdateAct($queryObject);
        }
        $this->param = $queryObject->getArguments();
        $result = parent::_executeUpdateAct($queryObject, $with_values);
        unset($this->param);
        return $result;
    }

    /**
     * Handles deleteAct
     * @param Object $queryObject
     * @param boolean $with_values
     * @return resource
     */
    function _executeDeleteAct($queryObject, $with_values = false)
    {
        if ($this->use_prepared_statements != 'Y') {
            return parent::_executeDeleteAct($queryObject);
        }
        $this->param = $queryObject->getArguments();
        $result = parent::_executeDeleteAct($queryObject, $with_values);
        unset($this->param);
        return $result;
    }

    /**
     * Handle selectAct
     * In order to get a list of pages easily when selecting \n
     * it supports a method as navigation
     * @param Object $queryObject
     * @param resource $connection
     * @param boolean $with_values
     * @return Object
     */
    function _executeSelectAct($queryObject, $connection = null, $with_values = false)
    {
        if ($this->use_prepared_statements != 'Y') {
            return parent::_executeSelectAct($queryObject, $connection);
        }
        $this->param = $queryObject->getArguments();
        $result = parent::_executeSelectAct($queryObject, $connection, $with_values);
        unset($this->param);
        return $result;
    }

    /**
     * Get the ID generated in the last query
     * Return next sequence from sequence table
     * This method use only mysql
     * @return int
     */
    function db_insert_id()
    {
        /** @var $connection PDO */
        $connection = $this->_getConnection('master');
        return $connection->lastInsertId();
    }

    /**
     * Fetch a result row as an object
     * @param resource $result
     * @return object
     */
    function db_fetch_object(PDOStatement &$result)
    {
        return $result->fetch((PDO::FETCH_OBJ));
    }

    /**
     * Free result memory
     * @param resource $result
     * @return boolean Returns TRUE on success or FALSE on failure.
     */
    function db_free_result(PDOStatement &$result)
    {
        return $result->closeCursor();
    }
}
?>
