<?php defined('CORE_DIR') || exit('入口错误'); ?>
<?php
class ctl_backup extends adminPage{

    var $workground ='tools';

    function index(){
        $this->path[] = array('text'=>__('数据备份'));
        if($time = $this->system->getConf("system.last_backup")){
            $this->pagedata['time'] = date('Y-m-d H:i:s',$time);
        }
        $this->page('system/backup/backup.html');
    }

    function backup(){
        if(constant('SAAS_MODE')){
            exit;
        }
        header("Content-type:text/html;charset=utf-8");
        $params['sizelimit'] = 1024;
        $params['filename'] = ($_GET["filename"]=="")?date("YmdHis", time()):$_GET["filename"];
        $params['fileid'] = ($_GET["fileid"]=="")?"0":intval($_GET["fileid"]);
        $params['tableid'] = ($_GET["tableid"]=="")?"0":intval($_GET["tableid"]);
        $params['startid'] = ($_GET["startid"]=="")?"-1":intval($_GET["startid"]);
        if ($params['sizelimit']!="")
        {
            $oBackup=&$this->system->loadModel('system/backup');
            if(!$oBackup->startBackup($params,$nexturl)){
                echo __('正在备份第').($params['fileid']+2).__('卷，请勿进行其他页面操作。').'<script>runbackup("'.$nexturl.'")</script>';
            }
            else{
                $this->system->setConf('system.last_backup',time(),true);
                echo "<a href='index.php?ctl=system/sfile&act=getDB&p[0]=multibak_{$params['filename']}.tgz' target='_blank'>备份完毕，请点击本处下载</a>";
            }
        }
    }

}
?>