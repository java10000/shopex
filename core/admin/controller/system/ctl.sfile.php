<?php defined('CORE_DIR') || exit('入口错误'); ?>
<?php
/**
 * ctl_sfile{
 *
 * @package
 * @version $Id: ctl.sfile.php 1867 2008-04-23 04:00:24Z flaboy $
 * @copyright 2003-2007 ShopEx
 * @author Wanglei <flaboy@zovatech.com>
 * @license Commercial
 */
class ctl_sfile extends adminPage{

    function ctl_sfile(){
        parent::adminPage();
        regex_file($_GET);
    }

    function getDB($ident){
            $sfile = &$this->system->loadModel('system/sfile');
            $sfile->outputDB($ident);
    }

    function upload(){
        $sfile = &$this->system->loadModel('system/sfile');
        $finfo = $sfile->save($_FILES['Filedata'],array('usedby'=>$_POST['usedby']));
        if($_POST['handle'] && $p=strpos($_POST['handle'],':')){

            $cls = substr($_POST['handle'],0,$p);
            $act = substr($_POST['handle'],$p+1);

            if($cls  = &$this->system->loadModel($cls)){
                $finfo = $cls->$act(
                    ($p=strpos($_POST['usedby'],':'))?substr($_POST['usedby'],$p+1):$_POST['usedby'],
                     $finfo);
            }
        }
        echo json_encode($finfo);
    }

    function goodsicon($schema_id){
        header('Content-type: image/png');
        $this->system->sfile(PLUGIN_DIR.'/schema/'.$schema_id.'/icon-48x48.png');
    }
}
?>
