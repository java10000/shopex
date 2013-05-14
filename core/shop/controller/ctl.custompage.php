<?php defined('CORE_DIR') || exit('入口错误');

class ctl_custompage extends shopPage{

    function index($nid){
        $this->_register_resource("custom", array(array(&$this,"__resource_db_source"),
                                       array(&$this,"__resource_db_timestamp")));

        $oTemplate=&$this->system->loadModel('system/template');
        $theme = $oTemplate->applyTheme(constant('TPL_ID'));
        $this->theme = $theme['theme'];
        $this->fetch('custom:'.$nid,1);
    }


    function __resource_db_source($tpl_name, &$tpl_source, &$smarty){
        $tmpl = &$this->system->loadModel('content/systmpl');
        if ( !$tpl_source = $tmpl->get(md5($tpl_name)) ) {
            $this->system->error(404);
        }  //解决自定义页面不存在仍可访问页面的问题
        
        $tpl_source = str_replace("[header]",'<{require file="block/header.html"}>',$tpl_source);
        $tpl_source = str_replace("[footer]",'<{require file="block/footer.html"}>',$tpl_source);
        return $tpl_source !== false;
    }

    function __resource_db_timestamp($tpl_name, &$tpl_timestamp, &$smarty)
    {
        $tpl_timestamp = time();
        return is_int($tpl_timestamp);
    }

}

