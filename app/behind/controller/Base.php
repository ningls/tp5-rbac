<?php
namespace app\behind\controller;
use \app\common\controller\CBase;
use \think\Request;
use \think\Db;
use \app\common\logic\CacheKey;

class Base extends CBase
{
	protected $error_msg;

	//全局设置
	protected $global_setting;

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
		//获取全局设置
		$setting = Db::name('global_setting')->select();
		foreach($setting as $v) {
		    $this->global_setting[$v['key']] = $v['value'];
        }
		//sign控制器时跳出
        if(strtolower(request()->controller()) === 'sign') {
            return false;
        }
		if(!$this->is_login()) {
			$this->redirect('sign/login');
		}
		//设置菜单
		$this->set_menu();
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
            session('data',['id'=>$id,'admin_user' => $request->param('admin_user','','htmlspecialchars'),'admin_name'=>$request->param('admin_name','','htmlspecialchars'),'role_id'=>1]);
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
        	return  Db::batchQuery($sql);
        }
        catch(\think\Exception $e){
        	return false;
        }
    }

  
    /**
     * 生成api权限控制表
     */
    protected function set_menu()
    {
    	//top_menu
    	//调试模式可见禁用菜单
    	if(APP_DEBUG) {
    		$condition['status'] = ['in',[0,1]];
    	}
    	else {
    		$condition['status'] = 0;
    	}
    	$condition['parent_id'] = 0;

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
