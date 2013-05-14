<?php defined('CORE_DIR') || exit('入口错误'); ?>
<?php
include_once('objectPage.php');
class ctl_product extends objectPage{

    var $workground = 'goods';
    var $object = 'goods/products';
    var $allowImport = true;
    var $ioType = 'goods';
    var $finder_default_cols = '_cmd,bn,name,cat_id,price,store,marketable,brand_id,weight,d_order,type_id,mktprice';
    var $finder_action_tpl = 'product/finder_action.html';
    var $finder_filter_tpl = 'product/finder_filter.html';
    var $simpleGoodsId = 1;

    function goodsAlert(){
        $params['list_type']= array('v'=>'lack','t'=>'商品库存报警');
        $_GET['filter'] = serialize( $params );
        parent::index();
    }

    function __construct() {
        parent::__construct();
        // 商品雷达 机制要改变
        $appmgrMdl =& $this->system->loadModel('system/appmgr');
        $radarApp =& $appmgrMdl->load('commodity_radar');
        if ( $radarApp->is_active ) {
            $this->finder_default_cols = str_replace('_cmd,','_cmd,_commodity_radar,',$this->finder_default_cols);
        }
    }
    /**
     * 注:查看成本同步的商品(定价),故重载了父类方法 2009-11-17 16:14 wubin
     *
     */
    function index() {
        if($_GET['filter']){
            $cat_id=unserialize($_GET['filter']);
            $this->pagedata['cat_id']=$cat_id['cat_id']['v'];
        }
        if($_GET['supplier_id']) {
            $this->filter['supplier_id'] = $_GET['supplier_id'];
        }
        if($_GET['costsync']) {
            $this->filter['costsync'] = 1;
        }
        parent::index();
    }

    function _detail(){
        return array('show_detail'=>array('label'=>__('商品详细信息'),'tpl'=>'product/detail.html'));
    }

    function save_cell_value($id,$key){
        if( $key == 'bn' ){
            $oGoods = &$this->system->loadModel('trading/goods');
            $gnum = $oGoods->checkGoodsValid( $d = array( 'bn' => $_POST['data'],'goods_id'=>$id ) );
            if( $gnum > 0 ){
                return;
            }
        }else if( $key == 'name' ){
            if(!empty($_POST['data'])){
                $oPro = &$this->system->loadModel('goods/products');
                $oPro->setProNameByGoodsId($id,$_POST['data']);
            }else{
                echo "商品名称不能为空";exit;
            }
        }
        parent::save_cell_value($id,$key);
     }

    function show_detail($gid){
      
        $o = &$this->system->loadModel('trading/goods');
        $goods=$o->getFieldById($gid, array('thumbnail_pic','disabled','marketable','rank_count','view_count','view_w_count','buy_count','buy_w_count','count_stat'));
        $this->pagedata['goods'] = &$goods;
        $this->pagedata['is_pub'] = ($goods['marketable']!='false' && $goods['disabled']!='true');
        $this->pagedata['url'] = $this->system->realUrl('product','index',array($gid),null);

        $this->pagedata['buy_w_count'] = $goods['buy_w_count'];
        $this->pagedata['view_w_count'] = $goods['view_w_count'];

        $this->pagedata['buy_count'] = $goods['buy_count'];
        $this->pagedata['view_count'] = $goods['view_count'];

        $this->pagedata['status'] = unserialize($goods['count_stat']);

        $today = day(time());
        $view_chardata=array();
        $buy_chardata=array();

        foreach(range($today-14,$today) as $day){
            $view_chardata[$day]=intval($this->pagedata['status']['view'][$day]);
            $buy_chardata[$day]=intval($this->pagedata['status']['buy'][$day]);
        }
        $this->pagedata['view_chart'] = $this->_linechart($view_chardata);
        $this->pagedata['buy_chart'] = $this->_linechart($buy_chardata);
    }

    function _linechart($chardata){

        $max = max(10,intval(max($chardata)*1.25));

        $w = 300;
        $y = array();
        $xday = array();
        $i=0;
        $xmonty = array();
        $lastMonth = null;
        $count = count($chardata);
        $xmark=array('B,76A4FB,0,0,0');
        $color_1 = '000099';

        foreach($chardata as $d=>$v){

            $d = $d*3600*24;

            if($lastMonth!=($month=date('Y.m',$d))){
                $lastMonth = $month;
                $xmonth[intval($i*(100/$count))] = $month;
                if($i>0)$xmark[] = 'V,'.$color_1.',0,'.$i.',1';
            }
            $i++;
            $xday[] = date('d',$d);
        }
        return 'chs='.$w.'x100&chd=t:'.implode(',',$chardata).
        '&chxt=x,y,x,r&chco=224499&chxl=0:|'.implode('|',$xday).'|1:|0|'.round($max/2).'|'.$max.'|2:|'.implode('|',$xmonth).
        '|3:|0|'.round($max/2).'|'.$max.'&cht=lc&chds=0,'.$max.'&chxp=2,'.implode(',',array_keys($xmonth)).'&chxs=2,'.$color_1.',13&chm='.implode('|',$xmark).'&chg=5,25,1';
    }

    function showfilter($type_id){
      
        $obj = &$this->system->loadModel('goods/products');
        $this->pagedata['filter'] = $obj->getFilterByTypeId(array('type_id'=>$type_id));
        $this->pagedata['filter_interzone'] = $_POST;
        $this->pagedata['view'] = $_POST['view'];
        $this->display('product/filter_addon.html');
    }

    function nospec($cat_id=0){
        $this->_editor($_POST['type_id']);
        $this->display('product/nospec.html');
    }

    function taskman(){
       
        $this->pagedata['items'] = &$_POST['scheduled'];
        $this->display('product/detail/taskman.html');
    }

    function taskrows(){
        $items = $_POST['scheduled'];
        if(!$_POST['time']){
            $this->pagedata['message'] = '您还没有选择执行日期，请选择后添加';
        }elseif($_POST['time']>time()){
            $items[$_POST['time']] = trim($_POST['mode']);
            ksort($items);
        }else{
            $this->pagedata['message'] = '请输入将来的时间';
        }
        $this->pagedata['items'] = &$items;
        $this->display('product/detail/taskrows.html');
    }

    function recycle(){

        parent::recycle();
        $objProduct = &$this->system->loadModel('goods/products');
       
            $objProduct->setDisabled($_POST['goods_id'],'true');
    
        
    }

    function active(){
        parent::active();
        $objProduct = &$this->system->loadModel('goods/products');
        $objProduct->setDisabled($_POST['goods_id'], 'false');
    }

    function delete(){
        $objPdt = &$this->system->loadModel('goods/products');
        $aId = array();
        $objPdt->disabledMark = 'recycle';
        $aGoods = $objPdt->getList('goods_id', $_POST, 0, -1);
        foreach($aGoods as $row){
            $aId[] = $row['goods_id'];
        }
        $objGoods = &$this->system->loadModel('trading/goods');
        foreach((array)$aId as $id){
            $objGoods->toRemove($id);
        }
        echo __('清除完成！');
    }

