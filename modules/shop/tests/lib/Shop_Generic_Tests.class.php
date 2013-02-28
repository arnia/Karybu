<?php
require_once "TestAgainstDatabase.php";
require_once "Shop_DbUnit_ArrayDataSet.class.php";

abstract class Shop_Generic_Tests_DatabaseTestCase extends TestAgainstDatabase
{
    // only instantiate pdo once for test clean-up/fixture load
    static private $pdo = null;

    // only instantiate PHPUnit_Extensions_Database_DB_IDatabaseConnection once per test
    private $conn = null;

    final public function getConnection()
    {
        if ($this->conn === null) {
            if (self::$pdo == null) {
                self::$pdo = new PDO($GLOBALS['DB_DSN'], $GLOBALS['DB_USER'], $GLOBALS['DB_PASSWD']);
//                self::$pdo = new PDO("mysql:dbname=xe15;host=localhost", "root", "eenie");
            }
            $this->conn = $this->createDefaultDBConnection(self::$pdo, $GLOBALS['DB_DBNAME']);
        }
        return $this->conn;
    }

//    public function getDataSet()
//    {
//        /*
//        //mysqldump --xml -t -u [username] --password=[password] [database] > dumped_from_mysql.xml
//        return $this->createMySQLXMLDataSet('dumped_from_mysql.xml');
//        */
//    }
}