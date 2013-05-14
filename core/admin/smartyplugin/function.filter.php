<?php
function tpl_function_filter($params, &$smarty){
    $_tpl_tpl_vars = $smarty->_vars;
    $system = &$GLOBALS['system'];
    if(!($o = &$system->loadModel($params['type']))){
        $smarty->trigger_error('Wrong finder tfype: "'.$mod.'"',E_USER_ERROR);
    }
    if(include_once(CORE_DIR.'/admin/controller/'.dirname($o->adminCtl).'/ctl.'.basename($o->adminCtl).'.php')){
        $ctlClass = 'ctl_'.basename($o->adminCtl);
        $obj = new $ctlClass;
        $info = get_object_vars($obj);
        unset($obj);
        $value = $params['from']?$params['from']:$params['value'];
        parse_str($value,$data);
        $objCat = &$system->loadModel('goods/productCat');
        if($mod=='member/member'){
            $obj=$o->getFilter(array_merge($data,array($params['params'])));
        }else{
            $obj=$o->getFilterByTypeId(array_merge($data,array($params['params'])));
        }
        $include_var = array(
            'filter'=>$obj,
            '_finder'=>array(
                'gtype'=>$objCat->getTypeList(),
                'type' => $params['type'],
                'name' => $params['name'],
                'view' => $info['finder_filter_tpl'],
                'from' => $value, //要改成value
                'value' => $value,
                'params' => $params['params'],
                'json' => json_encode($data),
                'data' => $data,
                'controller'=>$o->adminCtl,
                'domid' => substr(md5(rand(0,time())),0,6)
            ));
        $smarty->template_dir = CORE_DIR.'/admin/view/';
        echo $smarty->_fetch_compile_include('finder/filter.html', $include_var);
        $smarty->_vars = $_tpl_tpl_vars;
        unset($_tpl_tpl_vars);
    }else{
        $smarty->trigger_error('adminCtl ??',E_USER_ERROR);
    }
}
?>