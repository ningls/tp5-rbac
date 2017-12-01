<?php
namespace app\behind\controller;
use \app\behind\model\AdminMenu as MenuModel;
use app\common\logic\StatusCode;

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
        $data = $model->show_menus();
        $menu = [];
        $loop_status = 0;
        $parent_id = 0;
        $parent_name = [];
        while(count((array)$data) > 0) {
            foreach($data as $k => $v) {
                if($v['parent_id'] == 0) {
                    $menu[] = $v;
                    $parent_name[$v['id']] = $v['name'];
                    $parent_id = $v['id'];
                    unset($data[$k]);
                    goto loop;
                }
            }

            loop:
            //已根据parent_id排序
            foreach($data as $k1 => $v1) {
                if($v1['parent_id'] == $parent_id) {
                    $v1['parent_name'] = $parent_name[$v1['parent_id']];
                    $menu[] = $v1;
                    unset($data[$k1]);
                    $loop_status = 1;
                }
                else{
                    if($loop_status === 1) {
                        break;
                    }
                }
            }
        }
        $this->assign('menu',$menu);
        return $this->fetch();
    }
}