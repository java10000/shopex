<?php
class UpgradeScript extends Upgrade{
    
    var $noticeMsg = array();
    function upgrade_checkdb(){
        $this->db->exec("UPDATE `sdb_widgets_set` SET `base_file` = substring(`base_file`,6) WHERE `base_file` LIKE '%page:%';");
        $this->db->exec("UPDATE `sdb_comments` SET `p_index` = '0' WHERE `p_index` is null");
        return 'finish';
    }
}
?>