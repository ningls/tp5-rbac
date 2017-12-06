<?php
namespace app\behind\controller;
use app\behind\model\AdminMenu;
use \think\Request;
use \app\behind\model\AdminLog;
use \app\behind\model\AdminUser;
use \app\common\logic\StatusCode;

class System extends Base
{
    /**
     * 系统配置
     */
	public function config()
    {

    }

    /**
     * 行为日志
     */
    public function log(Request $request):string
    {
        $model = new adminLog();
        $start = $request->get('start_time','');
        $end = $request->get('end_time','');
        $admin_id = $request->get('admin_id',0,'intval');
        $url = $request->get('url','');
        $where = [];
        if($start !== '') {
            $where['view_at'] = ['egt',strtotime($start)];
        }
        if($end !== '') {
            $where['view_at'] = ['elt',strtotime($end) + 86400];
        }
        if($admin_id !== 0) {
            $where['admin_id'] = $admin_id;
        }
        if($url !== '') {
            $where['view_url'] = $url;
        }
        $page = $request->get('number_page',0,'intval')??$this->global_setting['page_limit'];
        $data = $model->get_log($page, $where);

        $user_model = new AdminUser();
        $user_data = $user_model->get_user_id_and_name();
        $menu_model = new AdminMenu();
        $menu_data = $menu_model->show_son_menus();
        foreach($menu_data as $k => $v) {
            if($v['status'] != 0) {
                $menu_data[$k]['name'] .= StatusCode::menu_status[$v['status']];
            }
        }
        foreach($user_data as $k => $v) {
            if($v['status'] != 0) {
                $user_data[$k]['admin_name'] .= StatusCode::admin_user_status[$v['status']];
            }
        }
        $this->assign([
            'user_data' => $user_data,
            'log_data' => $data,
            'menu_data' => $menu_data,
            'page' => $data->render(),
            'where' => [
                'start_time' => $start,
                'end_time' => $end,
                'admin_id' => $admin_id,
                'view_url' => $url,
                'page' => $page
            ],
            'log_open'=>$this->global_setting['log_open']
        ]);
        fetch:
        return $this->fetch();

    }
}