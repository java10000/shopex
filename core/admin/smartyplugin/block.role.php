<?php
function tpl_block_role($params, $content, &$smarty,$s){
    if(null!==$content){
        $system = &$GLOBALS['system'];
        if($system->op_is_super)return $content;
        $opmod = &$system->loadModel('admin/operator');
        $act = &$opmod->getActions($system->op_id);
        $require = explode(',',$params['require']);
        if(count($require)>1){
            if($params['mode']=='or'){
                $pass=0;
                foreach($require as $r){
                    if(isset($act[$r])){
                        return $content;
                    }
                }
                return null;
            }else{
                foreach($require as $r){
                    if(!isset($act[$r])){
                        return;
                    }
                }
            }
        }else{
            if(!isset($act[$require[0]])){
                return;
            }
        }
        return $content;
    }
}

?>