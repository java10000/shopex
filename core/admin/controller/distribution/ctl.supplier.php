<?php defined('CORE_DIR') || exit('入口错误'); ?>
<?php
/**
 * ctl_supplier
 *
 * @uses adminPage
 * @package
 * @version $Id: ctl.supplier.php 2009-04-30 04:00:24Z hujianxin $
 * @copyright 2003-2009 ShopEx
 * @author hujianxin <hjx@shopex.cn>
 * @license Commercial
 */

class ctl_supplier extends adminPage{

    var $name='供应商';
    var $workground = 'distribution';
    var $object = 'distribution/supplier';


    function ctl_supplier(){
        parent::adminPage();
        if(!$this->system->getConf('certificate.distribute')){
            $this->errorJump();
        }
        
    }
    
    /**
     * 错误跳转处理 (从构造函数中剥离出来) wubin 2009-11-10 10:50
     */
    function errorJump(){
            switch($_GET['act']){
                case 'suppilerApiList':
                    echo "[]";
                    break;
                    exit;
                case 'doDataSync':
                    echo '{"status":"finish"}';
                    break;
                    exit;
                case 'doGoodsSync':
                    echo '{"cat_id":null,"msg":"","status":"finish"}';
                    break;
                    exit;
                case 'doImagesSync':
                    echo '{"status":"finish"}';
                    break;
                    exit;
                case 'doAutoSync':
                    echo '{"status":"done"}';
                    break;
                    exit;
                case 'doCostSync':
                    echo '{"status":"done"}';
                    break;
                    exit;
                default:
                    header("Location: index.php?ctl=dashboard&act=index");
                    break;
            }
        }

    function index(){
        set_error_handler(array(&$this,'_pageErrorHandler'));
        $page = $_POST['page'];
        if($page){
            $this->_supplierList($filter, $page);
        }else{
            $this->_supplierList($filter);
        }
        $this->pagedata['otype'] = 'index';
        $this->page("distribution/index.html");
    }

    function supplierList(){
        set_error_handler(array(&$this,'_pageErrorHandler'));
        $filter['supplier_brief_name'] = $_POST['sname'];
        $page = $_POST['page'];
        if($page){
            $this->_supplierList($filter, $page);
        }else{
            $this->_supplierList($filter);
        }
        $this->pagedata['otype'] = 'list';
        $this->pagedata['sname'] = $filter['supplier_brief_name'];
        $this->display('distribution/supplier_list.html');
    }

    function _supplierList( $filter, $page=1, $limit=20){
        $oSupplier = $this->system->loadModel('distribution/supplier');
        $sList = $oSupplier->getList('*',$filter,($page-1)*$limit,$limit);
        $count = $oSupplier->count($filter);

        // 供应商价格同步状态 wubin 2009-09-16 15:54:24
        $oCostSync = $this->system->loadModel('distribution/costsync');
        $sList = $oCostSync->getSupplierCostSyncStatus($sList);

//        $oSupplier->filterSupplierList($sList);
        $oSupplier->filterPlatformSupplierList($sList);

        $pager = array(
            'current'=> $page,
            'total'=> ceil($count/$limit),
            'link'=> 'javascript:turnp(_PPP_)',
            'token'=> '_PPP_'
        );

        $this->pagedata['status'] = array(
            '1' => '正常状态',
            '2' => '经销商解除关系',
            '3' => '供应商解除关系'
        );
        $this->pagedata['pager'] = $pager;
        $this->pagedata['supplier'] = $sList;
        $this->pagedata['page'] = $page;
        $this->pagedata['today_time'] = strtotime(date("Y-m-d").' 00:00:00');
    }

    function productLine(){
        set_error_handler(array(&$this,'_ajaxErrorHandler'));
        $oDataSync = $this->system->loadModel('distribution/datasync');
        $plList = $oDataSync->getProductLine(trim($_POST['supplier_id']));
        $cat = $this->system->loadModel('goods/productCat');
        $this->pagedata['supplier_id'] = $_POST['supplier_id'];
        $this->pagedata['cats'] = $cat->get_cat_list();
        $this->pagedata['plList'] = $plList;
        $this->display('distribution/product_line.html');
    }

    /**
     * 检查是否有分销权限
     * 注意：暂时使用，以后可以看supplier表的分销权限的字段来确定是否有分销权限
     *
     * @param int $supplier_id
     * @return boolean
     */
    function _checkSync($supplier_id){
        $oDataSync = $this->system->loadModel('distribution/datasync');
        $plList = $oDataSync->getProductLine(trim($supplier_id));
        if(empty($plList)){
            return false;
        }else{
            return true;
        }
    }

    /**
     * 供前台ajax调用检查是否有分销权限
     *
     */
    function checkSync(){
        set_error_handler(array(&$this,'_ajaxErrorHandler'));
        if(!$this->_checkSync($_POST['supplier_id'])){
            echo "invalid";
        }else{
            echo "valid";
        }
    }

