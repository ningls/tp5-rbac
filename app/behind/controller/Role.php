<?php
namespace app\behind\controller;

use app\behind\model\AdminRole;
use app\behind\model\AdminUser;
use app\common\logic\StatusCode;
use think\Db;
use think\Request;

class Role extends Base
{
    /**
     * 角色管理
     */
    public function index()
    {
        $model = new AdminRole();
        $where = [];
        if(!$this->global_setting['show_del_role']) {
            $where['r.status'] = ['neq',9];
        }
        $role = $model->get_role($where);
        $role = $role->toArray()['data'];
        $role = $this->get_son_array($role,session('user.role_id'));
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
     * 角色管理
     */
    public function admin_user()
    {
        $admin_role = session('user.role_id');
        $where = [];
        if((int)$admin_role !== 1) {
            $where['role_id'] = ['egt',$admin_role];
        }
        if(!$this->global_setting['show_del_user']) {
            $where['u.status'] = ['neq',9];
        }
        $user_model = new AdminUser();
        $user_data = $user_model->get_group_user($where);

        $user_data = $user_data->toArray()['data'];
        $user_data = $this->get_son_array($user_data,$admin_role,'role_id');
        foreach($user_data as $k => $v) {
            $user_data[$k]['status_name'] = StatusCode::admin_user_status[$v['status']];
            if($v['parent_id'] != 0) {
                $menu[$k]['role_name'] = '|' . str_repeat('------',(int)$v['level']) . $v['name'];
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

        }
        elseif($request->isGet()) {
            $admin_role = session('user.role_id');
            dump($admin_role);
        }
    }

    /**
     * 编辑角色
     */
    public function edit_role(Request $request)
    {

    }

    /**
     * 禁用激活角色
     */
    public function disable_role(Request $request)
    {

    }

    /**
     * 删除角色
     */
    public function del_role(Request $request)
    {

    }

    /**
     * 新增管理用户
     */
    public function add_admin_user(Request $request)
    {

    }

    /**
     * 编辑管理用户
     */
    public function edit_admin_user(Request $request)
    {

    }

    /**
     * 禁用/激活管理用户
     */
    public function disable_admin_user(Request $request)
    {

    }

    /**
     * 删除管理用户
     */
    public function del_admin_user(Request $request)
    {

    }


    /**
     * 通过parent_id查找子孙数组(包含自己)   -- 必须根据role_id asc排序 否则会出现错漏
     * @param array $data
     * @param int $parent_id
     * @param string $find_key
     * @return array $res
     */
    protected function get_son_array(array $data,int $parent_id,string $find_key = 'id'):array
    {
        //对data进行排序
        $data = $this->arraySequence($data,$find_key);
        $find = [];
        $res = [];
        foreach($data as  $v) {
            if($v[$find_key] == $parent_id || in_array($v['parent_id'],$find)) {
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