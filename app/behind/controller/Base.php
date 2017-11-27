<?php
namespace app\behind\controller;
use \app\common\controller\CBase;
use \think\Request;
use \think\Db;

class Base extends CBase
{
	protected $error_msg;

	private $_prefix;
	/**
	* 检测是否已经含有表以便生成admin
	*/
	public function _initialize()
	{
	    parent::_initialize();
		$action = request()->action();
		$this->_prefix = config('database.prefix');
		if(in_array($action,['create_auth', 'auth_exists', 'create_admin', 'error_page'])) return false;
		try{
			Db::Query("show tables");
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
		if(!$this->is_login()) {
			$this->redirect('sign/login');
		}
	}

	/**
	* 判断是否已存在权限表
	*/
	private function auth_exists()
	{
		try {
			return Db::name('admin_user')->select()?true:false;
		} catch (\think\Exception $e) {
			return false;
		}
		return true;

	}


	//rbac初始化
	final public  function create_admin(Request $request)
	{
		if($this->auth_exists()) {
			$this->redirect('index/index');
		}
		if($request->isAjax()) {
            if(!$this->create_auth()) {
            	return json(['code'=>-1,'msg'=>'无法创建数据表，请检查数据库权限']);
            }
            $id = Db::name('admin_user')->insert([
            	'admin_user' => $request->param('admin_user','','htmlspecialchars'),
            	'admin_pass' => md5(md5($request->param('admin_pass','','htmlspecialchars'))),
            	'admin_name' => $request->param('admin_name','','htmlspecialchars'),
            	'admin_phone'=> $request->param('admin_phone',0,'int'),
            	'role_id'    => 1,
            	'status'     => 0,
            	'add_time'   => time()
            ]);
            session('data',['id'=>$id,'role_id'=>1]);
            return json(['code'=>0,'msg'=>'初始化成功!']);
            
        }
        elseif ($request->isGet()) {
            return $this->fetch();
        }
        else {
		    return '请求错误！';
        }
	}


    /**
     * 生成auth权限表
     */
    private function create_auth()
    {
    	if(!is_file('./create_auth.sql')) {
    		exit('文件 create_auth.sql 不存在！');
    	}
        $sql = preg_replace('/\[\[PREFIX\]\]/',$this->_prefix,file_get_contents('create_auth.sql'));
        $sql = explode(';', $sql);
        array_pop($sql);
        try{
        	$res = Db::batchQuery($sql);
        }
        catch(\think\Exception $e){
        	return $false;
        }
        return true;
    }

    /**
     * 生成全局配置表
     */
    private function create_global()
    {

    }

    /**
     * 生成api权限控制表
     */
    private function create_api_auth()
    {

    }

	//error_page
	public function error_page()
	{
		$this->assign('error_msg', $this->error_msg);
		return $this->fetch();
	}

	/**
	* 是否登录
	*/
	protected function is_login(){
		return session('data')?true:false;
	}

}