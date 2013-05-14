<?php
/*
 * 使用Apc作为系统缓存。
 * 使用方法：
 *     确保你的服务器支持apc时. 添加define('CACHE_METHOD','cacheApc');到你的config.php中。
 */

class cache_apc extends cachemgr{

    var $name = 'Alternative PHP Cache (APC)';
    var $desc = 'The Alternative PHP Cache (APC) is a free and open opcode cache for PHP. It was conceived of to provide a free, open, and robust framework for caching and optimizing PHP intermediate code.';

    function store($key,&$value){
        return apc_store($key,$value,0);
    }

    function fetch($key,&$data){
        $data = apc_fetch($key);
        return $data!==false;
    }

    function clear(){
        return apc_clear_cache('user');
    }

    function status(&$curBytes,&$totalBytes){
        $minfo = apc_sma_info();
        $cinfo = apc_cache_info('user');
        foreach($minfo['block_lists'] as $c){
            $blocks[] = count($c);
        }

        $curBytes = $minfo['seg_size']-$minfo['avail_mem'];
        $totalBytes = $minfo['seg_size'];

        $return[] = array('name'=>'子系统运行时间','value'=>timeLength(time()-$cinfo['start_time']));
        $return[] = array('name'=>'可用内存','value'=>formatBytes($minfo['avail_mem']).' / '.formatBytes($minfo['seg_size']));
        $return[] = array('name'=>'内存使用方式','value'=>$cinfo['memory_type']);
        $return[] = array('name'=>'内存数据段','value'=>$minfo['num_seg'].'块 ('.implode(',',$blocks).')');
        $return[] = array('name'=>'缓存命中','value'=>$cinfo['num_hits'].'次');
        $return[] = array('name'=>'缓存未命中','value'=>$cinfo['num_misses'].'次');
        $return[] = array('name'=>'已缓存数据条数','value'=>$cinfo['num_entries'].'条');
        $return[] = array('name'=>'数据锁定方式','value'=>$cinfo['locking_type']);
        return $return;
    }

}
?>