    function datasync(){
        set_error_handler(array(&$this,'_ajaxErrorHandler'));
        $syncJob = $this->system->loadModel('distribution/syncjob');
        $supplier = $this->system->loadModel('distribution/supplier');
        $datasync = $this->system->loadModel('distribution/datasync');

        // 生成同步自动更新配置文件 2009-09-07 wubin
        $oAutoSync = $this->system->loadModel('distribution/autosync');
        $oAutoSync->generateAutoSyncConfigFile();

        $time = time();
        $supplier->clearTmpData($_POST['supplier_id']); //删除临时数据
        $syncJob->addDataSyncJob($_POST['supplier_id']);
        $datasync->filterUpdateList_1( $_POST['supplier_id'],'sync' );
        $supplier->updateSupplierSynctime($_POST['supplier_id'],$time);
        $supplier->updateSupplierHasNew($_POST['supplier_id']);
        echo 'success';
    }

    function syncDataList(){
       set_error_handler(array(&$this,'_pageErrorHandler'));
        $searchData = array(
            'update_content' => intval($_POST['update_content']),
            'ctrl_status' => intval($_POST['ctrl_status']),
            's_update_time' => empty($_POST['s_update_time'])?false:($_POST['s_update_time']),
            'e_update_time' => empty($_POST['e_update_time'])?false:($_POST['e_update_time']),
            'search_name' => trim($_POST['search_name'])
        );
        $page =  $_POST['page']?intval($_POST['page']):1;
        $this->_syncDataList($_GET['supplier_id'],$searchData, $page);
        $this->pagedata['otype'] = 'index';
        $this->page('distribution/data_sync.html');
    }

    function _syncDataList($supplierid, $searchData, $page=1, $limit=20){
        set_error_handler(array(&$this,'_pageErrorHandler'));
        $supplier = $this->system->loadModel('distribution/supplier');
        $sList = $supplier->getSyncDataList($supplierid, $searchData, $count, $page , $limit);
        foreach( $sList as $sk => $sv ){
 //           $slist[$sk]['img_failed'] = $supplier->checkImgDownload($sv['command_id']);
            $sList[$sk]['goods_id'] = $supplier->getLocalGoodsId($supplierid, $sv['object_id']);
        }
        $command = array(
            '1-1' => '商品上架',
            '1-2' => '商品下架',
            '2' => '货品库存变更',
            '3' => '商品图片更新',
            '4' => '商品更新',
            '5' => '货品更新',
            '6' => '商品新增',
            '7' => '商品删除'
        );
        $status = array(
            'unoperated' => '<span class="fontcolorRed fontbold">未操作</span>',
            'unmodified' => '<span class="fontcolorRed">已下载未处理</span>',
            'uncompleted_image' => '<span class="fontcolorRed">图片未下载完全</span>',
            'done' => '<span class="fontcolorLightGray">操作完成</span>'
        );

        $pager = array(
            'current'=> $page,
            'total'=> ceil($count/$limit),
            'link'=> 'javascript:turnd(_PPP_)',
            'token'=> '_PPP_'
        );
        $suDomain = $supplier->getSupplierInfo($supplierid,'domain');
        $this->pagedata['supplier_domain'] = (substr( $suDomain['domain'],-1,1 ) == '/')?$suDomain['domain']:$suDomain['domain'].'/';
        $this->pagedata['pager'] = $pager;
        $this->pagedata['page'] = $page;
        $this->pagedata['status'] = $status;
        $this->pagedata['command'] = $command;
        $this->pagedata['sList'] = $sList;
        $this->pagedata['supplier_id'] = $supplierid;
    }

    function getSyncDataList(){
        set_error_handler(array(&$this,'_pageErrorHandler'));
        $searchData = array(
            'update_content' => intval($_POST['update_content']),
            'ctrl_status' => intval($_POST['ctrl_status']),
            's_update_time' => empty($_POST['s_update_time'])?false:($_POST['s_update_time']),
            'e_update_time' => empty($_POST['e_update_time'])?false:($_POST['e_update_time']),
            'search_name' => trim($_POST['search_name'])
        );
        $page =  $_POST['page']?intval($_POST['page']):1;
        $this->_syncDataList($_GET['supplier_id'],$searchData, $page);
        $this->pagedata['otype'] = 'list';
        $this->display('distribution/data_sync_list.html');
    }

