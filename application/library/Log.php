<?php

/**
 * Created by PhpStorm.
 * User: CPR007
 * Date: 2017/10/21
 * Time: 9:57
 */
class Log {
    private $userId = '';
    private $userName = '';
    protected $tableName = '';
    protected $type = 0;
    private static $model;
    public $module;
    public $controller;
    public $action;

    public function __construct($tableName = '', $type,$moduleName,$controllerName,$actionName) {
        $this->userId = Yaf_Session::getInstance()->get('user_id') ?: -1;
        $this->userName = Yaf_Session::getInstance()->get('username') ?: 'system';
        $this->tableName = $tableName ? $tableName . '_' . date("Ym", time()) : "common_log_" . date("Ym", time());//当月表名
        $this->module = $moduleName;
        $this->controller = $controllerName;
        $this->action = $actionName;
        $this->type = $type;
        $this->db = Yaf_Registry::get('db');
        self::$model = $this->db->selectDB('test');
        $this->_createTable();
    }


    /**
     * 写入日志
     * @param  String $message 操作信息
     * @return void
     */
    public function writeLog($message) {
        $sql = "insert into $this->tableName (`user_name`,`user_id`,`module`,`controller`,`action`,`type`,`msg`) values('{$this->userName}',{$this->userId},'{$this->module}','$this->controller','$this->action',$this->type,'$message')";
        self::$model->query($sql);
    }

    /**
     * 创建日志表
     */
    protected function _createTable() {
        $createSql = "CREATE TABLE IF NOT EXISTS `{$this->tableName}` (
                          `id` bigint(20) NOT NULL AUTO_INCREMENT,
                          `user_name` varchar(32) NOT NULL,
                          `user_id` bigint(20) NOT NULL,
                          `module` varchar(255) NOT NULL,
                          `controller` varchar(255) NOT NULL,
                          `action` varchar(255) NOT NULL,
                          `type`   tinyint(1) NOT NULL DEFAULT 0 COMMENT '日志类型',
                          `msg` text NOT NULL,
                          `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
                          PRIMARY KEY (`id`)
                        ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8";
        self::$model->query($createSql);
    }
}