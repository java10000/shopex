<?php

class UpgradeScript extends Upgrade{

    var $noticeMsg = array(
        'V4.84版本推出了全新的配送方式体系，升级后<span style="color:red">原有配送方式将无法使用</span>，您需要重新添加设置，否则顾客<span style="color:red">无法正常在前台下订单</span>，请参照您商店的原有配送方式进行设置，<a href="../home/logs/oldDeliveryData.csv" target="_blank">点击查看原配送方式</a>',
        'V4.84版本支持在前台商品列表页通过规格对商品进行筛选，升级后您需要在商品类型中添加（绑定）相关规格，<a  href="http://click.shopex.cn/free_click.php?id=81&func=sl" target="_blank">详请点击查看</a>'
        );

    function upgrade_first(){
        return (
            $this->system->setConf('system.guide','true') 
//&&      $this->db->exec('TRUNCATE TABLE sdb_goods_spec_index ')
//            && $this->db->exec('TRUNCATE TABLE sdb_gimages')
//            $this->db->exec('DELETE FROM sdb_specification') && 
//            $this->db->exec('DELETE FROM sdb_spec_values')
        )?'finish':'error';
    }

    function upgrade_type(){
        $this->title = '商品类型';
        $sql="select type_id, props from sdb_goods_type";
        $row=$this->db->select($sql);
        if ($row){
            foreach($row as $key => $val){
                $data=unserialize($val['props']);
                if( $data ){
                    foreach( $data as $dk => $dv ){
                        $data[$dk]['show']=1;
                    }
                    $props=serialize($data);
                    $this->db->exec('update sdb_goods_type set props=\''.$props.'\' where type_id='.intval($val['type_id']));
                }
            }
        }
        /*****************************/
        /**********升级后台配置*******/
        $sql="select s_data from sdb_settings where s_name='goodsprop'";
        $row=$this->db->selectrow($sql);
        if ($row){
            $data=unserialize($row['s_data']);
            $data['display.position'] = 1;
            $aData['s_data']=serialize($data);
            $rs=$this->db->exec('select * from sdb_settings where s_name=\'goodsprop\'');
            $sql=$this->db->getUpdateSQL($rs,$aData);
            $this->db->exec($sql);
        }
        else{
            $data=array(
                'display.switch'=>1,
                'display.position'=>1
            );
            $aData=array(
                's_name'=>'goodsprop',
                's_data'=>serialize($data),
                's_time'=>time(),
                'disabled'=>'false'
            );
            $rs=$this->db->exec('select * from sdb_settings where 0=1');
            $sql=$this->db->getInsertSQL($rs,$aData);
            $this->db->exec($sql);
        }
        /******************************/

        return 'finish';
    }

