<?php defined('CORE_DIR') || exit('入口错误'); ?>
<?php
include_once('objectPage.php');
class ctl_tmpimage extends objectPage{

    var $workground ='site';
    var $object = 'resources/tmpimage';
    var $noRecycle = true;
    var $deleteAble = false;
    var $allowExport = false;
    var $filterUnable = true;


    function ctl_tmpimage(){
        parent::objectPage();
        regex_file($_GET);
    }


    function _detail(){
        return array('show_detail'=>array('label'=>__('模板文件信息'),'tpl'=>'system/template/tplresource.html'));
    }

    function _index(){
        if(($_GET['_systmpl'] || $_COOKIE['edit_systmpl']) && !$_GET['istheme']){
            $this->istheme = false;
            setcookie('edit_systmpl','1');
        }else{
            $this->istheme = true;
            setcookie('edit_systmpl','');
        }
    }

    function index(){
        $this->_index();
        $this->filter['tmpid'] = $_GET['theme'];
        if($_GET['type']){
            $this->filter['type'] = $_GET['type'];
        }
        $this->pagedata['_detail_func'] = $this->_detail($object_id);
        $this->pagedata['path'] = $_GET['theme'];
        if($this->pagedata['path'] == '../core/shop/view'){
            $this->pagedata['lastpath']='../core/shop/view';
        }
        else{
            $length=strlen(end(explode('/',$this->pagedata['path'])))+1;
            $this->pagedata['lastpath']=substr($this->pagedata['path'],0,-$length);
        }
        $oImg = &$this->system->loadModel("resources/tmpimage");
        $data=array(
             'tmpid' => $_GET['theme'],
             'show_bak' => 1 ,
             'type' => 'all'
             );
        $file = $oImg->_fileList($data,$this->istheme);
        foreach($file as $k=>$v){
            $name=explode('.',$k);
            if(substr($name[1], 0, 4)=='bak_')
            unset($file[$k]);
            if($v['filetype']=='Folder'){
            unset($file[$k]);
            array_push($file,$v);
            }
        }

        $this->pagedata['file'] = array_reverse($file);

        $this->pagedata['_PAGE_']='system/template/tplresource.html';
        $this->display('system/template/map.html');

    }

    function show_detail($sName) {
        $this->_index();
        $oImg = &$this->system->loadModel("resources/tmpimage");
        $aId = $oImg->getId($sName);
        $file = $oImg->getFile($aId['name'],$aId['tmpid'],$this->istheme);
        $this->pagedata['file'] = $file;
        $this->pagedata['tmpid'] = $aId['tmpid'];
        $this->pagedata['path'] = $aId['tmpid'].$_GET['theme'];

        if($this->istheme){
            $this->pagedata['basic_path'] = '../themes/'.$aId['tmpid'].'/';
        }else{
            $this->pagedata['basic_path'] = CORE_DIR.'/shop/view/'.$aId['tmpid'].'/';
        }
        if($file['filetype'] == "css"||$file['filetype'] == "html"||$file['filetype'] == "xml"||$file['filetype'] == "js"){
            $dir = $this->pagedata['basic_path'];
            $this->pagedata['file']['content'] = file_get_contents($dir.$file['name']);
        }

        $this->output();

    }

    function delete_file($path){
        $this->_index();
        if($this->istheme){
            $path= THEME_DIR.'/'.$path;
        }else{
            $path= CORE_DIR.'/shop/view/'.$path;
        }
        if(isset($path))  unlink($path);
    }

    function delete($fileName){
        $this->_index();
        $this->begin('index.php?ctl=system/tmpimage&act=detail&p[0]='.$_POST['id']);
        $this->system->setConf('system.theme_last_modified',time());
        $oImg = &$this->system->loadModel("resources/tmpimage");
        $this->end($oImg->toRemove($fileName, $_POST['tmpid'], $this->istheme), __('文件删除成功'));
        return true;
    }

    function saveImage(){
        $this->_index();
        $this->begin('index.php?ctl=system/tmpimage&act=detail&p[0]='.$_POST['id']);
        $this->system->setConf('system.theme_last_modified',time());
        $oImg = &$this->system->loadModel("resources/tmpimage");

        if(($result=$oImg->saveFile(array_merge($_POST, $_FILES),$this->istheme))===true){
            $this->end(true,__('操作成功'));
        }else{
            $this->end(false,__($result));
        }

    }

    function saveSource(){
        $this->_index();
        $this->system->setConf('system.theme_last_modified',time());
        $this->begin('index.php?ctl=system/tmpimage&act=detail&p[0]='.$_POST['id']);
        
        $extend = pathinfo($_POST['name'],PATHINFO_EXTENSION);
        if( !check_file_name($extend) ){
            $this->end(false, __('保存文件名不合法'));
        }
        
        $oImg = &$this->system->loadModel('resources/tmpimage');
        $this->end($oImg->saveSource($_POST,$this->istheme), __('样式文件保存成功'));
    }

    function recoverSource($sName, $dest, $tmpid){
        $this->_index();
        $this->system->setConf('system.theme_last_modified',time());
        $this->begin('index.php?ctl=system/tmpimage&act=detail&p[0]='.$tmpid.'-'.$dest);
        $oImg = &$this->system->loadModel('resources/tmpimage');
        $this->end($oImg->recoverSource($sName, $dest, $tmpid, $this->istheme), __('恢复成功'));
    }
}
?>