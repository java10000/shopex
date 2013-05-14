<?php
function tpl_input_object($params,$ctl){
    $system = &$GLOBALS['system'];
    if(!($o = &$system->loadModel($params['object']))){
        $ctl->trigger_error('Wrong finder tfype: "'.$mod.'"',E_USER_ERROR);
        return;
    }

    if(isset($params['filter'])){
        if(!is_array($params['filter'])){
            parse_str($params['filter'],$params['filter']);
        }
    }
    $params['filter'] = array_merge($params['filter'],array('object_filter'=>true));


    if(isset($params['multiple']) && $params['multiple']){
        $params['params'] = serialize($params['params']);
        $params['domid'] = $ctl->new_dom_id();

        if(is_string($params['value'])){
            $params['value'] = explode(',',$params['value']);
        }
        if(!$params['cols']){
            $params['cols'] = $o->textColumn;
        }

        if(count($params['value'])>0){
            $params['idcol'] = $o->idColumn;
            $params['keycol'] = $params['key']?$params['key']:$o->idColumn;
            $params['textcol'] = $o->textColumn;
            $params['items'] = &$o->getList($o->idColumn.','.$params['keycol'].','.$params['cols'],array($o->idColumn=>$params['value']),0,-1);
        }

        return $ctl->_fetch_compile_include('finder/input.html', array('_input'=>$params));
    }else{
        $key = $params['key']?$params['key']:$o->idColumn;
        if($params['value']){
            $row = $o->getList($o->textColumn,array($key=>$params['value']),0,1);
            $string = $row[0][$o->textColumn];
        }else{
            $string = $params['emptytext']?$params['emptytext']:'请选择...';
        }
        $input = '<input type="hidden" name="'.htmlspecialchars($params['name']).'" value="'.htmlspecialchars($params['value']).'" />';
        if(!$params['id']){
            $params['id'] = $ctl->new_dom_id();
        }
        $object = http_build_query($params);
        if($params['select']=='checkbox'){
            $addons='multiple="multiple"';
        }
        $output=<<<EOF
        <div id="{$params['id']}" {$addons} class='object-select clearfix'>
        <div class='label' onclick="new Dialog('index.php?ctl=editor&act=selectobj&{$object}',{width:550,title:'选择...',callback:obj_finder_call_back.bind($('{$params['id']}'))})">{$string}</div>
        <div class='handle' onclick="new Dialog('index.php?ctl=editor&act=selectobj&{$object}',{width:550,title:'选择...',callback:obj_finder_call_back.bind($('{$params['id']}'))})">&nbsp;</div>
        {$input}
      </div>
EOF;
        echo $output;
    }
}
