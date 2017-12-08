<?php
namespace app\behind\controller;

use app\behind\model\AdminRole;
use app\behind\model\AdminUser;
use app\common\logic\Authority;
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
        $this->role_id = (int)Db::name('admin_user')->where(['id' => session('user.id')])->value('role_id');
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
        if($this->role_id === 1) {
            $role = $this->get_tree_by_parent_id($role);
        }
        else {
            $role = $this->get_son_array($role,$admin_role);
        }

        foreach($role as $k => $v) {
            $role[$k]['status_name'] = StatusCode::role_status[$v['status']];
            if($v['parent_id'] != 0) {
                $role[$k]['role_name'] = '|' . str_repeat('------',(int)$v['level']) . $v['role_name'];
            }
        }
        $this->assign('role',$role);
        $this->assign('role_id',$this->role_id);
        return $this->fetch();
    }

    /**
     * 用户管理
     */
    public function admin_user()
    {
        $admin_role = $this->role_id;
        $where = [];

        if(!$this->global_setting['show_del_user']) {
            $where['u.status'] = ['neq',9];
        }
        $user_model = new AdminUser();
        $user_data = $user_model->get_group_user($where);
        $user_data = $user_data->toArray()['data'];
        if($this->role_id === 1) {
            $user_data = $this->get_tree_by_parent_id($user_data);
        }
        else {
            $user_data = $this->get_son_array($user_data,$admin_role);
        }
        $user_data = $this->get_son_array($user_data,$admin_role,'role_id');
        $tmp = [];
        foreach($user_data as $k => $v) {
            $user_data[$k]['status_name'] = StatusCode::admin_user_status[$v['status']];
            if($v['parent_id'] != 0) {
                $user_data[$k]['role_name'] = '|' . str_repeat('------',(int)$v['level']) . $v['name'];
            }
        }
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
            $data['parent_id'] = $request->post('parent_id','');
            $id = 0;
            if($data['role_name'] == false && $this->code = 9023) {
                goto res;
            }
            if($data['parent_id'] === '' || $this->role_id !== 1 && !Authority::is_role_parent($data['parent_id'],$this->role_id)) {
                $this->code = 9998;
                goto res;
            }
            if(Db::name('admin_role')->where(['role_name'=>$data['role_name']])->find()) {
                $this->code = 9024;
                goto res;
            }
            $data['create_user_id'] = session('user.id');
            $data['add_time'] = time();
            try{
                $id = Db::name('admin_role')->insertGetId($data);
                $this->code = 0;
            }
            catch(\PDOException $e) {
                $this->code = 9999;
            }
            res:
            return json(['code'=>$this->code,'msg'=>ErrorCode::error[$this->code],'data'=> !empty($id)?url('auth/auth_by_role',['role_id'=>$id]):null]);
        }
        else {
            $admin_role = $this->role_id;
            $model = new AdminRole();
            $where['r.status'] = ['neq',9];
            $role = $model->get_role($where);
            $role = $role->toArray()['data'];
            if($this->role_id === 1) {
                $role = $this->get_tree_by_parent_id($role);
            }
            else {
                $role = $this->get_son_array($role,$admin_role);
            }
            foreach($role as $k => $v) {
                if($v['status'] != 0) {
                    $role[$k]['role_name'] = $role[$k]['role_name'] . "(".StatusCode::role_status[$v['status']].")";
                }
                if($v['parent_id'] != 0) {
                    $role[$k]['role_name'] = '|' . str_repeat('------',(int)$v['level']) . $role[$k]['role_name'];
                }
            }
            $this->assign([
                'role_list'=> $role,
                'role_id' => $this->role_id
            ]);
            return $this->fetch();
        }
    }

    /**
     * 编辑角色
     */
    public function edit_role(Request $request)
    {
        $id = $request->param('id',0,'intval');

        if(!$id) {
            $this->code = 9013;
            if($request->isGet()){
                $this->error(ErrorCode::error[$this->code]);
            }
            else {
                goto res;
            }

        }
        $parent_id = Db::name('admin_role')->where(['id'=>$id])->value('parent_id');
        if($this->role_id !== 1 && !Authority::is_role_parent($parent_id,$this->role_id)) {
            $this->code = 9998;
            if($request->isGet()){
                $this->error(ErrorCode::error[$this->code]);
            }
            else {
                goto res;
            }
        }
        if($request->isAjax()) {
            $data['role_name'] = $request->post('role_name','','htmlspecialchars');
            $data['parent_id'] = $request->post('parent_id','');
            if($data['role_name'] == false && $this->code = 9023) {
                goto res;
            }
            if($data['parent_id'] === '' || $id == $data['parent_id'] ||$this->role_id !== 1 && !Authority::is_role_parent($data['parent_id'],$this->role_id)) {
                $this->code = 9998;
                goto res;
            }
            if(Db::name('admin_role')->where(['role_name'=>$data['role_name'],'id'=>['neq',$id]])->find()) {
                $this->code = 9024;
                goto res;
            }
            try{
                $model = new AdminRole();
                $this->code = $model->update_role($data,$id,9016);
            }
            catch(\PDOException $e) {
                $this->code = 9999;
            }
            res:
            return json(['code'=>$this->code,'msg'=>ErrorCode::error[$this->code]]);
        }
        elseif($request->isGet()){
            $admin_role = $this->role_id;
            $model = new AdminRole();
            $where['r.status'] = ['neq',9];
            $role = $model->get_role($where);
            $role = $role->toArray()['data'];
            if($this->role_id === 1) {
                $role = $this->get_tree_by_parent_id($role);
            }
            else {
                $role = $this->get_son_array($role,$admin_role);
            }
            foreach($role as $k => $v) {
                if($v['status'] != 0) {
                    $role[$k]['role_name'] = $role[$k]['role_name'] . "(".StatusCode::role_status[$v['status']].")";
                }
                if($v['parent_id'] != 0) {
                    $role[$k]['role_name'] = '|' . str_repeat('------',(int)$v['level']) . $role[$k]['role_name'];
                }
            }
            $this->assign([
                'role_list'=> $role,
                'role_id' => $this->role_id,
                'id'=> $id,
                'info'=>Db::name('admin_role')->where(['id'=>$id])->find()
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
        $parent_id = Db::name('admin_role')->where(['id'=>$id])->value('parent_id');
        if($this->role_id !== 1 && !Authority::is_role_parent($parent_id,$this->role_id)) {
            $this->code = 9998;
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
        $parent_id = Db::name('admin_role')->where(['id'=>$id])->value('parent_id');
        if($this->role_id !== 1 && !Authority::is_role_parent($parent_id,$this->role_id)) {
            $this->code = 9998;
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
            $data['admin_user'] = $request->post('admin_user','','htmlspecialchars');
            $data['admin_name'] = $request->post('admin_name','','htmlspecialchars');
            $data['role_id'] = $request->post('role_id',0,'intval');
            $data['admin_phone'] = $request->post('admin_phone','');

            if($data['admin_user'] == false && ($this->code = 9025) || $data['admin_name'] == false && ($this->code = 9029) || ($data['admin_phone'] == false || !preg_match('/^1[34578][\d]{9}$/',$data['admin_phone']) && ($this->code = 9001)) || !preg_match('/^[\w]{4,20}$/',$data['admin_user']) && ($this->code = 9030)) {
                goto res;
            }
            $parent_id = Db::name('admin_role')->where(['id'=>$data['role_id']])->value('parent_id');
            if($this->role_id !== 1 && !Authority::is_role_parent($parent_id,$this->role_id)) {
                $this->code = 9998;
                goto res;
            }

            if(Db::name('admin_user')->where(['admin_user'=>$data['admin_user']])->find()) {
                $this->code = 9026;
                goto res;
            }
            if(Db::name('admin_user')->where(['admin_phone'=>$data['admin_phone']])->find()) {
                $this->code = 9028;
                goto res;
            }
            $data['create_user_id'] = session('user.id');
            $data['add_time'] = time();
            $data['admin_pass'] = md5(md5($this->global_setting['user_init_pwd']));
            try{
                Db::name('admin_user')->insert($data);
                $this->code = 0;
            }
            catch(\PDOException $e) {
                $this->code = 9999;
            }
            res:
            return json(['code'=>$this->code,'msg'=>ErrorCode::error[$this->code]]);
        }
        else {
            $admin_role = $this->role_id;
            $model = new AdminRole();
            $where['r.status'] = ['neq',9];
            $role = $model->get_role($where);
            $role = $role->toArray()['data'];
            if($this->role_id === 1) {
                $role = $this->get_tree_by_parent_id($role);
            }
            else {
                $role = $this->get_son_array($role,$admin_role);
            }
            foreach($role as $k => $v) {
                if($v['status'] != 0) {
                    $role[$k]['role_name'] = $role[$k]['role_name'] . "(".StatusCode::role_status[$v['status']].")";
                }
                if($v['parent_id'] != 0) {
                    $role[$k]['role_name'] = '|' . str_repeat('------',(int)$v['level']) . $role[$k]['role_name'];
                }
            }
            $this->assign([
                'role_list'=> $role,
                'role_id' => $this->role_id
            ]);
            return $this->fetch();
        }
    }

    /**
     * 编辑管理用户
     */
    public function edit_admin_user(Request $request)
    {
        $id = $request->param('id',0,'intval');

        //不是超级管理员时，判断是否是当前用户下级用户
        if(!$id && ($this->code = 9013) || $this->role_id !== 1 && !Authority::is_user_parent($id,$this->role_id) && ($this->code = 9998)) {
            if($request->isGet()){
                $this->error(ErrorCode::error[$this->code],url('admin_user'));
            }
            else {
                goto res;
            }

        }
        if($request->isAjax()) {
            $data['admin_user'] = $request->post('admin_user','','htmlspecialchars');
            $data['admin_name'] = $request->post('admin_name','','htmlspecialchars');
            $data['role_id'] = $request->post('role_id',0,'intval');
            $data['admin_phone'] = $request->post('admin_phone','','intval');
            if($data['admin_user'] == false && $this->code = 9025 || $data['admin_name'] == false && $this->code = 9029 || ($data['admin_phone'] == false || !preg_match('/^1[3|5|7|8][\d]{9}$/',$data['admin_phone'])) && $this->code = 9001 || !preg_match('/^[\w]{4,20}$/',$data['admin_user']) && $this->code = 9030) {
                goto res;
            }
            $parent_id = Db::name('admin_role')->where(['id'=>$data['role_id']])->value('parent_id');
            if($this->role_id !== 1 && !Authority::is_role_parent($parent_id,$this->role_id)) {
                $this->code = 9998;
                goto res;
            }

            if(Db::name('admin_user')->where(['admin_user'=>$data['admin_user']])->find()) {
                $this->code = 9026;
                goto res;
            }
            if(Db::name('admin_user')->where(['admin_phone'=>$data['admin_phone']])->find()) {
                $this->code = 9028;
                goto res;
            }
            try{
                $model = new AdminUser();
                $this->code = $model->update_user($data,$id,9016);
            }
            catch(\PDOException $e) {
                $this->code = 9999;
            }
            res:
            return json(['code'=>$this->code,'msg'=>ErrorCode::error[$this->code]]);
        }
        elseif($request->isGet()){
            $admin_role = $this->role_id;
            $model = new AdminRole();
            $where['r.status'] = ['neq',9];
            $role = $model->get_role($where);
            $role = $role->toArray()['data'];
            if($this->role_id === 1) {
                $role = $this->get_tree_by_parent_id($role);
            }
            else {
                $role = $this->get_son_array($role,$admin_role);
            }
            foreach($role as $k => $v) {
                if($v['status'] != 0) {
                    $role[$k]['role_name'] = $role[$k]['role_name'] . "(".StatusCode::role_status[$v['status']].")";
                }
                if($v['parent_id'] != 0) {
                    $role[$k]['role_name'] = '|' . str_repeat('------',(int)$v['level']) . $role[$k]['role_name'];
                }
            }
            $this->assign([
                'role_list'=> $role,
                'role_id' => $this->role_id,
                'id'=> $id,
                'info'=>Db::name('admin_user')->where(['id'=>$id])->find()
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
        if($this->role_id !== 1 && !Authority::is_user_parent($id,$this->role_id)) {
            $this->code = 9998;
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
        if($this->role_id !== 1 && !Authority::is_user_parent($id,$this->role_id)) {
            $this->code = 9998;
            goto res;
        }
        $model = new AdminUser();
        $this->code = $model->set_user_status($id,9,9022);
        res:
        return json(['code'=>$this->code,'msg'=>ErrorCode::error[$this->code]]);
    }


    /**
     * 通过parent_id查找子孙数组(包含自己)
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
        $data = $this->arraySequence($data,$find_key); //根据find_key排序
        $res = [];
        $get_son = function(array $data,array $parent_id,string $find_key = 'id') use(&$res,&$get_son) {
            $son_key = [];
            foreach($data as $k => $v) {
                if(in_array($v['parent_id'],$parent_id) || in_array($v[$find_key],$parent_id)) {
                    $res[] = $v;
                    $son_key[] = $v[$find_key]; //把查找的key作为下一次查找的父id
                    unset($data[$k]);
                }
            }
            if($son_key !== []) {
                $get_son($data,$son_key,$find_key);
            }
        };

        $get_son($data,[$parent_id],$find_key);
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