    function upgrade_gimages(){
        $this->title = '商品图片';
        $aGoods = $this->db->select('SELECT goods_id,type_id, image_default, image_file FROM sdb_goods ORDER BY goods_id ASC LIMIT '.(($this->step-1)*100).', 100');
        if(empty($aGoods)){
            $this->updateMsg =  update_message('商品图片升级成功');
            return 'finish';
        }
        foreach( $aGoods as $gv ){
            $hasG = $this->db->selectrow('SELECT COUNT(gimage_id) AS c FROM sdb_gimages WHERE goods_id = '.$gv['goods_id']);
            if( $hasG['c'] )
                continue;
            foreach( explode(',',$gv['image_file']) as $iv ){
                $iv = trim($iv);
                
                $gimagesData['is_remote'] = 'false';
//                $gimagesData['source'] = $iv;
                $gimagesData['goods_id'] = $gv['goods_id'];

                $defPicSrc = '';

                if( substr( $iv, 0, 4 ) == 'http' ){
                    $gimagesData['is_remote'] = 'true';

                    $defPicSrc = 'N';

                    foreach( array('small','big','thumbnail') as $iType )
                        $gimagesData[$iType] =  $iv;
                }else{
                    $oldImgSrc = explode('|',$iv);
                    $defPicSrc = $oldImgSrc[0];
                    $extName = strrchr($defPicSrc, '.');
                    
                    mkdir_p(dirname(HOME_DIR.'/upload/'.$defPicSrc));
                    copy(BASE_DIR.$defPicSrc, HOME_DIR.'/upload/'.$defPicSrc);
                    unlink(BASE_DIR.$defPicSrc);
                    chmod(HOME_DIR.'/upload/'.$defPicSrc, 0777);

                    foreach( array('small','big','thumbnail') as $iType )
                        $gimagesData[$iType] =  str_replace($extName, '_'.$iType.$extName, $iv);
                }

                $this->db->exec('INSERT INTO sdb_gimages ( 
                    goods_id , is_remote , source , src_size_width , src_size_height , small , big , thumbnail , up_time 
                ) VALUES (
                    '.$gv['goods_id'].' , "'.$gimagesData['is_remote'].'" , "'.$defPicSrc.'" , 100 , 100 , "'.$gimagesData['small'].'" , "'.$gimagesData['big'].'" , "'.$gimagesData['thumbnail'].'" , '.time().'
                )');
                $lastId = $this->db->lastInsertId();

                if( $iv == trim($gv['image_default']) ) {
                    if(!$this->db->exec( 'UPDATE sdb_goods SET image_default = "'.$lastId.'" , small_pic = "'.$gimagesData['small'].'", big_pic = "'.$gimagesData['big'].'" WHERE goods_id = '.$gv['goods_id'] )){
                        $this->updateMsg =  update_message('商品图片升级失败',E_WARNING);
                        return 'error';
                    }
                }
            }
        }
        return 'continue';
    }

    function upgrade_goods(){
        $this->title = '商品规格';
        $aGoods = $this->db->select('SELECT goods_id,type_id,spec, pdt_desc, spec_desc FROM sdb_goods ORDER BY goods_id ASC LIMIT '.(($this->step-1)*100).', 100');
        if(empty($aGoods)){
            $this->updateMsg =  update_message('商品规格升级成功');
            return 'finish';
        }
        $typeList = array();
        $specList = array();
        $si = 1;
        foreach( $aGoods as $gk => $gv ){
            $spec = unserialize( $gv['spec'] );
            if( !$spec || unserialize( $gv['spec_desc'] ) )
                continue;
            $aPro = $this->db->select('SELECT product_id, props FROM sdb_products WHERE goods_id = '.$gv['goods_id']);
            $gPdtDesc = unserialize( $gv['pdt_desc'] );
            $specDesc = array();
            $specValueList = array();

            if( !isset( $typeList[$gv['type_id']] ) ){
                $typeRow = $this->db->selectrow('SELECT name FROM sdb_goods_type WHERE type_id = "'.$gv['type_id'].'" AND schema_id != "simple"');
                $typeList[$gv['type_id']] = $typeRow['name'];
            }
            $gv['type_name'] = $typeList[$gv['type_id']];

            foreach( $aPro as $pk =>$pv ){
                $aProps = unserialize( $pv['props'] );
                $newProps = array();
                if( array_key_exists($aProps['idata']) ){
                    $newProps['idata'] = $aProps['idata'];
                }
                foreach( $aProps['spec'] as $propsk => $propsv ){
                    $propsv = trim( $propsv );
                    
                    $specid = $this->db->selectrow('SELECT spec_id FROM sdb_specification WHERE spec_name = "'.$spec[$propsk].'" AND spec_memo = "'.$gv['type_name'].'"');
                    if( !$specid['spec_id'] ){
                        $this->db->exec('INSERT INTO sdb_specification (spec_name, spec_memo) VALUES ("'.$spec[$propsk].'", "'.$gv['type_name'].'")');
                        $specid['spec_id'] = $this->db->lastInsertId();
                    }

                    $specVid = $this->db->selectrow('SELECT spec_value_id FROM sdb_spec_values WHERE spec_id = '.$specid['spec_id'].' AND spec_value = "'.$propsv.'"');
                    if( !$specVid['spec_value_id'] ){
                        $this->db->exec('INSERT INTO sdb_spec_values (spec_id, spec_value) VALUES ( '.$specid['spec_id'].', "'.$propsv.'" )');
                        $specVid['spec_value_id'] = $this->db->lastInsertId();
                    }

                    if( !isset( $specValueList[$specVid['spec_value_id'].'__'.$propsv] ) ){
                        $specPVid = time().($si++);
                        $specDesc[$specid['spec_id']][$specPVid] = array(
                            'spec_value_id' => $specVid['spec_value_id'],
                            'spec_value' => $propsv,
                            'spec_type' => 'text',
                            'spec_image' => '',
                            'spec_goods_images' => ''
                        );
                        $specValueList[$specVid['spec_value_id'].'__'.$propsv] = $specPVid;
                    }else{
                        $specPVid = $specValueList[$specVid['spec_value_id'].'__'.$propsv];
                    }
                    $newProps['spec'][$specid['spec_id']] = $propsv;
                    $newProps['spec_private_value_id'][$specid['spec_id']] = $specPVid;
                    $newProps['spec_value_id'][$specid['spec_id']] = $specVid['spec_value_id'];

                    $this->db->exec('INSERT INTO sdb_goods_spec_index (type_id,spec_id, spec_value_id, goods_id, product_id) VALUES ( '.$gv['type_id'].','.$specid['spec_id'].','.$specVid['spec_value_id'].','.$gv['goods_id'].','.$pv['product_id'].' )');
                }
                $this->db->exec('UPDATE sdb_products SET props = \''.serialize($newProps).'\' WHERE product_id = '.$pv['product_id']);
            }

            if( !$this->db->exec('UPDATE sdb_goods SET spec_desc = \''.serialize($specDesc).'\' WHERE goods_id = '.$gv['goods_id']) ){
                $this->updateMsg =  update_message('商品规格升级失败',E_WARNING);
                return 'error';
            }
        }
        return 'continue';

    }

