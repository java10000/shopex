<?php defined('CORE_DIR') || exit('入口错误');

class ctl_search extends shopPage{
    function index(){
        $objBrand = &$this->system->loadModel('goods/brand');
        $this->pagedata['brand'] = $objBrand->getAll();
        $objCat = &$this->system->loadModel('goods/productCat');
        $this->pagedata['categorys'] = $objCat->get_cat_list();
        $this->pagedata['args'] = array($cat_id,$filter,$orderBy,$tab,$page);
        $this->output();
    }

    function result(){
        $oSearch = &$this->system->loadModel('goods/search');

        $cat_id = $_POST['cat_id'];
        
        foreach($_POST as $k=>$v) {
            switch ( true ) {
                case 'bn' == $k || 'name' == $k:
                    $_POST[$k][0] = addslashes(trim($_POST[$k][0])); break;
                case 'price' == $k:
                    $_POST[$k][0] = floatval($_POST[$k][0]);
                    $_POST[$k][1] = floatval($_POST[$k][1]);
                    break;
            }
        }

        if($filter = $oSearch->decode($_POST['filter'],$path)){
            $filter = array_merge($filter,$_POST);
        }else{
            $filter = $_POST;
        }
        
        header('Location: '.$this->system->mkUrl('gallery',$this->system->getConf('gallery.default_view'),array($cat_id,$oSearch->encode($filter))));
        exit;
    }

    function showCat(){
        $objCat = &$this->system->loadModel('goods/productCat');
        $this->pagedata['cat'] = $objCat->get($_POST['cat_id']);
        $this->__tmpl = 'search/showCat.html';
        $this->output();
    }
}
