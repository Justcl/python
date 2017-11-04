<?php

/**
 * Created by PhpStorm.
 * User: CPR007
 * Date: 2017/10/27
 * Time: 14:33
 */
class BaseController extends Yaf_Controller_Abstract {
    public $layoutDir;
    public $layoutFile;
    public $configList;
    public $viewDir;

    public function init() {
        $this->configList = Yaf_Registry::get('config');
        $this->viewDir = $this->configList->application->view->directory;
        $this->layoutDir = $this->configList->application->layout->directory;
        $this->layoutFile = $this->configList->application->layout->file;
    }

    /**
     * 调用模板文件
     * @param $view
     * @param array $params
     * @param bool $layout true时引入layout文件
     */
    public function _display($view, $params = [], $layout = false) {
        $html = '';
        $view = $this->viewDir . $view . '.html';
        $params = array_merge($params, array('actionName' => $this->getActionName(), 'controllerName' => $this->getControllerName()));
        $params['C'] = $this;
        if ($layout == true) {
            $html .= $this->getView()->render($this->layoutDir . $this->layoutFile, $params);
        }
        $html .= $this->getView()->render($view, array_merge($params, array('content' => $html)));
        echo $html;exit;
    }
    //获取路由参数
    public function getParam($name, $defaultValue=null) {
        $value = (null === $defaultValue) ? $this->getRequest()->getParam($name) : $this->getRequest()->getParam($name, $defaultValue);
        return !empty($value) ? addslashes($value) : $value;
    }

    //获取post参数
    public function getPost($name, $defaultValue=null) {
        $value = (null === $defaultValue) ? $this->getRequest()->getPost($name) : $this->getRequest()->getPost($name, $defaultValue);
        return !empty($value) ? addslashes($value) : $value;
    }

    //获取get参数 不包括路由参数
    public function getQuery($name, $defaultValue=null) {
        $value = (null === $defaultValue) ? $this->getRequest()->getQuery($name) : $this->getRequest()->getQuery($name, $defaultValue);
        return !empty($value) ? addslashes($value) : $value;
    }

    /**
     * 获取模块名
     * @return mixed
     */
    public function getModuleName() {
        return $this->getRequest()->getModuleName();
    }

    /**
     * 获取控制器名称
     * @return mixed
     */
    public function getControllerName() {
        return $this->getRequest()->getControllerName();
    }

    /**
     * 获取方法名
     * @return mixed
     */
    public function getActionName() {
        return $this->getRequest()->getActionName();
    }



    /**
     * @param $msg
     * @param int $type 日志类型 0 正常（包括回调 调用 记录） 1 错误
     */
    public function writeLog($msg,$type=0){
         $log=new Log(APP_NAME.'_log',$type,$this->getModuleName(),$this->getControllerName(),$this->getActionName());
         $log->writeLog($msg);
    }
}