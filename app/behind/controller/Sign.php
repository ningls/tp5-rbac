<?php

namespace app\behind\controller;
use app\common\logic\ErrorCode;
use think\Db;
use think\Request;
use think\Session;

class Sign extends Base
{
    protected $admin_user;
	/**
	* 登录
	*/
	public function login(Request $request)
	{
		if($request->isAjax()) {
		    //短信验证码登录
            if($this->global_setting['sms_verify']) {
                $admin_user = $request->post('admin_user','','htmlspecialchars');
                $phone = $this->find_admin_by_phone($admin_user);
                if(!$phone) {
                    $code = 9000;
                    goto login_over;
                }
                $code = $request->post('code',0,'int');

                if(!preg_match('/^1[3|5|7|8][\d]{9}$/',$phone)) {
                    $code = 9001;
                    goto login_over;
                }
                if(!preg_match('/[\d]{4,8}/',$code)) {
                    $code = 9002;
                    goto login_over;
                }
                if($code = $this->checkSMS($phone,$code,$this->global_setting['sms_expire'])){
                    goto login_over;
                }
            }
            //账号密码验证码登录
            else {
                $admin_user = $request->post('admin_user','','htmlspecialchars');
                $admin_pass = $request->post('admin_pass','','htmlspecialchars');
                $verify = $request->post('verify','','htmlspecialchars');
                if($code = $this->checkVerify($verify)) {
                    goto login_over;
                }
                if($code = $this->check_pass($admin_user,$admin_pass)) {
                    goto login_over;
                }
            }
            $status = $this->admin_user['status'];
            if($status != false) {
                $code = ErrorCode::admin_status[$status];
                goto login_over;
            }
            session('user',$this->admin_user);
            login_over:
            return json(['code'=>$code,ErrorCode::error[$code]]);
        }
        elseif($request->isGet()) {
            $this->assign('sms',$this->global_setting['sms_verify']);
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
	protected function find_admin_by_phone(int $phone):int
    {
        $this->admin_user =  Db::name('admin_user')->where(['admin_phone'=>$phone])->find();
        return $this->admin_user?0:9004;
    }

    /**
     * @param $admin_user
     * @param $admin_pass
     * @return int
     */
    protected function check_pass(string $admin_user,string $admin_pass):int
    {
        $condition['admin_user'] = $admin_user;
        $condition['admin_pass'] = md5(md5($admin_pass));
        $this->admin_user = Db::name('admin_user')->where($condition)->find();
        return $this->admin_user?0:9005;
    }

    protected function phone_by_username($admin_user) {
        return Db::name('admin_user')->where(['admin_user'=>$admin_user])->value('admin_phone');
    }

    /**
     * 登出
     */
    public function logout()
    {
        Session::destroy();
        $this->redirect('login');
    }

    /**
     * 重写析构方法
     */
    public function __destruct()
    {
        return false;
    }
}
