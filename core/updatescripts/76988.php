<?php

class UpgradeScript extends Upgrade{
    var $left_domain='shopex.cn';
    var $workground='setting';
    var $max_runtime = 5;
    var $safebytes = 10;
    var $set;
    var $noticeMsg = array();
    
    function upgrade_checkdb(){
        $sql = file_get_contents(dirname(__FILE__).'/76988.sql');
        $this->db->exec("DELETE FROM `sdb_regions` WHERE `region_id` >= 3267");
        
        $this->db->exec($sql);
        
        return 'finish';
    }
}
