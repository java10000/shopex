<?php
class entity_member extends entity{

    function &export_sdf_array($id){
        $oMem = &$this->system->loadModel("member/member");
        $oAdv = &$this->system->loadModel('member/advance');
        $oPointHistory = &$this->system->loadModel('trading/pointHistory');
        
        $tmpMem = $oMem->getBasicInfoById($id);
        $receiver['receiver'] = $oMem->getMemberAddr($id);
        $advList['advdata'] = $oAdv->getFrontAdvList($id,'','');
        $history['data'] = $oPointHistory->getPointHistoryList($id);
        
        $tmpMem = array_merge($tmpMem,$receiver);
        $tmpMem = array_merge($tmpMem,$history);
        $tmpMem = array_merge($tmpMem,$advList);
        $j_member_arr = $this->_getMember($tmpMem,$this->_schema_map());
        
        return $j_member_arr;
    }

    function import_sdf_array(&$sdf_array,$type){
            $array = array();
            $tamp = array();
            $receiver['receiver'] = array();
            foreach ($sdf_array[$type] as $k=>$v){
                switch($k){
                    //case 'member_id':
                        //$member_id[$k] = $v;
                        //array = array_merge($array,$member_id);
                    //break;
                    case 'member_group_id':
                        $member_lv_id['member_lv_id'] = $v;
                        $array = array_merge($array,$member_lv_id);
                    break;
                    
                    case 'lang':
                        $lang[$k] = $v;
                        $array = array_merge($array,$lang);
                    break;
                    
                    case 'currency':
                        $cur['cur'] = $v;
                        $array = array_merge($array,$cur);
                    break;
                    
                    case 'member_status':
                        $lv_name['lv_name'] = $v;
                        $array = array_merge($array,$lv_name);
                    break;
                    
                    case 'account':
                        if(count($v)>0){
                            foreach($v as $acc_k=>$acc_v){
                                $tmp_a[$acc_k] = $acc_v;
                            }
                        $array = array_merge($array,$tmp_a);
                        }
                    break;
                    case 'meta':
                    if(count($v)>0){
                        foreach($v as $meta_k=>$meta_v){
                            $tmp_m[$meta_v['key']] = $meta_v['value'];
                        }
                        $array = array_merge($array,$tmp_m);
                    }
                    break;
                    case 'contact':
                        foreach($v as $contact_v){
                            foreach($contact_v as $ck=>$cv){
                                if($ck=='default'||$ck=='zipcode'){
                                    $tmp_r['def_addr'] = $contact_v['default'];
                                    $tmp_r['zip'] = $contact_v['zipcode'];
                                }elseif($ck=='phone'){
                                    foreach($cv as $ph_k=>$ph_v){
                                        $tmp_r[$ph_v['type']]= $ph_v['value'];
                                    }
                                }elseif($ck=='firstname'||$ck=='lastname'){
                                    $firstname['firstname'] = $cv['firstname'];
                                    $lastname['lastname'] = $cv['lastname'];
                                    $array = array_merge($array,$lastname);
                                    $array = array_merge($array,$firstname);
                                    
                                }elseif($ck=='area'){
                                    if(count($cv)>0){
                                        $tmp_r[$ck] = $cv['id']; //$cv['value'].':'.
                                    }
                                }else{
                                    $tmp_r[$ck] = $cv;
                                }
                            }
                            array_push($receiver['receiver'],$tmp_r);
                        }
                        $array = array_merge($array,$receiver);    
                    break;
                    
                    case 'profile':
                        foreach($v as $profile_k=>$profile_v){
                            if($profile_k=='gender'){
                                $tmp_pr['sex'] = $profile_v;
                            }elseif($profile_k=='birthday'){
                                $bir = explode("-",$profile_v);
                                $tmp_pr['b_year'] = $bir[0];
                                $tmp_pr['b_month'] = $bir[1];
                                $tmp_pr['b_day'] = $bir[2];
                            }else{
                                $tmp_pr[$profile_k]=$profile_v;
                            }
                        }
                        $array = array_merge($array,$tmp_pr);
                    break;
                    
                    case 'advance':
                        $advdata['advdata'] = array();
                        foreach($v as $ad_k=>$ad_v){
                            if($ad_k=='total'||$ad_k=='freeze'){
                                $tmp_ad['advance_freeze'] = $v['freeze'];
                                $tmp_ad['advance'] = $v['total'];
                            }elseif($ad_k == 'event'){
                                foreach($ad_v as $ak=>$av){
                                    $tmp_ada['mtime']= $av['time'];
                                    $tmp_ada['memo']= $av['value'];
                                    array_push($advdata['advdata'],$tmp_ada);
                                }
                            }
                        }
                        $array = array_merge($array,$tmp_ad);
                        $array = array_merge($array,$advdata);
                        
                    break;
                    
                    case 'score':
                        $data['data'] = array();
                        foreach($v as $s_k=>$s_v){
                            if($s_k=='total'||$s_k=='freeze'){
                                $tmp_s['point_freeze'] = $v['freeze'];
                                $tmp_s['point'] = $v['total'];
                            }elseif($s_k == 'event'){
                                foreach($s_v as $ak=>$av){
                                    $tmp_da['time']= $av['time'];
                                    $tmp_da['describe']= $av['value'];
                                    array_push($data['data'],$tmp_da);
                                }
                            }
                        }
                        $array = array_merge($array,$tmp_s);
                        $array = array_merge($array,$data);
                    break;
                    
                    case 'memo':
                        $remark['remark'] = $v;
                        $array = array_merge($array,$remark);
                    break;
                }                
            }
            $basic_info = array();
            $receiver = $array['receiver'];
            $advance = $array['advdata'];
            $point_history = $array['data'];
            unset($array['receiver']);
            unset($array['advdata']);
            unset($array['data']);
            unset($array['lv_name']);
            
            $aRs = $this->db->query("SELECT * FROM sdb_members WHERE 0");
            $aRs_add = $this->db->query("SELECT * FROM sdb_member_addrs WHERE 0");
            $aRs_adv = $this->db->query("SELECT * FROM sdb_advance_logs WHERE 0");
            $aRs_phistory = $this->db->query("SELECT * FROM sdb_point_history WHERE 0");
            $sSql = $this->db->getInsertSql($aRs,$array);
            $this->db->exec($sSql);
            $new_member_id_row = $this->db->selectrow('SELECT MAX(member_id) FROM sdb_members');
            $new_member_id = $new_member_id_row['MAX(member_id)'];
            $nm['member_id'] = $new_member_id;
            foreach($receiver as $r){
                $r = array_merge($r,$nm);
                $sSql_add = $this->db->getInsertSql($aRs_add,$r);
                $this->db->exec($sSql_add);
            }
            
            
            foreach($advdata['advdata'] as $adv_log){
                $adv_log = array_merge($adv_log,$nm);
                $sSql_adv = $this->db->getInsertSql($aRs_adv,$adv_log);
                $this->db->exec($sSql_adv);
            }
            
            foreach($point_history as $history_log){
                $history_log = array_merge($history_log, $nm);
                $sSql_phistory = $this->db->getInsertSql($aRs_phistory,$history_log);
                $this->db->exec($sSql_phistory);
            }
            
            
        return $nm;
        
    }
    
