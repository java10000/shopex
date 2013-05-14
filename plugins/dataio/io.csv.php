<?php
class io_csv{

    var $name = 'csv-逗号分隔的文本文件';
    var $importforObjects = 'goods';

    function export_begin($keys,$type,$count,$filename=''){
        $filename = !empty($filename)?$filename:$type.'-'.date('YmdHis').'('.$count.')';
        download($filename.'.csv');
        if($keys) $this->export_rows(array($keys));
    }

    function export_rows($rows){
        include(CORE_DIR.'/lib/charset/char_local.php');
        include(CORE_DIR.'/lib/charset/char_utf.php');
        foreach($rows as $row){
            $row = removeBom($row);
            $char = implode('","',$this->_escape($row));
            foreach($char_utf as $k => $v){
                $char = str_replace($v, $char_replace[$k], $char);
            }
            $char = $this->charset->utf2local('"'.$char.'"','zh');
            foreach($char_local as $k => $v){
                $char = str_replace($char_replace[$k], $v, $char);
            }
            echo $char."\r\n";
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
        if(!setlocale(LC_ALL, 'zh_CN.gbk')){
            setlocale(LC_ALL, "chs");
        }
        $data = fgetcsv($handle, 100000, ",");
        foreach($data as $k=>$v){
            $data[$k] = $this->charset->local2utf($v,'zh');
        }
        return $data;
    }
    function import_rows(&$handle){
        if(!setlocale(LC_ALL, 'zh_CN.gbk')){
            setlocale(LC_ALL, "chs");
        }
        while($data = fgetcsv($handle, 100000, ",")){
            foreach($data as $k => $v){
                $tmpdata[$k]=$this->charset->local2utf($v,'zh');
            }
            $return[]=$tmpdata;
        }
        return $return;
    }

}
?>