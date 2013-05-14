<?php defined('CORE_DIR') || exit('入口错误'); ?>
<?php
class ctl_product extends shopPage{

    var $_call = 'call';
    var $type = 'goods';
    var $seoTag = array('shopname','brand','goods_name','goods_cat','goods_intro','goods_brief','brand_kw','goods_kw','goods_price','update_time','goods_bn');
    function ctl_product(){
        parent::shopPage();
        $this->title    = $this->system->getConf('site.goods_title');
        $this->keywords = $this->system->getConf('site.goods_meta_key_words');
        $this->desc     = $this->system->getConf('site.goods_meta_desc');
    }

    function call(){
        $args = func_get_args();
        $action = array_shift($args);

        if(method_exists($this,$action)){
            return call_user_func_array(array(&$this,$action),$args);
        }

        $objSchema = &$this->system->loadModel('goods/schema');
        $gid = array_shift($args);

        $objSchema->runFunc(+$gid,$action,$args);
    }

    function getVirtualCatById($cat_id=0){
        $vobjCat = &$this->system->loadModel('goods/virtualcat');
        $xml = &$this->system->loadModel('utility/xml');
        $result=$vobjCat->getVirtualCatById(intval($cat_id));

        $searchtools = &$this->system->loadModel('goods/search');
        foreach($result as $k=>$v){
            $filter=$vobjCat->_mkFilter($result[$k]['filter']);
            $cat_id=$filter['cat_id'];
            $filter=$searchtools->encode($filter);
            $result[$k]['url']=$this->system->mkUrl('gallery',$this->system->getConf('gallery.default_view'),array(implode(',',$cat_id),$filter,'0','','',$result[$k]['virtual_cat_id']));
        }
        echo json_encode($result);
    }

