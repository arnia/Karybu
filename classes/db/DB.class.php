<?php

use \Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Karybu\Event\QueryEvent;
use Karybu\Event\DBEvents;

/**
 * - DB parent class
 * - usage of db in Karybu is via xml
 * - there are 2 types of xml - query xml, schema xml
 * - in case of query xml, DB::executeQuery() method compiles xml file into php code and then execute it
 * - query xml has unique query id, and will be created in module
 * - queryid = module_name.query_name
 *
 * Obs. Here we have a design pattern misuse: DB is the same time a Factory and a parent for specific db classes.
 *      Hopefully this will change in the near future (current date: 14.03.2013)
 *
 * @author Arnia (dev@karybu.org)
 * @package /classes/db
 * @version 0.1
 */
class DB
{

    /**
     * count cache path
     * @var string
     */
    var $count_cache_path = 'files/cache/db';

    /**
     * operations for condition
     * @var array
     */
    var $cond_operation = array(
        'equal' => '=',
        'more' => '>=',
        'excess' => '>',
        'less' => '<=',
        'below' => '<',
        'notequal' => '<>',
        'notnull' => 'is not null',
        'null' => 'is null',
    );

    /**
     * master database connection string
     * @var array
     */
    var $master_db = null;
    /**
     * array of slave databases connection strings
     * @var array
     */
    var $slave_db = null;

    var $result = null;

    /**
     * error code (0 means no error)
     * @var int
     */
    var $errno = 0;
    /**
     * error message
     * @var string
     */
    var $errstr = '';
    /**
     * query string of latest executed query
     * @var string
     */
    var $query = '';
    var $connection = '';

    /**
     * Query info of the last query executed
     *
     * @var QueryEvent
     */
    private $query_event;

    /**
     * transaction flag
     * @var boolean
     */
    var $transaction_started = false;

    var $is_connected = false;

    /**
     * returns enable list in supported dbms list
     * will be written by classes/DB/DB***.class.php
     * @var array
     */
    var $supported_list = array();

    /**
     * location of query cache
     * @var string
     */
    var $cache_file = 'files/cache/queries/';

    /**
     * stores database type: 'mysql','cubrid','mssql' etc. or 'db' when database is not yet set
     * @var string
     */
    var $db_type;

    /**
     * flag to decide if class prepared statements or not (when supported); can be changed from db.config.info
     * @var string
     */
    var $use_prepared_statements;

    static private $logger = null;

    /** @var EventDispatcherInterface */
    private static $dispatcher;

    /**
     * returns instance of certain db type
     * @param string $db_type type of db
     * @return DB return DB object instance
     */
    public static function getInstance($db_type = null)
    {
        if (!$db_type) {
            $db_type = Context::getDBType();
        }
        if (!$db_type && Context::isInstalled()) {
            return new Object(-1, 'msg_db_not_setted');
        }

        if (!isset($GLOBALS['__DB__'])) {
            $GLOBALS['__DB__'] = array();
        }
        if(!isset($GLOBALS['__DB__'][$db_type])) {
            $class_name = 'DB'.ucfirst($db_type);
            $class_file = _KARYBU_PATH_."classes/db/$class_name.class.php";
            if (!file_exists($class_file)) {
                return new Object(-1, 'msg_db_not_setted');
            }

            // get a singletone instance of the database driver class
            require_once($class_file);
            $GLOBALS['__DB__'][$db_type] = call_user_func(array($class_name, 'create'), self::$logger);
            $GLOBALS['__DB__'][$db_type]->db_type = $db_type;
        }

        return $GLOBALS['__DB__'][$db_type];

    }

    /**
     * returns instance of db
     * @return DB return DB object instance
     */
    static function create()
    {
        return new DB;
    }

    /**
     * constructor
     * @return void
     */
    function DB()
    {
        $this->count_cache_path = _KARYBU_PATH_.$this->count_cache_path;
    }

    /**
     * returns list of supported dbms list
     * this list return by directory list
     * check by instance can creatable
     * @return array return supported DBMS list
     */
    function getSupportedList()
    {
        $oDB = new DB();
        return $oDB->_getSupportedList();
    }

    /**
     * returns enable list in supported dbms list
     * this list return by child class
     * @return array return enable DBMS list in supported dbms list
     */
    function getEnableList()
    {
        if (!$this->supported_list) {
            $oDB = new DB();
            $this->supported_list = $oDB->_getSupportedList();
        }

        $enableList = array();
        if (is_array($this->supported_list)) {
            foreach ($this->supported_list AS $key => $value) {
                if ($value->enable) {
                    array_push($enableList, $value);
                }
            }
        }
        return $enableList;
    }

