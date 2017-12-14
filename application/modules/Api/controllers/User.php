<?php

/**
 * Created by PhpStorm.
 * User: CPR007
 * Date: 2017/10/30
 * Time: 10:10
 */
class UserController extends BaseController {
    public $userModel;
    private $params;

    public function init() {
        $this->userModel = new UserModel();
        $this->params = json_decode(file_get_contents("php://input"), true);
    }

    public function getUserInfoAction() {
        echo 'this is user api';
    }

    public function getUserListAction() {
        $params = $this->params;
        $page = $params['page']??1;
        $pageNum = 10;
        $data = $this->userModel->getList2($page, $pageNum, '', 3);
        $this->_successReturn('获取用户列表成功', $data);
    }
}