<?php defined('CORE_DIR') || exit('入口错误'); ?>
<?php
class ctl_menus extends adminPage{
    var $workground ='site';

    function menus(){
        $oMenus=&$this->system->loadModel('content/menus');
        $this->pagedata['menus']=$oMenus->menusList();
        $this->page('content/menus.html');
    }
/*    function menusList(){
        $o=&$this->system->loadModel('content/menus');
        $this->system->output(json_encode($o->menusList()));
    }*/
    function menusDel(){
        $o=&$this->system->loadModel('content/menus');

        if(!$o->menusDel($_POST['id'])) {
            $this->splash('failed','index.php?ctl=content/menus&act=menus',__('对不起,菜单删除失败!'));
        }
        $this->splash('success','index.php?ctl=content/menus&act=menus',__('菜单删除成功!'));

    }

    function menusDetail(){
        $oMenus=&$this->system->loadModel('content/menus');
        $this->pagedata['menus']=$oMenus->menusDetailList($_POST['id']);
        $this->pagedata['id']=$_POST['id'];
        $this->page('content/menusDetail.html');
    }
/*    function menusDetailList(){
        $o=&$this->system->loadModel('content/menus');
        $this->system->output(json_encode($o->menusDetailList($_POST['id'])));
    }*/
    function menusDetailEditPage(){
        $o=&$this->system->loadModel('content/menus');
        $data=$o->menusDetail($_POST['id']);
        $this->pagedata['menu_id']=$_POST['id'];
        $this->pagedata['label']=$data['label'];
        $this->pagedata['type']=$data['type'];
        $this->pagedata['res_id']=$data['res_id'];
        $this->pagedata['setting']=unserialize($data['setting']);
/*
        if($data['type']==0){
            $link=
        }elseif($data['type']==1){
            $link=
        }elseif($data['type']==2){
            $link=
        }elseif($data['type']==3){
            $link=
        }elseif($data['type']==4){
            $link=
        }elseif($data['type']==5){
            $link=
        }*/

        $this->page('content/menusDetailEdit.html');
    }
    function menusDetialEdit(){
        $o=&$this->system->loadModel('content/menus');
        if($_POST['type']==0){
            $_POST['setting']=$_POST['link'];
        }elseif($_POST['type']==1){
            $_POST['setting']=$_POST['browser'];
        }elseif($_POST['type']==2){
            $_POST['res_id']=$_POST['product'];
        }elseif($_POST['type']==3){
            $_POST['setting']=$_POST['article'];
        }elseif($_POST['type']==4){
            $_POST['setting']=$_POST['art_cat'];
        }elseif($_POST['type']==5){
            $_POST['setting']=$_POST['tag'];
        }
        if (!$o->menusDetialEdit($_POST)) {
            $this->splash('failed','index.php?ctl=content/menus&act=menusDetailEditPage&id='.$_POST['menu_id'],__('对不起,操作失败'));
        }
        $this->splash('success','index.php?ctl=content/menus&act=menusDetailEditPage&id='.$_POST['menu_id'],__('操作成功'));

    }
    function menusDetailAddPage(){
        $o=&$this->system->loadModel('content/menus');
        if(empty($_POST['id'])){
            $_POST['id']=$o->menusAdd();
        }
        $this->pagedata['menu_grp_id']=$_POST['id'];
        $this->page('content/menusDetailAdd.html');
    }
    function menusDetailAdd(){
        $o=&$this->system->loadModel('content/menus');
        if($_POST['type']==0){
            $_POST['setting']=$_POST['link'];
        }elseif($_POST['type']==1){
            $_POST['setting']=$_POST['browser'];
        }elseif($_POST['type']==2){
            $_POST['res_id']=$_POST['product'];
        }elseif($_POST['type']==3){
            $_POST['setting']=$_POST['article'];
        }elseif($_POST['type']==4){
            $_POST['setting']=$_POST['art_cat'];
        }elseif($_POST['type']==5){
            $_POST['setting']=$_POST['tag'];
        }
        if (!$o->menusDetailAdd($_POST)) {
            $this->splash('failed','index.php?ctl=content/menus&act=menusDetail&id='.$_POST['menu_grp_id'],__('对不起,操作失败'));
        }
        $this->splash('success','index.php?ctl=content/menus&act=menusDetail&id='.$_POST['menu_grp_id'],__('操作成功'));
    }
    function menusDetailDel(){
        $o=&$this->system->loadModel('content/menus');

        if(!$o->menusDetailDel($_POST['id'])) {
            $this->splash('failed','index.php?ctl=content/menus&act=menusDetail&id='.$_POST['menu_grp_id'],__('对不起,操作失败'));
        }
        $this->splash('success','index.php?ctl=content/menus&act=menusDetail&id='.$_POST['menu_grp_id'],__('操作成功'));
    }
    function toRemove($id){
        $o=&$this->system->loadModel('content/menus');
        if($o->toRemoveDefineMenus($id,$msg)){
            $this->splash('success','index.php?ctl=content/menus&act=defineMenus',__('操作成功'));
        }else{
            $this->splash('failed','index.php?ctl=content/menus&act=defineMenus',__('对不起,操作失败'));
        }
    }

    function doAdd(){
        $o=&$this->system->loadModel('content/menus');
        if($o->addDefinemenus($_POST)){
            $this->splash('success','index.php?ctl=content/menus&act=defineMenus',__('操作成功'));
        }else{
            $this->splash('failed','index.php?ctl=content/menus&act=defineMenus',__('对不起,操作失败'));
        }
    }

}
?>