    /**
     * returns list of disable in supported dbms list
     * this list return by child class
     * @return array return disable DBMS list in supported dbms list
     */
    function getDisableList()
    {
        if (!$this->supported_list) {
            $oDB = new DB();
            $this->supported_list = $oDB->_getSupportedList();
        }

        $disableList = array();
        if (is_array($this->supported_list)) {
            foreach ($this->supported_list AS $key => $value) {
                if (!$value->enable) {
                    array_push($disableList, $value);
                }
            }
        }
        return $disableList;
    }

    /**
     * returns list of supported dbms list
     * this method is private
     * @return array return supported DBMS list
     */
    function _getSupportedList()
    {
        static $get_supported_list = '';
        if(is_array($get_supported_list)) {
            $this->supported_list = $get_supported_list;
            return $this->supported_list;
        }
        $get_supported_list = array();
        $db_classes_path = _KARYBU_PATH_."classes/db/";
        $filter = "/^DB([^\.]+)\.class\.php/i";
        $supported_list = FileHandler::readDir($db_classes_path, $filter, true);
        sort($supported_list);

        // after creating instance of class, check is supported
        for($i = 0; $i < count($supported_list); $i++) {
            $db_type = $supported_list[$i];

            if (version_compare(phpversion(), '5.0') < 0 && preg_match('/pdo/i', $db_type)) {
                continue;
            }

            $class_name = sprintf("DB%s%s", strtoupper(substr($db_type,0,1)), strtolower(substr($db_type,1)));
            $class_file = sprintf(_KARYBU_PATH_."classes/db/%s.class.php", $class_name);
            if (!file_exists($class_file)) {
                continue;
            }

            unset($oDB);
            require_once($class_file);
            $tmp_fn = create_function('', "return new {$class_name}();");
            $oDB    = $tmp_fn();

            if (!$oDB) {
                continue;
            }

            $obj = new stdClass();
            $obj->db_type = $db_type;
            $obj->enable = $oDB->isSupported() ? true : false;

            $get_supported_list[] = $obj;
        }
        $this->supported_list = $get_supported_list;
        return $this->supported_list;
    }

    /**
     * Return dbms supportable status
     * The value is set in the child class
     * @return boolean true: is supported, false: is not supported
     */
    function isSupported()
    {
        return false;
    }

    /**
     * Return connected status
     * @param string $type master or slave
     * @param int $indx key of server list
     * @return boolean true: connected, false: not connected
     */
    function isConnected($type = 'master', $indx = 0)
    {
        if ($type == 'master') {
            if (isset($this->master_db["is_connected"])) {
                return true;
            } else {
                return false;
            }
        } else {
            if (isset($this->slave_db[$indx]["is_connected"])) {
                return true;
            } else {
                return false;
            }
        }
    }

    /**
     * start recording log
     * @param string $query query string
     * @return void
     */
    function actStart($query)
    {
        $this->setError(0, 'success');
        $this->query = $query;

        $this->query_event = new QueryEvent($query);
        $this->query_event->startTiming();
        self::$dispatcher->dispatch(DBEvents::QUERY_STARTED, $this->query_event);
    }

    /**
     * finish recording log
     * @return void
     */
    function actFinish()
    {
        $this->query_event->stopTiming();

        $this->query_event->setConnection($this->connection);
        if (isset($this->query_id)) {
            $this->query_event->setQueryId($this->query_id);
        }
        if ($this->isError()) {
            $site_module_info = Context::get('site_module_info');
            $this->query_event->setModule($site_module_info->module);
            $this->query_event->setAct(Context::get('act'));
            $this->query_event->setResult("Failed");
            $this->query_event->setErrno($this->errno);
            $this->query_event->setErrstr($this->errstr);
        }else{
            $this->query_event->setResult("Success");
        }

        self::$dispatcher->dispatch(DBEvents::QUERY_ENDED, $this->query_event);
    }

    /**
     * set error
     * @param int $errno error code
     * @param string $errstr error message
     * @return void
     */
    function setError($errno = 0, $errstr = 'success')
    {
        $this->errno = $errno;
        $this->errstr = $errstr;
    }

