<?php defined('CORE_DIR') || exit('入口错误'); ?>
<?php
class ctl_artlist extends shopPage{

    var $type='artlist';
    var $seoTag=array('shopname','article_cat');
    function ctl_artlist(){
        parent::shopPage();
        $this->title    = $this->system->getConf('site.article_list_title');
        $this->keywords = $this->system->getConf('site.article_list_meta_key_words');
        $this->desc     = $this->system->getConf('site.article_list_meta_desc');
    }

    function index($cat_id,$page=1) {
        $this->customer_source_type='artlist';
        $this->customer_template_type = 'artlist';
        $this->customer_template_id=$cat_id;
        $this->id=array('node_id'=>$cat_id);
        if(intval($cat_id)){
            $objSitemap = &$this->system->loadModel('content/sitemap');
            $filter['node_id'] = intval($cat_id);
            $aInfo = $objSitemap->getPathById($filter['node_id'],false);
            foreach($aInfo as $r){
                if($r['node_id'] == $filter['node_id']){
                    $this->pagedata['cat_name'] = $r['title'];
                    break;
                }
            }
        }
        $filter['ifpub'] = 1;


        if($this->system->getConf('system.seo.noindex_catalog'))
            $this->header .= '<meta name="robots" content="noindex,noarchive,follow" />';

        $pageLimit = 20;
        $objArticle = &$this->system->loadModel('content/article');
        $this->pagedata['articles'] = $objArticle->getList('title,article_id,uptime',$filter,($page-1)*$pageLimit,$pageLimit);
        $count = $objArticle->count($filter);

        $this->pagedata['pager'] = array(
                'current'=>$page,
                'total'=>($count>$pageLimit)?(floor($count/$pageLimit)+1):1,
                'link'=>$this->system->mkUrl('artlist','index',array($cat_id,($tmp = time()))),'token'=>$tmp);
      
        if($page > $this->pagedata['pager']['total']){
            $this->system->error(404);
        }
        $this->path[]=array('title'=>'');
        $this->getGlobal($this->seoTag,$this->pagedata);
        $this->output();
    }
    function get_article_cat(&$result){
        return $result['cat_name'];
    }
}
?>
