<?php defined('CORE_DIR') || exit('入口错误'); ?>
<?php
class ctl_spec extends adminPage{

    var $workground = 'goods';

    function addCol(){
        $specDesc =  unserialize($_POST['goods_spec_desc']);
        
        $objSpec = &$this->system->loadModel('goods/specification');
        $aSpec = array();
        if(empty( $specDesc )){
            $aSpec = $objSpec->getListByTypeId($_GET['type_id']);
        }
        else{
            $aSpec = $objSpec->getListByIdArray( array_keys($specDesc) );
            foreach( $aSpec as $key => $rows ){
                $aSpec[$key]['sel_options'] = $specDesc[$rows['spec_id']];
            }
        }
        foreach($aSpec as $key => $rows){
            $aVal = $objSpec->getValueList($rows['spec_id']);
            foreach($aVal as $k=>$v){
                $aVal[$k]['spec_value'] = $v['spec_value'];
            }
            $aSpec[$key]['options'] = $aVal ;
            $aSpec[$key]['spec_name'] = $rows['spec_name'];
        }
        if( $_POST['goods'] ){
            $this->pagedata['goods_args'] = json_encode( array('goods_args'=>$_POST['goods']));
        }
        $this->pagedata['spec_default_pic'] = $this->system->getConf('spec.default.pic');
        $this->pagedata['specs'] = $aSpec;
        $this->pagedata['ctlType'] = $_POST['ctlType'];
        $this->display('product/spec_addcol.html');
    }

    function specValue($specId){
        $objSpec = &$this->system->loadModel('goods/specification');
        $aSpec = $objSpec->getFieldById($specId, array('*'));
        $aVal = $objSpec->getValueList($specId);
        $aSpec['options'] = $aVal ;

        $this->pagedata['sItem'] = $aSpec;
        $this->pagedata['spec_default_pic'] = $this->system->getConf('spec.default.pic');
        $this->display('product/spec_value.html');
    }

    function _doCreatePro( $pro, $spec , $goods_args ){
        if( empty( $spec ) ){
            $res = array();
            foreach( $pro as $pk => $pv ){
                $res[$pk]['sel_spec'] = $pv;
                foreach( $goods_args as $argsk => $argsv )
                    $res[$pk][$argsk] = $argsv;
            }
            return $res;
        }

        $firestSpec = array_shift( $spec );

        $rs = array();
        foreach( $firestSpec as $sitem ){
            foreach( $pro as $pitem ){
                $apitem = $pitem ;
                array_push( $apitem , $sitem );
                $rs[] = $apitem;
            }
            if( empty($pro) )
                $rs[] = array( $sitem );
        }
       return $this->_doCreatePro( $rs, $spec , $goods_args );
    }

    function selAlbumsImg(){
        $this->pagedata['selImgs'] = explode(',',$_POST['selImgs']);
        $this->pagedata['img'] = $_POST['img'];
        $this->display('product/spec_selalbumsimg.html');
    }

    function doAddCol(){
            $memberLevel = &$this->system->loadModel('member/level');
            $this->pagedata['mLevels'] = $memberLevel->getList('member_lv_id,dis_count');
            $this->pagedata['spec']['vars'] = $_POST['spec_vars'];
            $this->pagedata['goods']['spec_value_image'] = $_POST['spec_value_image'];
            $this->pagedata['goods']['spec_desc'] = $_POST['goods']['spec_desc'];
            $spec_vars = array();
            foreach( $_POST['spec_vars'] as $k =>$v )
                $spec_vars[$k]['spec_name'] = $v;
            $this->pagedata['specname'] = $spec_vars;
            $this->pagedata['goods']['spec_desc_str'] = urlencode(serialize($_POST['goods']['spec_desc']));
            if( $_POST['goods_args'] ){
                $this->pagedata['goods_args'] = json_encode( array( 'goods_args'=>$_POST['goods_args'] ) );
            }
            if( $_GET['create'] == 'true' ){
                $pro = array();
                $spec = array();

                $i = 1;
                foreach( $_POST['goods']['spec_desc'] as $sid => $sitem ){
                    $j = 1;
                    foreach( $sitem as $psid => $psitem ){
                        $spec[$i][$j] = array(
                            'spec_id'=>$sid,
                            'p_spec_value_id'=>$psid,
                            'spec_value'=>$psitem['spec_value'],
                            'spec_type'=>$psitem['spec_type'],
                            'spec_value_id'=>$psitem['spec_value_id'],
                            'spec_image'=>$psitem['spec_image'],
                            'spec_goods_images'=>$psitem['spec_goods_images']
                        );
                        $j++;
                    }
                    $i++;
                }
                $pro = $this->_doCreatePro( $pro, $spec , $_POST['goods_args'] );
                $this->pagedata['fromType'] = 'create';
                $this->pagedata['goods']['products'] = $pro;

            }
            $this->pagedata['needUpValue'] = json_encode($_POST['needUpValue']);
            $this->pagedata['spec_default_pic'] = $this->system->getConf('spec.default.pic');
            $this->pagedata['mlevels'] = $memberLevel->getList('member_lv_id,dis_count');
            $this->pagedata['storeplace_display_switch'] = $this->system->getConf('storeplace.display.switch');
            $this->display('product/spec.html');
    }

