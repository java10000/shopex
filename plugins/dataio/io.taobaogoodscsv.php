<?php
class io_taobaogoodscsv{

    var $name = 'csv-订单商品（兼容淘宝格式）';
    var $importforObjects = 'order';
    var $exportforObjects= 'order';
    //name,price,nums,addon from sdb_order_items
    var $columns = 'order_id';

    function export_begin($keys,$type,$count){
        download($type.'-'.date('YmdHis').'('.$count.').csv');
        if($keys) $this->export_rows(array($keys));
    }

    function export_rows($rows){
        foreach($rows as $row){
            echo $this->charset->utf2local('"'.implode('","',$this->_escape($row)).'"','zh')."\r\n";
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