    //新增商品页面ctl
    function addNew($cat_id=null,$gid=null,$type_id=null,$brand_id=null){
        $this->pagedata['title'] = '添加商品';
        if($gid){
            $objGoods = &$this->system->loadModel('trading/goods');
            $aGoods = $objGoods->getFieldById($gid,array('cat_id','type_id','brand_id','brand','brief','unit','weight','p_1','p_2','p_3','p_4','p_5','p_6','p_7','p_8','p_9','p_10','p_11','p_12','p_13','p_14','p_15','p_16','p_17','p_18','p_19','p_20','p_21','p_22','p_23','p_24','p_25','p_26','p_27','p_28'));
            $cat_id = $aGoods['cat_id'];
            $type_id = $aGoods['type_id'];
            $this->pagedata['goods'] = $aGoods;
            $aKey = $objGoods->getKeywords($gid);
            foreach($aKey as $v){
                 $aTmp[] = $v['keyword'];
            }
            $this->pagedata['goods']['keywords'] = implode('|', uksort($aTmp));
        }
        if(!$cat_id){
            if($type_id){
                $this->simpleGoodsId = $type_id;
            }
            $this->pagedata['cat']['type_id'] = $this->simpleGoodsId;
            $this->pagedata['goods']['type_id'] = $this->simpleGoodsId;
            $this->_editor($this->simpleGoodsId);
        }else{
            $cat = &$this->system->loadModel('goods/productCat');
            $this->pagedata['goods']['cat_id'] = $cat_id;
            $this->pagedata['goods']['marketable'] = 'true';
            $aCat = $cat->getFieldById($cat_id, array('type_id'));
            if(!$type_id){
                $type_id = $aCat['type_id'];
            }
            $this->_editor($type_id);
        }
        $this->pagedata['goods']['brand_id'] = $brand_id;
        $this->pagedata['goods']['cost'] = 0;
        $oGtype = &$this->system->loadModel('goods/gtype');
        $this->pagedata['goodsbn_display_switch'] = $this->system->getConf('goodsbn.display.switch');
        $this->pagedata['gtype']['status'] = $oGtype->checkDefined();
        header("Cache-Control:no-store");
        $gimage = &$this->system->loadModel('system/storager');
        $max_upload= $gimage->get_pic_upload_max();
        $this->pagedata['max_upload'] = $max_upload;
        $this->singlepage('product/detail/frame.html');
    }

    //编辑商品页面ctl
    function edit($goods_id){
        $tag_data=$this->tagList(false);
        $this->goods_id = $goods_id;
        $oGoods = &$this->system->loadModel('trading/goods');
        $goods = $oGoods->getGoods($goods_id);
        $spec['vars'] = $goods['spec'];
        $gimage = &$this->system->loadModel('goods/gimage');
        $goods['gimages'] = $gimage->get_by_goods_id($goods_id);

        if($goods['products']){
            foreach($goods['products'] as $i=>$product){
                $spec['bn'][$i] = $product['bn'];
                $spec['price'][$i] = $product['price'];
                $spec['store'][$i] = $product['store'];
                $spec['alert'][$i] = $product['alert'];
                $spec['cost'][$i] = $product['cost'];
                $spec['weight'][$i] = $product['weight'];

                foreach($product['mprice'] as $levelid => $price){
                    if($product['autoset'][$levelid] == 1)
                        $goods['products'][$i]['mprice'][$levelid] = '';
                }
                foreach($spec['vars'] as $k=>$v){
                    $spec['val'][$k][$i] = $product['props']['spec'][$k];
                    $spec['pSpecId'][$k][$i] = $product['props']['spec_private_value_id'][$k];
                }
                foreach($goods['prototype']['setting']['data'] as $k=>$v){
                    $spec['idata'][$k][$i] = $product['props']['idata'][$k];
                }
            }
        }else{
            foreach($goods['mprice'] as $levelid=>$price){
                if($goods['autoset'][$levelid] == 1) $goods['mprice'][$levelid] = '';
            }
        }

        $goods['intro']=str_replace('\r','<br>',stripslashes($goods['intro']));
        $this->pagedata['spec'] = &$spec;    //商品规格
        $this->pagedata['prototype'] = &$goods['prototype'];
        $this->pagedata['goods'] = &$goods;
        $this->pagedata['goods']['adjparams']['dis_goods'][] = $goods_id;
        $this->pagedata['goods']['scheduled'] = $oGoods->get_auto_task($goods_id);

        foreach($oGoods->getLinkList($goods_id) as $rows){
            if($rows['goods_1'] == $goods_id){
                $aLinkList[] = $rows['goods_2'];
                $linkType[$rows['goods_2']] = array('manual'=>$rows['manual']);
            }else{
                $aLinkList[] = $rows['goods_1'];
                $linkType[$rows['goods_1']] = array('manual'=>$rows['manual']);
            }
        }
        $this->pagedata['goods']['glink']['items'] = $aLinkList;
        $this->pagedata['goods']['glink']['moreinfo'] = $linkType;
        $this->_editor($goods['type_id']);

        $oGtype = &$this->system->loadModel('goods/gtype');
        $this->pagedata['gtype']['status'] = $oGtype->checkDefined();
        $this->pagedata['pop']=$_GET['pop'];
        $keywords = array();
        foreach( $oGoods->getKeywords($goods_id) as $keyword )
            $keywords[] = $keyword['keyword'];
        uksort($keywords);

        if( $goods['spec_desc'] ){
            $objSpec = &$this->system->loadModel('goods/specification');
            $aSpec = $objSpec->getListByIdArray( array_keys($goods['spec_desc']) );
            $this->pagedata['specname'] = $aSpec;
            $aSpecImg = array();
            foreach( $aSpec as $asv ){
                if( $asv['spec_type'] == 'image' ){
                    foreach( $objSpec->getValueList( $asv['spec_id'] ) as $svk => $svv ){
                        $aSpecImg[$svk] = $gimage->getUrl( $svv['spec_image'] );
                    }
                }
            }
            $this->pagedata['goods']['spec_value_image'] =$aSpecImg ;
        }
        $gimage = &$this->system->loadModel('system/storager');
        $max_upload= $gimage->get_pic_upload_max();
        $this->pagedata['title'] = $goods['name'];
        $this->pagedata['max_upload'] = $max_upload;
        $this->pagedata['goodsbn_display_switch'] = $this->system->getConf('goodsbn.display.switch');
        $this->pagedata['goods']['keywords'] = implode('|', $keywords);
        $this->pagedata['params']['goods_id'] = $goods['goods_id'];
        $this->pagedata['spec_default_pic'] = $this->system->getConf('spec.default.pic');
        $this->pagedata['tag_id'] = $goods['goods_id'];

        if( $goods['supplier_id'] ){
            if( is_array($goods['products']) && count($goods['products']) ){
                foreach( $goods['products'] as $proItemk => $proItem ){
                    $sourceBn[$proItemk] = $proItem['bn'];
                }
            }else{
                $sourceBn[] = $goods['bn'];
            }
            $this->pagedata['old_bn'] = $sourceBn;
            $this->pagedata['supplier_id'] = $goods['supplier_id'];
            $this->pagedata['object_id'] = $goods['supplier_goods_id'];
        }
        if( $_GET['commandtype'] ){
            $this->pagedata['commandtype'] = $_GET['commandtype'];
            $this->pagedata['supplier_id'] = $_GET['supplier_id'];
            $this->pagedata['object_id'] = $_GET['object_id'];
            $this->pagedata['command_id'] = $_GET['command_id'];

            // 与本地商品 '对照编辑' 2009-11-25 17:17 wubin
            if($_GET['commandtype'] == 4) {
                $oDatasync = $this->system->loadModel('distribution/datasync');
                $oSupplier = $this->system->loadModel('distribution/supplier');

                $b2b_goods = $oDatasync->getSupplierGoodsInfo($_GET['supplier_id'], $_GET['object_id']);
                $supplierName = $oSupplier->getSupplierInfo($_GET['supplier_id'],'supplier_brief_name');
                $b2b_goods['type_props'] = unserialize($b2b_goods['type_props']);
                $b2b_goods['params'] = unserialize($b2b_goods['params']);

                // 整理商品扩展属性 不用在模板上做相关逻辑 2009-11-25 18:17 wubin
                foreach($b2b_goods['type_props'] as $key => $row) {
                    if($row['type'] == 'select') {
                        $b2b_goods['type_props'][$key]['selected'] = $row['options'][$b2b_goods['p_'.$key]];
                    } else {
                        $b2b_goods['type_props'][$key]['selected'] = $b2b_goods['p_'.$key];
                    }
                }

                $this->pagedata['supplier_brief_name'] = $supplierName['supplier_brief_name'];
                $this->pagedata['b2b_goods'] = $b2b_goods;
            }
       }
        $this->singlepage('product/detail/frame.html');
    }

