<?php
namespace app\behind\model;

use think\Model;

class AdminMenu extends Model
{
    /**
     * 显示所有的菜单
     * @param bool $show_del_menu
     * @return object
     */
    public function show_menus($show_del_menu = true)
    {
        if($show_del_menu) {
            $where = ' and status <> 9';
        }
        else {
            $where = '';
        }
        $prefix = config('database.prefix');
        $sql = "select * from {$prefix}admin_menu where parent_id in (select id from {$prefix}admin_menu where parent_id = 0) or parent_id = 0 {$where} order by parent_id asc,sort asc";
        return $this->query($sql);
    }

    /**
     * 显示所有非父菜单
     * 系统访问日志调用
     * @return mixed
     */
    public function show_son_menus()
    {
        $where['parent_id'] = ['neq',0];
        return $this->where($where)->order('status')->select();
    }

    /**
     * 设置status
     * @param $id
     * @param $status
     * @param $code
     * @return int
     */
    public function set_menu_status($id,$status,$code)
    {
        try{
            return $this->isUpdate()->save(['status'=>$status],['id'=>$id])?0:9017;
        }catch(\PDOException $e){
            return $code;
        }
    }

    /**
     * 更新菜单信息
     * @param $data
     * @param $id
     * @param $code
     * @return int
     */
    public function update_menu($data,$id,$code)
    {
        try{
            return $this->isUpdate()->save($data,['id'=>$id])?0:9017;
        }catch(\PDOException $e){
            return $code;
        }
    }

}