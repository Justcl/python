<?php
/**
 * Created by PhpStorm.
 * User: CPR007
 * Date: 2017/10/27
 * Time: 11:12
 */
class UserModel{
    public $db;
    private $userTable='user';
    public function __construct() {
        $this->db=Yaf_Registry::get('db');//获取db实例
    }

    public function getList(){
        $sql="select * from ".$this->userTable." limit 10";
        $data=$this->db->selectDB('test')->get_all($sql);
        return $data;
    }

    public function getList2($page,$pageNum,$url,$pageType){
        $totalSql="select count(1) as num from ".$this->userTable.' limit 1';
        $totalInfo=$this->db->selectDB('test2')->get_all($totalSql);

        $total=$totalInfo[0]['num'];
        $ind=($page-1)*$pageNum;
        $sql="select * from ".$this->userTable ." limit ".$ind.",".$pageNum;
        $data['list']=$this->db->selectDB('test2')->get_all($sql);
        $page=new Page($page,$total,$pageNum,'','');
        $data['page']=$page->getHtml(1);
        return $data;
    }
}