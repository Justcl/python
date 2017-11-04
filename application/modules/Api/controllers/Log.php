<?php
/**
 * Created by PhpStorm.
 * User: CPR007
 * Date: 2017/10/30
 * Time: 14:18
 */
class LogController extends BaseController{

    public function getLogInfoAction(){
        echo 'this log id is：'.$this->getParam('id').' title is： '.$this->getParam('title');
    }

}