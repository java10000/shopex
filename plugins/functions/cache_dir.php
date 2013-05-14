<?php
/*
 * 使用一个个的小文件作为文件缓存的存储端
 * 使用方法：
 *     添加define('CACHE_METHOD','cachedir');到你的config.php中。
 */

class cache_dir extends cachemgr{

    var $name = '独立文件缓存';
    var $desc = '一个个的小文件';

    function cache_dir(){
        $system = &$GLOBALS['system'];
        $this->db = &$system->database();
        $this->totalBytes = 15<<20;

$sql = <<<EOF
create table if not exists sdb_cachedir
(
   cache_file                     varchar(32)                    not null,
   cache_size                     mediumint unsigned             not null,
   last_update                    int unsigned                   not null,
   primary key (cache_file)
)type = MyISAM
EOF;
$cachedir_sql=$this->db->exec($sql,1,1);

        if(true===$cachedir_sql){
            $row = $this->db->selectrow('select sum(cache_size) as size from sdb_cachedir');
            $this->curBytes = $row['size'];
            if($row['size'] > $this->totalBytes){
                $this->_free(10,$row['size']-$this->totalBytes);
            }
        }
        parent::cachemgr();
    }

    function _free($step,$to_free){
        $free = $i = 0;
        while($free<$to_free && $i++<10){
            $deleted = array();
            foreach($this->db->select('SELECT cache_file,cache_size FROM sdb_cachedir order by last_update limit 0,'.$step) as $cache_item){
                if($size = $this->_remove_file($cache_item)){
                    $free+=$size;
                    $deleted[] = $cache_item['cache_file'];
                }
            }
            $this->db->exec('delete from sdb_cachedir where cache_file in ("'.implode('","',$deleted).'")',1,1);
        }
    }

    function _path($key,$mkdir=false){
        $dir = HOME_DIR.'/cache/'.$key{0}.$key{1};
        if($mkdir){
            if(!mkdir_p($dir)){
                return false;
            }
        }
        return $dir.'/'.substr($key,2);
    }

    function store($key,&$value){
        $path = $this->_path($key,true);
        $data = serialize($value);
        $this->db->exec('replace into sdb_cachedir (cache_file,cache_size,last_update)VALUES("'.$key.'",'.strlen($data).','.time().')',1,1);
        return $path && file_put_contents($path,$data);
    }

    function fetch($key,&$data){
        $file = $this->_path($key);
        if(file_exists($file)){
            if(filemtime($file) < filemtime(HOME_DIR.'/cache/cache.stat')){
                return false;
            }
            $data = unserialize(file_get_contents($file));
            $this->db->exec('update sdb_cachedir set last_update='.time().' where cache_file="'.$key.'"',1,1);
            return $data!==false;
        }else{
            return false;
        }
    }

    function _remove_file($cache_item){
        $f=$this->_path($cache_item['cache_file']);
        if(!file_exists($f) || unlink($f)){
            return $cache_item['cache_size'];
        }else{
            error_log('Can\'t delete '.$f,3,HOME_DIR.'/log/cachedir.log'."\n");
            return false;
        }
    }

    function clear(){
        set_time_limit(2);
        $now = time();
        $rows = $this->db->select('SELECT cache_file,cache_size FROM sdb_cachedir where last_update<'.$now.' limit 0,10');
        while(count($rows)>0){
            $deleted = array();
            foreach($rows as $cache_item){
                if($this->_remove_file($cache_item)){
                    $deleted[] = $cache_item['cache_file'];
                }
            }
            $this->db->exec('delete from sdb_cachedir where cache_file in ("'.implode('","',$deleted).'")',1,1);
            $rows = $this->db->select('SELECT cache_file,cache_size FROM sdb_cachedir where last_update<'.$now.' limit 0,10');
        }
        return true;
    }

    function status(&$curBytes,&$totalBytes){
        $curBytes = $this->curBytes;
        $totalBytes = $this->totalBytes;
        $row = $this->db->selectrow('select count(*) as count from sdb_cachedir');
        return array(
                array('name'=>'总缓存对象数量','value'=>$row['count'])
            );
    }

}
?>