    /**
     * Return error status
     * @return boolean true: error, false: no error
     */
    function isError()
    {
        return $this->errno === 0 ? false : true;
    }

    /**
     * Returns object of error info
     * @return object object of error
     */
    function getError()
    {
        $this->errstr = Context::convertEncodingStr($this->errstr);
        return new Object($this->errno, $this->errstr);
    }

    /**
     * Execute Query that result of the query xml file
     * This function finds xml file or cache file of $query_id, compiles it and then execute it
     * @param string $query_id query id (module.queryname)
     * @param array|object $args arguments for query
     * @param array $arg_columns column list. if you want get specific colums from executed result, add column list to $arg_columns
     * @return object result of query
     */
    function executeQuery($query_id, $args = null, $arg_columns = null)
    {
        static $cache_file = array();

        if (!$query_id) {
            return new Object(-1, 'msg_invalid_queryid');
        }
        if (!$this->db_type) {
            return;
        }

        $this->actDBClassStart();

        $this->query_id = $query_id;
        if(!isset($cache_file[$query_id]) || !file_exists($cache_file[$query_id])) {
            $id_args = explode('.', $query_id);
            if(count($id_args) == 2) {
                $target = 'modules';
                $module = $id_args[0];
                $id = $id_args[1];
            } elseif(count($id_args) == 3) {
                $target = $id_args[0];
                $typeList = array('addons'=>1, 'widgets'=>1);
                if(!isset($typeList[$target])){
                    $this->actDBClassFinish();
                    return;
                }
                $module = $id_args[1];
                $id = $id_args[2];
            }
            if(!$target || !$module || !$id){
                $this->actDBClassFinish();
                return new Object(-1, 'msg_invalid_queryid');
            }

            $xml_file = sprintf('%s%s/%s/queries/%s.xml', _KARYBU_PATH_, $target, $module, $id);
            if(!file_exists($xml_file)){
                $this->actDBClassFinish();
                return new Object(-1, 'msg_invalid_queryid');
            }

            // look for cache file
            $cache_file[$query_id] = $this->checkQueryCacheFile($query_id, $xml_file);
        }
        $result = $this->_executeQuery($cache_file[$query_id], $args, $query_id, $arg_columns);

        $this->actDBClassFinish();
        return $result;
    }


    /**
     * Look for query cache file
     * @param string $query_id query id for finding
     * @param string $xml_file original xml query file
     * @return string cache file
     */
    function checkQueryCacheFile($query_id, $xml_file)
    {
        // first try finding cache file
        $cache_file = sprintf(
            '%s%s%s.%s.%s.cache.php',
            _KARYBU_PATH_,
            $this->cache_file,
            $query_id,
            __KARYBU_VERSION__,
            $this->db_type
        );

        if (file_exists($cache_file)) {
            $cache_time = filemtime($cache_file);
        }
        else {
            $cache_time = -1;
        }

        // if there is no cache file or is not new, find original xml query file and parse it
        if ($cache_time < filemtime($xml_file) || $cache_time < filemtime(
            _KARYBU_PATH_ . 'classes/db/DB.class.php'
        ) || $cache_time < filemtime(_KARYBU_PATH_ . 'classes/xml/XmlQueryParser.150.class.php')
        ) {
            require_once(_KARYBU_PATH_.'classes/xml/XmlQueryParser.150.class.php');
            $oParser = new XmlQueryParser();
            $oParser->parse($query_id, $xml_file, $cache_file);
        }

        return $cache_file;
    }


    /**
     * Execute query and return the result
     * @param string $cache_file cache file of query
     * @param array|object $source_args arguments for query
     * @param string $query_id query id
     * @param array $arg_columns column list. if you want get specific colums from executed result, add column list to $arg_columns
     * @return object result of query
     */
    function _executeQuery($cache_file, $source_args, $query_id, $arg_columns)
    {
        global $lang;

        if (!file_exists($cache_file)) {
            return new Object(-1, 'msg_invalid_queryid');
        }

        if ($source_args) {
            $args = @clone($source_args);
        }

        $output = include($cache_file);

        if ((is_a($output, 'Object') || is_subclass_of($output, 'Object')) && !$output->toBool()) {
            return $output;
        }

        // execute appropriate query
        switch($output->getAction()) {
            case 'insert' :
            case 'insert-select' :
                $this->resetCountCache($output->tables);
                $output = $this->_executeInsertAct($output);
                break;
            case 'update' :
                $this->resetCountCache($output->tables);
                $output = $this->_executeUpdateAct($output);
                break;
            case 'delete' :
                $this->resetCountCache($output->tables);
                $output = $this->_executeDeleteAct($output);
                break;
            case 'select' :
                $arg_columns = is_array($arg_columns)?$arg_columns:array();
                $output->setColumnList($arg_columns);
                $connection = $this->_getConnection('slave');
                $output = $this->_executeSelectAct($output, $connection);
                break;
        }

        if ($this->isError()) {
            $output = $this->getError();
        }
        else {
            if (!is_a($output, 'Object') && !is_subclass_of($output, 'Object')) {
                $output = new Object();
            }
        }
        $output->add('_query', $this->query);
        $output->add('_elapsed_time', sprintf("%0.5f", $this->query_event->getElapsedTime()));

        return $output;
    }