    //检测支付接口
    function upgrade_payment(){
        $this->title = '支付接口';
        if( $this->db->exec('UPDATE sdb_payment_cfg SET pay_type = "alipay" WHERE pay_type = "alipaytrad"') ){
            $this->updateMsg =  update_message('支付接口升级成功');
            return 'finish';
        }else{
            $this->updateMsg =  update_message('支付接口升级失败',E_WARNING);
            return 'error';
        }
    }

    //配送方式升级
    function upgrade_delivery(){
        /*
        if( file_exists(HOME_DIR.'/logs/oldDeliveryData.csv') ){
            $this->updateMsg =  update_message('配送方式升级成功');
            return 'finish';
        }
        $this->title = '配送方式';
        if( $this->backupOldData() && $this->clearOldData() && $this->installNewData() ){
            $this->updateMsg =  update_message('配送方式升级成功');
            return 'finish';
        }else{
                    $this->updateMsg =  update_message('配送方式升级失败',E_WARNING);
                    return 'error';
                }
            }

    function upgrade_last(){
        $this->updateMsg =  update_message('升级成功');
        return 'finish';
    }


    /**  私有方法 */
    //配送方式
    }
    function backupOldData(){
        $dbType = array(
            '0'=>'公式计算',
            '1'=>'定额配送',
            '2'=>'第三方网关计算'
        );
        $charset = &$this->system->loadModel('utility/charset');
        $fDFile = fopen( HOME_DIR.'/logs/oldDeliveryData.csv','w+');
        fwrite( $fDFile, $charset->utf2local('"","名称","说明","配送公式","类型","第三方网关接口","是否保价","保价汇率","最低保价费用","货到付款","排序","快递公司","状态"')."\n" );
        $i = 1;
        foreach( $this->db->select('SELECT t.dt_id, t.dt_name , t.detail , t.price , t.type , t.gateway, t.protect , t.protect_rate , t.minprice, t.has_cod , t.ordernum  , c.name AS corp_id , t.disabled FROM sdb_dly_type t LEFT JOIN sdb_dly_corp c ON c.corp_id = t.corp_id') as $dv ){
            $dtype = $dv['type'];
            $dtid = $dv['dt_id'];
            unset($dv['dt_id']);
            $dv['type'] = $dbType[$dv['type']];
            $dv['protect'] = $dv['protect']?'是':'否';
            $dv['has_cod'] = $dv['has_cod']?'是':'否';
            $dv['disabled'] = $dv['disabled']?'正常':'删除';
            fwrite( $fDFile, '"'.($i++).'","'.$charset->utf2local(implode('","',$dv)).'"'."\n" );
            if( $dtype == "0" ){
                fwrite( $fDFile,$charset->utf2local('"","配送地区","配送费用","配送公式","货到付款","排序"')."\n" );
                foreach( $this->db->select('SELECT a.name AS area_id , h.price , h.expressions , h.has_cod , h.ordernum FROM sdb_dly_h_area h LEFT JOIN sdb_dly_area a ON a.area_id = h.area_id WHERE h.dt_id = '.$dtid) as $hv ){
                    $hv['has_cod'] = $hv['has_cod']?'是':'否';
                    fwrite( $fDFile, $charset->utf2local('"","'.implode('","',$hv).'"')."\n");
                }
            }
            fwrite( $fDFile, "\"\"\n" );
        }
        fclose($fDFile);
        return true;
    }
    function clearOldData(){
        return (
            $this->db->exec('DELETE FROM sdb_regions where package=\'mainland\'') &&
            $this->db->exec('TRUNCATE TABLE sdb_dly_type') && 
            $this->db->exec('TRUNCATE TABLE sdb_dly_h_area') 
        );
    }
    function installNewData(){
        if($handle = fopen(dirname(__FILE__)."/".$this->version."/area.txt","r")){
            $i = 0;
            $sql = "INSERT INTO `sdb_regions` (`region_id`, `package`, `p_region_id`,`region_path`,`region_grade`, `local_name`, `en_name`, `p_1`, `p_2`) VALUES ";
            while ($data = fgets($handle, 1000)){
                $data = trim($data);
                if(substr($data, -2) == '::'){
                    if($aSql){
                        $sqlInsert = $sql.implode(',', $aSql).";";
                        $this->db->exec($sqlInsert);
                        unset($path);
                    }
                    $i++;
                    $path[]=$i;
                    $regionPath=",".implode(",",$path).",";
                    $aSql = array();
                    $aTmp = explode('::', $data);
                    $aSql[] = "(".$i.", 'mainland', NULL, '".$regionPath."', '".count($path)."', '".$aTmp[0]."', NULL, NULL, NULL)";
                    $f_pid = $i;
                }else{
                    if(strstr($data, ':')){
                        $i++;
                        $aTmp = explode(':', $data);
                        unset($sPath);
                        $sPath[]=$f_pid;
                        $sPath[]=$i;
                        $regionPath=",".implode(",",$sPath).",";
                        $aSql[] = "(".$i.", 'mainland', ".intval($f_pid).", '".$regionPath."', '".count($sPath)."', '".$aTmp[0]."', NULL, NULL, NULL)";
                        if(trim($aTmp[1])){
                            $pid = $i;
                            $aTmp = explode(',', trim($aTmp[1]));
                            foreach($aTmp as $v){
                                $i++;
                                $tmpPath=$regionPath.$i.",";
                                $grade = count(explode(",",$tmpPath))-2;
                                $aSql[] = "(".$i.", 'mainland', ".intval($pid).", '".$tmpPath."', '".$grade."', '".$v."', NULL, NULL, NULL)";
                            }
                        }
                    }elseif($data){
                        $i++;
                        $tmpPath=$regionPath.$i.",";
                        $grade = count(explode(",",$tmpPath))-2;
                        $aSql[] = "(".$i.", 'mainland', ".intval($f_pid).", '".$tmpPath."','".$grade."','".$data."', NULL, NULL, NULL)";
                    }
                }
            }
            fclose($handle);
            return true;
        }else{
            return false;
        }
    }

}
?>