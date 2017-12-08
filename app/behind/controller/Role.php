<?php
namespace app\behind\controller;

use app\behind\model\AdminRole;
use app\behind\model\AdminUser;
use app\common\logic\StatusCode;
use think\Db;
use think\Request;
use app\common\logic\ErrorCode;

//todo 判断操作用户是否为被操作用户的上级角色

class Role extends Base
{
    protected $role_id;

    public function _initialize()
    {
        parent::_initialize();
        $this->role_id = Db::name('admin_user')->where(['id' => session('user.id')])->value('role_id');
    }
    /**
     * 角色管理
     */
    public function index()
    {
        $admin_role = $this->role_id;
        $model = new AdminRole();
        $where = [];
        if(!$this->global_setting['show_del_role']) {
            $where['r.status'] = ['neq',9];
        }
        $role = $model->get_role($where);
        $role = $role->toArray()['data'];
        $role = $this->get_son_array($role,$admin_role);
        foreach($role as $k => $v) {
            $role[$k]['status_name'] = StatusCode::role_status[$v['status']];
            if($v['parent_id'] != 0) {
                $menu[$k]['role_name'] = '|' . str_repeat('------',(int)$v['level']) . $v['name'];
            }
        }
        $this->assign('role',$role);
        return $this->fetch();
    }

    /**
     * 用户管理
     */
    public function admin_user()
    {
        //重新查询，防止更高级别角色更改本角色的角色
        $admin_role = $this->role_id;
        $where = [];
        if((int)$admin_role !== 1) {
            $where['role_id'] = ['gt',$admin_role];
        }
        if(!$this->global_setting['show_del_user']) {
            $where['u.status'] = ['neq',9];
        }
        $user_model = new AdminUser();
        $user_data = $user_model->get_group_user($where);

        $user_data = $user_data->toArray()['data'];
        $user_data = $this->get_son_array($user_data,$admin_role,'role_id');
        $tmp = [];
        foreach($user_data as $k => $v) {
            $user_data[$k]['status_name'] = StatusCode::admin_user_status[$v['status']];
            //删除其他同级用户并将自己提到顶部
            if($v['role_id'] == $admin_role) {
                if($v['id'] == session('user.id')) {
                    $tmp = $user_data[$k];
                }
                unset($user_data[$k]);
            }
            if($v['parent_id'] != 0) {
                $menu[$k]['role_name'] = '|' . str_repeat('------',(int)$v['level']) . $v['name'];
            }
        }
        array_unshift($user_data,$tmp);
        $this->assign('user',$user_data);
        return $this->fetch();

    }

    /**
     * 新增角色
     */
    public function add_role(Request $request)
    {
        if($request->isAjax()) {
            $data['role_name'] = $request->post('role_name','','htmlspecialchars');
            $data['parent_id'] = strtolower($request->post('parent_id',''));
            $data['sort'] = $request->post('sort',0,'intval');
            $data['parent_id'] = $request->post('parent_id',0,'intval');
            $id = 0;
            if( $data['name'] == false && $this->code = 9010 || $data['url'] == false && $this->code = 9011 ) {
                goto res;
            }
            if($data['url'] != '') {
                if(!preg_match('/^[\w]+\/[\w]+$/',$data['url'])) {
                    $this->code = 9012;
                    goto res;
                }
                if(Db::name('admin_menu')->where(['url'=>$data['url']])->find()) {
                    $this->code = 9018;
                    goto res;
                }
            }
            $data['add_time'] = time();
            try{
                $id = Db::name('admin_menu')->insertGetId($data);
                //重新缓存菜单
                $this->code = 0;
            }
            catch(\PDOException $e) {
                $this->code = 9999;
            }
            res:
            $this->code != 0 || $this->reflash_menu();
            return json(['code'=>$this->code,'msg'=>ErrorCode::error[$this->code],'data'=> !empty($id)?url('auth/auth_by_menu',['menu_id'=>$id]):null]);
        }
        else {
            if(($menu = cache(CacheKey::BEHIND_CACHE['menu_list'])) == false) {
                $menu = $this->cache_menu();
            }
            foreach($menu as $k => $v) {
                if($v['parent_id'] == 0) {
                    $menu[$k]['name'] = '|-' . $v['name'];
                }
                else {
                    $menu[$k]['name'] = '|---' . $v['name'];
                }
            }
            $this->assign('menu',$menu);
            return $this->fetch();
        }
    }

