<?php
namespace app\behind\controller;

class Index extends Base
{
    public function index()
    {
    	return $this->fetch();
    }
}
