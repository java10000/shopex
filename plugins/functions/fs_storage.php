<?php
/**
 * Storager:图片文件的存储方式
 * fs_storager是默认的存放方法,即将图片文件存放在images文件夹下
 */
class fs_storage{

    function save($file,&$url,$type,$addons){
        $id = $this->_get_ident($file,$type,$addons,$url,$path);
        if($path && copy($file,$path)){
            @unlink($file);
            @chmod($path,0644);
            return $id;
        }else{
            return false;
        }
    }

    function replace($file,$id){

        $path = MEDIA_DIR.$id;
        $dir = dirname($path);

        if(file_exists($path)){
            if(!unlink($path)){
                return false;
            }
        }elseif(!is_dir($dir)){
            if(!mkdir_p($dir)){
                return false;
            }
        }

        if($path && rename($file,$path)){
            return $id;
        }else{
            return false;
        }
    }

    function save_upload($file,&$url,$type,$addons){
        $id = $this->_get_ident($file,$type,$addons,$url,$path);

        if($path && move_uploaded_file($file,$path)){
            @chmod($path,0644);
            return $id;
        }
        
        return false;
    }

    function _get_ident($file,$type,$addons,&$url,&$path){

        $addons = implode('-',$addons);
        $dir = '/'.$type.'/'.date('Ymd').'/';

        $uri = $dir.substr(md5(($addons?$addons:$file).microtime()),0,16).ext_name(basename($addons?$addons:$file));
        $path = MEDIA_DIR.$uri;
        $url = 'images'.$uri;

        if(file_exists($path) && !unlink($path)){
            return false;
        }

        $dir = dirname($path);
        if(!is_dir($dir)){
            if(!mkdir_p($dir)){
                return false;
            }
        }
        return $uri;
    }

    function remove($id){
        if($id && file_exists(MEDIA_DIR.'/'.$id)){
            return @unlink(MEDIA_DIR.'/'.$id);
         }else{
            return true;
        }
    }

    function getFile($id){
        if($id && file_exists(MEDIA_DIR.'/'.$id)){
            return MEDIA_DIR.'/'.$id;
         }else{
            return true;
        }
    }

}