    //个别更新
    function downloadGoods(){
        set_error_handler(array(&$this,'_ajaxErrorHandler'));
        $oDataSync = $this->system->loadModel('distribution/datasync');
        $oSupplier = $this->system->loadModel('distribution/supplier');
        $syncInfo = $oDataSync->downloadGoods($_POST['command_id'],$_POST['supplier_id'],$_POST['object_id']);
        $oSupplier->updateSyncStatus($_POST['command_id'],$_POST['supplier_id'],'unmodified');
        $cat = $this->system->loadModel('goods/productCat');
        $brand = $this->system->loadModel('goods/brand');
        $brand->brand2json();
        $cat->cat2json();
        $rs = array();
        if(!empty($syncInfo)){
            $cat_id = $syncInfo['locals']['local_cat_id'];
            $cat_info = $cat->getFieldById($cat_id,array('cat_name'));
            
            $rs['msg'] = "已下载到\"".$cat_info['cat_name']."\"中\n";
            foreach( $syncInfo['type'] as $stv )
                $rs['msg'] .= "已新增".$stv."类型\n";
            foreach( $syncInfo['spec'] as $ssv )
                $rs['msg'] .= "已新增".$ssv."规格\n";
            foreach( $syncInfo['brand']['add'] as $sbav )
                $rs['msg'] .= "已新增".$sbav."品牌\n";
            foreach( $syncInfo['brand']['update'] as $sbuv )
                $rs['msg'] .= "已更新".$sbuv."品牌\n";
            foreach( $syncInfo['cat'] as $scv )
                $rs['msg'] .= "已新增".$scv."分类\n";
        }else{
            $rs['msg'] = "操作完成";
        }
        $rs['goods_id'] = $oSupplier->getLocalGoodsId($_POST['supplier_id'], $_POST['object_id']);

        echo json_encode($rs);
    }
    function updateGoods(){
        set_error_handler(array(&$this,'_ajaxErrorHandler'));
        $oDataSync = $this->system->loadModel('distribution/datasync');
        $oSupplier = $this->system->loadModel('distribution/supplier');
        $syncInfo = $oDataSync->preDownload($_POST['supplier_id'],$_POST['object_id'],$_POST['command_id']);
        $oSupplier->updateSyncStatus($_POST['command_id'],$_POST['supplier_id'],'unmodified');
        $cat = $this->system->loadModel('goods/productCat');
        $brand = $this->system->loadModel('goods/brand');
        $brand->brand2json();
        $cat->cat2json();

        $rs = array();
        $rs['msg'] = "";
        foreach( $syncInfo['type'] as $stv )
            $rs['msg'] .= "已新增 ".$stv." 类型\n";
        foreach( $syncInfo['spec'] as $ssv )
            $rs['msg'] .= "已新增 ".$ssv." 规格\n";
        foreach( $syncInfo['brand']['add'] as $sbav )
            $rs['msg'] .= "已新增 ".$sbav." 品牌\n";
        foreach( $syncInfo['brand']['update'] as $sbuv )
            $rs['msg'] .= "已更新 ".$sbuv." 品牌\n";
        foreach( $syncInfo['cat'] as $scv )
            $rs['msg'] .= "已新增 ".$scv." 分类\n";

        echo json_encode($rs);

    }
    function updateGoodsImage(){
        set_error_handler(array(&$this,'_ajaxErrorHandler'));
        $oDataSync = $this->system->loadModel('distribution/datasync');
        $oSupplier = $this->system->loadModel('distribution/supplier');
        $oSupplier->updateGoodsImageFailed($_POST['command_id'],$_POST['supplier_id']);
        $oDataSync->updateGoodsImage($_POST['command_id'],$_POST['supplier_id'],$_POST['object_id']);
        $oSupplier->updateSyncStatus($_POST['command_id'],$_POST['supplier_id'],'done');
    }
    function syncStore(){
        set_error_handler(array(&$this,'_ajaxErrorHandler'));
        $oDatasync = $this->system->loadModel('distribution/datasync');
        $oSupplier = $this->system->loadModel('distribution/supplier');
        $oDatasync->syncProductStore($_POST['supplier_id'],$_POST['object_id']);
        $oSupplier->updateSyncStatus($_POST['command_id'],$_POST['supplier_id'],'done');
    }
    function syncMarketable(){
        $oSupplier = $this->system->loadModel('distribution/supplier');
        $commandInfo = $oSupplier->getCommandInfo($_POST['command_id'], $_POST['supplier_id']);
        $oSupplier->updateGoodsMarketable($_POST['supplier_id'],$_POST['object_id'],$commandInfo['goods_info']['marketable']);
        $oSupplier->updateSyncStatus($_POST['command_id'],$_POST['supplier_id'],'done');
    }

    function syncUnMarketable(){
        $oSupplier = $this->system->loadModel('distribution/supplier');
        $oSupplier->updateGoodsMarketable($_POST['supplier_id'],$_POST['object_id'],'false');
        $oSupplier->updateSyncStatus($_POST['command_id'],$_POST['supplier_id'],'done');
    }


