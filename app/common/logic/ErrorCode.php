<?php
namespace app\common\logic;

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
        9007 => '该管理员已被删除',
        9008 => 'create_auth.sql文件不存在！',
        9009 => '执行create_auth.sql失败！'
    ];

	//管理员状态对应错误码
	const admin_status = [
        1 => 9006,
        9 => 9007,
    ];
}
