<?php
namespace app\behind\controller;

use app\behind\model\AdminRole;
use app\behind\model\AdminUser;
use app\common\logic\StatusCode;

class Role extends Base
{
    /**
     * 角色管理
     */
    public function index()
    {
        $model = new AdminRole();
        $role = $model->get_role();
        $parents = [];
        foreach($role as $k => $v) {
            $parents[$v['id']] = $v['role_name'];
            if($v['parent_id'] == 0) {
                $role[$k]['parent_name'] = '-';
            }
            else {

            }
        }
    }

    /**
     * 角色管理
     */
    public function admin_user()
    {
        $model = new AdminUser();
        $user = $model->get_admin_user($this->global_setting['page_limit']);
        foreach($user as $k => $v) {
            $user[$k]['status_name'] = StatusCode::admin_user_status[$v['status']];
        }
        $this->assign('user',$user);
        return $this->fetch();
    }

    /**
     * 新增角色
     */
    public function add_role()
    {

    }

    /**
     * 编辑角色
     */
    public function edit_role()
    {

    }

    /**
     * 禁用激活角色
     */
    public function disable_role()
    {

    }

    /**
     * 删除角色
     */
    public function del_role()
    {

    }

    /**
     * 新增管理用户
     */
    public function add_admin_user()
    {

    }

    /**
     * 编辑管理用户
     */
    public function edit_admin_user()
    {

    }

    /**
     * 禁用/激活管理用户
     */
    public function disable_admin_user()
    {

    }

    /**
     * 删除管理用户
     */
    public function del_admin_user()
    {

    }
}