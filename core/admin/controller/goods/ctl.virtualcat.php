<?php defined('CORE_DIR') || exit('入口错误'); ?>
<?php
include_once('objectPage.php');
class ctl_virtualcat extends objectPage{

    var $workground = 'goods';
    var $object = 'goods/virtualcat';
    var $finder_action_tpl = 'product/finder_action.html';
    var $finder_filter_tpl = 'product/finder_filter.html';
    var $allowImport = true;
    var $ioType = 'goods';

    function addNew($id=0){
        $this->path[] = array('text'=>__('商品虚拟分类新增'));
        $vobjCat = &$this->system->loadModel('goods/virtualcat');
        $objCat = &$this->system->loadModel('goods/productCat');
        $aCat = $vobjCat->get_virtualcat_list(true);
        $aCatNull[] = array('cat_id'=>0,'cat_name'=>__('----无----'),'step'=>1);
        if(empty($aCat)){
            $aCat = $aCatNull;
        }else{
            $aCat = array_merge($aCatNull, $aCat);
        }
        $this->pagedata['catList'] = $aCat;
        $this->pagedata['gtypes'] = $objCat->getTypeList();
        $oGtype = &$this->system->loadModel('goods/gtype');
        $this->pagedata['gtype']['status'] = $oGtype->checkDefined();
        if($id){
            $aCat = $this->model->instance($id);
            $this->pagedata['cat']['parent_id'] = $aCat['virtual_cat_id'];
            $this->pagedata['cat']['type_id'] = $aCat['type_id'];
        }else{
            $aTmp = $oGtype->getDefault();
            $this->pagedata['cat']['type_id'] = $aTmp[0]['type_id'];
        }
        $this->pagedata['cat']['p_order'] = 0;

        $this->page('product/virtualcat/info.html');
    }

    function doAdd(){
        $this->begin('index.php?ctl=goods/virtualcat&act=index');
        $objCat = &$this->system->loadModel('goods/virtualcat');
        $cat = $_POST['cat'];
        $cat['virtualcat_template'] = $_POST['virtualcat_template'];
        if($objCat->addNew($cat)){
            $this->end(true,__('保存成功'));
        }else{
            $this->end(false,__('保存失败'));

        }
    }
    function doImport(){

        if(!is_array($_POST['cat']) || empty($_POST['cat'])){
            $this->splash('failed', 'index.php?ctl=goods/virtualcat&act=import', __('请选择商品分类源节点'));
        }else{
            $this->begin('index.php?ctl=goods/virtualcat&act=index');
            foreach($_POST['cat'] as $key=>$v){
                if($v){
                    $search[]=$v;
                }
            }
            $objCat = &$this->system->loadModel('goods/virtualcat');
            $this->end($objCat->doImport($search,$_POST['vCat_id'],$_POST['defaultfilter']),__('保存成功'));
        }
    }
    function import(){
        $vobjCat = &$this->system->loadModel('goods/virtualcat');
        $aCat = $vobjCat->get_virtualcat_list(true);
        $objCat = &$this->system->loadModel('goods/productCat');
        $this->pagedata['tree']=$objCat->getMapTree(0,'');

        $aCatNull[] = array('cat_id'=>0,'cat_name'=>__('根目录'),'step'=>1);
        if(empty($aCat)){
            $aCat = $aCatNull;
        }else{
            $aCat = array_merge($aCatNull, $aCat);
        }
        $this->pagedata['catList'] = $aCat;
        $this->page('product/virtualcat/import.html');
    }

    function getGoodsCatById($cat_id=0){
        $vobjCat = &$this->system->loadModel('goods/virtualcat');
        echo json_encode($vobjCat->getGoodsCatById($cat_id));

    }


    function edit($catid){
        $this->path[] = array('text'=>__('商品虚拟分类编辑'));
        $vobjCat = &$this->system->loadModel('goods/virtualcat');
        $objCat = &$this->system->loadModel('goods/productCat');
        //$aCat = $vobjCat->getFieldById($catid);
        $aCat = $this->model->instance($catid);
        $this->pagedata['virtual_cat_name'] = $aCat['virtual_cat_name'];
        $aCat['addon'] = unserialize($aCat['addon']);
        $this->pagedata['cat'] = $aCat;
        $aCat = $vobjCat->get_virtualcat_list(true);
        $aCatNull[] = array('cat_id'=>0,'cat_name'=>__('----无----'),'step'=>1);
        $aCat = array_merge($aCatNull, $aCat);
        $this->pagedata['catList'] = $aCat;
        $this->pagedata['gtypes'] = $objCat->getTypeList();
        $oGtype = &$this->system->loadModel('goods/gtype');
        $this->pagedata['gtype']['status'] = $oGtype->checkDefined();

        $this->page('product/virtualcat/info.html');
    }




    function index(){
        $objCat = &$this->system->loadModel('goods/virtualcat');
        if($objCat->checkTreeSize()){
            $this->pagedata['hidenplus']=true;
        }

        $tree=$objCat->get_virtualcat_list();
        $this->pagedata['tree_number']=count($tree);
        foreach($tree as $k=>$v){
           parse_str($v['filter'],$filter);
           $link = array();
           foreach($filter as $n=>$f){
               if($n=='pricefrom'&&!$f){
                   $link[$n] = array('v'=>0,'t'=>$v['cat_name']);
               }
               if($f){
                    //(is_array($f)?implode(',',$f):$f)
                   $link[$n] = array('v'=>$f,'t'=>$v['cat_name']);
               }
           }
           $tree[$k]['link'] = $link;
        }
        $this->pagedata['tree']=&$tree;
        $depath=array_fill(0,$objCat->get_virtualcat_depth(),'1');
        $this->pagedata['depath']=$depath;
        $this->page('product/virtualcat/map.html');

    }

    function getChildCat($pid){
        $pid = $_POST['pid'];
        $objType=&$this->system->loadModel('goods/virtualcat');
        $this->pagedata['tree']=$objType->getTreeList($pid, false);
        $this->display('product/virtualcat/rows.html');
    }


    function toRemove($id){

        $this->begin('index.php?ctl=goods/virtualcat&act=index');
        $objType=&$this->system->loadModel('goods/virtualcat');
        $this->end($objType->toRemove($id),__('分类删除成功'));
    }

    function update(){

        $this->begin('index.php?ctl=goods/virtualcat&act=index');
        $objType=&$this->system->loadModel('goods/virtualcat');
        $this->end($objType->updateOrder($_POST['p_order']), __('更新成功'));
    }
}
?>