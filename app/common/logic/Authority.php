<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/12/8
 * Time: 10:50
 */

namespace app\common\logic;


use think\Db;

class Authority
{
    /**
     * 判断被操作角色是否是操作者的下级
     */
    static function is_role_parent(int $parent_id,int $role_id)
    {
        if($parent_id === $role_id) {
            return true;
        }
        $data = Db::name('admin_role')->order('parent_id')->select();
        return self::find_parent((array)$data,$parent_id,$role_id,'id');
    }

    /**
     * 判断被操作者是否是操作角色的下级
     */
    static function is_user_parent(int $user_id,$role_id)
    {
        $parent_id = Db::name('admin_user')->alias('u')->join('admin_role r','u.role_id=r.id')->where(['u.id'=>$user_id])->value('parent_id');
        if($parent_id == false) {
            return false;
        }
        if($parent_id == $role_id) {
            return true;
        }
        $data = Db::name('admin_role')->order('parent_id')->select();
        return self::find_parent((array)$data,$parent_id,$role_id,'id');
    }

    /**
     * 判断parent_id的父辈元素中是否含有id元素是否为查找元素的父元素 -- find_key正向排序性能更好
     */
    private static function find_parent(array $data,int $parent_id,int $id,string $find_key)
    {
        if($parent_id == $id) {
            return true;
        }
        $sort = function($data,$parent_id) use ($find_key,$id,&$sort) {
            if($parent_id == $id) {
                return true;
            }
            if($parent_id == 0) {
                return false;
            }
            foreach($data as $v) {
                if($v[$find_key] == $parent_id) {
                    return $sort($data,$v['parent_id']);
                }
            }
            return false;
        };
        return $sort($data,$parent_id);
    }
}