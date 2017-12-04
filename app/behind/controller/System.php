<?php
namespace app\behind\controller;
use \think\Request;
use \app\behind\model\Adminlog;
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
        $page = $request->get('number_page',0,'intval')??$this->global_setting['page_limit'];
        $data = $model->get_log($page, $where);

        $user_model = new AdminUser();
        $user_data = $user_model->get_user_id_and_name();
        foreach($user_data as $k => $v) {
            if($v['status'] != 0) {
                $user_data[$k]['admin_name'] .= StatusCode::admin_user_status[$v['status']];
            }
        }
        
        $this->assign([
            'user_data' => $user_data,
            'log_data' => $data,
            'page' => $data->render()
        ]);
        return $this->fetch();

    }
}