<?php
function widget_treelist(&$setting,&$system){
    $sitemap = $system->loadModel('content/sitemap');
    $rest=$sitemap->getDefineMap($GLOBALS['runtime']['path'],$setting['treenum'],$setting['treelistnum']);
    $content="";
    
    if($setting['showroot']=="true" && $rest['hidden']=='false'){
        if($rest['item_id']=='1') $jump="target='_blank'";
        $content.=' <div class="cat'.$rest['depth'].'"><a href="'.$rest['link'].'" '.$jump.'>'.$rest['label'].'</a></div>';
    }
    
    undoTree($rest['sub'],$content,($setting['treenum']+$rest['depth']));
    return $content;
}
function undoTree($result,&$content,$length){
    foreach($result as $k=>$v){
        if($v['item_id']==1){
            $jump="target='_blank'";
        }
        if($v['depth']>$length){
            break;
        }else{
            if($v['hidden']=='false'){
                    $content.=' <div class="cat'.$v['depth'].'"><a href="'.$v['link'].'" '.$jump.'>'.$v['label'].'</a></div>';
            }
        }
        if($v['sub']){
            undoTree($v['sub'],$content,$length);
        }
    }
}
?>
