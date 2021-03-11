<?php


namespace app\api\controller;


use app\BaseController;
use app\model\ArticleArticle;
use think\facade\App;

class Sitemap extends Base
{
    protected $books;
    protected function initialize()
    {
        parent::initialize(); // TODO: Change the autogenerated stub
        $end_point = config('seo.end_point');
        $num = config('seo.sitemap_gen_num');
        if ($num <= 0) {
            $this->books = ArticleArticle::select();
        } else {
            $this->books = ArticleArticle::order('articleid', 'desc')->limit($num)->select();
        }
        foreach ($this->books as &$book) {
            if ($end_point == 'id') {
                $book['param'] = $book['articleid'];
            } else {
                $book['param'] = $book['backupname'];
            }
        }
    }
    public function index()
    {
        $pc_array = $this->create_array('pc');
        $m_array = $this->create_array('m');
        $mip_array = $this->create_array('mip');

        $this->gensitemap($pc_array, 'pc');
        $this->gensitemap($m_array, 'm');
        $this->gensitemap($mip_array, 'mip');

        $this->genurls('pc');
        $this->genurls('m');
        $this->genurls('mip');
        return json([
            'success' => 1,
            'url' => [
                "/sitemap_pc.xml",
                "/sitemap_m.xml",
                "/sitemap_mip.xml"
            ]
        ]);
    }

    private function gensitemap($array, $name) {
        $content = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<urlset>\n";
        foreach ($array as $data) {
            $content .= $this->create_item($data);
        }
        $content .= '</urlset>';
        $fp = fopen(App::getRootPath() .'public/sitemap_'.$name.'.xml', 'w+');
        fwrite($fp, $content);
        fclose($fp);
    }

    private function genurls($option) {
        if ($option == 'pc') {
            $site_name = config('site.domain');
        } else if ($option == 'm') {
            $site_name = config('site.mobile_domain');
        } else {
            $site_name = config('site.mip_domain');
        }
        $urls = '';
        foreach ($this->books  as $key => $book) {
            $urls .= $site_name.'/'.BOOKCTRL.'/'.$book->id."\n";
            $fp = fopen(App::getRootPath() .'public/'.$option.'.txt', 'w+');
            fwrite($fp, $urls);
        }
    }

    private function create_array($option){
        if ($option == 'pc') {
            $site_name = config('site.domain');
        } else if ($option == 'm') {
            $site_name = config('site.mobile_domain');
        } else {
            $site_name = config('site.mip_domain');
        }

        $data = array();
        $main = array(
            'loc' => $site_name,
            'priority' => '1.0'
        );
        $booklist= array(
            'loc' => $site_name.'/booklist',
            'priority' => '0.5',
            'lastmod' => date("Y-m-d"),
            'changefreq' => 'yearly'
        );


        foreach ($this->books  as $key => $book){ //这里构建所有的内容页数组
            $temp = array(
                'loc' => $site_name.'/'.BOOKCTRL.'/'.$book['param'],
                'priority' => '0.9',
            );
            array_push( $data,$temp);
        }

        array_push($data,$main);
        array_push($data,$booklist);
        return $data;
    }

    private function create_item($data)
    {
        $item = "<url>\n";
        $item .= "<loc>" . $data['loc'] . "</loc>\n";
        $item .= "<priority>" . $data['priority'] . "</priority>\n";
        $item .= "</url>\n";
        return $item;
    }
}