    function index($gid,$specImg='',$spec_id='') {
        $this->id = +$gid;

        $this->customer_source_type='product';
        $this->customer_template_type='product';
        $this->customer_template_id=$gid;
        
        $oseo = &$this->system->loadModel('system/seo');
        $seo_info=$oseo->get_seo('goods',$gid);
        $this->title    = $seo_info['title']?$seo_info['title']:$this->system->getConf('site.goods_title');
        $this->keywords = $seo_info['keywords']?$seo_info['keywords']:$this->system->getConf('site.goods_meta_key_words');
        $this->desc     = $seo_info['descript']?$seo_info['descript']:$this->system->getConf('site.goods_meta_desc');
        $member_lv = intval($this->system->request['member_lv']);
        $objProduct = &$this->system->loadModel('goods/products');
        $objGoods = &$this->system->loadModel('trading/goods');
        if(!$aGoods = $objGoods->getGoods($gid,$member_lv)){
            $this->system->responseCode(404);
        }

        if($aGoods['goods_type'] == 'bind'){    //如果捆绑商品跳转到捆绑列表
            $this->redirect('package','index');
            exit;
        }
        if(!$aGoods || $aGoods['disabled'] == 'true' || (empty($aGoods['products']) && empty($aGoods['product_id']))){
            $this->system->error(404);
            exit;
        }

        $objCat = &$this->system->loadModel('goods/productCat');
        $aCat = $objCat->getFieldById($aGoods['cat_id'], array('cat_name','addon'));
        $aCat['addon'] = unserialize($aCat['addon']);
        if($aGoods['seo']['meta_keywords']){
            if(empty($this->keyWords))
            $this->keyWords = $aGoods['seo']['meta_keywords'];
        }else{
            if(trim($aCat['addon']['meta']['keywords'])){
                $this->keyWords = trim($aCat['addon']['meta']['keywords']);
            }
        }
        if($aGoods['seo']['meta_description']){
            $this->metaDesc = $aGoods['seo']['meta_description'];
        }else{
            if(trim($aCat['addon']['meta']['description'])){
                $this->metaDesc = trim($aCat['addon']['meta']['description']);
            }
        }
        $tTitle=(empty($aGoods['seo']['seo_title']) ? $aGoods['name'] : $aGoods['seo']['seo_title']).(empty($aCat['cat_name'])?"":" - ".$aCat['cat_name']);
        if(empty($this->title))
        $this->title = $tTitle;
        $objPdtFinder = &$this->system->loadModel('goods/finderPdt');
        foreach($aGoods['adjunct'] as $key => $rows){    //loop group
            if($rows['set_price'] == 'minus'){
                $cols = 'product_id,goods_id,name, pdt_desc, store, freez, price, price-'.intval($rows['price']).' AS adjprice';
            }else{
                $cols = 'product_id,goods_id,name, pdt_desc, store, freez, price, price*'.($rows['price']?$rows['price']:1).' AS adjprice';
            }
            if($rows['type'] == 'goods'){
                if(!$rows['items']['product_id']) $rows['items']['product_id'] = array(-1);
                $arr = $rows['items'];
            }else{
                parse_str($rows['items'].'&dis_goods[]='.$gid, $arr);
            }
            if($aAdj = $objPdtFinder->getList($cols, $arr, 0, -1)){
                $aAdjGid = array();
                foreach($aAdj as $item){
                    $aAdjGid['goods_id'][] = $item['goods_id'];
                }
                if(!empty($aAdjGid)){
                    foreach($objProduct->getList('marketable,disabled',$aAdjGid,0,1000) as $item){
                        $aAdjGid[$item['goods_id']] = $item;
                    }
                    foreach($aAdj as $k => $item){
                        $aAdj[$k]['marketable'] = $aAdjGid[$item['goods_id']]['marketable'];
                        $aAdj[$k]['disabled'] = $aAdjGid[$item['goods_id']]['disabled'];
                    }
                }
                $aGoods['adjunct'][$key]['items'] = $aAdj;
            }else{
                unset($aGoods['adjunct'][$key]);
            }
        }

        //$smarty = $this->system->loadModel('system/frontend'); 
          $this->_plugins['function']['selector'] = array(&$this,'_selector');

        //初始化货品

        if(!empty($aGoods['products'])){
            foreach($aGoods['products'] as $key => $products){
                $a = array();
                foreach($products['props']['spec'] as $k=>$v){
                    $a[] = trim($k).':'.trim($v);
                }
                $aGoods['products'][$key]['params_tr'] = implode('-',$a);
                $aPdtIds[] = $products['product_id'];
                if($aGoods['price'] > $products['price']){
                    $aGoods['price'] = $products['price'];//前台默认进来显示商品的最小价格
                }
            }
        }else{
            $aPdtIds[] = $aGoods['product_id'];
        }
        if($this->system->getConf('site.show_mark_price')){
            $aGoods['setting']['mktprice'] = $this->system->getConf('site.market_price');
        }else{
            $aGoods['setting']['mktprice'] = 0;
        }
        $aGoods['setting']['saveprice'] = $this->system->getConf('site.save_price');
        $aGoods['setting']['buytarget'] = $this->system->getConf('site.buy.target');
        $aGoods['setting']['score'] = $this->system->getConf('point.get_policy');
        $aGoods['setting']['scorerate'] = $this->system->getConf('point.get_rate');
        if($aGoods['setting']['score'] == 1){
            $aGoods['score'] = intval($aGoods['price'] * $aGoods['setting']['scorerate']);
        }

        /*--------------规格关联商品图片--------------*/
        if (!empty($specImg)){
            $tmpImgAry=explode("_",$specImg);
            if (is_array($tmpImgAry)){
                foreach($tmpImgAry as $key => $val){
                    $tImgAry = explode("@",$val);
                    if (is_array($tImgAry)){
                          $spec[$tImgAry[0]]=$val;
                          $imageGroup[]=substr($tImgAry[1],0,strpos($tImgAry[1],"|"));
                          $imageGstr .= substr($tImgAry[1],0,strpos($tImgAry[1],"|")).",";
                          $spec_value_id = substr($tImgAry[1],strpos($tImgAry[1],"|")+1);
                          if ($aGoods['specVdesc'][$tImgAry[0]]['value'][$spec_value_id]['spec_value'])
                            $specValue[]=$aGoods['specVdesc'][$tImgAry[0]]['value'][$spec_value_id]['spec_value'];
                          if ($aGoods['FlatSpec']&&array_key_exists($tImgAry[0],$aGoods['FlatSpec']))
                              $aGoods['FlatSpec'][$tImgAry[0]]['value'][$spec_value_id]['selected']=true;
                          if ($aGoods['SelSpec']&&array_key_exists($tImgAry[0],$aGoods['SelSpec']))
                              $aGoods['SelSpec'][$tImgAry[0]]['value'][$spec_value_id]['selected']=true;
                    }
                }
                if ($imageGstr){
                    $imageGstr=substr($imageGstr,0,-1);
                }
            }

            /****************设置规格链接地址**********************/
            if (is_array($aGoods['specVdesc'])){
                foreach($aGoods['specVdesc'] as $gk => $gv){
                    if (is_array($gv['value'])){
                        foreach($gv['value'] as $gkk => $gvv){
                            if(is_array($spec)){
                                $specId = substr($gvv['spec_goods_images'],0,strpos($gvv['spec_goods_images'],"@"));
                                foreach($spec as $sk => $sv){
                                    if ($specId != $sk){
                                        $aGoods['specVdesc'][$gk]['value'][$gkk]['spec_goods_images'].="_".$sv;
                                        if ($aGoods['FlatSpec']&&array_key_exists($gk,$aGoods['FlatSpec'])){
                                            $aGoods['FlatSpec'][$gk]['value'][$gkk]['spec_goods_images'].="_".$sv;
                                        }
                                        if ($aGoods['SelSpec']&&array_key_exists($gk,$aGoods['SelSpec'])){
                                            $aGoods['SelSpec'][$gk]['value'][$gkk]['spec_goods_images'].="_".$sv;
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }

            /*************************************/
            //页面提示所选规格名称
            $this->pagedata['SelectSpecValue'] = array('value'=>implode("、",$specValue),'selected'=>1);
        }
        else{
            if (is_array($aGoods['FlatSpec'])&&count($aGoods['FlatSpec'])>0){
                foreach($aGoods['FlatSpec'] as $agk => $agv){
                    $specValue[]=$agv['name'];
                }
            }
            if (is_array($aGoods['SelSpec'])&&count($aGoods['SelSpec'])>0){
                foreach($aGoods['SelSpec'] as $agk => $agv){
                    $specValue[]=$agv['name'];
                }
            }
            $this->pagedata['SelectSpecValue'] = array('value'=>implode("、",$specValue),'selected'=>0);
        }


        $this->pagedata['specShowItems'] =$specValue;
        /*--------------*/
        //$gImages=$this->goodspics($gid,$imageGroup,$imageGstr);
        if (is_array($gImages)&&count($gImages)>0){
            $this->pagedata['images']['gimages'] = $gImages;
        }
        else{
            /*-------------商品图片--------------*/
            $gimage = &$this->system->loadModel('goods/gimage');
            $this->pagedata['images']['gimages'] = $gimage->get_by_goods_id($gid);
            /*----------------- 8< --------------*/
        }

        
        /********-------------------*********/
        $aGoods['product2spec'] = json_encode( $aGoods['product2spec'] );

        $aGoods['spec2product'] = json_encode( $aGoods['spec2product'] );
        $this->pagedata['goods'] = $aGoods;
       

        if ($this->pagedata['goods']['products']){
            $priceArea = array();
           
            $qujian_price =array();
            $goods_mktprice=$this->pagedata['goods']['mktprice'];
    
             foreach($this->pagedata['goods']['products'] as $p => $m){      
                 $qujian_price[]=($m['price']/$goods_mktprice)*10;
                 $qujian_youhui[]=($m['price']/$goods_mktprice)*100;
                 $qujian_jisheng[]=$goods_mktprice-$m['price'];
             }
          
                   $minprice_qujian = min($qujian_price);
                   $maxprice_qujian = max($qujian_price);/*折扣*/
                   
                   $minyouhui_qujian = min($qujian_youhui);
                   $maxyouhui_qujian = max($qujian_youhui);/*优惠*/

                   $minjiesheng_qujian = min($qujian_jisheng);
                   $maxjiesheng_qujian = max($qujian_jisheng);/*节省*/

            $goods_price=$this->pagedata['goods']['price'];
             $goods_mktprice=$this->pagedata['goods']['mktprice'];
          
           

            $max_discount=round($maxprice_qujian,1);
            $min_discount=round($minprice_qujian,1);

            $max_jiesheng=round($maxjiesheng_qujian,1);
            $min_jiesheng=round($minjiesheng_qujian,1);

            $max_youhui=round($maxyouhui_qujian,1);
            $min_youhui=round($minyouhui_qujian,1);


           /*-------折扣区间 begin----*/
            if($minprice_qujian >= 1){
                 if($max_discount== $min_discount){
                       $this->pagedata['goods']['price_qujian'] = $max_discount;

                 }else{
                       $this->pagedata['goods']['price_qujian'] = $max_discount."-".$min_discount;
                 }
            }else{
                 if($max_discount!= $min_discount){
                   $this->pagedata['goods']['price_qujian'] = $min_discount."-".$max_discount;
                 }else{
                   $this->pagedata['goods']['price_qujian'] = round(($goods_price/$goods_mktprice*10),1);
                 }
            }
            /*--------折扣区间 end----*/

            /*-------节省区间 begin----*/
            if($min_jiesheng && $max_jiesheng){
                 $this->pagedata['goods']['jiesheng_min'] = $min_jiesheng;
                 $this->pagedata['goods']['jiesheng_max'] = $max_jiesheng;
            }
            /*--------节省区间 end----*/

            /*-------优惠区间 begin----*/
            if($min_youhui && $max_youhui){
                 $this->pagedata['goods']['youhui_max'] = $min_youhui;
                 $this->pagedata['goods']['youhui_min'] = $max_youhui;
            }
            /*--------优惠区间 end----*/
         

            if ($_COOKIE['MLV'])
                $MLV = $_COOKIE['MLV'];
            else{
                $MLV=false;
            }
            if ($MLV){
                foreach($this->pagedata['goods']['products'] as $gpk => $gpv){
                   $priceArea[]=$gpv['mprice'][$MLV];
   
                   $mktpriceArea[]=$gpv['mktprice'];
                }
            }
            else{
                foreach($this->pagedata['goods']['products'] as $gpk => $gpv){
                   $priceArea[]=$gpv['price'];
                   $mktpriceArea[]=$gpv['mktprice'];
                }
            }

            if (count($priceArea)>1){
                $minprice = min($priceArea);
                $maxprice = max($priceArea);
                //if ($minprice<>$maxprice){
                    $this->pagedata['goods']['minprice'] = $minprice;
                    $this->pagedata['goods']['maxprice'] = $maxprice;
                //}
            }
            if (count($mktpriceArea)>1){
                $mktminprice = min($mktpriceArea);
                $mktmaxprice = max($mktpriceArea);
                //if ($mktminprice<>$mktmaxprice){
                    $this->pagedata['goods']['minmktprice'] = $mktminprice;
                    $this->pagedata['goods']['maxmktprice'] = $mktmaxprice;
                //}
            }                                                               //增加了市场价范围
          
          //计算货品冻结库存总和
            foreach($this->pagedata['goods']['products'] as $key => $val){
                $totalFreez += $val['freez'];
            }

        }
        else{
            $totalFreez = $this->pagedata['goods']['freez'];
            $this->pagedata['goods']['price_qujian'] = round(($aGoods['price']/$aGoods['mktprice']*10),1);
        }

        $mLevelList = $objProduct->getProductLevel($aPdtIds);

        $this->pagedata['mLevel'] = $mLevelList;
       
        /**** begin 商品品牌 ****/
        if($this->pagedata['goods']['brand_id'] > 0){
            $brandObj = &$this->system->loadModel('goods/brand');
            $aBrand = $brandObj->getFieldById($this->pagedata['goods']['brand_id'], array('brand_name'));
        }
        $this->pagedata['goods']['brand_name'] = $aBrand['brand_name'];
        /**** begin 商品品牌 ****/

        /**** begin 商品评论 ****/
        $aComment['switch']['ask'] = $this->system->getConf('comment.switch.ask');
        $aComment['switch']['discuss'] = $this->system->getConf('comment.switch.discuss');
        $aComment['power']['ask'] = $this->system->getConf('comment.power.ask');
        $aComment['power']['discuss'] = $this->system->getConf('comment.power.discuss');
        $this->pagedata['comment']['member_lv']=$GLOBALS['runtime']['member_lv'];

        foreach($aComment['switch'] as $item => $switchStatus){
            if($switchStatus == 'on'){
                
              $objComment= &$this->system->loadModel('comment/comment');
              $commentList = $objComment->getGoodsIndexComments($gid, $item);
              $aComment['list'][$item] = $commentList['data'];
              $aComment[$item.'Count'] = $commentList['total'];
              $aId = array();
                if ($commentList['total']){
                    foreach($aComment['list'][$item] as $rows){
                      $aId[] = $rows['comment_id'];
                    }
                   if(count($aId)) $aReply = $objComment->getCommentsReply($aId, true);
                    reset($aComment['list'][$item]);
                   foreach($aComment['list'][$item] as $key => $rows){
                     foreach($aReply as $rkey => $rrows){
                        if($rows['comment_id'] == $rrows['for_comment_id']){
                           $aComment['list'][$item][$key]['items'][] = $aReply[$rkey];
                        }
                     }
                      reset($aReply);
                   }
                }else{
                    $aComment['null_notice'][$item] = $this->system->getConf('comment.null_notice.'.$item);;
                }
 
 

            }
        }
           $aComment['member_lv']=$GLOBALS['runtime']['member_lv'];
           $this->pagedata['comment'] = $aComment;

        /**** end 商品评论 ****/

        /**** begin 相关商品 ****/
        $aLinkId['goods_id'] = array();
        foreach($objGoods->getLinkList($gid) as $rows){
            if($rows['goods_1']==$gid) $aLinkId['goods_id'][] = $rows['goods_2'];
            else $aLinkId['goods_id'][] = $rows['goods_1'];
        }
        if(count($aLinkId['goods_id'])>0){
            $aLinkId['marketable'] = 'true';
            $objProduct = &$this->system->loadModel('goods/products');
            $this->pagedata['goods']['link'] = $objProduct->getList('*',$aLinkId,0,500);
            $this->pagedata['goods']['link_count'] = $objProduct->count($aLinkId);
        }
        /**** end 相关商品 ****/

        //更多商品促销活动
        $PRICE = $this->pagedata['goods']['price'];//todo 此处PRICE 为会员价格,需要统一接口
        $oPromotion = &$this->system->loadModel('trading/promotion');
        /**
         * @todo user without login: $member_lv == -1, how to take out promotion to these user?
         * we create a user group named "未登陆用户" it's member_lv is 4
         */
        $MLV = $_COOKIE['MLV'] ? $_COOKIE['MLV'] : constant('MLV_NOLOGIN');
        $aPmt = $oPromotion->getGoodsPromotion($gid, $this->pagedata['goods']['cat_id'], $this->pagedata['goods']['brand_id'], $member_lv == -1 ? $MLV : $member_lv);

        if ($aPmt){
            $this->pagedata['goods']['pmt_id'] = $aPmt['pmt_id'];
            $arr= $oPromotion->getPromotionList($aPmt['pmta_id']);

            foreach($arr as $keys=>$vals) {
                 $arr[$keys]['pmt_solution']=unserialize($arr[$keys]['pmt_solution']);
                 if(!in_array($MLV,$arr[$keys]['pmt_solution']['condition'][0][1]))
                 unset($arr[$keys]);
            }
            $this->pagedata['promotions']=$arr;
            $aTrading = array (
                'price' => $this->pagedata['goods']['price'],
                'score' => $this->pagedata['goods']['score'],
                'gift'  => array(),
                'coupon' => array()
            );
            $oPromotion->apply_single_pdt_pmt($aTrading, unserialize($aPmt['pmt_solution']),$member_lv == -1 ? $MLV : $member_lv);
            $oGift = &$this->system->loadModel('trading/gift');
            if (!empty($aTrading['gift'])) {
                $this->pagedata['gift'] = $oGift->getGiftByIds($aTrading['gift']);
            }
            $oCoupon = &$this->system->loadModel('trading/coupon');
            if (!empty($aTrading['coupon'])) {
                $this->pagedata['coupon'] = $oCoupon->getCouponByIds($aTrading['coupon']);
            }
            $this->pagedata['trading'] = $aTrading;
        }
        $oPackage = &$this->system->loadModel('trading/package');
        if (!empty($aPdtIds)) {
            $aPkgList = $oPackage->findPmtPkg($aPdtIds);
            foreach($aPkgList as $k => $row){
                $aPkgList[$k]['items'] = $oPackage->getPackageProducts($row['goods_id']);
            }
            $this->pagedata['package'] = $aPkgList;
        }
        if($GLOBALS['runtime']['member_lv']<0){
            $this->pagedata['login'] = 'nologin';
        }
        $cur = &$this->system->loadModel('system/cur');
        $this->pagedata['readingGlass'] = $this->system->getConf('site.reading_glass');
        $this->pagedata['readingGlassWidth'] = $this->system->getConf('site.reading_glass_width');
        $this->pagedata['readingGlassHeight'] = $this->system->getConf('site.reading_glass_height');

        $sellLogList = $objProduct->getGoodsSellLogList($gid,0,$this->system->getConf('selllog.display.listnum'));
        $sellLogSetting['display'] = array(
                'switch'=>$this->system->getConf('selllog.display.switch') ,
                'limit'=>$this->system->getConf('selllog.display.limit') ,
                'listnum'=>$this->system->getConf('selllog.display.listnum')
            );

        $this->pagedata['goods']['product_freez'] = $totalFreez;
        $this->pagedata['sellLog'] = $sellLogSetting;
        $this->pagedata['sellLogList'] = $sellLogList;
        $this->pagedata['money_format'] = json_encode($cur->getFormat($this->system->request['cur']));
        $this->pagedata['askshow'] = $this->system->getConf('comment.verifyCode.ask');
        $this->pagedata['goodsBnShow'] = $this->system->getConf('goodsbn.display.switch');
        $this->pagedata['discussshow'] = $this->system->getConf('comment.verifyCode.discuss');
        $this->pagedata['showStorage'] = intval($this->system->getConf('site.show_storage'));
        $this->pagedata['specimagewidth'] = $this->system->getConf('spec.image.width');
        $this->pagedata['specimageheight'] = $this->system->getConf('spec.image.height');
        $this->pagedata['goodsproplink'] = $this->system->getConf('goodsprop.display.switch');
        $this->pagedata['goodspropposition'] = $this->system->getConf('goodsprop.display.position');
        $this->getGlobal($this->seoTag,$this->pagedata);
        $GLOBALS['pageinfo']['goods'] = &$GLOBALS['runtime']['goods_name'];
        $GLOBALS['pageinfo']['brand'] = &$GLOBALS['runtime']['brand'];
        $GLOBALS['pageinfo']['gcat'] = &$GLOBALS['runtime']['goods_cat'];
        $this->output();
    }

       /*
       @author litie@shopex.cn


       $gids like:  2,3,4,5,6,7

       @return like:
       [{"goods_id":"39","thumbnail_pic":"http:\/\/pic.shopex.cn\/pictures\/gimages\/77900fbf8fcc94de.jpg","small_pic":"http:\/\/pic.shopex.cn\/pictures\/gimages\/4d927b00ab29b199.jpg","big_pic":"http:\/\/pic.shopex.cn\/pictures\/gimages\/389e97389f1616f7.jpg"},{"goods_id":"42","thumbnail_pic":"http:\/\/pic.shopex.cn\/pictures\/gimages\/54d1c53bc455244f.jpg","small_pic":"http:\/\/pic.shopex.cn\/pictures\/gimages\/9dce731f131aab5e.jpg","big_pic":"http:\/\/pic.shopex.cn\/pictures\/gimages\/ac4420118e680927.jpg"}]
    */
    function picsJson(){
         $gids = explode(',',$_GET['gids']);

         $data = $this->system->loadModel('goods/products')->getPicsByGoodsId($gids);

         $storagerMdl = $this->system->loadModel('system/storager');

         foreach($data as $k => $v){
             $data[$k]['thumbnail_pic'] = $storagerMdl->getUrl($v['thumbnail_pic']);
             $data[$k]['small_pic'] = $storagerMdl->getUrl($v['small_pic']);
             $data[$k]['big_pic'] = $storagerMdl->getUrl($v['big_pic']);
         }

         echo json_encode($data);
    }


    function viewpic($goodsid, $selected='def'){

        $objGoods = &$this->system->loadModel('trading/goods');
        $gImg = &$this->system->loadModel('goods/gimage');
        $dImg=$gImg->get_by_goods_id($goodsid);
        $aGoods = $objGoods->getFieldById($goodsid, array('name'));

        $this->pagedata['goods_name'] = urlencode(htmlspecialchars($aGoods['name'],ENT_QUOTES));
        $this->pagedata['goods_name_show'] = $aGoods['name'];
        $this->pagedata['company_name'] = str_replace(array("%","'"),array("％","‘"),htmlspecialchars($this->system->getConf('system.shopname')));
        
        if(!$dImg){
            $selected=0;
            $storager = &$this->system->loadModel('system/storager');
            $id=rand(0,10);
            $dImg[$id]=array(
                'gimage_id'=>$id,
                'goods_id'=>$goodsid,
                'small'=>$storager->getUrl($this->system->getConf('site.default_small_pic')),
                'big'=>$storager->getUrl($this->system->getConf('site.default_big_pic')),
                'thumbnail'=>$storager->getUrl($this->system->getConf('site.default_thumbnail_pic'))
            );
        }

        $this->pagedata['image_file'] = $dImg;
        if($selected=='def'){
            $selected=current($dImg);
            $selected=$selected['gimage_id'];
        }
        $this->pagedata['selected'] = $selected;
        $this->__tmpl='product/viewpic.html';
        $this->output();
    }

    function diff(){

        $comare=explode("|",$_COOKIE['GCOMPARE']);

        foreach($comare as $ci){
           $ci = json_decode($ci,true);

           $gids[] = $ci['gid'];
        }

        $oGoods = &$this->system->loadModel('trading/goods');
        if($GLOBALS['runtime']['member_lv']<0){
            $this->pagedata['LOGIN'] = 'nologin';
        }

        $this->pagedata['diff'] = $oGoods->diff($gids);

        foreach($this->pagedata['diff']['goods'] as $row){
            $goods_name[] = $row['name'];
        }
        $this->pagedata['goods']['name'] = implode(',',$goods_name);
        $this->getGlobal(array('shopname', 'goods_name'),$this->pagedata);

        $this->pagedata['setting']['buytarget'] = $this->system->getConf('site.buy.target');
        $this->output();
    }

    function photo(){
    }

    function pic(){
    }

    // 到货通知
    function gnotify($goods_id=0,$product_id=0){
        if($_POST['goods']['goods_id']){
            $goods_id = $_POST['goods']['goods_id'];
            $product_id = $_POST['goods']['product_id'];
        }
            $this->id =$goods_id;
        $objGoods = &$this->system->loadModel('trading/goods');
        $aProduct = $objGoods->getProducts($goods_id, $product_id);

        $this->pagedata['goods'] = $aProduct[0];
        if($this->member['member_id']){
            $objMember = &$this->system->loadModel('member/member');
            $aMemInfo = $objMember->getFieldById($this->member['member_id'], array('email,mobile'));
            $this->pagedata['member'] = $aMemInfo;
        }

        $this->output();
    }

    function toNotify(){

        $this->begin($this->system->mkUrl("index"));
        $this->_verifyMember(false);
        $aTemp = array();
        $oNotify = &$this->system->loadModel('goods/goodsNotify');
        $toolsMdl = &$this->system->loadModel('utility/tools');
        if(!$toolsMdl->is_email($_POST['email'])){
                $this->end(false, __('邮箱格式错误'));
        }else{
                foreach($_POST['item'] as $key=>$val){

                    $aTemp['goods_id'] = +$val['goods_id'];
                    $aTemp['product_id'] = +$val['product_id'];
                    $aTemp['member_id'] = (int)$this->member['member_id'];
                    $aTemp['email'] = $_POST['email']!=''? $_POST['email'] : $this->member['email'];
                    $aTemp['creat_time'] = time();

                    $oNotify->createNotify($aTemp);
                }

                $this->end(true, __('提交成功'));

        }
    }

    function selllog($gid,$nPage){
        $nPage = $nPage?$nPage:1;
        $oPro = &$this->system->loadModel('goods/products');
        $sellLogList = $oPro->getGoodsSellLogList($gid, $nPage-1 );
        $this->pagedata['sellLogList'] = $sellLogList;
        $this->pagedata['pager'] = array(
                'current'=> $nPage,
                'total'=> $sellLogList['page'],
                'link'=> $this->system->mkUrl('product','selllog', array($gid,($tmp = time()))),
                'token'=>$tmp);

        $this->id = $gid;
        $member_lv = intval($this->system->request['member_lv']);
        $objGoods = &$this->system->loadModel('trading/goods');
        if(!$aGoods = $objGoods->getGoods($gid,$member_lv)){
            $this->system->responseCode(404);
        }
        if(!$aGoods || $aGoods['disabled'] == 'true' || (empty($aGoods['products']) && empty($aGoods['product_id']))){
            $this->system->error(404);
            exit;
        }

        $objCat = &$this->system->loadModel('goods/productCat');
        $aCat = $objCat->getFieldById($aGoods['cat_id'], array('cat_name','addon'));
        $aCat['addon'] = unserialize($aCat['addon']);
        if($aGoods['seo']['meta_keywords']){
            $this->keyWords = $aGoods['seo']['meta_keywords'];
        }else{
            if(trim($aCat['addon']['meta']['keywords'])){
                $this->keyWords = trim($aCat['addon']['meta']['keywords']);
            }
        }
        if($aGoods['seo']['meta_description']){
            $this->metaDesc = $aGoods['seo']['meta_description'];
        }else{
            if(trim($aCat['addon']['meta']['description'])){
                $this->metaDesc = trim($aCat['addon']['meta']['description']);
            }
        }
        $this->title = (empty($aGoods['seo']['seo_title']) ? $aGoods['name'] : $aGoods['seo']['seo_title']).(empty($aCat['cat_name'])?"":" - ".$aCat['cat_name'].__(' » 销售记录'));
        $this->output();
    }
    function goodspics($goodsId,$images=array(),$imgGstr=''){
        $Goods=&$this->system->loadModel('goods/gimage');
        $objGoods = &$this->system->loadModel('trading/goods');
        $gimg=$_POST['gimages'];
        $goodsId=$_POST['goodsId'];
        if ($gimg){
            $tmpGimg=explode(",",$_POST['gimages']);
            if ($tmpGimg){
                foreach($tmpGimg as $key => $val){
                    if (!$val)
                        unset($tmpGimg[$key]);
                }
                $tmpImage=$Goods->get_by_gimage_id($goodsId,$tmpGimg);
            }
        }
        else{
            $tmpImage = $Goods->get_by_goods_id($goodsId);
        }
        $this->pagedata['imgtype'] = 'spec';
        $this->pagedata['images']['gimages']=$tmpImage;
        $this->pagedata['goods'] = $objGoods->getGoods($goodsId);
        $this->__tmpl='product/goodspics.html';
        $this->output();
    }
    function _selector($params,&$smarty){
        if ($params['type']<>'b'){
            $args[0]='';
            $args[1] = 'tp,'.$params['type']."_".$params['key'].','.$params['value']."_p,0";
            $args[4] = 6;
            return $this->system->mkUrl('gallery',$smarty->_vars['curView'],$args);
        }
        else{
            $args[0]=$params['key'];
            /*$args[1]='b,'.$params['key'];
            $args[2]=0;
            $args[3]='';
            $args[4]=1;
            $args[5]='';*/
            return $this->system->mkUrl('brand',$smarty->_vars['curView'],$args);
        }

    }
    function get_brand($result){
        return $result['goods']['brand'];
    }
    function get_goods_name($result){
        return $result['goods']['name'];
    }
    function get_goods_bn($result){
        return $result['goods']['bn'];
    }
    function get_goods_cat($result){
        $pcat=$this->system->loadModel('goods/productCat');
        $row=$pcat->instance($result['goods']['cat_id'],'cat_name');
         return $row['cat_name'];
    }
    function get_goods_intro($result){
        $intro= strip_tags($result['goods']['intro']);
        if ($this->strlen($intro)>50)
            $intro=$this->substr($intro,0,50);
        return $intro;
    }
    function get_goods_brief($result){
        $brief= strip_tags($result['goods']['brief']);
        if ($this->strlen($brief)>50)
            $brief=$this->substr($brief,0,50);
        return $brief;
    }
    function get_brand_kw($result){
        $brand=$this->system->loadModel('goods/brand');
        $row=$brand->instance($result['goods']['brand_id'],'brand_keywords');
        return $row['brand_keywords'];
    }
    function get_goods_kw($result){
        $goods=$this->system->loadModel('trading/goods');
        $row=$goods->getKeywords($result['goods']['goods_id']);
        if ($row){
            foreach($row as $key => $val){
                $tmpRow[]=$val['keyword'];
            }
            return implode(",",$tmpRow);
        }
            return;
    }
    function get_goods_price($result){
        return $result['goods']['price'];
    }
    function get_update_time($result){
        return date("c",$result['goods']['last_modify']);
    }
    function strlen($str,$encode='utf8'){
        $enlen=0;
        if ($encode=='utf8')
            $minchar = 0x80;
        elseif ($encode=="gbk")
            $minchar = 0x00;
        $maxchar = 0xff;
        for($i=0;$i<strlen($str);$i++){
            if(ord($str{$i})>=$minchar&&ord($str{$i})<=$maxchar){
                $zh[]=ord($str{$i});
            }
            else
                $enlen++;
        }
        if ($encode=='utf8')
            $zhlen = count($zh)/3;
        else
            $zhlen = count($zh)/2;
        return $enlen + $zhlen;
    }
    function substr($str,$start=0,$offset=''){
        $j=0;
        $cn=0;
        if (!$offset)$offset=strlen($str);
        while($cn<$start){
            if (ord($str{$j})>=0x80&&ord($str{$j})<=0xff)
                $j=$j+3;
            else
                $j++;
            $cn++;
        }
        $i=$j;
        $exp=0;
        while($exp<$offset){
            if (ord($str{$i})>=0x80&&ord($str{$i})<0xff){
                $substr.=substr($str,$i,3);
                $i=$i+3;
            }
            else{
                $substr.=$str{$i};
                $i++;
            }
            $exp++;
        }
        return $substr;
    }

}
?>
