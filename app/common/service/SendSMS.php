<?php
namespace app\common\service;
abstract class SendSMS
{
	//app_id
	protected $app_id;
	//app_sercet
	protected $app_secret;
	//短信签名
	protected $sign;
	//短信模板
	protected $SMS_CODE;
	//发送手机号
	protected $phone;

	public function __construct($phone, $sign = '', $SMS_CODE = '')
	{
		$this->app_id = config('sms_app_id');
		$this->app_secret = config('sms_app_secret');
		$this->phone = $phone;
		if($sign !== '') {
			$this->sign = $sign;
		}
		if($SMS_CODE !== '') {
			$this->SMS_CODE = $SMS_CODE;
		}
	}

	public function send($phone, $code)
	{

	}

	public function multySend()
	{

	}
}