    function _editor($type_id){

        $cat = &$this->system->loadModel('goods/productCat');
        $this->pagedata['cats'] = $cat->get_cat_list();
        if(count($this->pagedata['cats'])<1){
            $this->splash('failed','index.php?ctl=goods/category&act=addNew',__('请先添加商品分类,转到商品分类添加'));
        }
        $objGtype = &$this->system->loadModel('goods/gtype');
        $this->pagedata['gtype'] = $objGtype->getList('*,type_id,name,schema_id','',0,-1);

        foreach($this->pagedata['gtype'] as $gtype){
            $this->pagedata['typeMap'][$gtype['type_id']] = $gtype['name'];
        }

        $gimage = &$this->system->loadModel('goods/gimage');
        $this->pagedata['uploader'] = $gimage->uploader();

        if($type_id){

            $prototype = $objGtype->getTypeDetail($type_id);
            if($prototype['setting']['use_brand']){
                $brandObj = &$this->system->loadModel('goods/brand');
                $this->pagedata['brandList'] = $brandObj->getTypeBrands($type_id);
            }

            if(is_array($prototype['setting']['data'])){
                $schema = &$this->system->loadModel('goods/schema');
                $typeObj = &$schema->instance($prototype['schema_id']);
                foreach($prototype['setting']['data'] as $k=>$v){
                    if($v['type']=='select' && is_string($v['options'])){
                        if(method_exists($typeObj,$v['options'])){
                            $method = $v['options'];
                            $prototype['setting']['data'][$k]['options'] = $typeObj->$method();
                        }
                    }
                }
            }
            $max_upload= $gimage->get_pic_upload_max();
            $this->pagedata['max_upload'] = $max_upload;
            $this->pagedata['sections'] = array();
            $sections = array(
                'basic'=>array(
                    'label'=>__('基本信息'),
                    'options'=>'',
                    'file'=>'product/detail/basic.html',
                ),
                'adj'=>array(
                    'label'=>__('配件'),
                    'options'=>'',
                    'file'=>'product/detail/adj.html',
                ),
                'content'=>array(
                    'label'=>__('详细介绍'),
                    'options'=>'',
                    'file'=>'product/detail/content.html',
                ),
                'params'=>array(
                    'label'=>__('属性参数'),
                    'options'=>'',
                    'file'=>'product/detail/params.html',
                ),
                'adv'=>array(
                    'label'=>__('搜索引擎优化'),
                    'options'=>'',
                    'file'=>'product/detail/adv.html',
                ),
                'rel'=>array(
                    'label'=>__('相关商品'),
                    'options'=>'',
                    'file'=>'product/detail/rel.html',
                )
            );
            foreach($sections as $key=>$section){
                if (!isset($prototype['setting']['use_'.$key]) || ($prototype['setting']['use_'.$key] && !empty($prototype[$key]))){
                    if(method_exists($this,($func = '_editor_'.$key))){
                        $this->$func();
                    }
                    $this->pagedata['sections'][$key] = $section;
                }
            }
            $this->pagedata['goods']['type_id'] = $type_id;
            if($this->pagedata['goods']['spec']){ // || $prototype['spec']
                if(!$this->pagedata['spec']['vars']){
                    $this->pagedata['spec']['vars'] = $prototype['spec'];
                }elseif(!$prototype['spec']){
                    $this->pagedata['spec']['vars'] = $this->pagedata['spec']['vars'];
                }else{
                    $aTmp = $this->pagedata['spec']['vars'];
                    foreach($prototype['spec'] as $k => $v){
                        if(!isset($aTmp[$k])){
                            $aTmp[$k] = $v;
                        }
                    }
                    $this->pagedata['spec']['vars'] = $aTmp;
                }
                $prototype['setting']['use_spec'] = 1;
                if(!$this->pagedata['goods']['products']){
                    $this->pagedata['goods']['products'] = array(1);
                }
            }
            $this->pagedata['prototype'] = $prototype;
        }

        $this->pagedata['point_setting'] = $this->system->getConf('point.get_policy');
        $this->pagedata['url'] = dirname($_SERVER['PHP_SELF']);
        $memberLevel = &$this->system->loadModel('member/level');
        $this->pagedata['mLevels'] = $memberLevel->getList('member_lv_id,dis_count');
    }

    function clone_goods_img(){
        $gd = &$this->system->loadModel('goods/gimage');
        $imgSrc = array();
        $imageDefault = $_POST['image_default'];
        if( $_POST['goods']['image_file'] )
            foreach( $_POST['goods']['image_file'] as $v ){
                $newImgSrc = $gd->clone_from_goods( $v );
                $imgSrc[] = $newImgSrc;
                if( $v == $imageDefault )
                    $imageDefault = $newImgSrc['gimage_id'];
            }
        $this->pagedata['goods'] = $_POST['goods'];
        $this->pagedata['goods']['gimages'] = $imgSrc;
        if( $imageDefault ){
            $this->pagedata['goods']['image_default'] = $imageDefault;
        }
        $this->display('product/gimage_goods.html');
    }

