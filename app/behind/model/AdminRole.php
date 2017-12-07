<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/12/1
 * Time: 17:21
 */

namespace app\behind\model;
use think\Model;

class AdminRole extends Model
{
    public function get_role($where =[])
    {
        $field = 'r.*,u.admin_name';
        return $this->alias('r')->field($field)->where($where)->join('admin_user u','r.create_user_id=u.id')->order('id asc')->paginate();
    }

    /**
     * 设置status
     * @param $id
     * @param $status
     * @param $code
     * @return int
     */
    public function set_role_status($id,$status,$code)
    {
        try{
            return $this->isUpdate()->save(['status'=>$status],['id'=>$id])?0:9017;
        }catch(\PDOException $e){
            return $code;
        }
    }

    /**
     * 更新角色信息
     * @param $data
     * @param $id
     * @param $code
     * @return int
     */
    public function update_role($data,$id,$code)
    {
        try{
            return $this->isUpdate()->save($data,['id'=>$id])?0:9017;
        }catch(\PDOException $e){
            return $code;
        }
    }
}