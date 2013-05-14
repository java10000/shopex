<?php
class io_gtype{
    var $name = '类型定义文件';
    var $importforObjects='gtype';
/*
    function export_begin($keys,$type,$count){
        echo '尚未实现';
        exit();
    }

    function export_rows($rows){
    }

    function export_finish(){
    }*/

    function import_rows($xmlContent){
        $system = $GLOBALS['system'];
        $xml = $system->loadModel('utility/xml');
        $arr = $xml->xml2array($xmlContent);
        if($arr['goodstype']){
            return $arr;
        }elseif($arr['goodstypes']){
            return $arr;
        }else{
            return array();
        }
    }
}
?>
