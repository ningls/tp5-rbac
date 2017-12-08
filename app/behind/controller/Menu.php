<?php
namespace app\behind\controller;
use app\behind\model\AdminMenu as MenuModel;
use app\common\logic\ErrorCode;
use app\common\logic\StatusCode;
use app\common\logic\CacheKey;
use think\Db;
use think\Request;
use think\Session;

/**
 * Class Menu
 * 菜单管理
 * @package app\behind\controller
 */
class Menu extends Base
{
    public function index()
    {
        if($menu = cache(CacheKey::BEHIND_CACHE['menu_tree'])) goto assign;
        $menu = $this->cache_tree_menu();
        assign:
        foreach($menu as $k => $v) {
            if($v['parent_id'] != 0) {
                $menu[$k]['name'] = '|' . str_repeat('------',(int)$v['level']) . $v['name'];
            }
            $menu[$k]['status_name'] = StatusCode::menu_status[$v['status']];
        }
        $this->assign('menu',$menu);
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
            $id = 0;
            if( $data['name'] == false && ($this->code = 9010) || $data['url'] == false && ($this->code = 9011) ) {
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
     * 编辑菜单
     */
    public function edit_menu(Request $request)
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
            if( $data['name'] == false && ($this->code = 9010) || $data['url'] == false && ($this->code = 9011) ) {
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
     * 禁用/激活菜单
     */
    public function disable_menu()
    {
        $id = request()->param('id',0,'intval');
        $status = request()->param('status',0,'intval');
        if(!$id || !in_array($status,[0,1])) {
            $this->code = 9013;
            goto res;
        }
        $set_status = $status?0:1;
        $model = new MenuModel();
        ($this->code = $model->set_menu_status($id,$set_status,9014)) || $this->reflash_menu();
        res:
        return json(['code'=>$this->code,'msg'=>ErrorCode::error[$this->code]]);
    }

    /**
     * 删除菜单
     */
    public function del_menu()
    {
        $id = request()->param('id',0,'intval');
        if(!$id) {
            $this->code = 9013;
            goto res;
        }
        $model = new MenuModel();
        ($this->code = $model->set_menu_status($id,9,9015)) || $this->reflash_menu();
        res:
        return json(['code'=>$this->code,'msg'=>ErrorCode::error[$this->code]]);
    }

    protected function reflash_menu()
    {
        $this->cache_menu();
        $this->cache_tree_menu();
        Session::delete('menu');
    }

    /**
    * 缓存菜单
    */
    protected function cache_menu()
    {
        $model = new MenuModel();
        $data = $model->show_menus($this->global_setting['show_del_menu']);
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

    /**
     * 缓存菜单树
     */
    protected function cache_tree_menu()
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