    function _schema_map(){
        
        $member_schema = array(
            'member_id'=>array('member_id'),
            'member_group_id'=>array('member_lv_id'),
            'lang'=>array('lang'),
            'account'=>array('uname','password','pw_question','pw_answer'),
            'contact'=>array('name','area','addr','zip','tel','mobile','def_addr'),
            'profile'=>array('b_year','b_month','b_day','sex','wedlock'),
            'currency'=>array('cur'),
            'member_status'=>array('lv_name'),
            'advance'=>array('advance','advance_freeze'),
            'score'=>array('point_freeze','point'),
            'meta'=>array('area','mobile','tel','email','zip','addr','province','city','order_num',
            'refer_id','refer_url','addon','education','vocation','interest','point_history','reg_ip',
            'regtime','state','pay_time','biz_money','custom','unreadmsg','login_count','experience','foreign_id'),
            'memo'=>array('remark'),
        );
        return $member_schema;
    }

    function _getMember($arr,$map_arr){
        $array['member'] = array();
        $member_id = $this->_getMemberId($arr,$map_arr);
        $member_group_id = $this->_getMemberGroupId($arr,$map_arr);
        $lang = $this->_getLang($arr,$map_arr);
        $currency = $this->_getCurrency($arr,$map_arr);
        $member_status = $this->_getMemberStatus($arr,$map_arr);
        $account = $this->_getAccount($arr,$map_arr);
        $contact = $this->_getContact($arr,$map_arr);
        $profile = $this->_getProfile($arr,$map_arr);
        $meta = $this->_getMemberMeta($arr,$map_arr);
        $advance = $this->_getAdvance($arr, $map_arr);
        $score = $this->_getScore($arr, $map_arr);
        $memo = $this->_getMemo($arr, $map_arr);
        $url = $this->_getUrl($arr);

        $array['member'] = array_merge($array['member'],$member_id);
        $array['member'] = array_merge($array['member'],$member_group_id);
        $array['member'] = array_merge($array['member'],$lang);
        $array['member'] = array_merge($array['member'],$currency);
        $array['member'] = array_merge($array['member'],$member_status);
        $array['member'] = array_merge($array['member'],$account);
        $array['member'] = array_merge($array['member'],$contact);
        $array['member'] = array_merge($array['member'],$profile);
        $array['member'] = array_merge($array['member'],$meta);
        $array['member'] = array_merge($array['member'],$advance);
        $array['member'] = array_merge($array['member'],$score);
        $array['member'] = array_merge($array['member'],$memo);
        $array['member'] = array_merge($array['member'],$url);


        return $array;

    }
    
    function _getMemberId($arr,$map_arr){
        foreach($arr as $k=>$v){
            if(in_array($k,$map_arr['member_id'])){
                $member_id[$k] = $v;
            }
        }
        return $member_id;
    }
    
    function _getMemberGroupId($arr,$map_arr){
        foreach($arr as $k=>$v){
            if(in_array($k,$map_arr['member_group_id'])){
                $member_group_id['member_group_id'] = $v;
            }
        }
        return $member_group_id;
    }