    function addSpecTab(){
        $objSpec = &$this->system->loadModel('goods/specification');
        $spec = $objSpec->getFieldById($_POST['spec_id'], array('*'));
        $spec['spec_name'] = $spec['spec_name'];
        $specValue = $objSpec->getValueList($_POST['spec_id']);
        foreach($specValue as $k=>$v){
            $specValue[$k]['spec_value'] = $v['spec_value'];
        }
        $this->pagedata['spec'] = $spec;
        $this->pagedata['specValue'] = $specValue;
        $this->pagedata['spec_default_pic'] = $this->system->getConf('spec.default.pic');
        $this->display('product/spec_addspectab.html');
    }

    function addSpecValue(){
        $_POST = stripslashes_array($_POST);
        foreach( $_POST['spec'] as $k => $v ){
           $this->pagedata[$k] = $v;
        }
        $this->pagedata['pSpecId'] = time().$_POST['sIteration'];
        $this->pagedata['spec_default_pic'] = $this->system->getConf('spec.default.pic');
        $this->display('product/spec_addspecvalue.html');
    }

    function addRow(){
        /*
        foreach($_POST['vars'] as $d=>$vs){
            $vars[] = array('id'=>$d,'name'=>$vs,'vars'=>json_encode(array_unique($_POST['val'][$d])));
        }
        */
        if( $_POST['bn'] ){
            if(!empty($_POST['bn'][0]))
            $_POST['bn'][0]=substr($_POST['bn'][0],0,-1).(substr($_POST['bn'][0],-1)+1);
            $_POST['goods_args'] = array(
                'price' => $_POST['price'][0],
                'cost' => $_POST['cost'][0],
                'product_bn' => $_POST['bn'][0],
                'mktprice' => $_POST['mktprice'][0],
                'weight' => $_POST['weight'][0],
                'store' => $_POST['store'][0]
            );
            foreach( $_POST['mprice'] as $mpk => $mpv ){
                $_POST['goods_args']['mprice'][$mpk] = $mpv[0];
            }
        }
        $spec_desc = unserialize($_POST['spec_desc']);
        $memberLevel = &$this->system->loadModel('member/level');
        $this->pagedata['mLevels'] = $memberLevel->getList('member_lv_id,dis_count');
        $this->pagedata['goods']['spec_desc'] = $spec_desc;
        $this->pagedata['goods_args'] = $_POST['goods_args'];
        $this->pagedata['spec_default_pic'] = $this->system->getConf('spec.default.pic');
        $this->pagedata['storeplace_display_switch'] = $this->system->getConf('storeplace.display.switch');

        $this->display('product/spec_row.html');
    }

    function addSpec($typeId = 0) {
        $objSpec = &$this->system->loadModel('goods/specification');
        $aSpec = array();
        if($typeId)
            $aSpec = $objSpec->getListByTypeId($typeId);
        else
            $aSpec = $objSpec->getListByIdArray();
        $this->pagedata['specs'] = $aSpec;
        $this->display('product/spec_select.html');
    }

}

?>
