<?php
namespace app\behind\controller;
use \app\common\controller\CBase;
use \think\Db;

class Base extends CBase
{
	protected $error_msg;
	protected $admin_data = []; 
	/**
	* 检测是否已经含有表以便生成admin
	*/
	public function _initialize()
	{
	    parent::_initialize();
		$action = request()->action();
		if(in_array($action,['create_auth', 'auth_exists', 'create_admin', 'error_page'])) return false;
		try{
			Db::Query("show databases");
		}
		catch(\PDOException $e){
			exit('数据库配置错误或或数据不存在！');
		}
		catch(\think\Exception $e) {
			exit('未配置数据库');
		}
		if(!$this->auth_exists()) {
			$this->redirect('base/create_admin');
		}

	}

	/**
	* 生成auth权限表
	*/
	final public function create_auth()
	{
		if($this->auth_exists()) {
			$this->redirect('index/index');
		}
	}

	/**
	* 判断是否已存在权限表
	*/
	final protected function auth_exists()
	{
		try {
			Db::name('admin_user')->select();
		} catch (\think\Exception $e) {
			return false;
		}
		return true;

	}


	//创建admin用户
	final public function create_admin()
	{
		if($this->auth_exists()) {
			$this->redirect('index/index');
		}
		return $this->fetch();
	}

	//error_page
	public function error_page()
	{
		$this->assign('error_msg', $this->error_msg);
		return $this->fetch();
	}

}