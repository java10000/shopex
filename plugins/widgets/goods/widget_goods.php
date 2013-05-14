<?php
function widget_goods(&$setting,&$system){

    $o=$system->loadModel('goods/products');
    $limit = (intval($setting['limit'])>0)?intval($setting['limit']):6;
    $config=$system->getConf('site.save_price');
    $data['onSelect']=$setting['onSelect']?$setting['onSelect']:0;
    $setting['max_length']=$setting['max_length']?$setting['max_length']:35;
    $setting['view'] = $system->getConf('gallery.default_view');
    $search = $system->loadModel('goods/search');
    $setting['str'] = $search->encode($filter);
    $oSearch = $system->loadModel('goods/search');
    $setting['restrict']=$setting['restrict']?$setting['restrict']:'on';
    $order=$setting['goods_orderby']?$o->orderBy($setting['goods_orderby']):null;
    if($setting['columNum']>1){
        for($i=1;$i<=$setting['columNum'];$i++){
            parse_str($setting['filter'.$i],$filter[$i]);
            $filter[$i] = getFilter($filter[$i]);
            if($filter[$i]['cat_id']){
                $setting['cat_id']=implode(",",$filter[$i]['cat_id']);
            }else{
                $setting['cat_id']=0;
            }
            if($filter[$i]['type_id'] && !is_array($filter[$i]['type_id'])){
                $filter[$i]['type_id']=array($filter[$i]['type_id']);
            }
            if($filter[$i]['pricefrom']){
                $filter[$i]['price'][0]=$filter[$i]['pricefrom'];
            }
             if($filter[$i]['priceto']){
                if(!$filter[$i]['price'][0]){
                    $filter[$i]['price'][0]=0;
                }
                $filter[$i]['price'][1]=$filter[$i]['priceto'];
            }
            $result['link'][($i-1)]=$system->mkUrl('gallery',$setting['view'],array(implode(",",$filter[$i]['cat_id']),$oSearch->encode($filter[$i]),$setting['goods_orderby']?$setting['goods_orderby']:0));
            $result['goods'][]=$o->getList(null,$filter[$i],0,$limit,$order['sql']);

            unset($filter[$i]);
        }

        return $result;

    }else{
        parse_str($setting['filter1'],$filter);
        $filter = getFilter($filter);
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
        $oSearch = $system->loadModel('goods/search');
        $result['link']=$system->mkUrl('gallery',$setting['view'],array(implode(",",$filter['cat_id']),$oSearch->encode($filter),$setting['goods_orderby']?$setting['goods_orderby']:0));

        $result['goods']=$o->getList(null,$filter,0,$limit,$order['sql']);
        $result['goods']=$o->getList(null,$filter,0,$limit,$order['sql']);
        $result['goods_num'] = count($result['goods']);
        return $result;
    }
}

function getFilter($filter){
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