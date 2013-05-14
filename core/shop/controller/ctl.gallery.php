<?php 
defined('CORE_DIR') || exit('入口错误'); 

class ctl_gallery extends shopPage{

    var $_call = 'index';
    var $type='goodsCat';
    var $seoTag=array('shopname','goods_amount','goods_cat','goods_cat_p','goods_type','brand','sort_path');
    function ctl_gallery(){

        parent::shopPage();
        $this->title = str_replace("{ENV_path}","{ENV_sort_path}",$this->system->getConf('site.list_title'));

        $this->keywords = str_replace("{ENV_path}","{ENV_sort_path}",$this->system->getConf('site.list_meta_key_words'));
        $this->desc = str_replace("{ENV_path}","{ENV_sort_path}",$this->system->getConf('site.list_meta_desc'));
    }

    function index($view,$cat_id='',$urlFilter=null,$orderBy=0,$tab=null,$page=1,$cat_type=null) {
        if($orderBy == 5 || $orderBy == 6){  //5是周访问次数  6是总访问次数
            $this->noCache = true;
        }
        $this->customer_source_type = 'cat';
        $this->customer_template_type = 'gallery';
        $this->customer_template_id = $cat_id;
        
        if($cat_type){
            $this->type = 'virtualcat';
            $this->cat_type = $cat_type;
            $this->customer_template_id = $cat_type;
            $this->customer_source_type = 'virtualcat';
            $virtualCat = &$this->system->loadModel('goods/virtualcat');
            $vcat = $virtualCat->instance($cat_type);
            
            parse_str($vcat['filter'],$type_filter);
        }

        $urlFilter = htmlspecialchars(urldecode($urlFilter));  //'s385,19219'  'p,0,0_3,1_4,1'
        
        if( $cat_id && $cat_id != '_ANY_' ){
            $cat_id = explode(",",$cat_id);
            foreach($cat_id as $k=>$v){
                if($v) $cat_id[$k] = intval($v);
            }
            $this->id = implode(",",$cat_id);
        }else{
            //if($cat_id == '_ANY_') unset($cat_id);
            $cat_id = array('');
            $this->id = '';
        }

        // 初始化操作
        $pageLimit = $this->system->getConf('gallery.display.listnum');
        $this->pagedata['pdtPic'] = array('width'=>100,'heigth'=>100);
        $this->pagedata['args'] = array($this->id,urlencode($urlFilter),$orderBy,$tab,$page,$cat_type);

        $productCat = &$this->system->loadModel('goods/productCat');
        
        if($cat_type){
            $this->pagedata['childnode'] = $virtualCat->getCatParentById($cat_type);
        }else{
            $this->pagedata['childnode'] = $productCat->getCatParentById($cat_id);//获取商品分类
        }
        
        $brandGroup = &$this->system->loadModel('goods/brand');
        $objGoods = &$this->system->loadModel('goods/products');
        $brandResult = $brandGroup->getBrandGroup($cat_id);//获取商品分类   ？？？
        
        $this->productCat = &$productCat;
        $cat = $productCat->get($cat_id,$view,$type_filter['type_id']); //对商品详细处理在相对应显示页面
        
        if( !in_array($view,$cat['setting']['list_tpl'])){
            header('Location: '.$this->system->mkUrl('gallery',current($cat['setting']['list_tpl']),$this->pagedata['args']),true,301);
        }

        // 商品分类的关键字
        if($cat_type){
            $vcat['addon'] = unserialize($vcat['addon']);
            if(trim($vcat['addon']['meta']['keywords'])){
                 $this->keywords = trim($vcat['addon']['meta']['keywords']);
            }
            if(trim($vcat['addon']['meta']['description'])){
                $this->desc = trim($vcat['addon']['meta']['description']);
            }
            
            $this->title = ($vcat['addon']['meta']['title']) ? trim($vcat['addon']['meta']['title']) : $vcat['virtual_cat_name'];
          
        }else{
            if(trim($cat['addon'])){
                $cat['addon'] = unserialize($cat['addon']);
                if(trim($cat['addon']['meta']['keywords'])){
                    $this->keywords = trim($cat['addon']['meta']['keywords']);
                }
                if(trim($cat['addon']['meta']['description'])){
                    $this->desc = trim($cat['addon']['meta']['description']);
                }
                if(trim($cat['addon']['meta']['title'])){
                    $this->title = trim($cat['addon']['meta']['title']);
                }
            }
        }
              
        $searchtools = &$this->system->loadModel('goods/search');
        $path = array();
        $filter = $searchtools->decode($urlFilter,$path,$cat);
        
        if($filter['name'][0]){
           $filter['name'][0] = str_replace('%xia%','_',$filter['name'][0]); 
   
        }
        if($filter['bn'][0]){
           $filter['bn'][0] = str_replace('%xia%','_',$filter['bn'][0]); 
   
        }

        $GLOBALS['search_result'] = $filter['name'][0];
        $this->filter = &$filter;
        
        // 搜索页面专用的title
        if($GLOBALS['search_result']){  
            $this->title = $GLOBALS['search_result'].'__{ENV_shopname}';
        }
       
        if(is_array($filter)){
            $filter = array_merge(array('cat_id'=>$cat_id,'marketable'=>'true'),$filter);
            if( ($filter['cat_id'][0] === '' || $filter['cat_id'][0] === null ) && !isset( $filter['cat_id'][1] ) ){
                unset($filter['cat_id']);
            }
            if( ($filter['tag'][0] === '' || $filter['tag'][0] === null ) && !isset( $filter['tag'][1] ) ){
                unset($filter['tag']);
            }
            if( ($filter['brand_id'][0] ==='' || $filter['brand_id'][0] === null) && !isset( $filter['brand_id'][1] )){
                unset($filter['brand_id']);
            }
        }else{
            $filter = array('cat_id'=>$cat_id,'marketable'=>'true');
        }

        // 获取类型关联的规格
        if ($vcat['type_id']){
            $type_id = $vcat['type_id'];
        }else{
            $type = $productCat->getFieldById($this->id,array('type_id'));
            $type_id = $type['type_id']; // 获取相应“所属分类”的type_id
        }
        
        $gType = &$this->system->loadModel('goods/gtype');
        $SpecList = $gType->getSpec($type_id,1); // 获取关联的规格
        
        if($path){
            foreach($path as $p){
                $arg = unserialize(serialize($this->pagedata['args']));
                $arg[1] = $p['str'];
                $title = array();
                if(is_numeric($p['type'])){
                    $cat_tmp = $productCat->get($cat_id,$view,$filter['type_id'][0]);
                    foreach($p['data'] as $i){
                        $name = $cat_tmp['props'][$p['type']]['options'][$i];
                        $title[] = $name?$name:$i;
                        $tip = $cat_tmp['props'][$p['type']]['name'];
                    }
                }elseif($p['type'] == 'brand_id'){
                    $brand = array();
                    foreach($cat['brand'] as $b){
                        $brand[$b['brand_id']] = $b['brand_name'];
                    }
                    foreach($p['data'] as $i){
                        $title[] = $brand[$i];
                        $tip = __("品牌");
                    }
                    unset($brand);
                }elseif(substr($p['type'],0,2) == 's_'){
                    $spec = array();
                    foreach($p['data'] as $spk => $spv){
                        $tmp = explode(",",$spv);
                        $tip = $SpecList[$tmp[0]]['name'];
                        $title[] = $SpecList[$tmp[0]]['spec_value'][$tmp[1]]['spec_value'];
                        $g['pdt_desc'] = $SpecList[$tmp[0]]['spec_value'][$tmp[1]]['spec_value'];/*前台搜索商品规格筛选，所要获取的pdt_desc*/
                    }
                    $curSpec[$tmp[0]] = $tmp[1];

                }
                $title = implode(',',$title);

                if($title){
                    $this->title .=' >> '.$title;
                    $this->path[] = array('title'=>" ".$title,'link'=>$this->system->mkUrl('gallery',$view,$arg),'tips'=>$tip);
                }
            }
        }
        
        //不索引目录页
        if($this->system->getConf('system.seo.noindex_catalog')){
            $this->header .= '<meta name="robots" content="noindex,noarchive,follow" />';
        }
        
        $filter['cat_id'] = $cat_id;

        $filter['goods_type'] = 'normal';
        $filter['marketable'] = 'true';
        
        //-----查找当前类别子类别的关联类型ID
        if ($urlFilter){
            if($vcat['type_id']){
                $filter['type_id']=null;
            }
        }
        //--------
        $this->pagedata['tabs'] = $cat['tabs'];
        $this->pagedata['cat_id'] = implode(",",$cat_id);
 
        $this->pagedata['views'] = $cat['setting']['list_tpl'];//商品展示模式
        $this->pagedata['orderBy'] = $objGoods->orderBy();//排序方式
        if($cat['tabs'][$tab]){
            parse_str($cat['tabs'][$tab]['filter'],$_filter);
            $filter = array_merge($filter,$_filter);
        }
        if($GLOBALS['runtime']['member_lv']){
            $filter['mlevel'] = $GLOBALS['runtime']['member_lv'];
        }
        if(!isset($this->pagedata['orderBy'][$orderBy])){
            $this->system->error(404);
        }else{
            $orderby = $this->pagedata['orderBy'][$orderBy]['sql'];
        }

        foreach($brandResult as $v=>$k){
            $brand_count[$k['brand_id']]['plus'] = $k['brand_cat'];
        }
        $selector = array();
        $search = array();
       
        if((!is_array($cat_id) && $cat_id) || $cat_id[0] || $cat_type){
            $goods_relate = $objGoods->getList("*",$filter,0,-1);
        }

        if ($goods_relate){
            unset($tmpSpecValue);
            foreach($goods_relate as $grk => $grv){
                if ($grv['spec_desc']){
                    $tmpSdesc = unserialize($grv['spec_desc']);

                    if(is_array($tmpSdesc)){
                        foreach($tmpSdesc as $tsk => $tsv){
                            foreach($tsv as $tk => $tv){
                                if (!in_array($tv['spec_value_id'],$tmpSpecValue)){
                                    $tmpSpecValue[] = $tv['spec_value_id'];
                                }
                            }
                        }
                    }
                }
            }
        }
        
        /***********************/
        if( $SpecList ){
            if( $curSpec ){
                $curSpecKey = array_keys($curSpec);
            }
            foreach($SpecList as $spk => $spv){
                $selected = 0;
                if( $curSpecKey && in_array($spk,$curSpecKey) ){
                    $spv['spec_value'][$curSpec[$spk]]['selected'] = true;
                    $selected = 1;
                }
                if( $spv['spec_style'] == "select" ){ //下拉
                    $SpecSelList[$spk] = $spv;
                    if( $selected )
                        $SpecSelList[$spk]['selected'] = true;
                }elseif ($spv['spec_style'] == "flat"){
                    $SpecFlatList[$spk] = $spv;
                    if ($selected){
                        $SpecFlatList[$spk]['selected'] = true;
                    }
                }
            }
        }
        $this->pagedata['SpecFlatList'] = $SpecFlatList;
        $this->pagedata['specimagewidth'] = $this->system->getConf('spec.image.width');
        $this->pagedata['specimageheight'] = $this->system->getConf('spec.image.height');
        /************************/
        if( is_array($cat['brand']) ){
            foreach($cat['brand'] as $bk => $bv){
                $bCount = 0;
                $brand = array('name'=>__('品牌'),'value'=>array_flip($filter['brand_id']));
                foreach($goods_relate as $gk => $gv){
                    if($gv['brand_id']){
                        if($gv['brand_id'] == $bv['brand_id']){
                            $bCount++;
                        }
                    }
                }
                if ($bCount>0){
                    $tmpOp[$bv['brand_id']] = $bv['brand_name']."<span class='num'>(".$bCount.")</span>";
                }
            }
            $brand['options'] = $tmpOp;
            $selector['brand_id'] = $brand;
        }
        
        if($cat['props']){
            foreach($cat['props'] as $prop_id=>$prop){
                if($prop['search'] == 'select'){
                    $prop['options'] = array_merge($prop['options']);
                    $prop['value'] = $filter['p_'.$prop_id][0];
                    $searchSelect[$prop_id] = $prop;
                }elseif($prop['search'] == 'input'){
                    $prop['value'] = ($filter['p_'.$prop_id][0]);
                    $searchInput[$prop_id] = $prop;
                }elseif($prop['search'] == 'nav'){
                    $prop['value'] = array_flip($filter['p_'.$prop_id]);
                    $plugadd = array();

                    foreach($goods_relate as $k=>$v){
                        if( $v["p_".$prop_id] != null ){
                            if( $plugadd[$v["p_".$prop_id]] ){
                                $plugadd[$v["p_".$prop_id]] = $plugadd[$v["p_".$prop_id]]+1;
                            }else{
                                $plugadd[$v["p_".$prop_id]] = 1;
                            }
                        }
                        $aFilter['goods_id'][] = $v['goods_id'];    //当前的商品结果集
                        $g['goods_id'] = $v['goods_id']; //前台搜索商品规格筛选，所要获取的goods_id
                    }
                    $navselector = 0;
                    
                    foreach($prop['options'] as $q=>$e){
                        if($plugadd[$q]){
                            $prop['options'][$q] = $prop['options'][$q]."<span class='num'>(".$plugadd[$q].")</span>";
                            if (!$navselector){
                                $navselector = 1;
                            }
                        }else{
                            unset($prop['options'][$q]);
                        }
                    }
                    $selector[$prop_id] = $prop;
                }
            }
        }

        if ($navselector){
            $nsvcount = 0;
            $noshow = 0;
            foreach($selector as $sk => $sv){
                if( $sv['value'] ){
                    $nsvcount++;
                }
                if (is_numeric($sk) && !$sv['show']){
                    $noshow++;
                }
            }
            if ( $nsvcount == intval(count($selector)-$noshow) ){
                $navselector = 0;
            }
        }
        
        foreach($cat['spec'] as $spec_id=>$spec_name){
            $sId['spec_id'][] = $spec_id;
        }

        $cat['ordernum'] = $cat['ordernum'] ? $cat['ordernum'] : array(''=>2);
        if ($cat['ordernum']){
            if ($selector){
                foreach($selector as $key => $val){
                    if(!in_array($key,$cat['ordernum']) && $val){
                        $selectorExd[$key] = $val;
                    }
                }
            }
        }

        $selector['ordernum'] = $cat['ordernum'];
        $objGoods->appendCols .= ',big_pic'; //appendCols big_pic update 2009年9月25日13:46:45
        
        if( $type_filter['type_id'] ){
            $filter['type_id'][] = $type_filter['type_id'];
        }
        /* 修复前台开启价格区间搜索 无法搜索到多规格商品价格 begin*/
        if( empty($g['pdt_desc']) || empty($g['goods_id']) ){
            if( is_array($filter['price']) && $filter['price'][1] ){
                $db = $this->system->database();
                $p_filter = $filter;
                unset($p_filter['goods_type']);
                $filter_goods = $db->select("select DISTINCT goods_id from sdb_products where price >= ".$p_filter['price'][0]." AND price <= ".$p_filter['price'][1]." AND disabled = 'false' AND marketable='true'");
                $f_goods = array(null);
                foreach( $filter_goods as $k=>$v ){
                    $f_goods[] = $v['goods_id'];
                }
                $filter_tmp['goods_id'] = $f_goods;
                $aProduct = $objGoods->getList(null,$filter_tmp,$pageLimit*($page-1),$pageLimit,$orderby);
                $count = $objGoods->count($filter_tmp);
            }else{
                $aProduct = $objGoods->getList(null,$filter,$pageLimit*($page-1),$pageLimit,$orderby);
                $count = $objGoods->count($filter);
            }//修复了如果进行价格区间的搜索，多规格商品信息筛选不精准
        /* 修复前台开启价格区间搜索 无法搜索到多规格商品价格 end*/
        }else{
            $shanxuan = $objGoods->filter_getList($g);/*前台搜索商品规格筛选*/
            if( $shanxuan[0]['goods_id'] ){ 
                $aProduct = $objGoods->getList(null,$filter,$pageLimit*($page-1),$pageLimit,$orderby);
                $count = $objGoods->count($filter);
            }
        }

        $this->pagedata['mask_webslice'] = $this->system->getConf('system.ui.webslice') ? ' hslice' : null;

        $this->pagedata['searchInput'] = &$searchInput;
        $this->pagedata['selectorExd'] = $selectorExd;
        $this->cat_id = $cat_id;
        $this->_plugins['function']['selector'] = array(&$this,'_selector');
        $this->pagedata['pager'] = array(
            'current' => $page,
            'total' => ceil($count/$pageLimit),
            'link' => $this->system->mkUrl('gallery',$view,array(implode(',',$cat_id),urlencode($p['str']),$orderBy,$tab,($tmp = time()),$cat_type)),
            'token' => $tmp,
        );

        if($page != 1 && $page > $this->pagedata['pager']['total']){
            $this->system->error(404);
        }
        if(!$count){
            $this->pagedata['emtpy_info'] = $this->system->getConf('errorpage.searchempty');
        }
        $objImage = &$this->system->loadModel('goods/gimage');
        $this->pagedata['searchtotal'] = $count;
        if(is_array($aProduct) && count($aProduct) > 0){
            $objGoods->getSparePrice($aProduct, $GLOBALS['runtime']['member_lv']);

            if($this->system->getConf('site.show_mark_price')){
                $setting['mktprice'] = $this->system->getConf('site.market_price');
            }else{
                $setting['mktprice'] = 0;
            }
            $setting['saveprice'] = $this->system->getConf('site.save_price');
            $setting['buytarget'] = $this->system->getConf('site.buy.target');
            $this->pagedata['setting'] = $setting;

            $this->pagedata['products'] = &$aProduct;
        }
        if($GLOBALS['runtime']['member_lv'] < 0){
            $this->pagedata['LOGIN'] = 'nologin';
        }
        if($SpecSelList){
            $this->pagedata['SpecSelList'] = $SpecSelList;
        }
        if($searchSelect){
            $this->pagedata['searchSelect'] = &$searchSelect;
        }
        $this->pagedata['selector'] = &$selector;
        $this->pagedata['cat_type'] = $cat_type;

        $this->pagedata['search_array'] = implode("+",$GLOBALS['search_array']); //搜索的关键字
        $this->pagedata['_PDT_LST_TPL'] = 'file:'.$cat['tpl'];
        $this->pagedata['_MAIN_'] = 'gallery/index.html';
      
        $this->getGlobal($this->seoTag,$this->pagedata);
        $this->output();
    }

