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
    public function get_role()
    {
        $field = 'r.*,u.admin_name';
        return $this->alias('r')->field($field)->join('admin_user u','r.create_user_id=u.id')->order('id desc')->select();
    }
}