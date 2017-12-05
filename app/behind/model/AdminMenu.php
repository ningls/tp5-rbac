<?php
namespace app\behind\model;

use think\Model;

class AdminMenu extends Model
{
    /**
     * 显示所有的菜单
     * @return object
     */
    public function show_menus()
    {
        $prefix = config('database.prefix');
        $sql = "select * from {$prefix}admin_menu where parent_id in (select id from {$prefix}admin_menu where parent_id = 0) or parent_id = 0 order by parent_id asc,sort asc";
        return $this->query($sql);
    }

    /**
     * 显示所有非父菜单
     */
    public function show_son_menus()
    {
        return $this->where(['parent_id'=>['neq',0]])->order('status')->select();
    }
}