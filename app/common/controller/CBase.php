<?php
namespace app\common\controller;
use \think\Controller;
use \think\Db;
use \app\common\exception\ErrorException;

class CBase extends Controller
{
	// 错误类型 1-中止程序，2-选择是否继续
	private $error_type; 

	private $error_msg;

	public function __construct()
	{
		parent::__construct();
		try{
			Db::Query("show databases");
			Db::Query('show tables');
		}
		catch(\PDOException $e) {
			$this->error_type = 1;
			$this->error_msg = '当前数据库未配置';
			$this->redirect('conf');
		}
		catch(\think\Exception $e) {
			$this->error_type = 1;
			$this->error_msg = 'database不存在';
			$this->redirect('conf');
		}
		
		$prefix = config('database.prefix');
		if ($prefix === '') {
			$this->error_type = 2;
			$this->error_msg = '当前数据表前缀为空，是否继续？';
			$this->redirect('conf');
		}


	}
	/**
	* 验证码
	*/
	public function verify()
	{
		
	}

	/**
	* 检查验证码
	*/
	public function checkVerify()
	{

	}

	/**
	* 发送短信
	*/
	public function sendSMS()
	{
		//发送短信
	}

	/**
	* 检查短信
	*/
	public function checkSMS()
	{

	}

	final public function conf()
	{
		return $this->fetch();
	} 


}