<?php
namespace app\behind\controller;

use app\behind\model\AdminRole;
use app\behind\model\AdminUser;
use app\common\logic\StatusCode;
use think\Db;

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
        $data = Db::name('admin_user')->where([])->select();
        dump($data);
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

    /**
     * 获取下级角色
     */
    protected function get_tree_role()
    {
        if(!$this->global_setting['show_del_menu']) {
            $data = Db::name('admin_menu')->where(['status'=>['neq',9]])->order('parent_id,sort')->select();
        }
        else{
            $data = Db::name('admin_menu')->order('parent_id,sort')->select();
        }
        $menu = [];

        $sort = function ($data , $parent_id = 0, $level = 0,$parent_name = '') use (&$menu,&$sort) {
            foreach($data as $k => $v) {
                if($v['status'] != 0) {
                    $v['name'] = $v['name'] . '(' . StatusCode::menu_status[$v['status']] . ')';
                }
                if($v['parent_id'] == $parent_id) {
                    $v['level'] = $level;
                    $v['parent_name'] = $parent_name;
                    $menu[] = $v;
                    unset($data[$k]);
                    $sort($data,$v['id'],$level+1,$v['name']);
                }
            }
        };
        $sort($data);
        cache(CacheKey::BEHIND_CACHE['menu_tree'], $menu);
        return $menu;

    }

    /**
     * 获取下级用户
     */
    protected function get_tree_user()
    {
        if(!$this->global_setting['show_del_menu']) {
            $data = Db::name('admin_menu')->where(['status'=>['neq',9]])->order('parent_id,sort')->select();
        }
        else{
            $data = Db::name('admin_menu')->order('parent_id,sort')->select();
        }
        $menu = [];

        $sort = function ($data , $parent_id = 0, $level = 0,$parent_name = '') use (&$menu,&$sort) {
            foreach($data as $k => $v) {
                if($v['status'] != 0) {
                    $v['name'] = $v['name'] . '(' . StatusCode::menu_status[$v['status']] . ')';
                }
                if($v['parent_id'] == $parent_id) {
                    $v['level'] = $level;
                    $v['parent_name'] = $parent_name;
                    $menu[] = $v;
                    unset($data[$k]);
                    $sort($data,$v['id'],$level+1,$v['name']);
                }
            }
        };
        $sort($data);
        cache(CacheKey::BEHIND_CACHE['menu_tree'], $menu);
        return $menu;

    }
}