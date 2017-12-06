<?php
namespace app\behind\model;
use \think\Model;

class AdminLog extends Model
{
	/**
	* 获取日志信息
	*/
	public function get_log($limit, $where = [])
	{
		$field = 'l.*,u.admin_name';
	
		return $this->alias('l')->field($field)->join('admin_user u','l.admin_id = u.id')->where($where)->order('l.id desc')->paginate($limit,false,[
			'query'=>request()->param()
		]);		
	}
}