    /**
     * Returns counter cache data
     * @param array|string $tables tables to get data
     * @param string $condition condition to get data
     * @return int count of cache data
     */
    function getCountCache($tables, $condition)
    {
        return false;
        if (!$tables) {
            return false;
        }
        if (!is_dir($this->count_cache_path)) {
            return FileHandler::makeDir($this->count_cache_path);
        }

        $condition = md5($condition);

        if (!is_array($tables)) {
            $tables_str = $tables;
        }
        else {
            $tables_str = implode('.', $tables);
        }

        $cache_path = sprintf('%s/%s%s', $this->count_cache_path, $this->prefix, $tables_str);
        if (!is_dir($cache_path)) {
            FileHandler::makeDir($cache_path);
        }

        $cache_filename = sprintf('%s/%s.%s', $cache_path, $tables_str, $condition);
        if (!file_exists($cache_filename)) {
            return false;
        }

        $cache_mtime = filemtime($cache_filename);

        if (!is_array($tables)) {
            $tables = array($tables);
        }
        foreach($tables as $alias => $table) {
            $table_filename = sprintf('%s/cache.%s%s', $this->count_cache_path, $this->prefix, $table) ;
            if (!file_exists($table_filename) || filemtime($table_filename) > $cache_mtime) {
                return false;
            }
        }

        $count = (int)FileHandler::readFile($cache_filename);
        return $count;
    }

    /**
     * Save counter cache data
     * @param array|string $tables tables to save data
     * @param string $condition condition to save data
     * @param int $count count of cache data to save
     * @return void
     */
    function putCountCache($tables, $condition, $count = 0)
    {
        return false;
        if (!$tables) {
            return false;
        }
        if (!is_dir($this->count_cache_path)) {
            return FileHandler::makeDir($this->count_cache_path);
        }

        $condition = md5($condition);

        if (!is_array($tables)) {
            $tables_str = $tables;
        }
        else {
            $tables_str = implode('.', $tables);
        }

        $cache_path = sprintf('%s/%s%s', $this->count_cache_path, $this->prefix, $tables_str);
        if (!is_dir($cache_path)) {
            FileHandler::makeDir($cache_path);
        }

        $cache_filename = sprintf('%s/%s.%s', $cache_path, $tables_str, $condition);

        FileHandler::writeFile($cache_filename, $count);
    }

    /**
     * Reset counter cache data
     * @param array|string $tables tables to reset cache data
     * @return boolean true: success, false: failed
     */
    function resetCountCache($tables)
    {
        return false;
        if (!$tables) {
            return false;
        }
        if (!is_dir($this->count_cache_path)) {
            return FileHandler::makeDir($this->count_cache_path);
        }

        if (!is_array($tables)) {
            $tables = array($tables);
        }
        foreach($tables as $alias => $table) {
            $filename = sprintf('%s/cache.%s%s', $this->count_cache_path, $this->prefix, $table);
            FileHandler::removeFile($filename);
            FileHandler::writeFile($filename, '');
        }

        return true;
    }

    /**
     * Returns supported database list
     * @return array list of supported database
     */
    function getSupportedDatabase()
    {
        $result = array();

        if (function_exists('mysql_connect')) {
            $result[] = 'MySQL';
        }
        if (function_exists('cubrid_connect')) {
            $result[] = 'Cubrid';
        }
        if (function_exists('ibase_connect')) {
            $result[] = 'FireBird';
        }
        if (function_exists('pg_connect')) {
            $result[] = 'Postgre';
        }
        if (function_exists('sqlite_open')) {
            $result[] = 'sqlite2';
        }
        if (function_exists('mssql_connect')) {
            $result[] = 'MSSQL';
        }
        if (function_exists('PDO')) {
            $result[] = 'sqlite3(PDO)';
        }

        return $result;
    }

