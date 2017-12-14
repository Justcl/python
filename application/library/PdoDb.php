<?php

/**
 * Created by PhpStorm.
 * User: CPR007
 * Date: 2017/11/4
 * Time: 9:56
 */
class PdoDb {
    static private $writeDbInstance = null;
    static private $readDbInstance = null;
    private $pdo = null;
    private $host;
    private $port;
    private $user;
    private $password;
    private $dbName;
    private $charset;
    private $pconnect = 0;

    private function __construct($dbConfig) {


    }

    public static function getWriteDb($config) {
        if (self::$writeDbInstance == null) {
            self::$writeDbInstance = new self($config);
        }
        return self::$writeDbInstance;
    }

    public static function getReadDb($config) {
        if (self::$readDbInstance == null) {
            self::$readDbInstance = new self($config);
        }
        return self::$readDbInstance;
    }

    //禁止clone
    private function __clone() {

    }

    public function selectDB($dbName) {
        $this->dbName = $dbName;
        $this->_connect();
        return $this;
    }

    private function _connect() {
        try {
            $this->pdo = new PDO(
                'mysql:host=' . $this->host . ';dbname=' . $this->dbName . ';port=' . $this->port, $this->user, $this->password,
                [
                    PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES ' . $this->charset,
                    PDO::ATTR_PERSISTENT => (bool)$this->pconnect,
                ]
            );
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            echo $e->getMessage();
            return false;
        }
    }

    public function _close(){
        $this->pdo=null;
    }

    public function beginTransaction(){
        $this->pdo->beginTransaction();
    }

    public function commit(){
        $this->pdo->commit();
    }

    public function rollBack(){
        $this->pdo->rollBack();
    }

}