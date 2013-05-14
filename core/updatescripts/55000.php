<?php

class UpgradeScript extends Upgrade{
     var $left_domain='shopex.cn';
     var $workground='setting';
     var $max_runtime = 5;
     var $safebytes = 10;
     var $set;
     var $noticeMsg = array();
     function upgrade_checkdb(){
         $this->db->exec("UPDATE sdb_member_attr SET attr_name = '移动电话' WHERE attr_name = '手机号码'");
     }
}
?>