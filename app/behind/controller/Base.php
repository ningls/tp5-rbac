<?php
namespace app\behind\controller;
use \app\common\controller\CBase;
use \think\Request;
use \think\Db;
use \app\common\logic\CacheKey;
use think\Session;

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
//	    return false;
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
		//获取权限
        $this->get_auth();
		//设置菜单
		$this->set_menu();
		//设置用户信息
        $this->set_user();
        
	}

	/**
	* 判断是否已存在权限表
	*/
	private function auth_exists():bool
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
            session('user',['id'=>$id,'admin_user' => $request->param('admin_user','','htmlspecialchars'),'admin_name'=>$request->param('admin_name','','htmlspecialchars'),'role_id'=>1]);
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
    private function create_auth():bool
    {
    	if(!is_file('./create_auth.sql')) {
    		return false;
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

    protected function get_auth()
    {
        if(session('auth') == false) {
            if(session('user.role_id') == 1) {
                session('auth',Db::name('admin_menu')->field('distinct url')->column('url'));
            }
            else {
                session('auth',json_decode(Db::name('admin_role_auth')->where(['role_id'=>session('user.role_id')])->value('role_auth'), true));
            }
        }
        return true;
    }

  
    /**
     * 输出菜单
     */
    protected function set_menu()
    {
        if($menus = session("menu")) goto menu;

        //调试模式可见禁用菜单
    	if(config('app_debug')) {
    		$condition['status'] = ['in',[0,1]];
    	}
    	else {
    		$condition['status'] = 0;
    	}
    	$condition['url'] = ['in',array_merge(session('auth'),[''])];
    	$data = Db::name('admin_menu')->field('id,name,url,parent_id,status')->where($condition)->order('parent_id, sort')->select();
    	$parents = [];
    	$menus = [];
    	foreach($data as $v) {
    	    if($v['parent_id'] == 0 && !array_key_exists($v['name'],$menus)) {
    	        $parents[$v['id']] = $v['name'];
                $menus[$v['name']] = [];
            }
            if($v['parent_id'] != 0 && isset($parents[$v['parent_id']])) {
    	        $v['parent_name'] = $parents[$v['parent_id']];
    	        $menus[$v['parent_name']][] = $v;
            }

        }
        unset($data);
    	session('menu',$menus);
    	menu:
        $this->assign('menu',$menus);
    }

    /**
     * 输出用户基本信息
     */
    protected function set_user()
    {
        $this->assign('user',[
            'admin_name'=> session('user.admin_name')
        ]);
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
		return session('user')?true:false;
	}

}
