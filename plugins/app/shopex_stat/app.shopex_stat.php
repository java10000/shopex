<?php

class app_shopex_stat extends app{
    var $ver = '3.6.4';
    var $name='营销统计工具';
    var $website = 'http://www.shopex.cn';
    var $author = 'Rocky Anjiaxin Fuxiudong';

    function output_modifiers(){
        return array(
            'shop:*'=>'modifiers:modify',
        );
    }
    
    function getMenu(&$menu){
        array_unshift(
            $menu['analytics']['items'],
            array(
                'type'=>'group',
                'label'=>'营销统计工具',
                'items'=>array(
                    array(
                        "type"=>'menu',
                        'label'=>__('查看分析报表'),
                        'link'=>'?ctl=plugins/syj&redirect=1'
                    )
                )
            )
        );
    }
    
    function modify_pagedata(&$ctl){
        $ctl->pagedata["shopex_stat_js"] = $this->get_js($ctl->pagedata);
    }

    function get_js($pagedata){
        $mdl_storager  = $this->system->loadModel('system/storager');
        $aData = array(
            'page_index'=>array(
                'pagetype'=>'485-index',
            ),
            'brand_showList'=>array(
                'pagetype'=>'485-brandlist',
            ),
            'brand_index'=>array(
                'pagetype'=>'485-branddetail',
                'pageid'=>$pagedata['data']['brand_id'],
                'methods'=>array('$appendBrand'=>array(array($pagedata['data']['brand_id'],$pagedata['data']['brand_name']))),
            ),
            'product_index'=>array(
                'pagetype'=>'485-goodsdetail',
                'pageid'=>$pagedata['goods']['goods_id'],
                'methods'=>array('$appendGoods'=>array(array(
                    $pagedata['goods']['cat_id'],
                    $pagedata['goods']['brand_id'],
                    $pagedata['goods']['name'],
                    $pagedata['goods']['goods_id'],
                    floatval($pagedata['goods']['price']),
                    $mdl_storager->getUrl($pagedata['goods']['small_pic'])
                ))),
            ),
            'passport_login'=>array(
                'pagetype'=>'485-login',
            ),
            'passport_index'=>array(
                'pagetype'=>'485-login',
            ),
            'passport_signup'=>array(
                'pagetype'=>'485-register',
                'extra_js'=>"$$('div.RegisterWrap')[0].addEvent('click',function(e){if(e.target.hasClass('btn-register')){_ecq.push(['\$regAction', 'registerClick']);}});",
            ),
            'gift_showList'=>array(
                'pagetype'=>'485-giftlist',
            ),
            'gift_index'=>array(
                'pagetype'=>'485-giftdetail',
                'pageid'=>$pagedata['details']['gift_id'],
                'methods'=>array('$appendGift'=>array(array(
                    $pagedata['details']['name'],
                    $pagedata['details']['gift_id'],
                    $pagedata['details']['giftcat_id']?$pagedata['details']['giftcat_id']:null,
                    $pagedata['details']['cat']?$pagedata['details']['cat']:null
                ))),
            ),
            'member_index'=>array(
                'pagetype'=>'485-memberindex',
            ),
            'member_orders'=>array(
                'pagetype'=>'485-memberorder',
            ),
            'member_pointHistory'=>array(
                'pagetype'=>'485-memberpoint',
            ),
            'member_comment'=>array(
                'pagetype'=>'485-comment',
            ),
            'cart_addPkgToCart'=>array(
                'pagetype'=>'485-addpkg',
                'pageid'=>intval($this->system->request['action']['args'][0])
            ),

            'cart_addGoodsToCart'=>array(
                'pagetype'=>'485-addGoodsToCart',
                'pageid'=>$_POST['goods']['goods_id'],
            ),
            'paycenter_result'=>array(
                'pagetype'=>'485-orderPayResult',
                'pageid'=>$pagedata['payment']['order_id'],
            ),
        );
        
        $request = $this->system->request['action'];
        $ctl_name = $request['controller'];
        $act_name = $request['method'];
        $request_name = $ctl_name."_".$act_name;

        switch($request_name){
            case 'product_diff':{
                foreach((array)$pagedata['diff']['goods'] as $goods){
                    $goodsdata[] = array($goods['goods_id'], $goods['name']);
                }
                $aData[$request_name] =  array(
                    'pagetype'=>'485-goodscomp',
                    'methods'=>array('$appendCompareItem'=>$goodsdata),
                );
                break;
            }
            case 'tools_products':{
                foreach((array)$pagedata['products'] as $goods){
                    $goodsdata[] = array($goods['goods_id'], $goods['name']);
                }
                $aData[$request_name] =  array(
                    'pagetype'=>'485-history',
                    'methods'=>array('$appendHistory'=>$goodsdata),
                );
                break;
            }
            case 'tools_history':{
                $aData[$request_name] =  array(
                    'pagetype'=>'485-history',
                );
                break;
            }
            case 'member_favorite':{
                foreach((array)$pagedata['favorite'] as $goods){
                    $goodsdata[] = array($goods['goods_id'], $goods['name']);
                }
                $aData[$request_name] =  array(
                    'pagetype'=>'485-memberfavorite',
                    'methods'=>array('$appendFavItem'=>$goodsdata),
                );
                break;
            }
            case 'member_orderdetail':{
                $order_id = $pagedata['order']['order_id'];
                $areainfo = explode(":",$pagedata['order']['receiver']['area']);
                list($province,$city,$town) = explode("/", $areainfo[1]);
                $aData[$request_name] = array(
                    'pagetype'=>'485-orderdetail',
                    'methods'=>array('$appendOrderDetail'=>array(array(
                    $pagedata['order']['order_id'],
                    floatval($pagedata['order']['amount']['final']),
                    floatval($pagedata['order']['shipping']['cost']),
                    $province."/".$city,
                    $town))),
                    );
                break;
            }
            case 'gallery_index':{
                if($pagedata['cat_id']){
                    $pagetype = "485-category";
                    $pageid = $pagedata['cat_id'];
                    $methods = array("\$appendCat"=>array(array($this->system->request['goods_cat'], $pagedata['cat_id'])));
                }
                else{
                    $pagetype = "485-searchresult";
                    $methods = array("\$appendSearchResult"=>array(array(urlencode($pagedata['search_array']),intval($pagedata['searchtotal']))));
                }
                $aData[$request_name] = array(
                    'pagetype'=>$pagetype,
                    'methods'=>$methods,
                );
                if($pageid){
                    $aData[$request_name]['pageid'] = $pageid;
                }
                break;
            }
            case 'cart_index':{
                $aData[$request_name] = $this->get_cart_data($pagedata, '485-cartview');
                break;
            }
            case 'cart_checkout':{
                $aData[$request_name] = $this->get_cart_data($pagedata, '485-checkout');
                break;
            }
            case 'order_index':{
                $mdl_storager = $this->system->loadModel("system/storager");
                $order_id = $pagedata['order']['order_id'];
                if(!$order_id){
                    break;
                }
                $giftItems = $this->db->select("SELECT gift_id, name, point, nums, getmethod FROM sdb_gift_items WHERE order_id='".addslashes($order_id)."'");
                foreach((array)$giftItems as $gift){
                    if($gift['getmethod'] == "exchange"){
                        $giftdata[] = array($order_id, $gift['gift_id'], $gift['name'], $gift['nums'], 'point_exchange', $gift['point']);
                    }
                    else{
                        $gift_type = "order";
                        $giftdata[] = array($order_id, $gift['gift_id'], $gift['name'], $gift['nums'], 'order', $order_id);
                    }
                }
                $goodsItems = $this->db->select("SELECT product_id, name, price, nums, addon, is_type FROM sdb_order_items WHERE is_type='goods' AND order_id='".addslashes($order_id)."'");
                $goodsImgs = array();
                $product2goods = array();
                foreach((array)$goodsItems as $item){
                    if($item['addon']){
                        $tmp_addon = unserialize($item['addon']);
                        $item['name'] .= $tmp_addon['adjname'];
                    }
                    if(!isset($product2goods[$item['product_id']])){
                        $productinfo = $this->db->selectrow("SELECT goods_id FROM sdb_products WHERE product_id=".$item['product_id']);
                        $product2goods[$item['product_id']] = $productinfo['goods_id'];
                    }
                    
                    $goods_id = $product2goods[$item['product_id']];
                    if(!isset($goodsImgs[$goods_id])){
                        $goodsinfo = $this->db->selectrow("SELECT small_pic FROM sdb_goods WHERE goods_id=".$goods_id);
                        $goodsImgs[$goods_id] = $mdl_storager->getUrl($goodsinfo['small_pic']);
                    }
                    
                    $goodsdata[] = array($order_id, $goods_id, $item['name'], $item['price'], $item['nums'], $this->system->mkUrl('product', 'index', array($goods_id)), $goodsImgs[$goods_id]);
                }
                $pkgItems = $this->db->select("SELECT product_id, name, price, nums, addon, is_type FROM sdb_order_items WHERE is_type='pkg' AND order_id='".addslashes($order_id)."'");
                foreach((array)$pkgItems as $item){
                    $pkgproducts = $this->db->select("SELECT p.name, p.product_id, p.goods_id, p.price, p.pdt_desc, pkg.pkgnum FROM sdb_package_product pkg JOIN sdb_products p ON pkg.product_id=p.product_id WHERE pkg.goods_id=".$item['product_id']);
                    if(!$pkgproducts) continue;

                    $pkgdata[] =  array($order_id, $item['product_id'], 'package', $item['name'], $item['price'], $item['nums']);

                    foreach($pkgproducts as $product){
                        if($product['pdt_desc']){
                            $product['name'] .= "(".$product['pdt_desc'].")";
                        }
                        if(!isset($goodsImgs[$product['goods_id']])){
                            $goodsinfo = $this->db->selectrow("SELECT small_pic FROM sdb_goods WHERE goods_id=".$product['goods_id']);
                            $goodsImgs[$product['goods_id']] = $mdl_storager->getUrl($goodsinfo['small_pic']);
                        }
                        $pkgGoodsData[] = array($order_id, $item['product_id'], 'package', $product['goods_id'], $product['name'], $product['price'], $product['pkgnum'], $this->system->mkUrl('product', 'index', array($product['goods_id'])), $goodsImgs[$product['goods_id']]);
                    }
                }

                $methods['$appendOrder'] = array(array($order_id, $pagedata['order']['amount']['final']));
                if($giftdata){
                    $methods['$appendOrderGift'] = $giftdata;
                    unset($giftdata);
                }
                if($goodsdata){
                    $methods['$appendItem'] = $goodsdata;
                    unset($goodsdata);
                }
                if($pkgdata){
                    $methods['$appendOrderPac'] = $pkgdata;
                    $methods['$appendOrderPacGoods'] = $pkgGoodsData;
                    unset($pkgdata);
                    unset($pkgGoodsData);
                }
                $aData[$request_name] = array(
                    'pagetype'=>'485-ordercreate',
                    'pageid'=>$order_id,
                    'methods'=>$methods,
                );
                break;
            }
            case 'cart_addGoodsToCart':{
                $aData[$request_name] = array(
                    'pagetype'=>'485-addGoodsToCart',
                    'pageid'=>$_POST['goods']['goods_id'],
                    'methods'=>array('$addGoodsToCart'=>array(array($_POST['goods']['goods_id'],$_POST['goods']['num']))),
                );
                break;
            }
            case 'paycenter_result':{
                $payment = $pagedata['payment'];
                $aData[$request_name] = array(
                    'pagetype'=>'485-orderPayResult',
                    'pageid'=>$payment['order_id'],
                    'methods'=>array('$orderPayResult'=>array(array($payment['order_id'],+$payment['money'],$payment['status']))),
                );
                break;
            }
        }

        if($_COOKIE['MEMBER']){
            $member = explode('-',$_COOKIE['MEMBER']);
        }else{
            $member = array(0);
        }
        $mdl_account = $this->system->loadModel("member/account");
        $memberinfo = $mdl_account->verify($member[0], $member[2]);
        $username = isset($memberinfo['uname'])?"'".$memberinfo['uname']."'":"null";
        $userid = isset($memberinfo['member_id'])?"'".$memberinfo['member_id']."'":"null";
        $tmp = $aData[$request_name];
        $client_ip = remote_addr();
        $aVersion = $this->system->version();
        $software_version = $aVersion['app']."-".$aVersion['rev'];
        //$js_code = array("try{(function(){");
        if($tmp) {
            $js_code[] = "_ecq.push(['\$setCommon', '".$tmp['pagetype']."', ".(isset($tmp['pageid'])?"'".$tmp['pageid']."'":"null").", ".$username.", ".$userid.", '".$client_ip."', '".$software_version."']);";
        }
        else {
            $js_code[] = "_ecq.push(['\$setCommon', '".$request_name."', ".(isset($tmp['pageid'])?"'".$tmp['pageid']."'":null).", ".$username.", ".$userid.", '".$client_ip."', '".$software_version."']);";
        }
        //$js_code[] = "_ecq.push(['_logConversion']);";

        if($tmp['methods']) {
            foreach((array)$tmp['methods'] as $k=>$v){
                foreach((array)$v as $tmpdata){
                    $str_data = "";
                    foreach($tmpdata as $v){
                        if($v === null){
                            $str_data .= "null";
                        }
                        else{
                            $str_data .= "'".$v."'";
                        }
                        $str_data .= ",";
                    }

                    $str_data = substr($str_data,0,-1);

                    $js_code[] = "_ecq.push(['".$k."',".$str_data."]);";
                }
            }
            
        }
        $js_code[] = "_ecq.push(['_logData']);";
        if($tmp['extra_js']){
            $js_code[] = $tmp['extra_js'];
        }
        $js_code[] = "_ecq.push(['_clearCommonData']);";
        //$js_code[] = '})();}catch(e){}';
        return implode("\r\n", $js_code);
    }

