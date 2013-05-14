<?php
include_once(CORE_DIR.'/api/shop_api_object.php');
/**
 * API member模块部份
 * @package
 * @version 3.0: 
 * @copyright 2003-2011 ShopEx
 * @author 张君华
 * @license Commercial
 */

class api_3_0_member extends shop_api_object {
    function api_3_0_member() {
        parent::shop_api_object();
    }
    
    function check_login($data){
        $data || ($data=$_POST);
        $sql = "SELECT member_id,password,regtime,mobile,name FROM sdb_members where uname=".$this->db->quote($data['uname']);
        $member=$this->db->selectrow($sql);
        $new_password = 's'.substr(md5($data['password'].$data['uname'].$member['regtime']),0,31); //新加密方式
        //此处验证兼容新老两种加密方式
        if($member && ($member['password']==$data['password']) || ($member['password'] == $new_password)){    
            $info['is_login']='success';
            $info['member_id']=$member['member_id'];
            $this->api_response('true','',$info);            
        }else{       
            $info['is_login']='fail';
            $this->api_response('fail','',$info);
        }
        
    }
    
    function get_member_detail($data){
        $data || ($data=$_POST);
        $sql="SELECT uname,password,name,mobile FROM sdb_members where member_refer = '0' and member_id = ".$data['member_id'];
        $member=$this->db->selectrow($sql);
        if($member){
            $info=$member;
            $info['result']='success';
            $this->api_response('true','',$info);
        }else{
            $info['result']='fail';
            $this->api_response('fail','',$info);
        }
    }
    
    /**
     * * check uname conflict
     * @param array $data
     */
    function check_uname($data,$return=false){
        $data || ( $data = $_POST );
        $uname = trim($data['uname']);
        $len = strlen($uname);
         if($len<3 || $len>20){
             if ( ! $return ) {
                $this->api_response('fail','','用户名长度应在3~20!');
             }
        } elseif(!preg_match('/^([@\.]|[^\x00-\x2f^\x3a-\x40]){2,20}$/i', $uname)) {
            if ( !$return ) {
                $this->api_response('fail','','用户名包含非法字符');
            }
        }else{
            $row = $this->db->selectrow("select uname from sdb_members where uname='{$uname}'");
            if($row['uname']){
                if ( ! $return ) {
                    $this->api_response('fail','','重复的用户名');
                }
            }else{
               if ($this->check_name_inuc($uname)==1) {
                   if ( $return ) {
                       return true;
                   } else {
                       $this->api_response('true','','用户名合法');
                   }
               }
                   
               else
                $this->api_response('fail','','用户名不可以注册');
            }
        }
    }
    
    function check_name_inuc($uname){
        $passport = &$this->system->loadModel('member/passport');
        if (!!$obj=$passport->function_judge('checkuser')){
            return $obj->checkuser($uname);
        } else {
            return true;
        }
    }
    
    function update_member($data){
        $data || ($data=$_POST);
        if(empty($data['uname']) && empty($data['password']) && empty($data['name']) && empty($data['mobile'])){
            $this->api_response('fail','','参数错误');
        }
        if(empty($data['member_id'])){
            $this->api_response('fail','','member_id不能为空');
        }
        $set='SET';
        if($data['uname']){
            $set .= " uname='".$data['uname']."' ,";
        }
        if($data['password']){
            //$data['password'] = 's'.substr(md5($data['password'].$data['uname'].$data['regtime']),0,31); //新加密方法 新店铺用
            $set .= " password='".$data['password']."' ,";
            
        }
        if($data['name']){
            $set .= " name='".$data['name']."' ,";
        }
        if($data['mobile']){
            $set .= " mobile='".$data['mobile']."' ,";
        }
        $set=trim($set,',');
        $sql="UPDATE sdb_members ".$set." WHERE member_id = ".$data['member_id'];
        $this->db->query($sql);
        $info['result']='success';
        $this->api_response('true','',$info);
    }
    
    function add_member($data){
        $data || ($data = $_POST);
        $data['email'] || ($data['email']='noemail@shopex.cn');
        $data['uname'] = trim(strtolower($data['uname']));
        $data['reg_ip'] = remote_addr();
        $data['regtime'] = time();
        //$data['password'] = 's'.substr(md5($data['password'].$data['uname'].$data['regtime']),0,31);  //新加密方法 新店铺用        
        if(!$this->check_uname($data, true)){
            $this->api_response('fail','','用户名不合法');
        }
        
        $row = $this->db->selectrow('select * from sdb_member_lv where default_lv="1"');
        $data['member_lv_id'] = $row['member_lv_id']?$row['member_lv_id']:0;

        $defcur = $this->db->selectrow('select cur_code from sdb_currency where def_cur="true"');
        $data['cur'] = $defcur['cur_code'];
        $rs = $this->db->exec('select * from sdb_members where uname='.$this->db->quote($data['uname']));

        //判断用户是否存在
        if(!$rs || $this->db->getRows($rs)){
            $this->api_response('fail','','存在重复的用户id');
        }
        $data['login_count'] = 1;
        $data['member_refer'] = 'mobile';

        $sql = $this->db->getInsertSQL($rs,$data);
        if($this->db->exec($sql)){
            $userId = $this->db->lastInsertId();
            $status = &$this->system->loadModel('system/status');
            $status->add('MEMBER_REG');
            $memberMdl =& $this->system->loadModel('member/account');
            $memberMdl->init($userId);
            $sql = 'select member_id,member_lv_id,uname,password,unreadmsg,cur,lang,point from sdb_members where member_id='.$userId;
            $row = $this->db->selectrow($sql);
            $row['secstr'] = $memberMdl->cookieValue($userId);
            $this->idColumn='member_id';
            $data['member_id'] = $userId;
            $memberMdl->fireEvent('register',$data,$userId);//会员注册成功事件
            $this->api_response('true','',array('member_id'=>$userId));
        }else{
            $this->api_response('fail','','注册失败');
        }
    }
}