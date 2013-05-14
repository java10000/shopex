<?php
class api_5_0_member extends shop_api_object {
 
    function get_member($data) {
        $where = $this->_filter($filter,$data);
        $count = $this->db->selectrow("SELECT count(1) AS c FROM sdb_members");
        $result['count'] = $count['c'];
        $member_list = $this->db->select("SELECT * FROM sdb_members $where");

        $matrixMdl = &$this->system->loadModel('system/matrix');
        foreach($member_list as $k => $v){
            $result['data_info'][] = $matrixMdl->format_matrix_member($v['member_id']);
        }

        $this->api_response('true',false,$result);
    }
}
