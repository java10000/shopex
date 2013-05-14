<?php
function widget_goods_show(&$setting,&$system){

    $o=$system->loadModel('goods/products');
    $limit = (intval($setting['limit'])>0)?intval($setting['limit']):6;
    $orderby=$setting['goods_orderby']?$o->orderBy($setting['goods_orderby']):null;


        parse_str($setting['g_filter'],$filter);

        $filter = gs_getFilter($filter);


        if(!is_array($filter['cat_id'])&&$filter['cat_id']){
            $filter['cat_id']=array($filter['cat_id']);
        }
        if(!$filter['cat_id']){
            unset($filter['cat_id']);
        }
        if($filter['type_id'] && !is_array($filter['type_id'])){
            $filter['type_id']=array($filter['type_id']);
        }
        if($filter['pricefrom']){
                $filter['price'][0]=$filter['pricefrom'];
        }
        if($filter['priceto']){
                if(!$filter['price'][0]){
                    $filter['price'][0]=0;
                }
                $filter['price'][1]=$filter['priceto'];
        }


        //$o->appendCols.='big_pic';

        $result=$o->getList(null,$filter,0,$limit,$orderby['sql']);

        if('on' == $setting['showMore']){
            $oSearch = $system->loadModel('goods/search');
            $result['link']=$system->mkUrl('gallery',$system->getConf('gallery.default_view'),array(implode(",",$filter['cat_id']),$oSearch->encode($filter),$setting['goods_orderby']?$setting['goods_orderby']:0));

        }
        return $result;

}

function gs_getFilter($filter){
    $filter = array_merge(array('marketable'=>"true",'disabled'=>"false",'goods_type'=>"normal"),$filter);
    if($GLOBALS['runtime']['member_lv']){
        $filter['mlevel'] = $GLOBALS['runtime']['member_lv'];
    }
    if($filter['props']){
        foreach($filter['props'] as $k=>$v){
            $filter['p_'.$k]=$v[0];
        }
    }
    return $filter;
}
?>