    /**
     * Drop tables
     * @param string $table_name
     * @return void
     */
    function dropTable($table_name)
    {
        if (!$table_name) {
            return;
        }
        $query = sprintf("drop table %s%s", $this->prefix, $table_name);
        $this->_query($query);
    }

    /**
     * Return select query string
     * @param object $query
     * @param boolean $with_values
     * @return string
     */
    function getSelectSql($query, $with_values = true)
    {
        $select = $query->getSelectString($with_values);
        if ($select == '') {
            return new Object(-1, "Invalid query");
        }
        $select = 'SELECT ' .$select;

        $from = $query->getFromString($with_values);
        if ($from == '') {
            return new Object(-1, "Invalid query");
        }
        $from = ' FROM '.$from;

        $where = $query->getWhereString($with_values);
        if ($where != '') {
            $where = ' WHERE ' . $where;
        }

        $tableObjects = $query->getTables();
        $index_hint_list = '';
        foreach($tableObjects as $tableObject){
            if (is_a($tableObject, 'CubridTableWithHint')) {
                $index_hint_list .= $tableObject->getIndexHintString() . ', ';
            }
        }
        $index_hint_list = substr($index_hint_list, 0, -2);
        if ($index_hint_list != '') {
            $index_hint_list = 'USING INDEX ' . $index_hint_list;
        }

        $groupBy = $query->getGroupByString();
        if ($groupBy != '') {
            $groupBy = ' GROUP BY ' . $groupBy;
        }

        $orderBy = $query->getOrderByString();
        if ($orderBy != '') {
            $orderBy = ' ORDER BY ' . $orderBy;
        }

        $limit = $query->getLimitString();
        if ($limit != '') {
            $limit = ' LIMIT ' . $limit;
        }

        return $select . ' ' . $from . ' ' . $where . ' ' . $index_hint_list . ' ' . $groupBy . ' ' . $orderBy . ' ' . $limit;
    }

    /**
     * Given a SELECT statement that uses click count
     * returns the corresponding update sql string
     * for databases that don't have click count support built in
     * (aka all besides CUBRID)
     *
     * Function does not check if click count columns exist!
     * You must call $query->usesClickCount() before using this function
     *
     * @param $queryObject
     */
    function getClickCountQuery($queryObject)
    {
        $new_update_columns = array();
        $click_count_columns = $queryObject->getClickCountColumns();
        foreach ($click_count_columns as $click_count_column) {
            $click_count_column_name = $click_count_column->column_name;

            $increase_by_1 = new Argument($click_count_column_name, null);
            $increase_by_1->setColumnOperation('+');
            $increase_by_1->ensureDefaultValue(1);

            $update_expression = new UpdateExpression($click_count_column_name, $increase_by_1);
            $new_update_columns[] = $update_expression;
        }
        $queryObject->columns = $new_update_columns;
        return $queryObject;
    }

    /**
     * Return delete query string
     * @param object $query
     * @param boolean $with_values
     * @param boolean $with_priority
     * @return string
     */
    function getDeleteSql($query, $with_values = true, $with_priority = false)
    {
        $sql = 'DELETE ';

        $sql .= $with_priority?$query->getPriority():'';
        $tables = $query->getTables();

        $sql .= $tables[0]->getAlias();

        $from = $query->getFromString($with_values);
        if ($from == '') {
            return new Object(-1, "Invalid query");
        }
        $sql .= ' FROM '.$from;

        $where = $query->getWhereString($with_values);
        if ($where != '') {
            $sql .= ' WHERE ' . $where;
        }

        return $sql;
    }

    /**
     * Return update query string
     * @param object $query
     * @param boolean $with_values
     * @param boolean $with_priority
     * @return string
     */
    function getUpdateSql($query, $with_values = true, $with_priority = false)
    {
        $columnsList = $query->getUpdateString($with_values);
        if ($columnsList == '') {
            return new Object(-1, "Invalid query");
        }

        $tables = $query->getFromString($with_values);
        if ($tables == '') {
            return new Object(-1, "Invalid query");
        }

        $where = $query->getWhereString($with_values);
        if ($where != '') {
            $where = ' WHERE ' . $where;
        }

        $priority = $with_priority?$query->getPriority():'';

        return "UPDATE $priority $tables SET $columnsList ".$where;
    }