    function _getMemberMeta($arr,$map_arr){
        $meta_arr['meta'] = array();
        if(is_array($arr)){
            foreach($arr as $k=>$v){
                if(in_array($k,$map_arr['meta'])){
                    $tmp = array('key'=>$k,'value'=>$v);
                    array_push($meta_arr['meta'],$tmp);
                }
            }
        }
        return $meta_arr;
    }

    function _getLang($arr,$map_arr){
        foreach($arr as $k=>$v){
            if(in_array($k,$map_arr['lang'])){
                $lang[$k] = $v;
            }
        }
        return $lang;
    }

    function _getCurrency($arr,$map_arr){
        foreach($arr as $k=>$v){
            if(in_array($k,$map_arr['lang'])){
                $k = 'currency';
                $currency[$k] = $v;
            }
        }
        return $currency;
    }

    function _getAccount($arr,$map_arr){
        $account = array();
        foreach($arr as $k=>$v){
            if(in_array($k,$map_arr['account'])){
                $tmp[$k] = $v;
            }
        }
        $new_account['account'] = array_merge($account,$tmp);
        return $new_account;
    }

    function _getContact($arr, $map_arr){
        $tmp['phone'] = array();
        $objContact['contact'] = array();
        $contact = array();
        foreach($arr as $k=>$v){
            if($k=='lastname'||$k=='firstname'){
                $tmp[$k]=$v;
            }
            if($k=='receiver'){
                foreach($v as $kk=>$vv){
                    foreach($vv as $key=>$value){
                        if(in_array($key, $map_arr['contact'])){
                            if($key=='zip'){
                                $tmp['zipcode']=$value;
                            }elseif($key=='area'){
                                $area_arr = explode(':',$value);
                                $area_id =  $area_arr[2];
                                array_pop($area_arr);
                                $tmp[$key] = array('id'=>$area_id);//'value'=>implode(":",$area_arr)
                            }elseif($key=='tel'||$key=='mobile'){
                                $ntmp = array('type'=>$key,'value'=>$value);
                                array_push($tmp['phone'],$ntmp);
                            }elseif($key=='def_addr'){
                                $tmp['default'] = $value;
                            }else{
                                $tmp[$key]=$value;
                            }
                        }
                    }
                    array_push($contact,$tmp);
                    $tmp['phone'] = array();
                }
            }

        }
        $objContact['contact'] = array_merge($objContact['contact'],$contact);
        return $objContact;
    }

    function _getProfile($arr,$map_arr){
        $profile['profile'] = array();
        foreach($arr as $k=>$v){
            if(in_array($k,$map_arr['profile'])){
                $birthday = $arr['b_year'].'-'.$arr['b_month'].'-'.$arr['b_day'];
                $tmp['birthday'] = $birthday;

                if($k=='sex'){
                    $tmp['gender']=$v;
                }
                if($k=='wedlock'){
                    $tmp[$k]=$v;
                }
            }
        }
        $profile['profile'] = array_merge($profile['profile'],$tmp);
        return $profile;
    }

    function _getMemberStatus($arr,$map_arr){
        foreach($arr as $k=>$v){
            if(in_array($k,$map_arr['member_status'])){
                $member_status['member_status'] = $v;
            }
        }
        return $member_status;
    }
    function _getUrl($arr){
        $url['url'] = 'http://127.0.0.1/485/src/shopadmin/index.php?ctl=member/member&act=detail&p[0]='.$arr['member_id'];
        return $url;
    }

    function _getScore($arr, $map_arr){
        $score['score'] = array();
        $tmp['event'] = array();
        foreach($arr as $k=>$v){
            if(in_array($k,$map_arr['score'])){
                $tmp['total'] = $arr['point'];
                $tmp['freeze'] = $arr['point_freeze'];
            }
            if($k=='data'){
                foreach($v as $kk=>$vv){
                    $ntmp = array('time'=>$vv['time'],'value'=>$vv['describe']);
                    array_push($tmp['event'],$ntmp);
                }
            }
        }
        $score['score'] = array_merge($score['score'],$tmp);
        return $score;
    }

    function _getAdvance($arr, $map_arr){
        $advance['advance'] = array();
        $tmp['event'] = array();
        foreach($arr as $k=>$v){
            if(in_array($k,$map_arr['advance'])){
                if($k=='advance_freeze'){
                    $k='freeze';
                    $tmp[$k]=$v;
                }elseif($k=='advance'){
                    $k='total';
                    $tmp[$k]=$v;
                }
            }
            if($k=='advdata'){
                foreach($v as $kk=>$vv){
                    $ntmp = array('time'=>$vv['mtime'],'value'=>$vv['memo']);
                    array_push($tmp['event'],$ntmp);
                }
            }
        }
        $advance['advance'] = array_merge($advance['advance'],$tmp);
        return $advance;
    }

    function _getMemo($arr, $map_arr){
        foreach($arr as $k=>$v){
            if(in_array($k, $map_arr['memo'])){
                $memo['memo']=$v;
            }
        }
        return $memo;
    }
}

?>