    function update(){
        unSafeVar($_POST);
        $goods = $_POST['goods'];
        $goods['name'] = trim($_POST['goods']['name']);
        $goods['image_default'] = $_POST['image_default'];
        $this->goods_id = $goods['goods_id'];

        $gd=&$this->system->loadModel('utility/magickwand');
        if($gd->magickwand_loaded){
            $loaded = true;
        }else{
            $gd=&$this->system->loadModel('utility/gdimage');
            $loaded = $gd->gd_loaded;
        }
        $this->pagedata['gd_loaded'] = $loaded;
        $this->pagedata['pic_bar'] = $_POST['pic_bar'];

    /*
        if($_POST['vars']){
            $goods['products'] = array(1);
            $spec['bn'] = $_POST['bn'];
            $spec['price'] = $_POST['price'];
            $spec['store'] = $_POST['store'];
            $spec['mprice'] = $_POST['mprice'];
            $spec['vars'] = $_POST['vars'];
            $spec['val'] = $_POST['val'];
            $spec['idata'] = $_POST['idata'];
            $spec['idataInfo'] = $_POST['idataInfo'];
            $this->pagedata['spec'] = &$spec;    //商品规格
        }
        */

        if( $_POST['bn'] && is_array($_POST['bn']) ){
            foreach( $_POST['bn'] as $gk => $gbn ){
                $goods['products'][$gk] = array(
                    'bn' => $gbn,
                    'cost' => $_POST['cost'][$gk],
                    'weight' => $_POST['weight'][$gk],
                    'store' => $_POST['store'][$gk],
                    'price' => $_POST['price'][$gk],
                );
                foreach( $_POST['mprice'] as $memlvid => $mprice ){
                    $goods['products'][$gk]['mprice'][$memlvid] = $mprice[$gk];
                }
                $goodsProps = array();
                foreach( $_POST['val'] as $valSpecId => $valSpec ){
                    $goodsProps['spec'][$valSpecId] = urldecode($valSpec[$gk]);
                    $goodsProps['spec_private_value_id'][$valSpecId] = $_POST['pSpecId'][$valSpecId][$gk];
                    $goodsProps['spec_value_id'][$valSpecId] = $_POST['specVId'][$valSpecId][$gk];
                }
                $goods['products'][$gk]['props'] = $goodsProps;
            }
        }

        foreach($_POST['adjunct']['name'] as $key => $name){
            $aItem['name'] = $name;
            $aItem['type'] = $_POST['adjunct']['type'][$key];
            $aItem['min_num'] = $_POST['adjunct']['min_num'][$key];
            $aItem['max_num'] = $_POST['adjunct']['max_num'][$key];
            $aItem['set_price'] = $_POST['adjunct']['set_price'][$key];
            $aItem['price'] = $_POST['adjunct']['price'][$key];
            if($aItem['type'] == 'goods') $aItem['items']['product_id'] = $_POST['adjunct']['items'][$key];
            else $aItem['items'] = $_POST['adjunct']['items'][$key];//.'&dis_goods[]='.$aData['goods_id']
            $goods['adjunct'][] = $aItem;
        }

        if( $goods['spec_desc'] ){
            $goods['spec_desc'] = unserialize(urldecode($goods['spec_desc']));
        }
        //做类型转换
        $oGType = $this->system->loadModel('goods/gtype');
        if( !$oGType->typeTransform($_POST['oldTypeId'] , $_POST['goods']['type_id'], $goods, $_POST) ){
            echo 'false';
            exit;
        }
        if(!empty( $_POST['vars'] )){
            if( $oGType->specTransformState == 'different' || $oGType->specTransformState == 'same' ){
                foreach( $_POST['vars'] as $dk => $dv ){
                    $_POST['specname'][$dk]['spec_name'] = $dv;
                }
            }else{
                foreach( $_POST['vars'] as $dk => $dv ){
                    $_POST['specname'][$oGType->specTransformHash[$dk]['spec_id']]['spec_name'] = $dv;
                }
            }
        }

        $this->pagedata['goods'] = &$goods;

        foreach($_POST['linkid'] as $k => $id){
            $aLinkType[$id] = array('manual'=>$_POST['linktype'][$k]);
        }
        $this->pagedata['goods']['glink']['items'] = $_POST['linkid'];
        $this->pagedata['goods']['glink']['moreinfo'] = $aLinkType;
        $this->pagedata['goods']['keywords'] = $_POST['keywords']['keyword'];

        foreach( $goods as $gk => $gv ){
            if( substr($gk,0,2) == 'p_' )
                $this->pagedata['goods'][$gk] = $gv;
        }
        if( $goods['spec_desc'] ){
            $objSpec = $this->system->loadModel('goods/specification');
            $specDescKeys = array_keys( $goods['spec_desc'] );
            if( !empty( $specDescKeys ) && $specDesckeys[0] )
                $aSpec = $objSpec->getListByIdArray( $specDescKeys );
            $this->pagedata['specname'] = $aSpec;
            $aSpecImg = array();
            $gimage = $this->system->loadModel('goods/gimage');
            foreach( $aSpec as $asv ){
                if( $asv['spec_type'] == 'image' ){
                    foreach( $objSpec->getValueList( $asv['spec_id'] ) as $svk => $svv ){
                        $aSpecImg[$svk] = $gimage->getUrl( $svv['spec_image'] );
                    }
                }
            }
            $this->pagedata['goods']['spec_value_image'] =$aSpecImg ;

            $tmpGoods=array();
            $specDefaultPic = $this->system->getConf('spec.default.pic');
            if ( is_array($goods['spec_desc']) ){
                foreach($goods['spec_desc'] as $key => $val){
                    $tmpRow = $objSpec->getFieldById($key,array('spec_name','spec_type','spec_show_type'));
                    $tmpGoods['spec_desc'][$key]['name']=$tmpRow['spec_name'];
                    foreach($val as $k=>$v){
                        if (empty($val[$k]['spec_image'])||is_null($val[$k]['spec_image'])){
                            $tValue=$objSpec->getValueById($val[$k]['spec_value_id'],array('spec_image'));
                            $val[$k]['spec_image'] = $tValue['spec_image'];
                        }
                        if (empty($val[$k]['spec_image'])||is_null($val[$k]['spec_image'])){
                            $val[$k]['spec_image'] = $specDefaultPic;
                        }
                        $goods['spec_desc'][$key][$k]['spec_type'] = $tmpRow['spec_type'];
                        $val[$k]['spec_type'] = $tmpRow['spec_type'];
                        $val[$k]['spec_goods_images']=$key."@".$v['spec_goods_images']."|".$k;
                        $spec_type = $tmpRow['spec_type'];
                    }
                    $tmpGoods['spec_desc'][$key]['value']=$val;
                }
                $this->pagedata['goods']['specVdesc'] = $tmpGoods['spec_desc'];
            }

        }
        $this->pagedata['specname'] = $_POST['specname'];
        $this->pagedata['goods']['spec_desc_str'] = urlencode( serialize($goods['spec_desc']) );
        $this->_editor($_POST['goods']['type_id']);
        $this->pagedata['goods']['taged'] = space_split($_POST['tags']);
        $this->pagedata['goodsbn_display_switch'] = $this->system->getConf('goodsbn.display.switch');

        if( $_POST['supplier_id'] ){
            $this->pagedata['command_id'] = $_POST['command_id'];
            $this->pagedata['object_id'] = $_POST['object_id'];
            $this->pagedata['supplier_id'] = $_POST['supplier_id'];
        }

        if($_POST['commandType']){
            $this->pagedata['commandtype'] = $_POST['commandType'];
        }

        $this->display('product/detail/page.html');
    }

