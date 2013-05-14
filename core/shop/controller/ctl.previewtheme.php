<?php defined('CORE_DIR') || exit('入口错误'); ?>
<?php
class ctl_previewtheme extends shopPage{
   
    function index($theme){
         if($theme){
            define('TPL_ID',$theme);
            $this->system->in_preview_theme = $theme;
         }
         $_SERVER['QUERY_STRING']='index.html';
         $output = $this->system->_frontend(array('query'=>'index.html'));
         $output['cache'] = false;
         $this->system->display($output);
       }


}
?>