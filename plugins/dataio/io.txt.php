<?php
class io_txt{

    var $name = 'txt-制表符分隔的文本文件';

    function export_begin($keys,$type,$count){
        download($type.'-'.date('YmdHis').'('.$count.').txt');
        echo implode("\t",$keys)."\r\n";
        flush();
    }

    function export_rows($rows){
        foreach($rows as $row){
            foreach($row as $k=>$v){
                $row[$k] = str_replace("\n",'\n',$v);
            }
            echo implode("\t",$row)."\r\n";
        }
        flush();
    }

    function export_finish(){
    }
}
?>
