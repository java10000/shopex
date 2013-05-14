<?php defined('CORE_DIR') || exit('入口错误'); ?>
<?php
class ctl_demo_data extends adminPage{
    var $workground ='site';
    function ctl_demo_data(){
        parent::adminPage();
    }
    
    function install_demo_data(){
         
        
        if($_POST['usrname']){
            $oOpt = &$this->system->loadModel('admin/operator');
            $result = $oOpt->tryLogin($_POST,true);
            if(!$result){
                $this->pagedata['error_msg'] = "账号或者密码错误";
                $this->tooplogin();
            }
        }else{
            $this->tooplogin();
        }

        if(isset($_POST['url'])){
            include(CORE_DIR.'/admin/controller/service/ctl.download.php');
            $download = new ctl_download();
            $pos = strrpos($_POST['url'],"/");
            $_POST = array(
                'download_list'=>array($_POST['url']),
                'succ_url'=>'http://'.HTTP_HOST.dirname(PHP_SELF)
                            .'/index.php?ctl=service/demo_data&act=do_install&p[0]=template',
                'template'=>substr($_POST['url'],0,$pos).'/templates'.substr($_POST['url'],$pos),
                );
            $download->start();
        }
        exit;
    }

    function do_install($action=false){
        $task = HOME_DIR.'/tmp/'.$_GET['download'];
        $temp_mess = file_get_contents($task.'/task.php');
        $down_data = unserialize($temp_mess);
        $url = $down_data['download_list'][0];
        $filename = substr($url,strrpos($url,"/")+1);
        $file_path = $task.'/'.$filename;
        if($action == 'template'){
            $tar=$this->system->loadModel('utility/tar');
            $d_install=$this->system->loadModel('service/data_install');
            if($tar->openTAR($file_path)){
                    foreach($tar->files as $id => $file) {
                        $fpath = $task.'/install.sql';
                        if(substr($file['name'],-4)=='.sql'){
                            $content=$tar->getContents($file);
                            if(!$d_install->do_install($content)){
                                echo $this->_fetch_compile_include('service/download_complete_handle.html',array('info'=>'体验数据安装失败'.$d_install->error));
                                exit();
                            }
                        }
                    }
                    $tar->closeTAR();
            }
            include(CORE_DIR.'/admin/controller/service/ctl.download.php');
            $download = new ctl_download();
             $_POST = array(
                'download_list'=>array($down_data['template']),
                'succ_url'=>'http://'.HTTP_HOST.dirname(PHP_SELF)
                            .'/index.php?ctl=service/demo_data&act=do_install'
                );
            $download->start();
            exit;
        }
        if($url){
            if(file_exists($file_path)){
                $file['tmp_name'] = $file_path;
                $file['name'] = time();
                $file['error'] = '0';
                $file['size'] = filesize($file_path);
                $template = &$this->system->loadModel("system/template");
                $template->upload($file,$msg);
                @unlink(MEDIA_DIR.'/brand_list.data');
                $this->clear_cache();
                $this->system->cache->clear();
            }else{
                $msg = "找不到安装文件。安装失败";
            }
        }
        echo $this->_fetch_compile_include('service/download_complete_handle.html',array('info'=>'体验数据安装成功'));
        exit();
    }


    function tooplogin(){
        $this->pagedata['download_url'] = $_POST['url'];
        $this->singlepage("admin/op_login.html");
        exit;
    }

}    
?>