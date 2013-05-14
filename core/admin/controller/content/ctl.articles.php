<?php defined('CORE_DIR') || exit('入口错误'); ?>
<?php
include_once('objectPage.php');
class ctl_articles extends objectPage{

    var $workground ='site';
    var $object = 'content/article';
    var $finder_action_tpl = 'content/article/finder_action.html'; //默认的动作html模板,可以为null
    var $finder_filter_tpl = 'content/article/finder_filter.html'; //默认的过滤器html,可以为null
    var $allowImport =false;
    var $allowExport = false;
    var $filterUnable = true;

    function index($node_id){
        $this->pagedata['node_id'] = $node_id;

        $oArticle = &$this->system->loadModel('content/article');

        parent::index(array('params'=>array('node_id'=>$node_id)));
    }

    function addArticle($node_id){
        if($_POST['ifpub']){$_POST['ifpub'] = 1;}else $_POST['ifpub'] = 0;
        if($_POST['goodslink']) $_POST['goodslink'] = 1;
        else $_POST['goodslink'] = 0;
        if($_POST['hotlink']) $_POST['hotlink'] = 1;
        else $_POST['hotlink'] = 0;
        $oArticle = &$this->system->loadModel('content/article');
        if(!$oArticle->addArticle($_POST,$msg)){
            $this->splash('failed','index.php?ctl=content/articles&act=index&p[0]='.$node_id,$msg);
        }
        $this->splash('success','index.php?ctl=content/articles&act=index&p[0]='.$node_id,__('文章添加成功'));
    }

    function _detail(){
        return array('show_detail'=>array('label'=>__('文章详细信息'),'tpl'=>'content/article/article.html'));
    }

    function show_detail($nConId){
        $oseo = &$this->system->loadModel('system/seo');
        $seo_info=$oseo->get_seo('article',$nConId);
        $this->pagedata['seo'] = $seo_info;
        $oArticle = &$this->system->loadModel('content/article');
        $sitemap = &$this->system->loadModel('content/sitemap');
        $this->pagedata['article'] = $oArticle->get($nConId);
        $goodsinfo = unserialize($this->pagedata['article']['goodsinfo']);
        if (is_array($goodsinfo)&&count($goodsinfo)>0)
            $this->pagedata['goodsinfo'] = $goodsinfo;
        $hotlink = $oArticle->gethotlink($nConId);
        if ($hotlink)
            $this->pagedata['hotlink'] = $hotlink;
        //$this->pagedata['path'] = $sitemap->getPathById($this->pagedata['article']['node_id'],false);
        $this->pagedata['node_id'] = $this->pagedata['article']['node_id'];
        $this->pagedata['article_cat'] = $oArticle->getArticleCat();

        $this->pagedata['article_id'] = $nConId;
    }

    function addNew($node_id){
        $this->path[] = array('text'=>__('添加文章'));
        $sitemap = &$this->system->loadModel('content/sitemap');
        $this->pagedata['node_id'] = $node_id;
        $this->pagedata['article']['ordernum'] = '50';//2011-8-16 新增 文章排序功能by zhangxuehui
        $oArticle = &$this->system->loadModel('content/article');
        $this->pagedata['article_cat'] = $oArticle->getArticleCat();
        $this->page('content/article/article.html');
    }

    function edit($article_id,$node_id){

        $this->path[] = array('text'=>__('编辑文章'));
        $sitemap = &$this->system->loadModel('content/sitemap');
        $this->pagedata['node_id'] = $node_id;
        $this->pagedata['path'] = $sitemap->getPathById($node_id,false);

        $oArticle = &$this->system->loadModel('content/article');
        $this->pagedata['article'] = $oArticle->get($article_id);

        $this->page('content/article/article.html');
    }

    function save($article_id){
        if($_POST['ifpub']) $_POST['ifpub'] = 1;
        else $_POST['ifpub'] = 0;
        if($_POST['goodslink']) $_POST['goodslink'] = 1;
        else $_POST['goodslink'] = 0;
        if($_POST['hotlink']) $_POST['hotlink'] = 1;
        else $_POST['hotlink'] = 0;
        $this->begin('index.php?ctl=content/articles&act=detail&p[0]='.$article_id);
        $oArticle = &$this->system->loadModel('content/article');
        $this->end($oArticle->saveArticle($_POST),__('文章保存成功'));
    }
    function getGoods($num,$goodsId){
        $article = &$this->system->loadModel('content/article');
        $keywords = $_POST['keywords'];
        $relateGoods=$article->getGoodsByKw($keywords,$num);
        $this->pagedata['goods'] = &$relateGoods;
        if ($goodsId){
            $goodsIdG=explode(",",intval($goodsId));
            if ($relateGoods)
                foreach($relateGoods as $key => $val){
                    if (in_array($val['goods_id'],$goodsIdG)){
                        $relateGoods[$key]['checked']="checked";
                    }
                }
        }
        $this->display('content/article/getgoods.html');
    }
}
?>