    function toAdd(){
        $data = $_POST['goods'];
        $data['spec_desc'] = urldecode( $data['spec_desc'] );
//        $data['spec_desc'] = addslashes_array($data['spec_desc']);
        $data['params'] = stripslashes_array($data['params']);

        if(!$data['goods_id']) unset($data['goods_id']);

        switch($_GET['but']){
            case 3:
            if($data['goods_id']){
                $but_type = 'edit';
                $url_href = 'index.php?ctl=goods/product&act=edit&p[0]='.$data['goods_id'];
            }else{
                $but_type = 'new';
                $url_href = 'index.php?ctl=goods/product&act=index';
            }
            break;
            case 1:
            //$url_href = 'index.php?ctl=goods/product&act=addNew&p[0]='.$data['cat_id'].'&p[1]='.$data['type_id'].'&p[2]='.$data['brand_id'];
            $url_href = 'index.php?ctl=goods/product&act=addNew&p[0]=&p[1]=';
            break;
            default:
            $url_href = 'index.php?ctl=goods/product&act=index';
            break;
        }
        $this->begin($url_href);
        if( is_array($_POST['bn']) ){
            foreach( $_POST['bn'] as $aTmpBnk => $aTmpBn ){
                $_POST['bn'][$aTmpBnk] = trim( $aTmpBn );
            }
        }
        
        (strlen($data['brief']) < 4*255) || trigger_error(__('商品介绍已超过255字节'),E_USER_ERROR);
        
        $data['bn'] = trim( $data['bn'] );
        $data['product_bn'] = trim($data['product_bn'] );
        $image_file = $data['image_file'];
        unset($data['image_file']);
        $udfimg = $data['udfimg'];
        unset($data['udfimg']);
        $data['marketable'] = $data['marketable'] ? $data['marketable'] : false;
        $data['adjunct'] = $_POST['adjunct'];
        if(count($_POST['price'])>0){    //开启规格 多货品
            foreach($_POST['vars'] as $vark=>$varv){
                $data['spec'][$vark] = $varv;
            }
            $data['spec'] = serialize($data['spec']);
            $sameProFlag = array();
            foreach($_POST['price'] as $k => $price){    //设置销售多货品销售价等价格
                $data['price'] = $data['price']?min($price,$data['price']):$price;    //取最小商品价格
                $data['cost'] = $data['cost']?min($_POST['cost'][$k],$data['cost']):$_POST['cost'][$k];
                $data['weight'] = $data['weight']?min($_POST['weight'][$k],$data['weight']):$_POST['weight'][$k];

                if(!$_POST['mktprice'][$k]){ //没有市场价
                    $oMath = &$this->system->loadModel('system/math');
                    if($this->system->getConf('site.show_mark_price')){
                        if($this->system->getConf('site.market_price') == '1')
                            $_POST['mktprice'][$k] = $this->system->getConf('site.market_rate')*$oMath->getOperationNumber($price);
                        if($this->system->getConf('site.market_price') == '2')
                            $_POST['mktprice'][$k] = $this->system->getConf('site.market_rate')+$oMath->getOperationNumber($price);
                    }
                }

                $products[$k]['price'] = $price;
                $products[$k]['bn'] = $_POST['bn'][$k];
                $products[$k]['store'] = (trim($_POST['store'][$k]) === '' ? null : intval($_POST['store'][$k]));
                $products[$k]['alert'] = $_POST['alert'][$k];
                $products[$k]['cost'] = $_POST['cost'][$k];
                $products[$k]['weight'] = $_POST['weight'][$k];
                $products[$k]['mktprice'] = $_POST['mktprice'][$k];
                $products[$k]['store_place'] = $_POST['store_place'][$k];
                $products[$k]['marketable'] = $_POST['marketable'][$k];

                $data['mktprice'] = $data['mktprice']?min($_POST['mktprice'][$k],$data['mktprice']):$_POST['mktprice'][$k];

                $newSpecI = 0;
                $proSpecFlag = '';
                foreach($_POST['vars'] as $i=>$v){

                    $products[$k]['props']['spec'][$i] = urldecode(trim($_POST['val'][$i][$k]));        //array('规格(颜色)序号'=>'规格值(红色)')
                    $products[$k]['props']['spec_private_value_id'][$i] = trim($_POST['pSpecId'][$i][$k]);
                    $products[$k]['props']['spec_value_id'][$i] = trim($_POST['specVId'][$i][$k]);
                    if( trim($products[$k]['props']['spec'][$i]) === '' ){
                        trigger_error(__('请为所有货品定义规格值'),E_USER_ERROR);
                        $this->end(false,__('请为所有货品定义规格值'));
                        exit;
                    }
                    $proSpecFlag .= $products[$k]['props']['spec_private_value_id'][$i].'_';
                }
                if( in_array( $proSpecFlag, $sameProFlag ) ){
                    trigger_error(__('不能添加相同规格货品'),E_USER_ERROR);
                    $this->end(false,__('不能添加相同规格货品'));
                    exit;
                }
                $sameProFlag[$k] = $proSpecFlag;
                reset($proSpecFlag);

                reset($_POST['vars'],$_POST['pSpecId']);
                $products[$k]['pdt_desc'] = implode('、', $products[$k]['props']['spec']);    //物品描述
                $products[$k]['pdt_desc'] = addslashes_array($products[$k]['pdt_desc']);

                foreach($_POST['idata'] as $i=>$v){
                    $products[$k]['props']['idata'][$i] = $v[$k];
                }

                //设置会员价格
                if(is_array($_POST['mprice']))
                    foreach($_POST['mprice'] as $levelid => $rows){
                        $products[$k]['mprice'][$levelid] = floatval($rows[$k]);
                    }
            }
            unset( $sameProFlag );
            $data['products'] = &$products;
        }else{
            if(!$data['mktprice']){
                $oMath = &$this->system->loadModel('system/math');
                if($this->system->getConf('site.show_mark_price')){
                    if($this->system->getConf('site.market_price') == '1')
                        $data['mktprice'] = $this->system->getConf('site.market_rate')* $oMath->getOperationNumber( $data['price'] );
                    if($this->system->getConf('site.market_price') == '2')
                        $data['mktprice'] = $this->system->getConf('site.market_rate')+$oMath->getOperationNumber( $data['price'] );
                }
            }
            $data['props']['idata'] = $_POST['idata'];
        }

        $objGoods = &$this->system->loadModel('trading/goods');
        foreach($products as $k => $p){
            if(empty($p['bn'])) continue;
            if($objGoods->checkProductBn($p['bn'], $data['goods_id'])){
                trigger_error(__('您所填写的货号已被使用，请检查！'),E_USER_ERROR);
            }
            $aBn[] = $p['bn'];
        }

        if(!empty($data['product_bn'])){
            if($objGoods->checkProductBn($data['product_bn'], $data['goods_id'])){
                trigger_error(__('您所填写的货号已被使用，请检查！'),E_USER_ERROR);
            }
        }

        if(count($aBn) > count(array_unique($aBn))){
            trigger_error(__('您所填写的货号已被使用，请检查！'),E_USER_ERROR);
        }

        if(!$data['type_id']){
            $objCat = &$this->system->loadModel('goods/productCat');
            $aCat = $objCat->getFieldById($data['cat_id'], array('type_id'));
            $data['type_id'] = $aCat['type_id'];
        }
        
        if(!($gid = $objGoods->save($data))){
            trigger_error(__('保存失败，请重试！'),E_USER_ERROR);
        }

        $scheduled = array();
        $now = time();
        foreach($_POST['scheduled'] as $time=>$action){
            if($time>$now){
                $scheduled[] = array('tasktime'=>$time,'action'=>$action);
            }
        }

        $objGoods->set_auto_task($gid,$scheduled);
        if(isset($data['marketable']) && $data['marketable'] != $_POST['goods']['old_marketable']){
           $objGoods->updateUpDownTime($data['marketable'],$gid,true);
        }
        
        $keywords = array();
        foreach( $objGoods->getKeywords($gid) as $keywordvalue )
            $keywords[] = $keywordvalue['keyword'];
        $keyword = implode('|', $keywords);

        if($keyword != $_POST['keywords']['keyword']){
            $objGoods->deleteKeywords($gid);
            if( $_POST['keywords']['keyword'] )
                $objGoods->addKeywords($gid, explode('|',$_POST['keywords']['keyword']) );
        }

        //处理商品图片
        $gimage= &$this->system->loadModel('goods/gimage');
        $gimage->saveImage($data['goods_id'], $data['db_thumbnail_pic'], $_POST['image_default'], $image_file, $udfimg, $_FILES);

        //相关商品
        foreach($_POST['linkid'] as $k => $id){
            $aLink[] = array('goods_1' => $data['goods_id'], 'goods_2' => $id, 'manual' => $_POST['linktype'][$id], 'rate' => 100);
        }
        $objProduct = &$this->system->loadModel('goods/products');
        $objProduct->toInsertLink($data['goods_id'], $aLink);

        $oSupplier = $this->system->loadModel('distribution/supplier');

        if( $_POST['supplier_id'] ){
            $newBn = array();
            if( isset($_POST['bn']) && is_array($_POST['bn']) ){
                foreach( $_POST['bn'] as $nbnk => $nbnv ){
                    $newBn[$_POST['old_bn'][$nbnk]] = $nbnv;
                    unset($_POST['source_bn'][$_POST['old_bn'][$nbnk]]);
                }
            }else{
                $newBn[$_POST['old_bn']] = $_POST['goods']['product_bn'];
                unset($_POST['src_bn']);
            }
            $oSupplier->updateSupplierPdtBn($newBn,$_POST['source_bn'],$_POST['supplier_id']);
        }

        if( isset($_POST['commandType']) ){
            if( in_array($_POST['commandType'], array('4','5','6') ) ){
                $oSupplier->updateSyncStatus($_POST['command_id'],$_POST['supplier_id'],'done');
            }
        }

        $oseo = &$this->system->loadModel('system/seo');
        $aData=array(
            'keywords'=>$_POST['goods']['seo']['meta_keywords'],
            'descript'=>$_POST['goods']['seo']['meta_description'],
            'title'=>$_POST['goods']['seo']['seo_title']
        );
        $oseo->set_seo('goods',$gid,$aData);
        
        $eventData = array('goods_id'=>$gid,'event_type'=>'post');
        $objGoods->fireEvent('save',$eventData);
        
        if($but_type == 'new'){
            $this->end(true,__('保存成功').'<input type=hidden id="g_id" value='.$gid.'>','index.php?ctl=goods/product&act=edit&p[0]='.$gid);
        }else{
            if($_GET['but'] == 1){
                $this->end(true,__('保存成功').'<input type=hidden id="g_id" value='.$gid.'>',$url_href.$gid);
            }else{
                $this->end(true,__('保存成功').'<input type=hidden id="g_id" value='.$gid.'>');
            }
        }
    }

