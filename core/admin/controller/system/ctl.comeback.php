<?php defined('CORE_DIR') || exit('入口错误'); ?>
<?php
class ctl_comeback extends adminPage{

    var $workground = 'tools';

    function index(){
        if(constant('SAAS_MODE')){
            exit;
        }
        $this->path[] = array('text'=>__('数据恢复'));
        $pkg = &$this->system->loadModel('system/backup');
        $this->pagedata['archive']=$pkg->getList(HOME_DIR.'/backup');
        $this->pagedata['appver']=$this->system->version();
        $this->page('system/comeback/tgzFileList.html');
    }
    function comeback($filename,$mtime,$vols){
        if(constant('SAAS_MODE')){
            exit;
        }
        $this->pagedata['filename']=$filename;
        $this->pagedata['mtime']=$mtime;
        $this->pagedata['vols']=$vols;
        $this->display('system/comeback/comeback.html');
    }
    function recover($filename,$vols,$fileid){
        if(constant('SAAS_MODE')){
            exit;
        }
        $recoverProgress = &$this->system->loadModel('system/backup');
        $recoverProgress->recover($filename,$vols,$fileid);
        if($vols==$fileid){
            echo __('数据库已恢复完毕 <script>$("btnarea").innerHTML = \'<button class="btn"  onclick="sqlcomeback.close()" type="button"><span><span>确定</span></span></button>\';</script>');
        }
        else{
            echo __('正在恢复第').($fileid+1).__('卷 共').$vols.__('卷<script>dorecover("index.php?ctl=system/comeback&act=recover&p[0]=').$filename.'&p[1]='.$vols.'&p[2]='.($fileid+1).'");</script>';
        }
        if(is_file(MEDIA_DIR.'/goods_cat.data'))
            @unlink(MEDIA_DIR.'/goods_cat.data');
        $this->system->cache->clear();
    }

    function removeTgz(){
        if(constant('SAAS_MODE')){
            exit;
        }
        regex_file($_POST['tgz']);
        $backup= &$this->system->loadModel('system/backup');
        if(count($_POST['tgz'])>0){
            $backup->removeTgz($_POST['tgz']);
            $this->splash('success','index.php?ctl=system/comeback&act=index');
        }else{
            $this->splash('failed','index.php?ctl=system/comeback&act=index',__('删除失败：请选择操作的记录'));
        }

    }
}
?>