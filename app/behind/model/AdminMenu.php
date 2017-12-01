<?php
namespace app\behind\model;

use think\Model;

class AdminMenu extends Model
{
    /**
     * 显示所有的一级菜单
     * @return object
     */
    public function show_parent_menus()
    {
        $condition['status'] = ['in',[0,1]];
        return $this->where($condition)->select();
    }
}