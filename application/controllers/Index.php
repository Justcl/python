<?php
class IndexController extends BaseController {
    public $userModel;
    public $session;
    public function init() {
        parent::init();
        //如果是Ajax请求, 则关闭HTML输出
        $this->userModel = new UserModel();
    }

    public function indexAction() {
        //Yaf_Session::getInstance()->set('name','haha1113'); //session to redis 测试
        $this->getView()->assign('content', 333);
        $this->getView()->assign('name', 3313);
        $this->render('/index');
    }

    public function userListAction() {
        //echo Yaf_Session::getInstance()->get('name');die;  //session to redis 测试
//        $page=$this->getRequest()->getQuery('page');
//        $pageNum=10;
//        $data = $this->userModel->getList2($page,$pageNum,'',3);

        $this->_display("/index/userList", [], true);

    }

}
