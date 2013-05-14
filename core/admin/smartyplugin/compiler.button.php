<?php
function tpl_compiler_button($params, &$smarty) {
    if(!function_exists('tpl_compiler_img')){
        require(dirname(__FILE__).'/compiler.img.php');
    }
    if($params['icon']){
        if($icon = ($params['icon']{0}=='"' || $params['icon']{0}=='\'')?substr($params['icon'],1,-1):$params['icon']){
            $icon = '<?php '.tpl_compiler_img(array('src'=>'"images/bundle/'.$icon.'"','class'=>'"icon"')).'?>';
            if($params['wrapimg']){
                $icon = '<i class="finder-icon">'.$icon.'</i>';
            }
        }
        unset($params['icon']);
    }
    if($params['class']){
        $class = ' '.(($params['class']{0}=='"' || $params['class']{0}=='\'')?substr($params['class'],1,-1):$params['class']);
        unset($params['class']);
    }else{
        $class = '';
    }
    $attrs = array();

    if(!$params['label']){
        $label = '';
    }elseif(strpos($params['label'],'$')===false){
        $label = ($params['label']{0}=='"' || $params['label']{0}=='\'')?substr($params['label'],1,-1):$params['label'];
    }else{
        $label = '<?php echo '.$params['label'].'; ?>';
    }
    unset($params['label']);

    $type = ($params['type']{0}=='"' || $params['type']{0}=='\'')?substr($params['type'],1,-1):$params['type'];

    if($type=='link'){
        $element = 'a';
    }else{
        $element = 'button';
        if($params['href']){
            $params['href'] = ($params['href']{0}=='"' || $params['href']{0}=='\'')?substr($params['href'],1,-1):$params['href'];
            $params['onclick'] = '"W.page(\''.$params['href'].'\')"';
            unset($params['href']);
        }
        if($type!='submit'){
            $params['type'] = '"button"';
        };
    }

    if($params['dropmenu']){
        $params['id'] = 'x_btn_'.(($params['dropmenu']{0}=='"' || $params['dropmenu']{0}=='\'')?substr($params['dropmenu'],1,-1):$params['dropmenu']);
        if($type!='dropmenu'){
            $element = 'span';
            $class .= ' btn-drop-menu drop-active';
            $drop_handel_id = $params['id'].'-handel';
            $dropmenu = '<img dropfor="'.$params['id'].'" id="'.$drop_handel_id.'" dropmenu='.$params['dropmenu'].' src="images/transparent.gif" class="drop-handle drop-handle-stand" />';
            unset($params['dropmenu']);
        }else{
            $drop_handel_id = $params['id'];
            $dropmenu = '<img src="images/transparent.gif" class="drop-handle" />';
        }
        $dropmenu_opts = ($params['dropmenu_opts']{0}=='"' || $params['dropmenu_opts']{0}=='\'')?substr($params['dropmenu_opts'],1,-1):$params['dropmenu_opts'];
        $scripts = '<script>new DropMenu("'.$drop_handel_id.'",{'.(strpos($dropmenu_opts,'$this->')==false?$dropmenu_opts:'<?php echo "'.$dropmenu_opts.'";?>').'});';
        $scripts .= '</script>';
    }

    foreach($params as $k=>$v){
        $attrs[]=$k.'='.(strpos($v,'{$')==false?$v:'"<?php echo '.$v.';?>"');
    }

    return '?><'.$element.' '.implode(' ',$attrs).' class="btn'.$class.'"><span><span>'.$icon.$label.$dropmenu.'</span></span></'.$element.'>'.$scripts.'<?php ';
}
?>