    function deleteGoods(){
        $oSupplier = $this->system->loadModel('distribution/supplier');
        $oSupplier->removeGoods($_POST['supplier_id'],$_POST['object_id']);
        $oSupplier->updateSyncStatus($_POST['command_id'],$_POST['supplier_id'],'done');
    }
    function updateGoodsProducts(){
        set_error_handler(array(&$this,'_ajaxErrorHandler'));
        $oDatasync = $this->system->loadModel('distribution/datasync');
        $oSupplier = $this->system->loadModel('distribution/supplier');
        $oDatasync->updateGoodsProduct($_POST['supplier_id'],$_POST['object_id'],$_POST['command_id']);
        $oSupplier->updateSyncStatus($_POST['command_id'],$_POST['supplier_id'],'unmodified');
    }





    function refreshSupplier(){
        set_error_handler(array(&$this,'_ajaxErrorHandler'));
        $osync = $this->system->loadModel('distribution/datasync');
        $count = "";
        //TODO:分页更新供应商？
        $osync->syncSupplier(NULL,$count);
    }
    function downloadPline(){
        $datasync = $this->system->loadModel('distribution/datasync');

        if(!$datasync->ifDownloading($_POST['supplier_id'])){
            set_error_handler(array(&$this,'_ajaxErrorHandler'));
            //清空该供应商的下载数的记录文件
            $log_file = HOME_DIR . "/logs/goodsdown.log";
            $log_info = json_decode(file_get_contents($log_file),true);
            if(isset($log_info[$_POST['supplier_id']])){
                unset($log_info[$_POST['supplier_id']]);
            }
            file_put_contents($log_file,json_encode($log_info),LOCK_EX);

            $oSyncJob = $this->system->loadModel('distribution/syncjob');
            $oSupplier = $this->system->loadModel('distribution/supplier');

            $time = time();
            //$oSupplier->updatePlineInSupplier($_POST['supplier_id'],$_POST['pline_id']);
            foreach( $_POST['pline_id'] as $k => $v ){
                $v = explode('|',$v);
                $_POST['pline_id'][$k] = array(
                    'cat_id' => ( $v[1]?($v[0].','.$v[1]):$v[0] ),
                    'brand_id' => $v[2]
                );
            }

            $datasync->addSyncTmpData($_POST['supplier_id'],$_POST['pline_id']);  //分配下载上游全部品牌、类型、规格到临时数据表的任务
            $oSyncJob->addDataSyncJob($_POST['supplier_id'],$_POST['pline_id'], true,20,$_POST['cat']);
            $datasync->filterUpdateList_1( $_POST['supplier_id'],'download' ); 
            $oSupplier->updateSupplierSynctime($_POST['supplier_id'],$time);
            $oSupplier->updateSupplierHasNew($_POST['supplier_id']);
        }else{
            header('HTTP/1.1 501 Not Implemented');
            $msg = urlencode('正在下载中，请不要重复下载');
            header('notify_msg:'.$msg);
        }
    }

    //下载数据
    function doDataSync(){return true;
        set_error_handler(array(&$this,'_ajaxErrorHandler'));
        $oSyncJob = $this->system->loadModel('distribution/syncjob');
        if( ($_POST['step'] == '1' && $oSyncJob->checkLock('data_sync'))){
             echo json_encode( array('status'=>'lock') );
        }else{
            if($oSyncJob->doDataSyncJob() === 0){
                echo json_encode( array('status'=>'finish') );
            }else{
                $oSupplier = $this->system->loadModel('distribution/supplier');
                $jobList = $oSupplier->getDoSyncJobList();
                if( empty( $jobList ) ){
                    echo json_encode( array('status'=>'continue') );
                }else{
                    echo json_encode( array('status'=>'continue','joblist'=>$jobList) );
                }
            }
        }
    }
    function doGoodsSync(){return true;
        set_error_handler(array(&$this,'_ajaxErrorHandler'));
        $oSyncJob = $this->system->loadModel('distribution/syncjob');
        if($_POST['step'] == '1' && $oSyncJob->checkLock('download_goods')){
            $echoJson['msg'] = '';
            $echoJson['status'] = 'lock';
            echo json_encode($echoJson);
        }else{
            $rs = $oSyncJob->doGoodsDownloadJob();
            $echoJson = array(
                'cat_id'=> (($rs['current'] == $rs['count'])?$rs['cat_id']:'')
            );

            if( $rs === 0 ){
                    if( $_POST['step'] != '1' ){
                    $echoJson['msg'] = '下载完成';
                    $echoJson['status'] = 'finish';
                    echo json_encode($echoJson );
                    $cat = $this->system->loadModel('goods/productCat');
                    $brand = $this->system->loadModel('goods/brand');
                    $brand->brand2json();
                    $cat->cat2json();
                }else{
                    $echoJson['msg'] = '';
                    $echoJson['status'] = 'finish';
                    echo json_encode($echoJson);
                }
            }
            else{
                $oSupplier = $this->system->loadModel('distribution/supplier');
                $suinfo = $oSupplier->getSupplierInfo($rs['supplier_id'],'supplier_brief_name');
                $echoJson['msg'] = '已经下载了'.$rs['current'].'/'.$rs['count'].'条 '.$suinfo['supplier_brief_name'];
                $echoJson['status'] = 'continue';
                echo json_encode( $echoJson );
            }
        }
    }
    function doImagesSync(){return true;
        set_error_handler(array(&$this,'_ajaxErrorHandler'));
        $oSyncJob = $this->system->loadModel('distribution/syncjob');

        // 指定command_id下载图片,而不是下载所有在任务列表中的任务 2009-10-10 18:08 wubin
        if(empty($_POST['command_id'])) {
            $commandid = null;
        } else {
            $commandid = $_POST['command_id'];
        }

        if( ($_POST['step'] == '1' && $oSyncJob->checkLock('download_image')) || $oSyncJob->downloadImage(false,$commandid) === 0 )  //去除锁的限制，因为执行下载商品任务时会锁任务，此时再检查会导致死锁。by hujianxin
            echo json_encode( array('status'=>'finish') );
        else
            echo json_encode( array('status'=>'continue') );
    }

