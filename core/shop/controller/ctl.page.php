<?php defined('CORE_DIR') || exit('入口错误'); ?>
<?php
class ctl_page extends shopPage{
    var $_call='show';
    var $seoTag=array('shopname');
    function ctl_page(){
        parent::shopPage();

    }

    function show($ident) {
        $ident = urldecode($ident);
        $page = &$this->system->loadModel('content/systmpl');
        $this->customer_template_type='page';
        $this->customer_template_id=$ident;

        $title=$page->getTitle($ident);
        if($title){
            $this->pagedata['page'] = 'page:'.$ident;
            $this->pagedata['_MAIN_'] = 'page/single-page.html';
            foreach($title as $k=>$v){
                $uLink=explode(":",$title[$k]['link']);
                $this->path[]=array('title'=>$title[$k]['title'],'link'=>$this->system->mkUrl('page',$uLink[1]));
            }
            $titles = array_reverse($title);
            foreach($titles as $k=>$v){
                $seoTitle .= '  '.$titles[$k]['title'];
            }
        } elseif ( 'index' != $ident ) {
            $this->system->error(404);
        }

        $title=$page->getTitle($ident);
        $this->title = $seoTitle.'  '.$this->system->getConf('site.homepage_title');
        $this->keywords = $seoTitle.'  '.$this->system->getConf('site.homepage_meta_key_words');
        $this->desc  = $seoTitle.'  '.$this->system->getConf('site.homepage_meta_desc');
        $this->getGlobal($this->seoTag,$this->pagedata);
        //if($ident!='index') $this->path[]=$page->getTitle($ident);
        $this->output();
    }

    function error($errArr){
        $this->pagedata['error'] = $errArr;
        $this->pagedata['_MAIN_'] = 'page/error.html';
        $this->output();
    }
}
?>
