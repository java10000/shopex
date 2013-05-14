<?php
function widget_comment(&$setting,&$system){
    $comment = &$system->loadModel('comment/comment');
    $setting['content_length']=$setting['content_length']?$setting['content_length']:'200';
    $result=$comment->getTopComment($setting['limit']);
    return $result;
}
?>