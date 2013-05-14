<?php
/*
 * Smarty plugin
 * -------------------------------------------------------------
 * File:     compiler.tplheader.php
 * Type:     compiler
 * Name:     tplheader
 * Purpose:  Output header containing the source file name and
 *           the time it was compiled.
 * -------------------------------------------------------------
 */
function tpl_compiler_finder_lister($params,&$compiler){
    $controller = &$compiler->_parent;
    $o = &$controller->model;
    $table_define = var_export($o->_columns(),1);

    $ret=<<<EOF
    \$o = &\$this->model;
    \$o->__table_define = {$table_define};
    if(!function_exists('action_finder_lister')){
        require(CORE_INCLUDE_DIR.'/core/action.finder_lister.php');
    }
    action_finder_lister(\$this);
    \$this->_vars['_finder']['id'] = '{$o->idColumn}';
    \$this->_vars['_finder']['hasTag'] = '{$o->hasTag}';
    echo \$this->_fetch_compile_include(\$this->_vars['_finder']['current_view'],array());
    \$o = null;
EOF;
    return $ret;
}
?>