    function doSupplierApiListJob($supplier_id,$api_name,$api_action){
        set_error_handler(array(&$this,'_ajaxErrorHandler'));
        $oSupplier = $this->system->loadModel('distribution/supplier');
        if($oSupplier->doSupplierApiListJob($supplier_id,$api_name,$api_action)){
            echo json_encode( array('status'=>'continue') );
        }else{
            echo json_encode( array('status'=>'finish') );
        }
    }

    function suppilerApiList(){
        $oSupplier = $this->system->loadModel('distribution/supplier');
        echo json_encode( $oSupplier->getSupplierApiList() );
    }

    /*
    function supplierGoodsInfo(){
        $oDatasync = $this->system->loadModel('distribution/datasync');
        $goods = $oDatasync->getSupplierGoodsInfo($_GET['supplier_id'],$_GET['object_id']);
        $this->pagedata['goods'] = $goods;
        $this->setView('distribution/goods/goods_info.html');
        $this->output();
    }
     */

    function coverGoodsInfo(){
        set_error_handler(array(&$this,'_pageErrorHandler'));
        $oDatasync = $this->system->loadModel('distribution/datasync');

        $goods = $oDatasync->getSupplierGoodsInfo($_POST['supplier_id'],$_POST['object_id']);
        $goods['params'] = unserialize($goods['params']);

        $goods['type_id'] = $oDatasync->_getLocalTypeByPlatType($_POST['supplier_id'],$goods['type_id']);
        $goods['brand_id'] = $oDatasync->_getLocalBrandByPlatBrand($_POST['supplier_id'],$goods['brand_id']);
        $gType = $this->system->loadModel('goods/gtype');
        $typeinfo = $gType->getTypeDetail($goods['type_id']);
        $oBrand = $this->system->loadModel('goods/brand');
        $brandList = $oBrand->getTypeBrands($goods['type_id']);
        
        // 整理商品扩展属性 不用在模板上做相关逻辑 2009-11-26 10:54 wubin
        foreach($typeinfo['props'] as $key => $row) {
            $typeinfo['props'][$key]['selected'] = $goods['p_'.$key];
             /* edit by TT
             $typeinfo['props'][$key]['name'] = "goods[p_$key]";
             if($row['type'] == 'select') {
                  $typeinfo['props'][$key]['selected'] = $row['options'][$goods['p_'.$key]];
             } else {
                  $typeinfo['props'][$key]['selected'] = $goods['p_'.$key];
             }
             */
        }
        // 整理属性参数 不用在模板上做相关逻辑 2009-11-26 13:14 wubin
        if($typeinfo['params']) {
            foreach($typeinfo['params'] as $key => $row){
                foreach($row as $key1 => $row1) {
                    $typeinfo['params'][$key][$key1] = array(); // 改变值的类型 2009-11-26 13:56 wubin
                    $typeinfo['params'][$key][$key1]['value'] = $row1;
                    $typeinfo['params'][$key][$key1]['name'] = "goods[params][$key][$key1]";
                    $typeinfo['params'][$key][$key1]['selected'] = $goods['params'][$key][$key1];
                }
            }
        }

        $goodsJson = array();
        $goodsJson['type_id'] = $goods['type_id'];
        $goodsJson['brand_id'] = $goods['brand_id'];
        $goodsJson['unit'] = $goods['unit'];
        $goodsJson['brief'] = $goods['brief'];
        $goodsJson['mktprice'] = $goods['mktprice'];
        $goodsJson['name'] = $goods['name'];
        $goodsJson['weight'] = $goods['weight'];

        $this->pagedata['brandList'] = $brandList;
        $this->pagedata['goodsInfoJson'] = json_encode($goodsJson);
        $this->pagedata['goods'] = $goods;
        $this->pagedata['prototype'] = $typeinfo;
        $this->display('distribution/goods/goods_type_info.html');
    }
    
