<?php
function widget_transport(&$setting,&$system){
    $delivery =&$system->loadModel('trading/delivery');
    $number=intval($setting['rowNum'])?intval($setting['rowNum']):5;
    $filter['status']="Y";
    $aTmp=array();
    $result=$delivery ->getTopDelivery($number);
    
    $setting['smallPic'] and $setting['smallPic'] = $system->base_url() . 'statics/icons/' . $setting['smallPic'];

        foreach($result as $key => $val){
            $aTmp[$i]['transport'] = $val['logi_name']?$val['logi_name']:$val['delivery'];
            $aTmp[$i]['delivery_id'] = $val['delivery_id'];
            $aTmp[$i]['logi_no'] = $val['logi_no'];
            $aTmp[$i]['order_id'] = $val['order_id'];
            $aTmp[$i]['ship_name'] = $val['ship_name'];

            //$aTmp[$key]['status'] = $val['status'];
            //$aTmp[$key]['date'] = date("Y-m-d",$val['createtime']);
            //$aTmp[$key]['total_amount'] = $val['total_amount'];
            $i++;
        }
    return $aTmp;
}
?>