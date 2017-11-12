<?php
namespace app\common\controller;
use \think\Controller;
use \think\Db;
use \app\common\exception\ErrorException;

class CBase extends Controller
{
	protected $error_msg;
	protected $error_page;
	public function _initialize()
	{
		parent::_initialize();
		$this->error_page = config('ERROR_PAGE')??'public/error';
		if(!$this->checkConfig()) {
			$this->assign('error_msg', $this->error_msg);
		}

	}

	private function checkConfig()
	{
		$sql = 'show1 databases';
		try{
			Db::query($sql);
			return true;
		}
		catch(\think\Exception $e) {
			$this->error_msg = '数据库配置错误';
			abort(500,'数据库配置错误!');
			exit;
		}
	}
}