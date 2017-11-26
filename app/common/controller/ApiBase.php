<?php
namespace app\common\controller;
use \think\Controller;
use \think\Request;

class ApiBase extends Controller
{
	public function _initialize()
	{
		$request = Request::instance();
		$module = $request->module();
		$contro = $request->controller();
		$action = $request->action();
		dump($module);
		dump($contro);
		dump($action);
	}
}