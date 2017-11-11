<?php
namespace app\behind\controller;

class Index extends Base
{
    public function index()
    {
    	$this->assign('base',11);
    	return $this->fetch();
    }
}
