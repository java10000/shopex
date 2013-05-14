<?php
function tpl_function_uploader($params, &$smarty){
    echo $smarty->_fetch_compile_include('system/tools/uploader.html',$params);
}
?>