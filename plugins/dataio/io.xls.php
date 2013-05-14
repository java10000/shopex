<?php
class io_xls{

    var $name = 'xls-Excel文件';

    function export_begin($keys,$type,$count){

        download($type.'-'.date('YmdHis').'('.$count.').xls');

        echo '<head><meta http-equiv="Content-Type" content="text/html; charset=utf-8" /><style>td{vnd.ms-excel.numberformat:@}</style></head>';
        echo '<table width="100%" border="1">';
        echo '<tr><th filter=all>'.implode('</th><th filter=all>',$keys)."</th></tr>\r\n";
        flush();
    }

    function export_rows($rows){
        foreach($rows as $row){
            echo '<tr><td>'.implode('</td><td>',$row)."</td></tr>\r\n";
        }
        flush();
    }

    function export_finish(){
        echo '</table>';
        flush();
    }
}
?>
