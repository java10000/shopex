<?php
class io_taobaoordercsv{

    var $name = 'csv-订单列表（兼容淘宝格式）';
    var $importforObjects = 'order';
    var $exportforObjects= 'order';
    var $columns = 'order_id,member_id,member_id as shop_cat,final_amount,cost_freight,score_g as tpayscore,total_amount,total_amount,score_g,payed,score_g as tscore,pay_status,memo,ship_name,ship_area,ship_addr,ship_zip,shipping_id,ship_tel,ship_mobile,createtime,acttime,tostr,tostr as tsort,order_id as delivery_id,shipping,mark_text';

    function export_begin($keys,$type,$count){
        download($type.'-'.date('YmdHis').'('.$count.').csv');
        if($keys) $this->export_rows(array($keys));
    }

    function export_rows($rows){
        foreach($rows as $row){
            echo $this->charset->utf2local('"'.implode('","',$this->_escape(removeBom($row))).'"','zh')."\r\n";
        }
        flush();
    }

    function export_finish(){
    }

    function _escape($arr){
        foreach($arr as $k=>$v){
            $arr[$k] = str_replace("\r",'\r',str_replace("\n",'\n',str_replace('"','""',$v)));
        }
        return $arr;
    }

    function import_row(&$handle){
        $data = fgetcsv($handle, 100000, ",");
        foreach($data as $k=>$v){
            $data[$k] = $this->charset->local2utf($v,'zh');
        }
        return $data;
    }
    function import_rows(&$handle){
        return false;
    }
}
?>