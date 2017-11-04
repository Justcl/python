<?php

class Bootstrap extends Yaf_Bootstrap_Abstract {
    public $arrConfig;

    public function _initConfig() {
        //把配置保存起来
        $this->arrConfig = Yaf_Application::app()->getConfig();
        Yaf_Registry::set('config', $this->arrConfig);
    }

    //加载公共方法
    public function _initCommonFun() {
        Yaf_Loader::import('function.php');
    }

    //debug是否开启
    public function _initError() {
        if ($this->arrConfig->debug){
            define('DEBUG_MODE', false);
            ini_set('display_errors', 'On');
        }else{
            define('DEBUG_MODE', false);
            ini_set('display_errors', 'Off');
        }
    }

    //载入数据库
    public function _initDatabase() {
        $db_config=$this->arrConfig->db->toArray();
        Yaf_Registry::set('db', new DB($db_config));
    }

    //载入缓存类redis
    public function _initCache() {
        $redisPort = $this->arrConfig->redis->port;
        $redisHost = $this->arrConfig->redis->host;
        Yaf_Registry::set('redis', new SafeRedis($redisHost, $redisPort));
    }

    //开启session
    public function _initSession() {
        if(!$this->arrConfig->session){
            Yaf_Session::getInstance()->start();
        }else{
            $sesConfig=$this->arrConfig->session->save->toArray();
            switch ($sesConfig['handle']){
                case 'redis':
                    SessionRedisManager::getInstance($sesConfig);
                    break;
                case 'memcache':

            }
        }
    }

    public function _initPlugin(Yaf_Dispatcher $dispatcher) {
        //注册一个插件
        $objLayoutPlugin = new LayoutPlugin();
        $dispatcher->registerPlugin($objLayoutPlugin);
    }

    public function _initRoute(Yaf_Dispatcher $dispatcher) {
        //在这里注册自己的路由协议,默认使用简单路由
        /* index.php?m=admin&c=index&a=index
        $router = Yaf_Dispatcher::getInstance()->getRouter();
        $route = new Yaf_Route_Simple("m", "c", "a");
        $router->addRoute("name", $route);
        */
        //
        $router = Yaf_Dispatcher::getInstance()->getRouter();
        $router->addConfig($this->arrConfig->routes);
    }

    public function _initView(Yaf_Dispatcher $dispatcher) {
        //在这里注册自己的view控制器，例如smarty,firekylin
    }
}