    /**
     * Return insert query string
     * @param object $query
     * @param boolean $with_values
     * @param boolean $with_priority
     * @return string
     */
    function getInsertSql($query, $with_values = true, $with_priority = false)
    {
        $tableName = $query->getFirstTableName();
        $values = $query->getInsertString($with_values);
        $priority = $with_priority?$query->getPriority():'';

        return "INSERT $priority INTO $tableName \n $values";
    }

    /**
     * Return index from slave server list
     * @return int
     */
    function _getSlaveConnectionStringIndex()
    {
        $max = count($this->slave_db);
        $indx = rand(0, $max - 1);
        return $indx;
    }

    /**
     * Return connection resource
     * @param string $type use 'master' or 'slave'. default value is 'master'
     * @param int $indx if indx value is NULL, return rand number in slave server list
     * @return resource
     */
    function _getConnection($type = 'master', $indx = null)
    {
        if($type == 'master'){
            if (!$this->master_db['is_connected']) {
                $this->_connect($type);
            }
            $this->connection = 'Master ' . $this->master_db['db_hostname'];
            return $this->master_db["resource"];
        }

        if ($indx === null) {
            $indx = $this->_getSlaveConnectionStringIndex($type);
        }

        if (!$this->slave_db[$indx]['is_connected']) {
            $this->_connect($type, $indx);
        }

        $this->connection = 'Slave ' . $this->slave_db[$indx]['db_hostname'];
        return $this->slave_db[$indx]["resource"];
    }

    /**
     * check db information exists
     * @return boolean
     */
    function _dbInfoExists()
    {
        if (!$this->master_db) {
            return false;
        }
        if (count($this->slave_db) === 0) {
            return false;
        }
        return true;
    }

    /**
     * DB disconnection
     * this method is protected
     * @param resource $connection
     * @return void
     */
    function _close($connection)
    {

    }

    /**
     * DB disconnection
     * @param string $type 'master' or 'slave'
     * @param int $indx number in slave dbms server list
     * @return void
     */
    function close($type = 'master', $indx = 0)
    {
        if (!$this->isConnected($type, $indx)) {
            return;
        }

        if ($type == 'master') {
            $connection = &$this->master_db;
        } else {
            $connection = &$this->slave_db[$indx];
        }

        $this->_close($connection["resource"]);

        $connection["is_connected"] = false;
    }

    /**
     * DB transaction start
     * this method is protected
     * @return boolean
     */
    function _begin()
    {
        return true;
    }

    /**
     * DB transaction start
     * @return void
     */
    function begin()
    {
        if (!$this->isConnected() || $this->transaction_started) {
            return;
        }

        if ($this->_begin()) {
            $this->transaction_started = true;
        }
    }

    /**
     * DB transaction rollback
     * this method is protected
     * @return boolean
     */
    function _rollback()
    {
        return true;
    }

    /**
     * DB transaction rollback
     * @return void
     */
    function rollback()
    {
        if (!$this->isConnected() || !$this->transaction_started) {
            return;
        }
        if ($this->_rollback()) {
            $this->transaction_started = false;
        }
    }

    /**
     * DB transaction commit
     * this method is protected
     * @return boolean
     */
    function _commit()
    {
        return true;
    }

    /**
     * DB transaction commit
     * @param boolean $force regardless transaction start status or connect status, forced to commit
     * @return void
     */
    function commit($force = false)
    {
        if (!$force && (!$this->isConnected() || !$this->transaction_started)) {
            return;
        }
        if ($this->_commit()) {
            $this->transaction_started = false;
        }
    }

    /**
     * Execute the query
     * this method is protected
     * @param string $query
     * @param resource $connection
     * @return void
     */
    function __query($query, $connection)
    {

    }

    /**
     * Execute the query
     * this method is protected
     * @param string $query
     * @param resource $connection
     * @return resource
     */
    function _query($query, $connection = null)
    {
        if ($connection == null) {
            $connection = $this->_getConnection('master');
        }

        // Start of query execution
        $this->actStart($query);

        // Run the query statement
        $result = $this->__query($query, $connection);

        // Notify to complete a query execution
        $this->actFinish();
        // Return result
        return $result;
    }

