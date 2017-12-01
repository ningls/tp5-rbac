<?php
namespace app\behind\controller;
use \app\behind\model\AdminMenu as MenuModel;

/**
 * Class Menu
 * 菜单管理
 * @package app\behind\controller
 */
class Menu extends Base
{
    public function index()
    {
        $model = new MenuModel();
        $menus = $model->show_parent_menus();
        dump($menus);
    }
}