<?php defined('CORE_DIR') || exit('入口错误'); ?>
<?php
class ctl_content extends adminPage{

    var $workground ='site';

    function urllinkIndex($ident){
        $sitemap = &$this->system->loadModel('content/sitemap');
        $result=$sitemap->getResult($ident);
        $this->path[]=array('text'=>__('新增链接:').$result[0]['title']);
        $this->pagedata['urladdress']=$result[0]['action'];
        $this->pagedata['url_set']=$result[0]['item_id'];
        $this->pagedata['ident'] = $ident;
        $this->page('content/page_url.html');
    }
    function indexOfgoodsCat($nId){
        $sitemap = $this->system->loadModel('content/sitemap');
        $result=$sitemap->getNowNod($nId);
        $this->path[]=array('text'=>__('分类连接'));
        $this->pagedata['filter']=$result[0]['action'];;
        $this->pagedata['id'] = $nId;
        $this->page('content/goodscat.html');
    }

    function urllinkSave(){

        if($_POST['ident'] && $_POST['url_address']){
            $sitemap = &$this->system->loadModel('content/sitemap');
            $setTitle = $sitemap->setAction($_POST['ident'],array('action'=>$_POST['url_address'],'item_id'=>$_POST['url_set']));
            $this->splash('success','index.php?ctl=content/sitemaps',__('页面成功保存'));
        }else{
            $this->splash('failed','index.php?ctl=content/sitemaps',__('页面保存失败'));
        }

    }

    function footEdit(){
        $this->path[] = array('text'=>__('网页底部信息'));
        $this->pagedata['footEdit'] = $this->system->getConf('system.foot_edit');
        $this->page('system/tools/footEdit.html');

    }
    function saveFoot(){
        if($this->system->setConf('system.foot_edit',$_POST['footEdit'])){
            $this->splash('success','index.php?ctl=content/content&act=footEdit',__('保存成功'));
        }
    }

    function definedDetailPage($id){
        $this->path[] = array('text'=>__('页面内容编辑'));
        $o=&$this->system->loadModel('content/page');
        $this->pagedata['data']=$o->htmEdit($id);
        $this->page('content/definedDetail.html');
    }
    function editDefined(){
        $o=&$this->system->loadModel('content/page');
        if (!$o->editDefined($_POST)) {
            $this->splash('failed','index.php?ctl=content/content&act=definedDetailPage&p[0]='.$_POST['page_name'],500,$msg);
        }
        $this->splash('success','index.php?ctl=content/content&act=definedDetailPage&p[0]='.$_POST['page_name'],500,__('编辑成功!'));
    }

    function type(){
        $this->path[] = array('text'=>__('文章分类'));
        $oArticle = &$this->system->loadModel('content/article');
        $this->pagedata['type'] = $oArticle->getTypeList();

        $this->page('content/type_list.html');
    }
    function showNewType(){
        $this->display('content/type_new.html');
    }
    function addType(){
        $oArticle = &$this->system->loadModel('content/article');
        if($oArticle->addType($_POST)) {
            $this->splash('success','index.php?ctl=content/content&act=type',__('添加成功'));
        }else{
            $this->splash('failed','iindex.php?ctl=content/content&act=type',__('添加失败'));
        }

    }


    function saveGoodsCat(){
        $searchtools = &$this->system->loadModel('goods/search');
        //$path =array();
        //parse_str($_POST['filter'],$filter);
        //$filter    = $searchtools->encode($filter);

        if($_POST['id']){
            $sitemap = &$this->system->loadModel('content/sitemap');
            //$searchtools = &$this->system->loadModel('goods/search');
            //parse_str($_POST['filter'],$filter);
            $setTitle = $sitemap->setAction($_POST['id'],$_POST['filter']);

            $this->splash('success','index.php?ctl=content/sitemaps',__('页面成功保存'));
        }else{
            $this->splash('failed','index.php?ctl=content/sitemaps',__('页面保存失败'));
        }


    }

    function editType($nTid){
        $this->path[] = array('text'=>__('文章分类编辑'));
        $oArticle = &$this->system->loadModel('content/article');
        $this->pagedata['type'] = $oArticle->getTypeById($nTid);
        $this->page('content/type_edit.html');
    }
    function saveType(){
        $oArticle = &$this->system->loadModel("content/article");
        if($oArticle->saveType($_POST)){
            $this->splash('success','index.php?ctl=content/content&act=type',__('修改成功'));
        }else{
            $this->splash('failed','index.php?ctl=content/content&act=type',__('修改失败'));
        }
    }
    function delType($sTypeId){
        $oArticle = &$this->system->loadModel('content/article');
        if($oArticle->delType($sTypeId)){
            $this->splash('success','index.php?ctl=content/content&act=type',__('删除成功'));
        }else{
            $this->splash('failed','index.php?ctl=content/content&act=type',__('删除失败'));
        }
    }

    function custompage($id){
        $this->path[] = array('text'=>"自定页面管理");
        $tmpl =&$this->system->loadModel('content/systmpl');
        $content = $tmpl->getByType(md5($id));
        $this->pagedata['content'] = $content;
        $this->pagedata['node_id'] = $id;
        $this->page('content/custompage.html');
    }

    function savecustompage(){
        $this->begin('index.php?ctl=content/content&act=custompage&p[0]='.$_POST['node_id']);
        $tmpl =&$this->system->loadModel('content/systmpl');
        $tmpl->updateContent(md5($_POST['node_id']),$_POST['content']);
        $this->end(true,__('修改成功'),'index.php?ctl=content/sitemaps&act=index');
    }

}
?>