    /**
     * DB info settings
     * this method is protected
     * @return void
     */
    function _setDBInfo()
    {
        $db_info = Context::getDBInfo();
        $this->master_db = isset($db_info->master_db) ? $db_info->master_db : null;
        $keys = array('db_hostname','db_port', 'db_userid', 'db_password', 'db_database');
        foreach ($keys as $key){
            if (!isset($db_info->master_db[$key])){
                $db_info->master_db[$key] = null;
            }
            if (!isset($db_info->slave_db[0][$key])){
                $db_info->slave_db[0][$key] = null;
            }
        }
        if($db_info->master_db["db_hostname"] == $db_info->slave_db[0]["db_hostname"]
            && $db_info->master_db["db_port"] == $db_info->slave_db[0]["db_port"]
            && $db_info->master_db["db_userid"] == $db_info->slave_db[0]["db_userid"]
            && $db_info->master_db["db_password"] == $db_info->slave_db[0]["db_password"]
            && $db_info->master_db["db_database"] == $db_info->slave_db[0]["db_database"]
        ) {
            $this->slave_db[0] = &$this->master_db;
        } else {
            $this->slave_db = $db_info->slave_db;
        }
        $this->prefix = isset($db_info->master_db["db_table_prefix"]) ? $db_info->master_db["db_table_prefix"] : null;
        if (isset($db_info->use_prepared_statements)) {
            $this->use_prepared_statements = $db_info->use_prepared_statements;
        }
    }

    /**
     * DB Connect
     * this method is protected
     * @param array $connection
     * @return void
     */
    function __connect($connection)
    {

    }

    /**
     * If have a task after connection, add a taks in this method
     * this method is protected
     * @param resource $connection
     * @return void
     */
    function _afterConnect($connection)
    {

    }

    /**
     * DB Connect
     * this method is protected
     * @param string $type 'master' or 'slave'
     * @param int $indx number in slave dbms server list
     * @return boolean - if connected or not
     */
    function _connect($type = 'master', $indx = 0) {
        if ($this->isConnected($type, $indx))
            return true;

        // Ignore if no DB information exists
        if (!$this->_dbInfoExists() || !$this->isSupported())
            return true;

        if ($type == 'master')
            $connection = &$this->master_db;
        else
            $connection = &$this->slave_db[$indx];

        $result = $this->__connect($connection);
        if($result === null || $result === false) {
            $connection["is_connected"] = false;
            return false;
        }

        // Check connections
        $connection["resource"] = $result;
        $connection["is_connected"] = true;

        // Save connection info for db logs
        $this->connection = ucfirst($type) . ' ' . $connection["db_hostname"];

        $this->_afterConnect($result);
        return true;
    }

    /**
     * Start recording DBClass log
     * @return void
     */
    function actDBClassStart()
    {
        $this->setError(0, 'success');
        self::$dispatcher->dispatch(DBEvents::EXECUTE_QUERY_STARTED, new QueryEvent($this->query));
    }

    /**
     * Finish recording DBClass log
     * @return void
     */
    function actDBClassFinish()
    {
        self::$dispatcher->dispatch(DBEvents::EXECUTE_QUERY_ENDED, new QueryEvent($this->query));
        if (!$this->query) {
            return;
        }
    }

    /**
     * Returns a database specific parser instance
     * used for escaping expressions and table/column identifiers
     *
     * Requires an implementation of the DB class (won't work if database is not set)
     * this method is singleton
     *
     * @param boolean $force force load DBParser instance
     * @return DBParser
     */
    function &getParser($force = false)
    {
        static $dbParser = null;
        if(!$dbParser || $force) {
            $oDB = DB::getInstance();
            $dbParser = $oDB->getParser();
        }

        return $dbParser;
    }

    static function getDbParser($force = false){
        static $dbParser = null;
        if(!$dbParser || $force) {
            $oDB = DB::getInstance();
            $dbParser = $oDB->getParser();
        }
        return $dbParser;
    }

    /**
     * Sets the Logger which will be passed to specific DB instances.
     *
     * @param LoggerInterface $logger - logger
     */
    public static function setLogger(LoggerInterface $logger = null)
    {
        self::$logger = $logger;
    }

    /**
     * Initialises event dispatcher for the database
     *
     * @param EventDispatcherInterface $dispatcher
     */
    public static function setDispatcher(EventDispatcherInterface $dispatcher)
    {
        self::$dispatcher = $dispatcher;
    }
}
