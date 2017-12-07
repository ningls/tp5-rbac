<?php

namespace app\behind\model;
use think\Model;

class AdminUser extends Model
{
	/**
	* 获取用户id与角色名
	*/
    public function get_admin_user($limit = 15,$where = [])
    {
        $field = 'u.id,u.admin_user,u.admin_name,u.admin_phone,u.last_login,u.status,u.add_time,r.role_name,uu.admin_name as create_user_name';
        return $this->alias('u')->field($field)->join('admin_role r','u.role_id=r.role_id')->join('admin_user uu','u.create_user_id=uu.id')->order('id desc')->paginate($limit);
    }

    /**
    * 获取所有角色id，name，状态
    */
    public function get_user_id_and_name()
    {
    	return $this->field('id,admin_name,status')->order('status asc')->select();
    }

    /**
     * 获取用户角色分组信息
     */
    public function get_group_user($where = [])
    {
        $field = 'u.id,u.admin_user,u.role_id,u.admin_name,u.admin_phone,u.last_login,u.status,u.add_time,r.role_name,r.parent_id,uu.admin_name as create_user_name';
        return $this->alias('u')->field($field)->join('admin_role r','u.role_id=r.id')->join('admin_user uu','u.create_user_id=uu.id')->where($where)->order('u.role_id asc')->paginate();
    }

    /**
     * 设置status
     * @param $id
     * @param $status
     * @param $code
     * @return int
     */
    public function set_user_status($id,$status,$code)
    {
        try{
            return $this->isUpdate()->save(['status'=>$status],['id'=>$id])?0:9017;
        }catch(\PDOException $e){
            return $code;
        }
    }

    /**
     * 更新用户信息
     * @param $data
     * @param $id
     * @param $code
     * @return int
     */
    public function update_user($data,$id,$code)
    {
        try{
            return $this->isUpdate()->save($data,['id'=>$id])?0:9017;
        }catch(\PDOException $e){
            return $code;
        }
    }
}