    function toRemove($gid){
        $objGoods = &$this->system->loadModel('trading/goods');
        $objGoods->toRemove($gid);
        $this->splash('success','index.php?ctl=goods/product&act=index');
    }

    function enable(){
        $objGoods = &$this->system->loadModel('goods/products');
        $glist = $objGoods->setEnabled($_POST,true);
        echo __('选中商品上架完成');
    }

    function disable(){
        $objGoods = &$this->system->loadModel('goods/products');
        $glist = $objGoods->setEnabled($_POST,false);
        echo __('选中商品下架完成');
    }

    function sendNotify($goods_id){
        $notify = &$this->system->loadModel('goods/goodsNotify');
        $messenger = &$this->system->loadModel('system/messenger');
        foreach($notify->getNotifyByGId($goods_id) as $notify){
            $messenger->actionSend('goods-notify',array('email'=>$notify['email']),$notify['member_id'],$notify);
        }
        $this->splash('success','index.php?ctl=goods/product&act=edit&p[0]='.$goods_id);
    }

    //设定会员价格页
    function mprice(){
        $memberLevel = &$this->system->loadModel('member/level');
        foreach($memberLevel->getList('member_lv_id,name,dis_count,name') as $level){
            $level['dis_count'] = ($level['dis_count']>0 ? $level['dis_count'] : 1);
            $this->pagedata['mPrice'][$level['member_lv_id']] = array('name'=>$level['name'],'default'=>$level['dis_count']*$_POST['price'],'price'=>$_POST['level'][$level['member_lv_id']]);
        }
        $this->display('product/levelPrice.html');
    }

    function ratelist(){
       
        $objGoods = &$this->system->loadModel('goods/products');
        $glist = $objGoods->finderResult($_POST['items']);
        foreach($glist as $k=>$v){
            if($_POST['has'][$v])unset($glist[$k]);
        }
        if(count($glist)>0){
            $this->pagedata['goods']['rates'] = $objGoods->getList('name,goods_id,image_default,100 as rate',array('goods_id'=>$glist),'name');
            $this->display('product/ratelist.html');
        }
    }




    function editItem($goods_id, $feild, $val){
        $objGoods = &$this->system->loadModel('trading/goods');
        $aData['goods_id'] = $goods_id;
        $aData[$feild] = $val;
        $aGoods = $objGoods->updateGoods($aData);
    }

    function view_src_img($gimage_id){
        $gimage= &$this->system->loadModel('goods/gimage');
        $gimage->display_source_by_id($gimage_id);
    }

    function newPic($gid, $type){
        if($type=='add'){

            $obj= &$this->system->loadModel('goods/gimage');

            $this->pagedata['gimage'] = $obj->save_upload($_FILES['Filedata']);
            $this->display('product/gimage.html');
        }
    }

    function removePic(){
        $ident = $_POST['ident'];
        if($ident){
            $gimage = &$this->system->loadModel('goods/gimage');
            $storager = &$this->system->loadModel('system/storager');
            $fileSet = $gimage->getFileSet($ident);
            $storager->remove($ident);
            foreach($fileSet as $v) $storager->remove($v);
            echo __('选中图片已成功删除');
        }
    }

    //商品数据导出
    function do_export($io='csv'){
        $this->system->__session_close(0);    //review: 这种方式是否还可以再。。。
        include_once('shopObject.php');
        if($io!='csv') {
            parent::do_export($io);
            exit;
        }
        $step = 20;
        $offset=0;
        $dataio = &$this->system->loadModel('system/dataio');
        $p = &$this->system->loadModel('goods/products');
        $gtype = &$this->system->loadModel('goods/gtype');

        $cols = 'goods_id,type_id,cat_id,marketable,pdt_desc,name,bn,brand,cost,brief,intro,mktprice,price,weight,unit,store,store_place,params,spec,spec_desc,image_file'; //todo ...当前列
        $cols_attr = 'p_1';
        for($i=2;$i<29 ;$i++){
            $cols_attr .= ',p_'.$i;
        }

        $last_type_id = -1;
        $list = $this->model->getList($cols.','.$cols_attr,$_POST,$offset,$step,array('type_id','asc'));
        $count = $this->model->count($_POST);
        while($count>$offset){
            if($offset==0){
                $dataio->export_begin($io,null,'goods',$count);
            }
            else{
                $list = $this->model->getList($cols.','.$cols_attr,$_POST,$offset,$step,array('type_id','asc'));
            }
            foreach($list as $v){
                if(trim($v['bn'])=='') continue;
                if($last_type_id!=$v['type_id']){
                    $aGtype = $gtype->instance($v['type_id']);
                    $keys = $p->getTypeExportTitle($aGtype);
                    $a = array($keys);
                    $dataio->export_rows($io,$a);
                    unset($proto);
                    foreach($keys as $k1=>$v1) $proto[$k1] = '';
                }
                $data = $p->getGoodsExportData($v,$proto,$aGtype['name'],unserialize($aGtype['props']),$aGtype['params']);
                $dataio->export_rows($io,$data);
                $last_type_id = $v['type_id'];
            }
            $offset+=$step;
        }
        $dataio->export_finish($io);
    }



    function import(){
        $this->path[] = array('text'=>__('批量上传'));
        $dataio = &$this->system->loadModel('system/dataio');
        $gtype =  &$this->system->loadModel('goods/gtype');
        $gtype_list = $gtype->getList('type_id,name','',0,-1);
        $gtype_options = array();
        foreach($gtype_list as $k=>$v){
            $gtype_options[$v['type_id']] = $v['name'];
        }
        $dataio->privateImport = true;
        if(defined('DEVELOPING')&&DEVELOPING){
            $this->pagedata['developing'] = true;
        }
        $this->pagedata['gtypes'] = $gtype_options;
        $this->pagedata['importer'] = $dataio->importer($this->ioType);
        $this->pagedata['optionsView'] = $this->importOptions;

        $this->page('product/import.html');
    }

    function importer(){
        $this->begin('index.php?ctl=goods/product&act=import');
        @unlink(HOME_DIR.'/tmp/uploadGoodsCsvWarning');
        @unlink(HOME_DIR.'/tmp/uploadGoodsCsvData');
        $p = &$this->system->loadModel('goods/products');
        if(!$p->checkImportData($_POST, $_FILES)){
            $this->end(false);
        }else{
            $p->insertCsvData();
            if(file_exists(HOME_DIR.'/tmp/uploadGoodsCsvWarning')){
                $aWarning = file(HOME_DIR.'/tmp/uploadGoodsCsvWarning');
                unlink(HOME_DIR.'/tmp/uploadGoodsCsvWarning');
                $warningInfo .= '<br>'.implode('<br>', $aWarning);
                $this->splash('notice','index.php?ctl=goods/product&act=import',__('商品数据已经导入，但存在以下问题：<br />').$warningInfo.'<br />',null,30);
            }else{
                $this->end(true,__('商品数据导入成功!'));
            }
        }
    }

