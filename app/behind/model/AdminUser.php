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
}