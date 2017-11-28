<?php

namespace app\behind\controller;
use app\common\controller\ErrorCode;
use think\Db;
use think\Request;

class Sign extends Base
{
	/**
	* 登录
	*/
	public function login(Request $request)
	{
		if($request->isAjax()) {
		    $code = 0;
		    //短信验证码登录
            if($this->global_setting['sms_verify']) {
                $phone = $request->post('phone',0,'int');
                $code = $request->post('code',0,'int');
                if(!preg_match('/1[3|5|7|8][\d]{9}/',$phone)) {
                    $code = 9001;
                    goto login_over;
                }
                if(!preg_match('/[\d]{4,8}/',$code)) {
                    $code = 9002;
                    goto login_over;
                }
                if($code = $this->find_admin_by_phone($phone) || $code = $this->checkSMS($phone,$code,$this->global_setting['sms_expire'])) {
                    goto login_over;
                }
            }
            //账号密码验证码登录
            else {
                $admin_user = $request->post('admin_user','','htmlspecialchars');
                $admin_pass = $request->post('admin_pass','','htmlspecialchars');
                $verify = $request->post('verify','','htmlspecialchars');
                if($code = $this->checkVerify()) {
                    goto login_over;
                }
                if(($code = $this->check_pass($admin_user,$admin_pass)) == 9005) {
                    goto login_over;
                }
                else {
                    $code = ErrorCode::admin_status[$code];
                    goto login_over;
                }
            }
            login_over:
            return json(['code'=>$code,ErrorCode::error[$code]]);
        }
        elseif($request->isGet()) {
		    return $this->fetch();
        }
        else {
		    exit('非法请求');
        }
	}

    /**
     * @param $phone
     * @return int errorcode
     */
	protected function find_admin_by_phone($phone)
    {
        return Db::name('admin_user')->where(['admin_phone'=>$phone])->find()?0:9004;
    }

    /**
     * @param $admin_user
     * @param $admin_pass
     * @return int|mixed
     */
    protected function check_pass($admin_user,$admin_pass)
    {
        $condition['admin_user'] = $admin_user;
        $condition['admin_pass'] = md5(md5($admin_pass));
        $status = Db::name('admin_user')->where($condition)->value('status');
        return $status?$status:9005;
    }

}
