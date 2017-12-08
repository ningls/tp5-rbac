<?php
namespace app\behind\controller;
use app\common\controller\CBase;
use app\common\logic\ErrorCode;
use think\Request;
use think\Db;
use app\common\logic\CacheKey;
use think\Session;

class Base extends CBase
{
	//状态码
	protected $code = 0;
	//错误页面信息 todo
    protected $error_msg;
	//全局设置
	protected $global_setting;
	//表前缀
	private $_prefix;
	//当前操作
	protected $act;
	//当前访问链接
	protected $view_url;
	//当前访问名称
	protected $view_name;
	/**
	* 检测是否已经含有表以便生成admin
	*/
	public function _initialize()
	{
	    parent::_initialize();
	    if(config('app_debug')) {
			Session::delete('auth');
		}
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
		    $this->assign('system',$this->global_setting);
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
        //判断执行权
        $this->view_auth();
		//设置菜单
		$this->set_menu();
		//设置当前访问
        $this->now_act();
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
		    $phone = $request->param('admin_phone',0);
            if(!preg_match('/1[3|5|7|8][\d]{9}/',$phone)) {
                $this->code = 9001;
                goto init_over;
            }

            if($this->code = $this->create_auth()) {
                goto init_over;
            }

            $id = Db::name('admin_user')->insertGetId([
            	'admin_user' => $request->param('admin_user','','htmlspecialchars'),
            	'admin_pass' => md5(md5($request->param('admin_pass','','htmlspecialchars'))),
            	'admin_name' => $request->param('admin_name','','htmlspecialchars'),
            	'admin_phone'=> $phone,
            	'role_id'    => 1,
            	'create_user_id' => 1,
            	'status'     => 0,
            	'last_login' => time(),
            	'add_time'   => time()
            ]);
            session('user',['id'=>$id,'admin_user' => $request->param('admin_user','','htmlspecialchars'),'admin_name'=>$request->param('admin_name','','htmlspecialchars'),'role_id'=>1]);
            $this->code = 0;
            init_over:
            return json(['code'=>$this->code,'msg'=>ErrorCode::error[$this->code]]);
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
    		return 9008;
    	}
        $sql = preg_replace('/\[\[PREFIX\]\]/',$this->_prefix,file_get_contents('create_auth.sql'));
        $sql = explode(';', $sql);
        array_pop($sql);
        try{
        	return Db::batchQuery($sql)?0:9009;
        }
        catch(\think\Exception $e){
        	return 9009;
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
    * 判断是否有执行权
     * todo 判断菜单状态 跳转错误页面
    */
    protected function view_auth()
    {
    	$this->view_url = strtolower(request()->controller()) . '/' . strtolower(request()->action());
    	if($this->view_url == 'index/index') {
    	    $this->view_name = '首页';
    	    return true;
        }
    	if(!in_array($this->view_url, session('auth'))) {
    	    $this->code = 10000;
    		exit(json_encode(['code'=>$this->code,'msg'=>ErrorCode::error[10000]]));
    	}
    }

    /**
     * 输出菜单
     */
    protected function set_menu()
    {
        session('menu',null);
        if($menus = session("menu")) goto menu;

        //调试模式可见禁用菜单
    	if(config('app_debug')) {
    		$condition['status'] = ['in',[0,1]];
//            $condition['status'] = ['in',[0]];
    	}
    	else {
    		$condition['status'] = 0;
    	}
    	$condition['url'] = ['in',array_merge(session('auth'),[''])];
    	$data = Db::name('admin_menu')->field('id,name,url,parent_id,status')->where($condition)->order('parent_id, sort')->select();
    	$parents = [];
    	$menus = [];
    	foreach($data as $v) {
            if($v['status'] == 1) {
                $v['name'] .= '(已禁用)';
            }
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
        $this->assign('left_menu',$menus);
    }

    /**
    * 获取当前操作 
    */
    protected function now_act()
    {
        if($this->view_url == 'index/index') {
            $this->act = ['parent_name'=>'','url'=>$this->view_url,'name'=>'首页'];
            goto assign;
        }
        $act = $this->view_url;

        $sql = "select url,parent_id from {$this->_prefix}admin_menu where id in (select parent_id from {$this->_prefix}admin_menu where url = '{$act}')";
        $menu_info = Db::Query($sql)[0];
        if($menu_info['parent_id'] != 0) {
        	$this->view_name = $name = Db::name('admin_menu')->where(['url'=>$act])->value('name');
        	$act = $menu_info['url'];
        }
        $menu = session('menu');
        foreach($menu as $v) {
            foreach($v as $vv) {
                if($vv['url'] == $act) {
                    $this->act = ['parent_name' => $vv['parent_name'],'url'=>$vv['url'],'name'=>$vv['name']];
                    $this->view_name = $vv['name'];
                    break;
                }
            }
        }
        if($menu_info['parent_id'] != 0) {
        	$this->act['name'] = $name??'';
        	$this->view_name = $name??'';
        }
        assign:
        $this->assign('now_act',$this->act);
    }

    /**
    * 写入日志
    */
    protected function act_log(string $url,string $name,string $info,int $user_id) {
        $user_id = $user_id??0;
    	$data['view_url'] = $url;
    	$data['view_name'] = $name??'';
    	$data['admin_id'] = $user_id;
    	$data['info'] = $info;
    	$data['view_at'] = time();
    	$data['view_ip'] = request()->ip();
    	$data['data'] = request()->method() . (request()->param()?json_encode(request()->param()):'');
    	try{
    		Db::name('admin_log')->insert($data);
    	}
    	catch(\PDOException $e) {
    		exit('写入日志表失败');
    	}
    	
    }


    /**
     * 输出用户基本信息
     */
    protected function set_user()
    {
        $this->assign('top_user',[
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
	protected function is_login():bool
    {
	    if(config('app_debug')) return true;
		return session('user')?true:false;
	}

    /**
     * 结束写入日志
     * 访问请求method为get，操作请求method为post
     */
	public function __destruct()
    {
        if($this->global_setting['log_open']) {
            if(request()->isGet()) {
                $msg = $this->code?ErrorCode::error[$this->code]:'访问';
                $this->act_log($this->view_url,$this->view_name,$msg,session('user.id'));
            }elseif (request()->isPost()) {
                $this->view_name .= ($this->code?'失败':'成功');
                $this->act_log($this->view_url,$this->view_name,ErrorCode::error[$this->code],session('user.id'));
            }
        }
    }

    /**
     * 二维数组根据字段进行排序
     * @params array $array 需要排序的数组
     * @params string $field 排序的字段
     * @params string $sort 排序顺序标志 SORT_DESC 降序；SORT_ASC 升序
     */
    protected function arraySequence($array, $field, $sort = 'SORT_ASC')
    {
        $arrSort = array();
        foreach ($array as $uniqid => $row) {
            foreach ($row as $key => $value) {
                $arrSort[$key][$uniqid] = $value;
            }
        }
        array_multisort($arrSort[$field], constant($sort), $array);
        return $array;
    }
    /**
     * 判断parent_id的父辈元素中是否含有id元素是否为查找元素的父元素 -- find_key正向排序性能更好
     */
    protected function find_parent(array $data,int $parent_id,int $id,string $find_key)
    {
        if($parent_id == $id) {
            return true;
        }
        $sort = function($data,$parent_id) use ($find_key,$id,&$sort) {
            if($parent_id == $id) {
                return true;
            }
            if($parent_id == 0) {
                return false;
            }
            foreach($data as $v) {
                if($v[$find_key] == $parent_id) {
                    return $sort($data,$v['parent_id']);
                }
            }
            return false;
        };
        return $sort($data,$parent_id);
    }

}

