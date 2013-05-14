<?php
function tpl_function_finder($params, &$smarty){
    include_once('shopObject.php');
    $objects = shopObject::objects();

    $system = &$GLOBALS['system'];
    if(!($mod = $objects[$params['type']]) || !($o = &$system->loadModel($mod))){
        $smarty->trigger_error('Wrong finder tfype: '.$params['type'],E_USER_ERROR);
    }

    $includeVars['filter'] = &$o->getFilter(null);
    $includeVars['_finder'] = &$params;

    $cols = $o->defaultCols;
    $params['order'] = $o->defaultOrder;
    $params['controller'] = $o->adminCtl;
    $params['filter'] = $params['params'];
    if($params['infoUrl']) $params['rowselect'] = true;
    if(!$params['plimit'])$params['plimit'] = 20;

    //todo：自定义列
    $includeVars['items'] = &$o->getFinder($cols,$params['filter'],0,$params['plimit'],$count,$params['order']);
    $params['_name'] = substr(md5($_SERVER['QUERY_STRING']),0,6);
    if(!$params['var']){
        $params['var'] = 'window.finder[\''.$params['_name'].'\']';
        $params['initvar'] = 'if(!window.finder)window.finder={};';
    }else{
        $params['initvar'] = 'var ';
    }

    $pager = array(
        'current'=> 1,
        'total'=> floor($count/$params['plimit'])+1,
        'link'=> 'javascript:'.$params['var'].'.jumpTo.bind('.$params['var'].')(_PPP_)',
        'token'=> '_PPP_'
    );

    if(!$params['finder_action_tpl'])$params['finder_action_tpl'] = $o->finder_action_tpl;
    if(!$params['finder_filter_tpl'])$params['finder_filter_tpl'] = $o->finder_filter_tpl;

    $params['id'] = $o->idColumn;
    $params['count'] = $count;

    $params['pager'] = &$pager;
    $params['searchOptions'] = $o->searchOptions();
    $params['orderBy'] = $o->defaultOrder[0];
    $params['orderType'] = $o->defaultOrder[1];

    $smarty->_fetch_compile_include($params['struct']?$params['struct']:'finder/common.html',$includeVars);
}
?>
