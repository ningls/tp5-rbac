<?php
namespace app\common\controller;

class ErrorCode
{
	const error = [
	    0 => 'SUCCESS',
	    9001 => '手机号格式错误',
        9002 => '验证码格式错误',
        9003 => '验证码错误',
        9004 => '不存在手机号为该号码的管理员',
        9005 => '用户名或密码错误',
        9006 => '该管理员已被禁用',
        9007 => '该管理员已被删除'
    ];

	const admin_status = [
        1 => 9006,
        9 => 9007,
    ];
}