    function gettypecsv(){
        $io='csv';
        $dataio = &$this->system->loadModel('system/dataio');
        $gtype = &$this->system->loadModel('goods/gtype');
        $p = &$this->system->loadModel('goods/products');
        $gtype_instance = $gtype->instance($_POST['gtype']);
        $keys = $p->getTypeExportTitle($gtype_instance);
        $gtype_instance['name'] = str_replace('"','\'',$gtype_instance['name']);
        $dataio->export_begin($io,$keys,'goods-csv-model',$gtype_instance['name']);
        $dataio->export_finish($io);
    }

    function updateSupplierData($goods_id){
        $g = $this->system->loadModel('goods/products');
        if($goods = $g->getList('*',array('goods_id'=>$goods_id))){
            exit($g->getGoodsJson($goods));
        }
    }

    //分别批量编辑
    function singleBatchEdit($editType=''){

        if(count($_POST['goods_id']) == 0 && $_POST['_finder']['select'] != 'multi' && !$_POST['_finder']['id'] && !$_POST['filter']){
            echo __('请选择商品记录');
            exit;
        }
        if($_POST['filter']){
            $_POST['_finder'] = unserialize($_POST['filter']);
            $editType = $_POST['updateAct'];
        }
        if($_GET['cat_id'] && $_GET['cat_id']!=''){
            $_POST['cat_id']=$_GET['cat_id'];
        }
        
        $this->pagedata['editInfo'] = $this->model->getBatchEditInfo($_POST);
        $oPro = &$this->system->loadModel('goods/products');
        if($_GET['cat_id']!=''){
          $_POST['cat_id']=array($_GET['cat_id']);
            }
        switch( $editType ){
            case 'uniformPrice':
                $oLevel = &$this->system->loadModel('member/level');
                $priceList = array('mktprice'=>__('市场价'),'price'=>__('销售价'),'cost'=>__('成本价'));
                $levelList = $oLevel->getMLevel();
                foreach($levelList as $v)
                    $priceList[$v['member_lv_id']] = $v['name'].__('价');

                $this->pagedata['updateName'] = $priceList;
                $this->pagedata['operator'] = array('+'=>'+','-'=>'-','*'=>'x');
                break;

            case 'differencePrice':
                $count = 0;
                $page = $_POST['pagenum']?$_POST['pagenum']:1;

                if( $_POST['pagenum'] ){

                    $oPro->batchUpdatePrice( $_POST['price'] );
                    $editType .= 'List';
                    $_POST = $_POST['_finder'];
 
                }
                if( empty( $_POST['cat_id'] ) || !$_POST['cat_id'][0] ){
                    unset($_POST['cat_id']);
                }
                if( empty( $_POST['goods_id'] ) || $_POST['goods_id'][0] == '_ALL_' ){
                    unset($_POST['goods_id']);
                }
                if($_POST['price']){
                    unset($_POST['price']);
                }
                $goodsList = $oPro->getList('goods_id, name, bn, mktprice, price',$_POST, ($page-1)*20, 20);
                $count = $oPro->count($_POST);
                $goodsId = array_map( create_function('$r','return$r["goods_id"];') ,$goodsList);

                $productList = $oPro->getProductLvPrice($goodsId);
                $oLevel = &$this->system->loadModel('member/level');
                $levelList = $oLevel->getMLevel();
                $pager = array(
                    'current'=> $page,
                    'total'=> ceil($count/20),
                    'link'=> 'javascript:$(\'pagenum\').value=_PPP_;W.page(\'index.php?ctl=goods/product&act=singleBatchEdit\', {update:$(\'dialogContent\'), data:$(\'dialogContent\'), method:\'post\'});',
                    'token'=> '_PPP_'
                );
                $this->pagedata['levelList'] = $levelList;

                $this->pagedata['goodsList'] = $goodsList;
                $this->pagedata['productList'] = $productList;
                $this->pagedata['page'] = $page;
                $this->pagedata['pager'] = $pager;
                break;

            case 'uniformStore':
                $this->pagedata['operator'] = array('+'=>'+','-'=>'-');
                break;

            case 'differenceStore':
                $count = 0;
                $page = $_POST['pagenum']?$_POST['pagenum']:1;

                if( $_POST['pagenum'] ){
                    $oPro->batchUpdateStore( $_POST['store'] );
                    $oPro->synchronizationStore(array_keys($_POST['store']));
                    $editType .= 'List';
                    $_POST = $_POST['_finder'];
                }
                if( empty( $_POST['cat_id'] ) || !$_POST['cat_id'][0] ){
                    unset($_POST['cat_id']);
                }
                if( empty( $_POST['goods_id'] ) || $_POST['goods_id'][0] == '_ALL_' ){
                    unset($_POST['goods_id']);
                }

                $goodsList = $oPro->getList('goods_id, name, bn',$_POST, ($page-1)*20 , 20, $count);
                $count = $oPro->count($_POST);
                $goodsId = array_map( create_function('$r','return$r["goods_id"];') ,$goodsList);
                $productList = $oPro->getProductStore($goodsId);
                $this->pagedata['goodsList'] = $goodsList;
                $this->pagedata['productList'] = $productList;
                $pager = array(
                    'current'=> $page,
                    'total'=> ceil($count/20),
                    'link'=> 'javascript:$(\'pagenum\').value=_PPP_;W.page(\'index.php?ctl=goods/product&act=singleBatchEdit\', {update:$(\'dialogContent\'), data:$(\'dialogContent\'), method:\'post\'});',
                    'token'=> '_PPP_'
                );
                $this->pagedata['page'] = $page;
                $this->pagedata['pager'] = $pager;
                break;

            case 'name':

                break;

            case 'cat':
                $oCat = &$this->system->loadModel('goods/productCat');
                $catMap  = $oCat->getMapTree();
                $catList = array();
                foreach( $catMap as $v )
                    $catList[$v['cat_id']] = $v['pid']=='0'?$v['cat_name']:$v['type_name'].$v['cat_name'];
                $this->pagedata['cat'] =  $catList;
                break;

            case 'brief':

                break;

            case 'dorder':

                break;

            case 'brand':
                $oBrand = &$this->system->loadModel('goods/brand');
                $brandMap  = $oBrand->getAll();
                $brandList = array();
                foreach( $brandMap as $v )
                    $brandList[$v['brand_id']] = $v['brand_name'];
                $this->pagedata['brand'] =  $brandList;
                break;

            case 'score':
                $this->pagedata['operator'] = array('+'=>'+','-'=>'-','*'=>'x');
                break;

            case 'weight':
                $this->pagedata['operator'] = array('+'=>'+','-'=>'-','*'=>'x');
                break;

        }
        unset($_POST['finder']);
        $this->pagedata['filter'] = htmlspecialchars(serialize($_POST));
        $this->pagedata['finder'] = $_GET['finder'];
        $this->display('product/batchEdit'.ucfirst($editType).'.html');
    }

