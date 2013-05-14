<?php

class cache_nocache extends cachemgr{
    function set($key,$value){return true;}
    function get($key,$value){return false;}
    function setModified(){;}
    function status(){;}
    function clear(){;}
    function exec(){;}
    function fetch(){return false;}
    function store(){return false;}
}