    /**
     * 编辑角色
     */
    public function edit_role(Request $request)
    {
        $id = request()->param('id',0,'intval');

        if(!$id) {
            $this->code = 9013;
            goto res;
        }
        if(request()->isAjax()) {
            $data['name'] = $request->post('name','','htmlspecialchars');
            $data['url'] = strtolower($request->post('url',''));
            $data['sort'] = $request->post('sort',0,'intval');
            $data['parent_id'] = $request->post('parent_id',0,'intval');
            if( $data['name'] == false && $this->code = 9010 || $data['url'] == false && $this->code = 9011 ) {
                goto res;
            }
            if($data['url'] != '') {
                if(!preg_match('/^[\w]+\/[\w]+$/',$data['url'])) {
                    $this->code = 9012;
                    goto res;
                }
                if(Db::name('admin_menu')->where(['url'=>$data['url'],'id'=>['neq',$id]])->find()) {
                    $this->code = 9018;
                    goto res;
                }
            }
            try{
                $model = new MenuModel();
                $this->code = $model->update_menu($data,$id,9016);
            }
            catch(\PDOException $e) {
                $this->code = 9999;
            }
            res:
            $this->code != 0 || $this->reflash_menu();
            return json(['code'=>$this->code,'msg'=>ErrorCode::error[$this->code]]);
        }
        elseif(request()->isGet()){
            $info = Db::name('admin_menu')->find($id);
            if(($menu = cache(CacheKey::BEHIND_CACHE['menu_list'])) == false) {
                $menu = $this->cache_menu();
            }
            foreach($menu as $k => $v) {
                if($v['parent_id'] == 0) {
                    $menu[$k]['name'] = '|-' . $v['name'];
                }
                else {
                    $menu[$k]['name'] = '|---' . $v['name'];
                }
            }
            $this->assign([
                'info' => $info,
                'menu' => $menu,
            ]);
            return $this->fetch();
        }
    }

    /**
     * 禁用激活角色
     */
    public function disable_role(Request $request)
    {
        $id = $request->param('id',0,'intval');
        $status = $request->param('status',0,'intval');
        if(!$id || !in_array($status,[0,1])) {
            $this->code = 9013;
            goto res;
        }
        $set_status = $status?0:1;
        $model = new AdminRole();
        $this->code = $model->set_role_status($id,$set_status,9019);
        res:
        return json(['code'=>$this->code,'msg'=>ErrorCode::error[$this->code]]);
    }

    /**
     * 删除角色
     */
    public function del_role(Request $request)
    {
        $id = $request->param('id',0,'intval');
        if(!$id) {
            $this->code = 9013;
            goto res;
        }
        $model = new AdminRole();
        ($this->code = $model->set_role_status($id,9,9020));
        res:
        return json(['code'=>$this->code,'msg'=>ErrorCode::error[$this->code]]);
    }

    /**
     * 新增管理用户
     */
    public function add_admin_user(Request $request)
    {
        if($request->isAjax()) {
            $data['name'] = $request->post('name','','htmlspecialchars');
            $data['url'] = strtolower($request->post('url',''));
            $data['sort'] = $request->post('sort',0,'intval');
            $data['parent_id'] = $request->post('parent_id',0,'intval');
            $id = 0;
            if( $data['name'] == false && $this->code = 9010 || $data['url'] == false && $this->code = 9011 ) {
                goto res;
            }
            if($data['url'] != '') {
                if(!preg_match('/^[\w]+\/[\w]+$/',$data['url'])) {
                    $this->code = 9012;
                    goto res;
                }
                if(Db::name('admin_menu')->where(['url'=>$data['url']])->find()) {
                    $this->code = 9018;
                    goto res;
                }
            }
            $data['add_time'] = time();
            try{
                $id = Db::name('admin_menu')->insertGetId($data);
                //重新缓存菜单
                $this->code = 0;
            }
            catch(\PDOException $e) {
                $this->code = 9999;
            }
            res:
            $this->code != 0 || $this->reflash_menu();
            return json(['code'=>$this->code,'msg'=>ErrorCode::error[$this->code],'data'=> !empty($id)?url('auth/auth_by_menu',['menu_id'=>$id]):null]);
        }
        else {
            if(($menu = cache(CacheKey::BEHIND_CACHE['menu_list'])) == false) {
                $menu = $this->cache_menu();
            }
            foreach($menu as $k => $v) {
                if($v['parent_id'] == 0) {
                    $menu[$k]['name'] = '|-' . $v['name'];
                }
                else {
                    $menu[$k]['name'] = '|---' . $v['name'];
                }
            }
            $this->assign('menu',$menu);
            return $this->fetch();
        }
    }