    function get_cart_data($pagedata, $pagetype){
        $cart_object = $pagedata['trading'];
        if($cart_object){
            $methods = array();
            //捆绑商品
            if($cart_object['package']){
                foreach($cart_object['package'] as $v){
                    $pkgdata[] = array($v['goods_id'], 'package', $v['name'], $v['price'], $v['nums']);
                    foreach((array)$v['items'] as $product){
                        $pkggoodsdata[] = array($v['goods_id'], 'package', $product['p_goods_id'], $product['name'], $product['price'], $product['pkgnum']);
                    }
                }
                $methods["\$appendCartPac"] = $pkgdata;
                $methods["\$appendCartPacItem"] = $pkggoodsdata;
                unset($pkgdata);
                unset($pkggoodsdata);
            }
            //普通商品
            if($cart_object['products']){
                foreach($cart_object['products'] as $v){
                    $name = $v['name'];
                    $goodsdata[] = array($v['goods_id'], $v['name'].$v['addon']['adjname'], $v['price'], $v['nums']);
                }
                $methods["\$appendCartItem"] = $goodsdata;
                unset($goodsdata);
            }
            //积分兑换赠品
            foreach((array)$cart_object['gift_e'] as $v){
                $giftdata[] = array($v['gift_id'], $v['name'], $v['nums'], 'point_exchange', floatval($v['point']));
            }
            //订单获赠商品
            foreach((array)$cart_object['gift_p'] as $v){
                $giftdata[] = array($v['gift_id'], $v['name'], $v['nums'], 'order', null);
            }
            if($giftdata){
                $methods["\$appendCartGift"] = $giftdata;
                unset($giftdata);
            }
            return array(
                'pagetype'=>$pagetype,
                'methods'=>$methods,
            );
        }
        return array(
            'pagetype'=>$pagetype,
        );
    }
}
