<?php
namespace app\common\controller;
use \think\Controller;
use \think\Db;
use \app\common\exception\ErrorException;

class CBase extends Controller
{
		
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