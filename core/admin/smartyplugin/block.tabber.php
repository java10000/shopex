<?php
function tpl_block_tabber($params, $content, &$smarty,$s)
{
    if(null===$content){
        $i = count($smarty->_tag_stack)-1;
        $smarty->_tag_stack[$i][1]['_tabid']=substr(md5(rand(0,time())),0,6);
        $smarty->_tag_stack[$i][1]['_i']=0;
    }else{
        foreach($params as $k=>$v){
            if($k!='items' && $k!='class'){
                $attrs[] = $k.'="'.htmlspecialchars($v).'"';
            }
        }

        foreach($params['items'] as $k=>$v){
            $cls = $k==$params['current']?'t-handle-current':'t-handle';
            $a = array_slice($params['items'],0,count($params['items']));
            unset($a[$k]);
            $a = "'".$k.'\',[\''.implode('\',\'',array_keys($a)).'\']';
            $handle[]="<span class=\"{$cls} {$v['class']}\"".($v['url']?('url="'.$v['url'].'"'):'')." onclick=\"setTab({$a})\" id=\"_{$k}\">{$v['name']}</span>";
        }
        return '<div class="handles'.($params['class']?(' '.$params['class']):'').'" '.implode(' ',$attrs).'>'.implode('&nbsp;',$handle).'</div><div class="tabs">'.str_replace('id="'.$params['current'].'" style="display:none"','id="'.$params['current'].'"',$content).'</div>';
    }
}

?>