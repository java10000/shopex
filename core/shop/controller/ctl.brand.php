<?php defined('CORE_DIR') || exit('入口错误'); ?>
<?php
class ctl_brand extends shopPage{
    var $seoTag=array('shopname','brand');
    function showList($page=1){
        $this->customer_template_type = 'brandlist';
        $this->title    = $this->system->getConf('site.brand_index_title');
        $this->keywords = $this->system->getConf('site.brand_index_meta_key_words');
        $this->desc     = $this->system->getConf('site.brand_index_meta_desc');
        $pageLimit = 24;
        $oGoods=&$this->system->loadModel('goods/brand');
        $result=$oGoods->getList('*', '',($page-1)*$pageLimit,$pageLimit);
        $brandCount = $oGoods->count();

        $oSearch = &$this->system->loadModel('goods/search');
        foreach($result as $k=>$v){
            $result[$k]['link']=$this->system->mkUrl('gallery','index',array('',$oSearch->encode(array('brand_id'=>array($v['brand_id'])))));
        }

        $sitemapts=&$this->system->loadModel('content/sitemap');
        $title=$sitemapts->getTitleByAction('brand:showList');
        $title=$title['title']?$title['title']:__('品牌');
        $this->path[]=array('title'=>$title);
        $this->pagedata['pager'] = array(
            'current'=>$page,
            'total'=>ceil($brandCount/$pageLimit),
            'link'=>$this->system->mkUrl('brand','showList',array(($tmp = time()))),
            'token'=>$tmp);
        if($page > $this->pagedata['pager']['total']){
            trigger_error(__('查询数为空'),E_USER_NOTICE);
        }
        $this->pagedata['data'] = $result;
        $this->getGlobal($this->seoTag,$this->pagedata,1);
        $this->output();
    }

    function index($brand_id, $page=1) {
        $oseo = &$this->system->loadModel('system/seo');
        $seo_info=$oseo->get_seo('brand',$brand_id);

        $this->title    = $seo_info['title']?$seo_info['title']:$this->system->getConf('site.brand_list_title');
        $this->keywords = $seo_info['keywords']?$seo_info['keywords']:$this->system->getConf('site.brand_list_meta_key_words');
        $this->desc     = $seo_info['descript']?$seo_info['descript']:$this->system->getConf('site.brand_list_meta_desc');
        $oGoods=&$this->system->loadModel('goods/brand');
        $argu=array("brand_id","brand_name","brand_url","brand_desc","brand_logo");
        $result= $oGoods->getFieldById($brand_id,$argu);
        $this->pagedata['data'] = $result;

        $this->path[] = array('title'=>__('品牌专区'),'link'=>$this->system->mkUrl('brand','showList'));
        $this->path[] = array('title'=>$result['brand_name']);
        $this->customer_template_type = 'brand';
        $this->customer_template_id = $brand_id;
        $view = $this->system->getConf('gallery.default_view');
        if($view=='index') $view='list';
        $this->pagedata['view'] = 'gallery/type/'.$view.'.html';

        $objGoods  = &$this->system->loadModel('goods/products');
        $filter = array();
        if($GLOBALS['runtime']['member_lv']){
            $filter['mlevel'] = $GLOBALS['runtime']['member_lv'];
        }
        $filter['brand_id'] = $brand_id;
        $filter['marketable'] = 'true';

        $pageLimit = 20;
        $start = ($page-1)*$pageLimit;

        $aProduct  = $objGoods->getList(null,$filter,$start,$pageLimit);

        $productCount = $objGoods->count($filter);
        $this->pagedata['count'] = $productCount;

        $this->pagedata['pager'] = array(
            'current'=>$page,
            'total'=>ceil($productCount/$pageLimit),
            'link'=>$this->system->mkUrl('brand','index',array($brand_id,$tmp=time())),
            'token'=>$tmp);
        if($page > $this->pagedata['pager']['total']){
            trigger_error(__('查询数为空'),E_USER_NOTICE);
        }

        if(is_array($aProduct) && count($aProduct) > 0){
            $objGoods->getSparePrice($aProduct, $GLOBALS['runtime']['member_lv']);
            $setting['mktprice'] = $this->system->getConf('site.market_price');
            $setting['saveprice'] = $this->system->getConf('site.save_price');
            $setting['buytarget'] = $this->system->getConf('site.buy.target');
            $this->pagedata['setting'] = $setting;
            $this->pagedata['products'] = $aProduct;
        }

        $oSearch = &$this->system->loadModel('goods/search');
        $this->pagedata['link'] = $this->system->mkUrl('gallery',$this->system->getConf('gallery.default_view'),array('',$oSearch->encode(array('brand_id'=>array($brand_id)))));
        $this->getGlobal(array('shopname','brand','goods_amount','brand_intro','brand_kw'),$this->pagedata,0);
        $this->output();
    }
    function get_brand(&$result,$list=0){
        if($list){
            foreach($result['data'] as $k => $v)
                $brandName[]=$v['brand_name'];
            return implode(",",$brandName);
        }else{
            return $result['data']['brand_name'];
        }
    }
    function get_goods_amount(&$result,$list=0){
        return $result['count'];
    }
    function get_brand_intro(&$result,$list=0){
        $brand_desc=preg_split('/(<[^<>]+>)/',$result['data']['brand_desc'],-1);
        if (strlen($brand_desc)>50)
            $brand_desc=substr($brand_desc,0,50);
        return $result['data']['brand_desc'];
    }
    function get_brand_kw(&$result,$list=0){
        $brand = $this->system->loadModel('goods/brand');
        $row=$brand->instance($result['data']['brand_id'],'brand_keywords');
        return $row['brand_keywords'];
    }
}
?>