    /**
     * 485此act 将不再使用
     *
     */
    function supplierGoodsInfo(){
        set_error_handler(array(&$this,'_pageErrorHandler'));
        $oDatasync = $this->system->loadModel('distribution/datasync');
        $oSupplier = $this->system->loadModel('distribution/supplier');
        $goods = $oDatasync->getSupplierGoodsInfo($_GET['supplier_id'], $_GET['object_id']);
        $supplierName = $oSupplier->getSupplierInfo($_GET['supplier_id'],'supplier_brief_name');
        $goods['type_props'] = unserialize($goods['type_props']);
        $goods['params'] = unserialize($goods['params']);
        foreach( $goods['products'] as $pk => $pv ){
        $goods['products'][$pk]['props'] = unserialize($pv['props']);
        }
        $this->pagedata['supplier_brief_name'] = $supplierName['supplier_brief_name'];
        $this->pagedata['goods'] = $goods;
        $this->pagedata['supplier_id'] = $_GET['supplier_id'];
        $this->pagedata['object_id'] = $_GET['object_id'];
        $this->pagedata['command_id'] = $_GET['command_id'];
        $this->pagedata['local_goods_id'] = $_GET['local_goods_id'];
        $this->display('distribution/goods/goods_info.html');
    }

    function syncComplete(){
        $oCat = $this->system->loadModel('goods/productCat');
        $catName = $oCat->getFieldById($_POST['cat_id'],array('cat_name'));
        $this->pagedata['cat_name'] = $catName['cat_name'];
        $this->pagedata['cat_id'] = $_POST['cat_id'];
        $this->display('distribution/sync_complete.html');
    }

    function generalize(){  //访问外部推广链接
        $this->pagedata['url'] = GENERALIZE_URL;
        $this->page('distribution/generalize.html');
    }

    /**
     * 做自动同步处理 wubin 2009-09-07 16:30
     *
     * @access public
     * @return void
     */
    function doAutoSync() {
        set_error_handler(array(&$this,'_ajaxErrorHandler'));

        $oAutoSync = $this->system->loadModel('distribution/autosync');
        if(empty($_POST['step'])) {
            $count = $oAutoSync->getAutoSyncTaskCount($_POST['supplier_id']);

            if(empty($count)) {
                $aResult['status'] = 'done';
                die(json_encode($aResult));
            }

            $aResult['count'] = $count;
        } else {
            $aResult['count'] = $_POST['step'];
        }

        // 取一条任务
        $aTask = $oAutoSync->getAutoSyncTask($_POST['supplier_id']);

        if(empty($aTask)) {
            $aResult['status'] = 'done';
            die(json_encode($aResult));
        }

        $oAutoSync->doSync($aTask['supplier_id'],$aTask['command_id'],$aTask['local_op_id']);
        $oAutoSync->deleteAutoSyncTask($aTask['supplier_id'],$aTask['command_id']);

        // 如果是新增商品或更新图片 将抛出图片下载的ajax任务 2009-10-10 18:12 wubin
        $aResult['command_id'] = $aTask['command_id'];
        $aResult['op_id'] = $aTask['local_op_id'];

        $aResult['status'] = 'continue';
        $aResult['count']--;

        echo json_encode($aResult);
    }

    /**
     * 自动同步任务 wubin 2009-09-10 15:13 (中断后继续)
     *
     * @access public
     * @return void
     */
    function autoSyncJob() {
        $oAutoSync = $this->system->loadModel('distribution/autosync');
        echo json_encode($oAutoSync->getAutoSyncSupplierList());
    }

    /**
     * 成本价同步 wubin 2009-09-14 15:45:15
     *
     * @access public
     * @return void
     */
    function doCostSync() {
        $oCostSync = $this->system->loadModel('distribution/costsync');
        // 生成成本价同步任务
        if(!isset($_POST['step'])) {
            $oCostSync->generateCostSyncJob($_POST['supplier_id']);
            $aResult['count'] = $oCostSync->getCostSyncJobCount($_POST['supplier_id']);
        }else{
            // 做成本价同步任务
            $aResult['status'] = $oCostSync->doCostSyncJob($_POST['supplier_id']);
            $aResult['count']  = $_POST['step'] - 1;
            if($aResult['status'] == 'done'){
                $aResult['count'] = $oCostSync->getCostSyncDoneCount($_POST['supplier_id']);
            }
        }

        die(json_encode($aResult));
    }

    /**
     * 成本价同步任务 wubin 2009-09-16 17:28:29 (中断后继续)
     *
     * @access public
     * @return void
     */
    function costSyncJob() {
        $oCostSync = $this->system->loadModel('distribution/costsync');
        echo json_encode($oCostSync->getCostSyncList());
    }

    function _ajaxErrorHandler( $errno, $errstr, $errfile, $errline){
        if($errstr == '0x015'){
            if($_REQUEST['act'] == 'refreshSupplier') {
                exit;
            }
            // 取消所有与supplier 相关的同步任务
            $this->_cancelTask($_REQUEST['supplier_id']);
            
            $this->_err_process('供应商:'.$_REQUEST['supplier_id'].'服务暂时不可用','ajax');
        }
        
        parent::_ajaxErrorHandler($errno, $errstr, $errfile, $errline);
    }
    
