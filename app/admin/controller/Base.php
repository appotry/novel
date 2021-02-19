<?php


namespace app\admin\controller;


use app\BaseController;
use think\facade\Env;
use think\facade\View;

class Base extends BaseController
{
    protected $prefix;
    protected $img_site;
    protected $end_point;
    protected $jieqi_ver;

    protected function initialize()
    {
        parent::initialize(); // TODO: Change the autogenerated stub
        if (session('xwx_admin') == null) {
            $this->redirect(url('login/login'));
        }
        $this->prefix = Env::get('database.prefix');
        $this->img_site = config('site.img_site');
        $this->end_point = config('seo.book_end_point');
        $this->jieqi_ver = floatval(config('site.jieqi_ver'));
        View::assign([
            'prefix' => $this->prefix,
            'admin' => cookie('xwx_admin'),
            'cdn' => config('site.cdn'),
            'url' => config('site.domain'),
        ]);
    }
}