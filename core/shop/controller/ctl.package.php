<?php defined('CORE_DIR') || exit('入口错误'); ?>
<?php
class ctl_package extends shopPage{
    var $customer_template_type='package';
    function index($page=1) {
        $objPackage = &$this->system->loadModel('trading/package');
        $aData = $objPackage->getPackageList($page-1);
        $this->pagedata['package'] = $aData['data'];
        $sitemapts=&$this->system->loadModel('content/sitemap');
        $title=$sitemapts->getTitleByAction('package:index');
        $title=$title['title']?$title['title']:__('捆绑促销');

        $this->path[]=array('title'=>$title);
        $this->title = $title;
        $this->pagedata['package'] = $aData['data'];

        if($this->system->getConf('site.show_mark_price')){
            $setting['mktprice'] = $this->system->getConf('site.market_price');
        }else{
            $setting['mktprice'] = 0;
        }
        $setting['saveprice'] = $this->system->getConf('site.save_price');
        $setting['buytarget'] = $this->system->getConf('site.buy.target');
        $this->pagedata['setting'] = $setting;
        $this->pagedata['pager'] = array(
                'current'=> $page,
                'total'=> $aData['page'],
                'link'=> $this->system->mkUrl('package','index', array($tmp = time())),
                'token'=> $tmp);

        $this->output();
    }
}
?>