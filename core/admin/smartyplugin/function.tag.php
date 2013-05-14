<?php
function tpl_function_tag($params, &$smarty){
    echo $smarty->_fetch_compile_include('finder/finder-tag.html', $params);
}
?>