    function _pageErrorHandler($errno, $errstr, $errfile, $errline){
        if($errstr == '0x015'){
            // 取消所有与supplier 相关的同步任务
            $this->_cancelTask($_REQUEST['supplier_id']);
            if($_REQUEST['act'] != 'index' && $_REQUEST['act'] !='supplierList') {
                $this->_err_process('供应商:'.$_REQUEST['supplier_id'].'服务暂时不可用','page','index.php?ctl=distribution/supplier&act=index');
            }
        }
        
        parent::_pageErrorHandler($errno, $errstr, $errfile, $errline);
    }
    
    /**
     * 取消指定的$supplier_id的同步任务
     *
     * @param int $supplier_id
     */
    function _cancelTask($supplier_id) {
        $supplier_id = intval($supplier_id);
        if(empty($supplier_id)) return;
        
        
        $oSupplier = $this->system->loadModel('distribution/supplier');
        $oSupplier->cancelTask($supplier_id);
    }
    
    function fixType(){
        $oDatasync = $this->system->loadModel('distribution/datasync');
        $oDatasync->fixType();
    }
    
    function clearGoods(){
        $oDatasync = $this->system->loadModel('distribution/datasync');
        $oDatasync->clearBadGoods();
        //$oDatasync->clearGoods();
    }
    
    function autoSync(){
        $oDatasync = $this->system->loadModel('distribution/datasync');
        $oDatasync->autoSync();
    }
    
    function fixGoodsImage($supplier_id){
        $oDatasync = $this->system->loadModel('distribution/datasync');
        $oDatasync->fixGoodsImage($supplier_id);
    }
    
    /*****  以下为平台同步B2C代码    开始     by yangyichao 2012-04-26  ****/
    function syncPlatformDataList(){
        set_error_handler(array(&$this,'_pageErrorHandler'));
        $searchData = array(
            'update_content' => intval($_POST['update_content']),
            'ctrl_status' => intval($_POST['ctrl_status']),
            's_update_time' => empty($_POST['s_update_time'])?false:($_POST['s_update_time']),
            'e_update_time' => empty($_POST['e_update_time'])?false:($_POST['e_update_time']),
            'search_name' => trim($_POST['search_name'])
        );
        $page =  $_POST['page']?intval($_POST['page']):1;
        $this->_syncPlatformDataList($_GET['supplier_id'],$searchData, $page);
        $oAutoSync = $this->system->loadModel('distribution/autosync');
        $sync_rules = $oAutoSync->get_single_rules();
        $this->pagedata['rule_detail']= $oAutoSync->getGoodsSyncRule($sync_rules);
        $this->pagedata['supplier_id'] = $_GET['supplier_id'];
        $this->page('distribution/platform_data_sync.html');
    }
    
    function _syncPlatformDataList($supplierid, $searchData, $page=1, $limit=20){
        set_error_handler(array(&$this,'_pageErrorHandler'));
        $command = array(
            '0' => '商品新增',
            '1' => '商品上下架',
            '2' => '货品库存',
            '3' => '商品图片更新',
            '4' => '商品更新',
            '5' => '货品更新',
            '6' => '商品新增',
            '8' => '价格更新',
            '9' => '商品新增',
        );
        $oSupplier = $this->system->loadModel('distribution/supplier');
        $oPlatform = $this->system->loadModel('distribution/platformsync');
        $sList = $oPlatform->getSyncDataList($supplierid, $searchData, $count, $page , $limit, $command);

        $status = array(
            'finish' => '<span class="fontcolorLightGray">已同步完成</span>',
            'hand' => '<span class="fontcolorRed">须手动同步</span>',
        );

        $pager = array(
            'current'=> $page,
            'total'=> ceil($count/$limit),
            'link'=> 'javascript:turnd(_PPP_)',
            'token'=> '_PPP_'
        );
        $suDomain = $oSupplier->getSupplierInfo($supplierid,'domain');
        $this->pagedata['supplier_domain'] = (substr( $suDomain['domain'],-1,1 ) == '/')?$suDomain['domain']:$suDomain['domain'].'/';
        $this->pagedata['pager'] = $pager;
        $this->pagedata['page'] = $page;
        $this->pagedata['status'] = $status;
        $this->pagedata['command'] = $command;
        $this->pagedata['sList'] = $sList;
        $this->pagedata['supplier_id'] = $supplierid;
    }
    
