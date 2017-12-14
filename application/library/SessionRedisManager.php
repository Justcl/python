<?php

/**
 * Created by PhpStorm.
 * User: CPR007
 * Date: 2017/10/23
 * Time: 15:46
 */
class SessionRedisManager {
    protected $redisHost;
    protected $redisPort;
    private static $redis;
    protected $expireTime;
    private static $instance;

    private function __construct($config) {
        $this->redisHost = $config['host'] ?: '127.0.0.1';
        $this->redisPort = $config['port'] ?: 6379;
        $this->expireTime = $config['expireTime'] ?: ini_get('session.gc_maxlifetime');
        $this->_checkHandler();
        self::$redis = new Redis();
        self::$redis->connect($this->redisHost, $this->redisPort);
        session_set_save_handler(
            array($this, 'open'),
            array($this, 'close'),
            array($this, 'read'),
            array($this, 'write'),
            array($this, 'destory'),
            array($this, 'gc')
        );
        //Yaf_Session::getInstance()->start();
    }

    public static function getInstance($config){
        if(!self::$instance){
            self::$instance=new self($config);
        }
        return self::$instance;
    }

    private function _checkHandler() {
        ini_get('session.save_handler') != 'user' ? ini_set('session.save_handler', 'user') : true;
    }

    public function open() {
        return true;
    }

    public function close() {
        self::$redis->close();
    }

    public function read($key) {
        return self::$redis->get($key);
    }

    public function write($key, $value) {
        return self::$redis->setex($key, $this->expireTime, $value);
    }

    public function destory($key) {
        return self::$redis->delete($key) ? true : false;
    }

    public function gc($lifetime) {
        return true;
    }

}


