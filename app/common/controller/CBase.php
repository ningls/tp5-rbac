<?php
namespace app\common\controller;
use \think\Controller;
use \think\Db;

class CBase extends Controller
{
    /**
     * @return bool
     */
    public function _initialize()
    {
       if(PHP_VERSION < 7) exit('该框架适用于php7以上版本');

       return true;
    }

    /**
	* 验证码
	*/
	public function verify()
	{
		
	}

	/**
	* 检查验证码
	* @param $verify
	* @return int errorcode
	*/
	public function checkVerify(string $verify):int
	{
		return 0;
	}

	/**
	* 发送短信
	* @param int $phone
	* @param string $sign
	* @param string $templete_id
	* @return int mixed
	*/
	public function sendSMS(int $phone,string $sign = '',string $templete_id = '')
	{
		return 0;
	}

    /**
     * 检查短信
     * @param $phone
     * @param $code
     * @param $expire
     * @return int
     */
	protected function checkSMS(int $phone,integer $code,int $expire):int
	{
        $condition['expire_time'] = ['elt', time() + $expire];
        $condition['phone'] = $phone;
        $condition['code'] = $code;
        $condition['status'] = 0;
        return Db::name('sms_code')->where($condition)->update(['status'=>1])?0:9003;
	}

}
