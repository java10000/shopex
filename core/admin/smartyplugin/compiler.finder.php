<?php
/*<{include file="{$_finder.current_view}#{$env.get.ctl}"}>*/
function tpl_compiler_finder($params, &$compiler) {

    $controller = &$compiler->_parent;
    $o = &$controller->model;

//    if($options['disabledMark'])  $o->disabledMark = $options['disabledMark']; //做回收站时使用

    $params['_name'] = substr(md5($_SERVER['QUERY_STRING']),0,6);
    $params['var'] = 'window.finderGroup[\''.$params['_name'].'\']';
    $params['allowImport'] = $controller->allowImport;
    $params['allowExport'] = $controller->allowExport;
    $params['deleteAble'] = $controller->deleteAble;
    $params['noRecycle'] = $controller->noRecycle;
    $params['hasTag'] = $controller->model->hasTag;
    $params['finder_action_tpl'] = $controller->finder_action_tpl;
    $params['controller'] = $controller->controller;
    $params['model'] = $controller->object;
    $params['listViews'] = $controller->listViews;
    $params['listViews']['finder/list.html'] = array('name'=>__('列表'),'icon'=>'images/bundle/view_text.gif');
    $params['searchOptions'] = $o->searchOptions();
    $params['filterUnable'] = $controller->filterUnable;
//    $params['disabledMark'] = $o->disabledMark;
    $params['currentSearchKey'] = key($params['searchOptions']);
    $params['viewsfilters'] = $controller->_views();
    $params['views'] = array_keys($params['viewsfilters']);

    if ( method_exists($controller,'custom_actions') ) {
        $params['customActions'] = $controller->custom_actions();
    }
    return '$this->with_nav=false;$this->_vars[\'_finder\'] = '.var_export($params,1).';$this->_vars[\'_finder\'][\'params\'] = $this->filter;$this->_vars[\'_finder\'][\'filterInit\'] = $this->filterInit;$this->_vars[\'_finder\'][\'viewParams\'] = $this->_vars[\'_finder\'][\'viewsfilters\'][$this->_vars[\'_finder\'][\'views\'][$_GET[\'view\']+0]];';

}
?>