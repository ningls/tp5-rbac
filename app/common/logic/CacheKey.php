<?php
namespace app\common\logic;
use \think\Cache;
/**
* 缓存建名
*/
class CacheKey
{
	//===========================================hebind============================================
	const BEHIND_CACHE = [
		'menu_list' => 'menu_list',
	];

	//session 对照
	private $session_key_value = [
		'user' => '用户信息',
		'auth' => '权限信息'
	];
}