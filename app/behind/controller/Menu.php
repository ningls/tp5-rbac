<?php
namespace app\behind\controller;
use \app\behind\model\AdminMenu as MenuModel;
use \app\common\logic\ErrorCode;
use  \app\common\logic\StatusCode;
use  \app\common\logic\CacheKey;
use \think\Db;

/**
 * Class Menu
 * 菜单管理
 * @package app\behind\controller
 */
class Menu extends Base
{
    public function index()
    {
        if($menu = cache(CacheKey::BEHIND_CACHE['menu_list'])) goto assign;
        $menu = $this->cache_menu();
        assign:
        $this->assign('menu',$menu);
        dump($menu);
        return $this->fetch();
    }

    /**
    * 新增菜单
    */
    public function add_menu(Request $request)
    {
        if($request->isAjax()) {
            $data['name'] = $request->post('name','','htmlspecialchars');
            $data['url'] = strtolower($request->post('url',''));
            $data['sort'] = $request->post('sort',0,'intval');
            $data['parent_id'] = $request->post('parent_id',0,'intval');
            if( $data['name'] == false && $code = 9010 || $data['url'] == false && $code = 9011 || !preg_match('/[\w]+\/[\w]+/',$data['url']) && $code = 9012 ) {
                goto res;
            }
            $data['add_time'] = time();
            try{
                Db::name('menu')->insert($data);
                $code = 0;
            }
            catch(\PDOException $e) {
                $code = 9999;
            }
            res:
            return json(['code'=>$code,'msg'=>ErrorCode::error[$code]]);
        }
        else {
            $menu = cache(CacheKey::BEHIND_CACHE['menu_list']);
            foreach($menu as $k => $v) {
                if($v['parent_id'] == 0) {
                    $menu[$k]['name'] .= '-';
                }
                else {
                    $menu[$k]['name'] .= '---';
                }
            }
            $this->assign('menu',$menu);
            return $this->fetch();
        }
    }

    /**
    * 缓存菜单
    */
    protected function cache_menu()
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
                        $loop_status = 0;
                        break;
                    }
                }
            }
        }
        cache(CacheKey::BEHIND_CACHE['menu_list'], $menu);
        return $menu;
    }
}