    /**
     * 编辑管理用户
     */
    public function edit_admin_user(Request $request)
    {
        $id = request()->param('id',0,'intval');

        if(!$id) {
            $this->code = 9013;
            goto res;
        }
        if(request()->isAjax()) {
            $data['name'] = $request->post('name','','htmlspecialchars');
            $data['url'] = strtolower($request->post('url',''));
            $data['sort'] = $request->post('sort',0,'intval');
            $data['parent_id'] = $request->post('parent_id',0,'intval');
            if( $data['name'] == false && $this->code = 9010 || $data['url'] == false && $this->code = 9011 ) {
                goto res;
            }
            if($data['url'] != '') {
                if(!preg_match('/^[\w]+\/[\w]+$/',$data['url'])) {
                    $this->code = 9012;
                    goto res;
                }
                if(Db::name('admin_menu')->where(['url'=>$data['url'],'id'=>['neq',$id]])->find()) {
                    $this->code = 9018;
                    goto res;
                }
            }
            try{
                $model = new MenuModel();
                $this->code = $model->update_menu($data,$id,9016);
            }
            catch(\PDOException $e) {
                $this->code = 9999;
            }
            res:
            $this->code != 0 || $this->reflash_menu();
            return json(['code'=>$this->code,'msg'=>ErrorCode::error[$this->code]]);
        }
        elseif(request()->isGet()){
            $info = Db::name('admin_menu')->find($id);
            if(($menu = cache(CacheKey::BEHIND_CACHE['menu_list'])) == false) {
                $menu = $this->cache_menu();
            }
            foreach($menu as $k => $v) {
                if($v['parent_id'] == 0) {
                    $menu[$k]['name'] = '|-' . $v['name'];
                }
                else {
                    $menu[$k]['name'] = '|---' . $v['name'];
                }
            }
            $this->assign([
                'info' => $info,
                'menu' => $menu,
            ]);
            return $this->fetch();
        }
    }

    /**
     * 禁用/激活管理用户
     */
    public function disable_admin_user(Request $request)
    {
        $id = $request->param('id',0,'intval');
        $status = $request->param('status',0,'intval');
        if(!$id || !in_array($status,[0,1])) {
            $this->code = 9013;
            goto res;
        }
        $set_status = $status?0:1;
        $model = new AdminUser();
        $this->code = $model->set_user_status($id,$set_status,9021);
        res:
        return json(['code'=>$this->code,'msg'=>ErrorCode::error[$this->code]]);
    }

    /**
     * 删除管理用户
     */
    public function del_admin_user(Request $request)
    {
        $id = $request->param('id',0,'intval');
        if(!$id) {
            $this->code = 9013;
            goto res;
        }
        $model = new AdminUser();
        ($this->code = $model->set_user_status($id,9,9022));
        res:
        return json(['code'=>$this->code,'msg'=>ErrorCode::error[$this->code]]);
    }


    /**
     * 通过parent_id查找子孙数组(不包含自己)   -- 必须根据role_id asc排序 否则会出现错漏
     * @param array $data
     * @param int $parent_id
     * @param string $find_key
     * @return array $res
     */
    protected function get_son_array(array $data,int $parent_id,string $find_key = 'id'):array
    {
        //对data进行排序
        if($data == []) {
            return [];
        }
        $data = $this->arraySequence($data,$find_key);
        $find = [];
        $res = [];
        foreach($data as  $v) {
            if( $v[$find_key] == $parent_id || in_array($v['parent_id'],$find)) {
                $res[] = $v;
                $find[] = $v[$find_key];
            }
        }
        $res = $this->get_tree_by_parent_id($res);
        return $res;
    }

    /**
     * 根据parent_id获取树状二维数组
     * @param array $data
     * @return array $res
     */
    protected function get_tree_by_parent_id(array $data):array
    {
        $res = [];
        $sort = function ($data , $parent_id = 0, $level = 0) use (&$res,&$sort) {
            foreach($data as $k => $v) {
                if($v['parent_id'] == $parent_id) {
                    $v['level'] = $level;
                    $res[] = $v;
                    unset($data[$k]);
                    $sort($data,$v['id'],$level+1);
                }
            }
        };
        $sort($data);
        return $res;
    }
}