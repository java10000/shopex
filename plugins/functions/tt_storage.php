<?php
/**
 * Storager:图片文件的存储方式
 * tt_storager将图片存在tt服务器里面
 * 详细介绍： http://live.shopex.cn/archives/192
 */
class tt_storage{

    function tt_storage(){
        $system=&$GLOBALS['system'];
        if(constant('IMG_MEMCACHE') && constant('IMG_MEMCACHE_PORT')){
            $this->memcache=new Memcache;
            $this->memcache->addServer(IMG_MEMCACHE,IMG_MEMCACHE_PORT);
            $this->memcache->pconnect();
        }
    }

    function save($file,&$url,$type,$addons){
        $id = $this->_get_ident($file,$type,$addons,$url,$path);
        if($path && $this->memcache->set($path,file_get_contents($file))){
            return $id;
        }else{
            return false;
        }
    }

    function replace($file,$id){
        if($this->memcache->set($id,file_get_contents($file))){
            return $id;
        }else{
            return false;
        }
    }

    function save_upload($file,&$url,$type,$addons){
        $id = $this->_get_ident($file,$type,$addons,$url,$path);
        if($path && $this->memcache->set($path,file_get_contents($file))){
            return $id;
        }else{
            return false;
        }
    }

    function _get_ident($file,$type,$addons,&$url,&$path){

        $addons = implode('-',$addons);
        $dir = ($type?($type.'/'):'').date('Ymd').'/';

        $uri = $dir.substr(md5(($addons?$addons:$file).microtime()),0,16).ext_name(basename($addons?$addons:$file));
        $path = $this->_ident($uri);
        $url = 'http://'.S_NAME.$path;
        return $uri;
    }

    function remove($id){
        if($id){
            return $this->memcache->delete($this->_ident($id),10);
        }else{
            return true;
        }
    }

    function _ident($id){
        return '/'.str_pad(HOST_ID, 10, "0", STR_PAD_LEFT).'/'.$id;
    }

    function getFile($id){
        $tmpfile = tempnam();
        if($id && file_put_contents($tmpfile,$this->memcache->get($id))){
            return $tmpfile;
         }else{
            return true;
        }
    }

}