<?php
function tpl_function_template_filter($params, &$smarty)
{
    $system = &$GLOBALS['system'];
    if($params['type']){
        $theme = $system->getConf('system.ui.current_theme');
        $o = $system->loadModel('system/template');
        $theme_list=$o->get_template_list($theme);
        $include_var['list']=$theme_list[$params['type']];
        $include_var['name']=$params['name'];
        $include_var['selected']=$o->get_customer_template($params['type'],$params['id'],$params['source_type']);
    }
    echo $smarty->_fetch_compile_include('system/template/template_filter.html',$include_var);
}
?>