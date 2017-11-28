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

	}

    /**
     * 检查短信
     * @param $phone
     * @param $code
     * @param $expire
     */
	protected function checkSMS($phone,$code,$expire)
	{
        $condition['expire_time'] = ['elt', time() + $expire];
        $condition['phone'] = $phone;
        $condition['code'] = $code;
        $condition['status'] = 0;
        return Db::name('sms_code')->where($condition)->update(['status'=>1])?0:9003;
	}

}
