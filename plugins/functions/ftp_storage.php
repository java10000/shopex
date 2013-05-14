<?php
/**
 * Storager:图片文件的存储方式
 * 本插件可用来将图片上传到指定的ftp服务器上
 *
 * 系统配置项：
 * system.storager.ftp.url  ->  访问地址前缀
 * system.storager.ftp.server  ->  访问地址前缀
 * system.storager.ftp.uname -> ftp用户名
 * system.storager.ftp.password  -> ftp密码
 * system.storager.ftp.dir  -> 上传路径
 * 
 */
 
define(__FTP_SERVER__,"192.168.0.114");
define(__FTP_UNAME__,"hjx");
define(__FTP_PASSWD__,"hjxisking");
define(__FTP_DIR__,"/shopex/");
 
class ftp_storage{

    function save($path='',$url,$id){

        $system = &$GLOBALS['system'];
        
        $fp = fopen(BASE_DIR.$path, 'rb');

        $id = $path;
        $url = "http://" . __FTP_SERVER__ . "/" . $path;
        
        $ftp_path = __FTP_DIR__ . "/" . $path;
        $file = $ftp_path;

        // connect to the server
        $conn_id = ftp_connect(__FTP_SERVER__);
        $login_result = ftp_login($conn_id, __FTP_UNAME__, __FTP_PASSWD__);


        $d=split("/", $ftp_path);
        $ftp_path="";

        if(substr(__FTP_DIR__,0,1) == "/"){
            $i = 1;
        }
        else{
            $i = 0;
        }
        
        for ($i;$i<count($d)-1;$i++)
        {
            $ftp_path.="/".$d[$i];
            if(!@ftp_chdir($conn_id,$ftp_path)){
                @ftp_chdir($conn_id,"/");
                if(!@ftp_mkdir($conn_id,$ftp_path)){
                    return false;
                }
            } 
        }

        // try to upload the file
        ftp_fput($conn_id, $file, $fp, FTP_BINARY);
        
        $o = $system->loadModel('goods/gimage');
        foreach($o->defaultImages as $tag){
            $ext = $o->getImageExt(BASE_DIR.$path);
            $otherimage = substr(BASE_DIR.$path,0,strlen(BASE_DIR.$path)-strlen($ext)) . "_" . $tag . $ext;
            if(file_exists($otherimage)){
                $fp = fopen($otherimage,'rb');
                ftp_fput($conn_id, $ftp_path."/".basename($otherimage), $fp, FTP_BINARY);
                fclose($fp);
                @unlink($otherimage);
            }
        }
        @unlink(BASE_DIR.$path);
        
        // close the connection and the file handler
        ftp_close($conn_id);
        fclose($fp);

        return true;
    }

    function remove($ident){
        $conn_id = ftp_connect(__FTP_SERVER__);
        $login_result = ftp_login($conn_id, __FTP_UNAME__,__FTP_PASSWD__);
        $ret = ftp_delete($conn_id,__FTP_DIR__ . "/" .$ident);
        ftp_close($conn_id);
        return $ret;
    }

}
?>