    //又是重载
    function saveBatchEdit(){
        $filter = unserialize($_POST['filter']);
       
    
        $oPro = &$this->system->loadModel('goods/products');

//        if( !in_array( $_POST['updateAct'], array('differencePrice', 'differenceStore') ) && $filter['_finder']['select'] == 'multi' ){
            $filter['goods_id'] = $oPro->getGoodsIdByFilter($filter);
//        }
        $haserror = false;

        switch( $_POST['updateAct'] ){

            case 'uniformPrice':
                if( is_numeric($_POST['updateName'][$_POST['updateType']]) ){ //修改会员价
                    $oPro->batchUpdateMemberPriceByOperator( $filter['goods_id'], $_POST['updateName'][$_POST['updateType']] ,abs(floatval($_POST['set'][$_POST['updateType']])), $_POST['operator'][$_POST['updateType']], $_POST['fromName'][$_POST['updateType']]  );
                }else{ //修改市场价 销售价 成本价
//                    $oPro->batchUpdateByOperator( $filter['goods_id'], 'sdb_goods', $_POST['updateName'][$_POST['updateType']] ,abs(floatval($_POST['set'][$_POST['updateType']])), $_POST['operator'][$_POST['updateType']], $_POST['fromName'][$_POST['updateType']] );

//                    if( $_POST['updateName'][$_POST['updateType']] != 'mktprice' ){
//                    $tableName = 'sdb_products';
//                    if( $_POST['fromName'][$_POST['updateType']] == 'mktprice' ){
//                        $tableName = 'sdb_goods a, sdb_products b';
//                        $_POST['updateName'][$_POST['updateType']] = 'b.'.$_POST['updateName'][$_POST['updateType']];
//                    }

                    foreach( array( 'sdb_goods','sdb_products' ) as $aTableName ){
                        $oPro->batchUpdateByOperator( $filter['goods_id'], $aTableName,$_POST['updateName'][$_POST['updateType']] ,abs(floatval($_POST['set'][$_POST['updateType']])), $_POST['operator'][$_POST['updateType']], $_POST['fromName'][$_POST['updateType']] );
                    }

//                    }
                }
                break;

            case 'differencePrice':
                $oPro->batchUpdatePrice( $_POST['price'] );
                break;

            case 'uniformStore':
                $oPro->batchUpdateByOperator( $filter['goods_id'], 'sdb_products', 'store' ,abs(intval($_POST['set'][$_POST['updateType']])), $_POST['operator'][$_POST['updateType']], $_POST['fromName'][$_POST['updateType']] );
                $oPro->synchronizationStore($filter['goods_id']);
                break;

            case 'differenceStore':
                $oPro->batchUpdateStore( $_POST['store'] );
                $oPro->synchronizationStore(array_keys($_POST['store']));
                break;

            case 'name':
                if( $_POST['updateType'] != 'name' || $_POST['set']['name'] != '' )
                    $oPro->batchUpdateText( $filter['goods_id'], $_POST['updateType'], 'name', $_POST['set'][$_POST['updateType']] );
                $oPro->syncProNameByGoodsId($filter['goods_id']);
                break;

            case 'cat':
                $oPro->batchUpdateInt( $filter['goods_id'], 'cat_id', intval($_POST['set']['cat']) );
                break;

            case 'brief':
                $oPro->batchUpdateText( $filter['goods_id'], $_POST['updateType'],'brief', $_POST['set'][$_POST['updateType']] );
                break;

            case 'brand':
                $oBrand = &$this->system->loadModel('goods/brand');
                $aBrand = $oBrand->getFieldById(intval($_POST['set']['brand']), array('brand_name'));
                $oPro->batchUpdateArray( $filter['goods_id'] , 'sdb_goods', array('brand_id','brand'), array( intval($_POST['set']['brand']), $aBrand['brand_name'] ) );
                break;

            case 'dorder':
                $oPro->batchUpdateInt( $filter['goods_id'], 'd_order', intval($_POST['set']['dorder']) );
                break;

            case 'score':
                $oPro->batchUpdateByOperator( $filter['goods_id'], 'sdb_goods', 'score' ,abs(intval($_POST['set'][$_POST['updateType']])), $_POST['operator'][$_POST['updateType']] );
                break;

            case 'weight':

                $oPro->batchUpdateByOperator( $filter['goods_id'], 'sdb_goods', 'weight' ,abs(floatval($_POST['set'][$_POST['updateType']])), $_POST['operator'][$_POST['updateType']] );
                $oPro->batchUpdateByOperator( $filter['goods_id'], 'sdb_products', 'weight' ,abs(floatval($_POST['set'][$_POST['updateType']])), $_POST['operator'][$_POST['updateType']] );
                break;

        }

        ini_set('track_errors','1');
        restore_error_handler();
        if(!$haserror){
            echo 'ok';
        }else{
            echo $GLOBALS['php_errormsg'];
        }
    }

    //图片批量处理
    function batchImage(){
        $goods = &$this->system->loadModel('goods/products');
        //$goods->getList('goods_id',$_POST,0,1);
        $count = $goods->count($_POST);
        $this->pagedata['goodscount'] = $count;
        $this->pagedata['filter'] = $_POST;
        $this->display('product/batchImage.html');
    }

    //处理下个商品的图片
    function nextImage($same_file_name=false){
        $filter = $_POST;
        $goods = &$this->system->loadModel('goods/products');
        $goodsList = $goods->getList('goods_id,image_default,udfimg',$filter,intval($_POST['present_id']),1);
        $gimage = &$this->system->loadModel('goods/gimage');
        $this->pagedata['goodscount'] = $goods->count($filter);

        if($goods_id = $goodsList[0]['goods_id']){
            $gimage->gen_all_size_by_goods_id($goods_id,$goodsList[0]['image_default'],$goodsList[0]['udfimg']=='true',$same_file_name);
        }
        $_POST['present_id'] = $_POST['present_id']+1;
        usleep(20);
        header('Content-Type: text/html;charset=utf-8');
        if($_POST['present_id']<=$_POST['allcount']){
            echo __('<font color="red">正在重新生成商品图片：').$_POST['present_id'].'/'.$_POST['allcount'].'</font><script>batchImage_rebulidRequest('.json_encode($_POST).')</script>';
        }else{
            echo __('<font color="green">图片生成完毕</font>').__('<script>$("batchImage_rebulid").retrieve("closebtn").setStyle("visibility","visible");$("batchImage_rebulid").getElement(".btnbuild").removeEvents().set("text","完成").addEvent("click",function(){$("batchImage_rebulid").retrieve("closebtn").fireEvent("click")});</script>');
        }
    }

    function viewGimages(){
        $gimg = &$this->system->loadModel('goods/gimage');

        $source = $gimg->get_source_by_id($_GET['gimage_id'],'source');
        if($source != 'N'){
            $this->pagedata['source'] = $this->system->base_url().str_replace(BASE_DIR.'/','',HOME_DIR).'/upload/'.$source;
        }
        $this->pagedata['big'] = $gimg->get_resource_by_id( $_GET['gimage_id'], 'big' );
        $this->pagedata['small'] = $gimg->get_resource_by_id( $_GET['gimage_id'], 'small' );
        $this->pagedata['thumbnail'] = $gimg->get_resource_by_id( $_GET['gimage_id'], 'thumbnail' );
        $this->display('product/detail/view_gimages.html');
    }

    function typeTurn($turnType){
        if($_POST['filter']){
            $_POST['_finder'] = unserialize(stripslashes($_POST['filter']));
            $editType = $_POST['updateAct'];
        }
        if($_GET['cat_id']){
            $_POST['cat_id']=$_GET['cat_id'];
        }
        $this->pagedata['editInfo'] = $this->model->getBatchEditInfo($_POST);

        if( $turnType == 'type' ){
            $oGType = $this->system->loadModel('goods/gtype');
            if($_GET['no_paging'])
                $no_paging = true;
            else
                $no_paging = false;
            $this->pagedata['gtype'] = $oGType->getList('type_id,name','',0,20,null,$no_paging);
        }else{
            $cat = $this->system->loadModel('goods/productCat');
            $this->pagedata['pcat'] = $cat->get_cat_list();
        }
        $this->pagedata['_filter'] = serialize($_POST);
        $this->pagedata['turn_type'] = $turnType;
        $this->display('product/type_turn.html');
    }

    function toTurnType(){
        $_POST['_filter'] = unserialize(stripslashes($_POST['_filter']));

        $oPro = $this->system->loadModel('goods/products');

        $rs = $oPro->toTurnType($_POST , $count);
        if( $rs == 'continue' )
            echo '<div id="msg">已完成'.(($_POST['step']-1)*100+$count).'个商品</div>';
        else
            echo '<div id="msg">全部完成</div>';
        echo '<div id="status">'.$rs.'</div>';
    }

}
?>
