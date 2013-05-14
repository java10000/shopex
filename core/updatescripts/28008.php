<?php
class UpgradeScript extends Upgrade{
    
    var $noticeMsg = array();
     function upgrade_checkdb(){
        $sql = "SELECT extend FROM sdb_orders";
        if(!$this->db->selectrow($sql)){
            $this->db->exec("ALTER TABLE `sdb_orders` ADD COLUMN `extend` varchar(255) default NULL");
        }
        
        return 'finish';
     }

}
?>