    function _selector($params, &$smarty){
        
        $filter = unserialize(serialize($this->filter));
        if(is_numeric($params['key'])){
            $data = &$filter['p_'.$params['key']];
        }elseif ($params['key']=="spec"){
            $tmp=explode(",",$params['value']);
            $data = &$filter['s_'.$tmp[0]];
        }else{
            $data = &$filter[$params['key']];
        }

        if($params['mod']=='append'){
            $data[] = $params['value'];
        }elseif($params['mod']=='remove'){
            $data = array_flip($data);
            unset($data[$params['value']]);
            $data = array_flip($data);
        }else{
            if ($params['key']=="spec"){
                $tmpData = explode(",",$params['value']);
                $data = array($tmpData[1]);
            }
            else
                $data = array($params['value']);
        }

        $searchtools = &$this->system->loadModel('goods/search');
        $args = unserialize(serialize($this->pagedata['args']));

        $args[1] = $searchtools->encode($filter);
        $args[4]=1;

        return $this->system->mkUrl('gallery',$smarty->_vars['curView'],$args);
    }

    function get_goods_amount(&$result){
        return $result['searchtotal'];
    }
    
    function get_goods_cat(&$result){
        $pcat=$this->system->loadModel('goods/productCat');
        $row=$pcat->instance($result['cat_id'],'cat_name');
        return $row['cat_name'];
    }
    function get_goods_cat_p(&$result){
        $pcat=$this->system->loadModel('goods/productCat');
        $row=$pcat->getpath($result['cat_id']);
        if ($row){
            foreach($row as $k => $v){
                $tmpRow[]=$v['title'];
            }
            return implode(",",$tmpRow);
        }
    }
    function get_goods_type(&$result){
        $pcat=$this->system->loadModel('goods/productCat');
        $row=$pcat->instance($result['cat_id'],'type_id');
        if ($row['type_id']){
            $gtype=$this->system->loadModel('goods/gtype');
            $grow=$gtype->instance($row['type_id'],'name');
            return $grow['name'];
        }
    }
    function get_brand(&$result){
        if($sExd=$result['selectorExd']){
            if ($sExdB=$sExd['brand_id']){
                if ($sExdB['options']){
                    foreach($sExdB['options'] as $key => $val){
                        $brandExd[]=substr($val,0,strpos($val,"<"));
                    }
                    return implode(",",$brandExd);
                }
            }
        }
    }
    function get_sort_path(&$result){
        $sitemap = &$this->system->loadModel('content/sitemap');
        $path = array_merge($sitemap->getPath('goodsCat',$result['cat_id'],'index'),$this->path);
        
        if ($path){
            for($i=count($path)-1;$i>0;$i--){
                if ($path[$i]['title'])
                    $tmpPath[]=$path[$i]['title'];
            }
            if ($tmpPath)
                return implode(",",$tmpPath);
        }
    }

}