    function getPlatformSyncDataList(){
        set_error_handler(array(&$this,'_pageErrorHandler'));
        $searchData = array(
            'update_content' => intval($_POST['update_content']),
            'ctrl_status' => intval($_POST['ctrl_status']),
            's_update_time' => empty($_POST['s_update_time'])?false:($_POST['s_update_time']),
            'e_update_time' => empty($_POST['e_update_time'])?false:($_POST['e_update_time']),
            'search_name' => trim($_POST['search_name'])
        );
        $page =  $_POST['page']?intval($_POST['page']):1;
        $this->_syncPlatformDataList($_GET['supplier_id'],$searchData, $page);
        $this->pagedata['supplier_id'] = $_GET['supplier_id'];
        $this->display('distribution/platform_data_sync_list.html');
    }
    
    function updatePlatformGoodsImage(){
        set_error_handler(array(&$this,'_ajaxErrorHandler'));
        $oDataSync = $this->system->loadModel('distribution/datasync');
        $oSupplier = $this->system->loadModel('distribution/supplier');
        $oDataSync->updateGoodsImage($_POST['command_id'],$_POST['supplier_id'],$_POST['object_id']);
        $oSupplier->updateSyncStatus($_POST['command_id'],$_POST['supplier_id'],'done');
    }
    
    function syncGoodsProducts(){
        set_error_handler(array(&$this,'_ajaxErrorHandler'));
        $oPlatform = $this->system->loadModel('distribution/platformsync');
//         echo '<pre>';
        $sync_info = $oPlatform->getSyncInfo($_POST['supplier_id'],$_POST['object_id']);
//        $localRelateData = $oPlatform->getRelationData($_POST['supplier_id']);

//         if($_POST['command_id']==3){
//              $oPlatform->_handle_images($_POST['supplier_id'],$sync_info['goods_id'],$sync_info['supplier_goods_id']);
//              $oPlatform->setSyncFinish($sync_info['sync_id'],$_POST['supplier_id']);
//         }else{
            $localRelateData = $oPlatform->getRelationData($_POST['supplier_id']);
            $goods_id = $oPlatform->_handle_goods($_POST['supplier_id'],$_POST['command_id'],$sync_info['goods_struct'],$sync_info['product_struct'],$localRelateData);
//         }
            $oPlatform->updateGoodsid($_POST['supplier_id'],$_POST['object_id'],$goods_id);
    }
    
    function syncUpdate(){
        header("Content-type:text/html;charset=utf-8");
        $supplier_id = $_GET['supplier_id'];
        $oPlatform = $this->system->loadModel('distribution/platformsync');
        if ($sync_info = $oPlatform->getSyncInfo($supplier_id)){
            $nexturl = 'index.php?ctl=distribution/supplier&act=syncUpdate&supplier_id='.$supplier_id.'&nexttime='.time();
            !$_SESSION['localRelateData']['cat'] and $_SESSION['localRelateData'] = $oPlatform->getRelationData($supplier_id);
//             if($sync_info['command']==3){
//                 $oPlatform->_handle_images($supplier_id,$sync_info['goods_id'],$sync_info['supplier_goods_id']);
//                 $oPlatform->setSyncFinish($sync_info['sync_id'],$supplier_id);
//             }else{
                $oPlatform->_handle_goods($supplier_id,$sync_info['command'],$sync_info['goods_struct'],$sync_info['product_struct'],$_SESSION['localRelateData']);
//             }
            echo __('同步商品【').($sync_info['goods_name']).__('】已成功，将继续下一个商品同步，请勿进行其他页面操作。').'<script>syncUpdate("'.$nexturl.'")</script>';
        }else{
            unset($_SESSION['localRelateData']);
            echo __("所有商品操作完成！");
        }
    }
    
    function syncImgUpdate(){
        header("Content-type:text/html;charset=utf-8");
        $supplier_id = $_GET['supplier_id'];
        @ini_set('memory_limit','128M');
        $oPlatform = $this->system->loadModel('distribution/platformsync');
        if ($sync_info = $oPlatform->getSyncImagesInfo($supplier_id)){
            $nexturl = 'index.php?ctl=distribution/supplier&act=syncImgUpdate&supplier_id='.$supplier_id.'&nexttime='.time();
            //!$_SESSION['localRelateData']['cat'] and $_SESSION['localRelateData'] = $oPlatform->getRelationData($supplier_id);
            $oPlatform->_handle_images($supplier_id,$sync_info['goods_id'],$sync_info['supplier_goods_id']);
            //$oPlatform->setSyncFinish($sync_info['sync_id'],$supplier_id);
            echo __('同步商品【').($sync_info['goods_name']).__('】已成功，将继续下一个商品同步，请勿进行其他页面操作。').'<script>syncUpdate('.$supplier_id.',"'.$nexturl.'")</script>';
        }else{
            //unset($_SESSION['localRelateData']);
            echo __("所有商品操作完成！");
        }
    }
    
    function getSyncGoodsid(){
        $oSupplier = $this->system->loadModel('distribution/supplier');
        $goods_id = $oSupplier->getLocalGoodsId($_POST['supplier_id'], $_POST['object_id']);
        echo $goods_id;
    }

    /*****  以下为平台同步B2C代码    结束  ********************************/
}
?>
