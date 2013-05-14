<?php
include_once(CORE_DIR.'/api/shop_api_object.php');
class api_1_0_supplier extends shop_api_object {

     //创建供应商信息
     function create_supplier($data){
         $params=array();
         $params['supplier_id'] = intval($data['supplier_id']);
         $params['domain'] = trim($data['domain']);
         $params['supplier_brief_name'] = $data['supplier_brief_name']?trim($data['supplier_brief_name']):'';
         $params['status'] = $data['status']?1:0;
         $params['sync_time'] = $data['sync_time']?intval($data['sync_time']):0;
         $params['has_new'] = $data['has_new']=='true'?'true':'false';
         $params['has_cost_new'] = $data['has_cost_new']=='true'?'true':'false';
         $params['sync_time_for_plat'] = $data['sync_time_for_plat']?intval($data['sync_time_for_plat']):0;
         
         if(!$this->db->selectrow('select supplier_id from '.DB_PREFIX.'supplier where supplier_id='.$params['supplier_id'])){
              $sql = 'insert into '.DB_PREFIX.'supplier(supplier_id,domain,supplier_brief_name,status,sync_time,has_new,has_cost_new,sync_time_for_plat)values
               ('.$params['supplier_id'].',"'.$params['domain'].'","'.$params['supplier_brief_name'].'",'.$params['status'].','.$params['sync_time'].',"'.$params['has_new'].'","'.$params['has_cost_new'].'",'.$params['sync_time_for_plat'].')';
               $this->db->exec($sql);

               $table_name = DB_PREFIX."data_platform_sync_" . $params['supplier_id'];
                    $create_table = <<<EOF
                        CREATE TABLE IF NOT EXISTS `{$table_name}` (
                          `sync_id` mediumint(8) NOT NULL AUTO_INCREMENT,
                          `goods_id` mediumint(8) unsigned NOT NULL DEFAULT '0',
                          `supplier_goods_id` mediumint(8) unsigned NOT NULL DEFAULT '0',
                          `goods_struct` longtext NOT NULL COMMENT '商品结构体',
                          `product_struct` longtext COMMENT '货品结构体',
                          `price_struct` text COMMENT '价格结构体',
                          `command` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT ' 操作状态',
                          `platform_sync_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '平台同步时间',
                          `user_op_time` int(10) NOT NULL DEFAULT '0' COMMENT '用户操作时间',
                          `status` enum('finish','hand') NOT NULL DEFAULT 'hand' COMMENT '状态',
                          `goods_name` varchar(200) NOT NULL,
                          `cat_name` varchar(100) NOT NULL,
                          `sync_image` enum('true','false') NOT NULL DEFAULT 'false',
                          `bn` varchar(200) NOT NULL,
                          `brand_name` varchar(100) NOT NULL,
                          `thumbnail_pic` varchar(150) NOT NULL,
                          `command_action` varchar(50) NOT NULL,
                          PRIMARY KEY (`sync_id`),
                          KEY `status` USING BTREE (`status`),
                          KEY `goods_id` USING BTREE (`goods_id`) 
                        ) ENGINE=MyISAM DEFAULT CHARSET=utf8;
EOF;
                    $this->db->exec($create_table);
                    
                    $table_images_name =DB_PREFIX.'sync_image';
                    $create_images_table = <<<EOF
                    CREATE TABLE IF NOT EXISTS `{$table_images_name}` (
                    `id` bigint(20) NOT NULL AUTO_INCREMENT,
                    `supplier_goods_id` mediumint(8) unsigned NOT NULL,
                    `data_struct` longtext NOT NULL,
                    `status` enum('finish','hand') NOT NULL DEFAULT 'finish',
                    `supplier_id` int(10) unsigned NOT NULL,
                    `type` enum('spec_value','brand_logo','udfimg','gimage') NOT NULL DEFAULT 'gimage',
                    `add_time` int(10) unsigned NOT NULL,
                    PRIMARY KEY (`id`)
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
EOF;
                    $this->db->exec($create_images_table);
        }else{
            $this->db->exec("update ".DB_PREFIX."supplier set domain='".$params['domain']."',supplier_brief_name='".$params['supplier_brief_name']."' where supplier_id=".$params['supplier_id'] );
        }
        $this->set_sync_status(array('supplier_id'=>$data['supplier_id'],'data_struct'=>json_encode(array('status'=>'true','delGoodsIds'=>''))));
     }
     
     //设置同步状态 删除商品
     function set_sync_status($data){
         @constant( "DEBUG_API" ) and $this->write_log($data,'status');
         $supplier_id = intval($data['supplier_id']);
         $data_struct = json_decode($data['data_struct'], true);
         if(!$data_struct = $this->decode_data_struct($data['data_struct'])) $this->api_response('fail','no data_struct');
         $status = $data_struct['status']=='true'?1:0;
         $str_sql = $status?' sync_time='.time():' sync_time_for_plat='.time();
         
         if($data_struct['delGoodsIds']){
             $sql = 'select goods_id,bn,supplier_goods_id from '.DB_PREFIX.'goods where supplier_id = '.$supplier_id.' and supplier_goods_id in ('.$data_struct['delGoodsIds'].') ';
             $objGoods = $this->system->loadModel('trading/goods');
             foreach($this->db->select($sql) as $goods){
                 $this->delPdtBn($goods['bn']);
                 $sql = 'select bn from sdb_products where goods_id ='.$goods['goods_id'];
                 foreach($this->db->select($sql) as $v){
                      $this->delPdtBn($v['bn']);
                 }
                 $objGoods->toRemove($goods['goods_id']);
                 $this->db->exec('DELETE FROM sdb_sync_image where supplier_id = '.$supplier_id.' and supplier_goods_id ='.$goods['supplier_goods_id']);
                 $this->db->exec('DELETE FROM '.DB_PREFIX.'data_platform_sync_'.$supplier_id.' WHERE goods_id = '.$goods['goods_id']);
             }
         }
         if($this->db->exec('update '.DB_PREFIX.'supplier set status = '.$status.','.$str_sql.' where supplier_id = '.$supplier_id)){
             $this->api_response('true',false,'succ');
         }else{
             $this->api_response('fail','update fail');
         }
     }
     
     function delPdtBn($bn){
          $this->db->exec('DELETE FROM sdb_supplier_pdtbn WHERE source_bn = \''.$bn.'\' ');
     }
     
     function create_supplier_brand($data){
         @constant( "DEBUG_API" ) and $this->write_log($data,'brand');
         $supplier_id = intval($data['supplier_id']);
         $supplier_shop_url = $this->getSingleData('supplier','domain', ' where supplier_id = '.$supplier_id);
         $oPlatform = $this->system->loadModel('distribution/platformsync');
         $lBrandRows = $this->db->select('select brand_id,supplier_brand_id from '.DB_PREFIX.'brand where supplier_id ='.$supplier_id);
         $lBrandData=array();
         foreach($lBrandRows as $k=>$v){
             $lBrandData[$v['supplier_brand_id']]=$v['brand_id'];
         }
         if(!$sData = $this->decode_data_struct($data['data_struct'])) $this->api_response('fail','no data_struct');
         foreach($sData as $k=>$v){
             if(!$this->_checkRemoteImage($v['brand_logo'])){
                 $v['brand_logo']=$oPlatform->get_remote_image($supplier_shop_url,$v['brand_logo']);
                  //$v['brand_logo']=('ZFZ'==$this->system->getConf('system.b2c_shop_type'))?$oPlatform->get_remote_image($supplier_shop_url,$v['brand_logo']):$oPlatform->downloadImage($v['brand_logo']);
             }
             $v['ordernum']=$v['ordernum']?$v['ordernum']:0;
             if($lBrandData[$v['brand_id']]){
                  $sql='update '.DB_PREFIX.'brand set brand_name="'.$v['brand_name'].'",brand_url="'.$v['brand_url'].'",brand_desc="'.addslashes($v['brand_desc']).'",brand_logo="'.$v['brand_logo'].'",brand_keywords="'.$v['brand_keywords'].'",disabled="'.$v['disabled'].'",ordernum='.$v['ordernum'].' where brand_id ='.$lBrandData[$v['brand_id']];
             }else{
                  $sql='insert into '.DB_PREFIX.'brand(supplier_id,supplier_brand_id,brand_name,brand_url,brand_desc,brand_logo,brand_keywords,disabled,ordernum)values('.$supplier_id.','.$v['brand_id'].',"'.$v['brand_name'].'","'.$v['brand_url'].'","'.addslashes($v['brand_desc']).'","'.$v['brand_logo'].'","'.$v['brand_keywords'].'","'.$v['disabled'].'",'.$v['ordernum'].')';
             }
             $this->db->exec($sql);
         }
         $this->api_response('true',false,'succ');
     }
     
     function create_supplier_spec($data){
         @constant( "DEBUG_API" ) and $this->write_log($data,'spec');
         $supplier_id = intval($data['supplier_id']);
         $lSpecRows = $this->db->select('select spec_id,supplier_spec_id from '.DB_PREFIX.'specification where supplier_id ='.$supplier_id);
         $lSpecData=array();
         foreach($lSpecRows as $k=>$v){
             $lSpecData[$v['supplier_spec_id']]=$v['spec_id'];
         }
         
         $lSpecValueRows = $this->db->select('select spec_value_id,supplier_spec_value_id from '.DB_PREFIX.'spec_values where supplier_id ='.$supplier_id);
         $lSpecValueData=array();
         foreach($lSpecValueRows as $k=>$v){
             $lSpecValueData[$v['supplier_spec_value_id']]=$v['spec_value_id'];
         }
         if(!$sData = $this->decode_data_struct($data['data_struct'])) $this->api_response('fail','no data_struct');
         if(!$sData = json_decode($data['data_struct'],true)) $this->api_response('fail','no data_struct');
         foreach($sData as $k=>$v){
             if($lSpecData[$v['spec_id']]){
                  $sql='update '.DB_PREFIX.'specification set disabled="'.$v['disabled'].'",spec_show_type="'.$v['spec_show_type'].'",spec_name="'.$v['spec_name'].'",spec_type="'.$v['spec_type'].'",spec_memo="'.$v['spec_memo'].'",p_order='.$v['p_order'].',lastmodify='.$v['last_modify'].' where spec_id ='.$lSpecData[$v['spec_id']];
                  $this->db->exec($sql);
                  $v['spec_id']=$lSpecData[$v['spec_id']];
             }else{
                  $sql='insert into '.DB_PREFIX.'specification(supplier_id,supplier_spec_id,spec_name,spec_type,spec_memo,p_order,lastmodify,disabled,spec_show_type)values('.$supplier_id.','.$v['spec_id'].',"'.$v['spec_name'].'","'.$v['spec_type'].'","'.$v['spec_memo'].'",'.$v['p_order'].','.$v['last_modify'].',"'.$v['disabled'].'","'.$v['spec_show_type'].'")';
                  $this->db->exec($sql);
                  $v['spec_id'] = $this->db->lastInsertId();
             }
             if($v['spec_values']) $this->create_supplier_spec_value($supplier_id,$v,$lSpecData,$lSpecValueData);
         }
         $this->api_response('true',false,'succ');
     }
     
     function create_supplier_spec_value($supplier_id,&$sData,&$lSpecData,&$lSpecValueData){
          $oPlatform = $this->system->loadModel('distribution/platformsync');
          $supplier_shop_url = $this->getSingleData('supplier','domain', ' where supplier_id = '.$supplier_id);
          foreach($sData['spec_values'] as $k=>$v){
              if(!$this->_checkRemoteImage($v['spec_image'])){
                  $v['spec_image']=$oPlatform->get_remote_image($supplier_shop_url,$v['spec_image']);
//                   $v['spec_image']=('ZFZ'==$this->system->getConf('system.b2c_shop_type'))?$oPlatform->get_remote_image($supplier_shop_url,$v['spec_image']):$oPlatform->downloadImage($v['spec_image']);
              }
              if($lSpecValueData[$v['spec_value_id']]){
                  $sql='update '.DB_PREFIX.'spec_values set spec_value="'.$v['spec_value'].'",spec_image="'.$v['spec_image'].'",spec_id='.$sData['spec_id'].',p_order='.$v['p_order'].' where spec_value_id ='.$lSpecValueData[$v['spec_value_id']];
              }else{
                  $sql='insert into '.DB_PREFIX.'spec_values(supplier_id,supplier_spec_value_id,spec_id,spec_value,spec_image,p_order)values('.$supplier_id.','.$v['spec_value_id'].','.$sData['spec_id'].',"'.$v['spec_value'].'","'.$v['spec_image'].'",'.$v['p_order'].')';
              }
              $this->db->exec($sql);
          }
     }
     
     function create_supplier_cat($data){
         @constant( "DEBUG_API" ) and $this->write_log($data,'cat');
         $supplier_id = intval($data['supplier_id']);
         
         if(!$mData = $this->decode_data_struct($data['data_struct'])) $this->api_response('fail','no data_struct');
         $lTypeRows = $this->db->select('select type_id,supplier_type_id from '.DB_PREFIX.'goods_type where supplier_id ='.$supplier_id);
         $lTypeData=array();
         foreach($lTypeRows as $k=>$v){
             $lTypeData[$v['supplier_type_id']]=intval($v['type_id']);
         }
         foreach($mData as $key=>$sData){
             $lRows = $this->db->select('select cat_id,supplier_cat_id from '.DB_PREFIX.'goods_cat where supplier_id ='.$supplier_id);
             $lData=array();
             foreach($lRows as $k=>$v){
                 $lData[$v['supplier_cat_id']]=$v['cat_id'];
             }
             if($lData[$sData['parent_id']]){
                 $cat_path = '';
                 $parent_id = $lData[$sData['parent_id']];
                 $aSupplier_cat_path = explode(',',$sData['cat_path']);
                 foreach($aSupplier_cat_path as $k1=>$v1){
                     if($lData[$v1]){
                       $cat_path = $cat_path.$lData[$v1].',';
                     }
                 }
             }else{
                   $parent_id = 0;
                   $cat_path = ',';
             }
        if(!$sData['goods_count']){
            $sData['goods_count'] = 0;
        }
        
        $type_id = $lTypeData[$sData['type_id']];
        if($lData[$sData['cat_id']]){
             $parentRows = $this->db->selectrow('select cat_id,count(cat_id) from '.DB_PREFIX.'goods_cat where parent_id = '.$lData[$sData['cat_id']].' and (supplier_id is null or supplier_id <>'.$supplier_id.') group by cat_id ');
             if($parentRows['cat_id']){
                $is_leaf = 'false';
             }else{
                $is_leaf = $sData['is_leaf'];
             }
             $cCount = $sData['child_count']+$parentRows['count(cat_id)'];
             $sql='update '.DB_PREFIX.'goods_cat set parent_id='.$parent_id.',supplier_cat_id='.$sData['cat_id'].",cat_path='".$cat_path."',is_leaf='".$is_leaf."',cat_name='".$sData['cat_name']."',type_id='".$type_id."',p_order='".$sData['p_order']."',goods_count='".$sData['goods_count']."',child_count=".$cCount.",tabs='".$sData['tabs']."',finder='".$sData['finder']."',addon='".$sData['addon']."' where cat_id = ".$lData[$sData['cat_id']];
         }else{
             $type_id=$type_id?$type_id:0;
             $sql='insert into '.DB_PREFIX.'goods_cat(supplier_id,parent_id,supplier_cat_id,cat_path,is_leaf,cat_name,type_id,p_order,goods_count,child_count,tabs,finder,addon)values('.$supplier_id.','.$parent_id.','.$sData['cat_id'].",'".$cat_path."','".$sData['is_leaf']."','".$sData['cat_name']."','".$type_id."','".$sData['p_order']."',".$sData['goods_count'].",".$sData['child_count'].",'".$sData['tabs']."','".$sData['finder']."','".$sData['addon']."')";
         }
             if(!$this->db->exec($sql)){
                error_log($sql."\n",3,HOME_DIR.'/logs/goods_cat_error_'.date('Y-m-d',time()).'.logs');
              }
         }
         $objCat = &$this->system->loadModel('goods/productCat');
         $objCat->cat2json();
         //if($lData) $this->db->exec('update '.DB_PREFIX.'goods_cat set disabled=\'true\' where cat_id ('.implode(',',array_values($lData)).')');
         $this->api_response('true',false,'succ');
     }
     
     
     function create_supplier_goods($data){
         @constant( "DEBUG_API" ) and $this->write_log($data,'goods');
         $supplier_id = intval($data['supplier_id']);
         $supplier_shop_url = $this->getSingleData('supplier','domain', ' where supplier_id = '.$supplier_id);
         $oPlatform = $this->system->loadModel('distribution/platformsync');
         //$localRelateData=array(1=>'商品上架',3=>'图片更新',4=>'商品更新',6=>'商品新增',8=>'商品下架',10=>'中断产品线分销权限');

         $localRelateData = $oPlatform->getRelationData($supplier_id);
//          $op_row = $this->db->select('SELECT rule.supplier_op_id,rule.local_op_id FROM `'.DB_PREFIX.'autosync_rule` as rule LEFT JOIN  '.DB_PREFIX.'autosync_rule_relation as rel on rule.rule_id=rel.rule_id where rel.supplier_id='.$supplier_id.' and rule.local_op_id=0 ');
         $op_row = $this->db->select('SELECT rule.supplier_op_id,rule.local_op_id FROM `'.DB_PREFIX.'autosync_rule` as rule LEFT JOIN  '.DB_PREFIX.'autosync_rule_relation as rel on rule.rule_id=rel.rule_id where rel.supplier_id='.$supplier_id.' and rule.local_op_id=0 ');
         if($op_row & 0==$op_row['local_op_id']) $is_hand=true;
         
         $handOperate=array();
         if(!$data_struct = $this->decode_data_struct($data['data_struct'])) $this->api_response('fail','no data_struct');
         foreach($data_struct as $k=>$v){
             $gxml_values = $v['gxml_values'];
             if(!$gxml_values){$this->write_log($v,'gxml_values_error');continue;}
             $gxml_encode = json_encode($v['gxml_values']);
             $pxml_encode = json_encode($v['pxml_values']);
         
             $supplier_goods_id = intval($gxml_values['goods_id']);
             $sync_status= array_pop(explode('_',$gxml_values['sync_status']));
             $sync_status == 0 and $sync_status == 9 and $sync_status = 6;
             $sync_status = $localRelateData['goods'][$supplier_goods_id]?$sync_status:6;//如果商品不存在，状态改为新增
             
             $handOperate['goods_id']=$localRelateData['goods'][$supplier_goods_id]?$localRelateData['goods'][$supplier_goods_id]:0;
             $handOperate['supplier_goods_id']=$supplier_goods_id;
             $handOperate['goods_name']=$gxml_values['name'];
             $handOperate['command']=$sync_status;
             $handOperate['goods_struct']=json_encode($v['gxml_values']);
             $handOperate['product_struct']=json_encode($v['pxml_values']);
             $handOperate['sync_image']=in_array($sync_status,array(6,3))?'true':'false';
             $handOperate['supplier_id']=$supplier_id;
             $handOperate['status']='finish';
             $handOperate['command_action']=$gxml_values['sync_status'];
             $handOperate['platform_sync_time']=time();
             $handOperate['thumbnail_pic']=$oPlatform->get_remote_image($supplier_shop_url,$gxml_values['thumbnail_pic']);
             $handOperate['brand_name']=$gxml_values['brand']?$gxml_values['brand']:'';
             $handOperate['bn']=$gxml_values['bn'];
             $gxml_values['cat_id'] and $handOperate['cat_name']=$this->getSingleData('goods_cat','cat_name',' where supplier_id = '.$supplier_id.' and cat_id ='.$gxml_values['cat_id']);
             
             if($is_hand){//手动操作
                 $handOperate['status']='hand';
             }else{
                 //以下只更新商品信息
                 $handOperate['goods_id'] = $oPlatform->_handle_goods($supplier_id,$sync_status,$gxml_encode,$pxml_encode,$localRelateData,false);
             }

             if($sync_id=$this->getSingleData('data_platform_sync_'.$supplier_id,'sync_id',' where supplier_goods_id = '.$supplier_goods_id)){
                 $handOperate['sync_id']=$sync_id;
                 $rs = $this->db->query('SELECT * FROM '.DB_PREFIX.'data_platform_sync_'.$supplier_id.'  WHERE sync_id='.$sync_id);
                 $sql = $this->db->GetUpdateSQL($rs,$handOperate);
             }else{
                unset($handOperate['sync_id']);
                $rs = $this->db->query('SELECT * FROM '.DB_PREFIX.'data_platform_sync_'.$supplier_id.'  WHERE 0=1');
                $sql = $this->db->GetInsertSQL($rs,$handOperate);
             }
             if(!$this->db->exec($sql)) error_log(print_r($handOperate,true)."\n",3,HOME_DIR.'/logs/syncdata_error_'.date('Y-m-d',$handOperate['platform_sync_time']).'.logs');
         }
         $this->api_response('true',false,'succ');
     }
     
     
     function create_supplier_type($data){
         @constant( "DEBUG_API" ) and $this->write_log($data,'type');
         $supplier_id = intval($data['supplier_id']);
         $lTypeRows = $this->db->select('select type_id,supplier_type_id from '.DB_PREFIX.'goods_type where supplier_id ='.$supplier_id);
         $lTypeData=array();
         foreach($lTypeRows as $k=>$v){
             $lTypeData[$v['supplier_type_id']]=$v['type_id'];
         }
         if(!$mData = $this->decode_data_struct($data['data_struct'])) $this->api_response('fail','no data_struct');
         foreach($mData as $key=>$sData){
            if($lTypeData[$sData['type_id']]){
                  $sql='update '.DB_PREFIX."goods_type set name='".$sData['name']."',alias='".$sData['alias']."',is_physical='".$sData['is_physical']."',supplier_id=".$supplier_id.",supplier_type_id=".$sData['type_id'].",setting='".$sData['setting']."',params='".$sData['params']."',ret_func='".$sData['ret_func']."',spec='".$sData['spec']."',minfo='".$sData['minfo']."',dly_func='".$sData['dly_func']."',props='".$sData['props']."',schema_id='".$sData['schema_id']."',lastmodify=".$sData['last_modify'].' where type_id ='.$lTypeData[$sData['type_id']];
             }else{
                  $sql='insert into '.DB_PREFIX."goods_type(name,alias,is_physical,supplier_id,supplier_type_id,setting,params,ret_func,spec,minfo,dly_func,props,schema_id,lastmodify)values('".$sData['name']."','".$sData['alias']."','".$sData['is_physical']."',".$supplier_id.",".$sData['type_id'].",'".$sData['setting']."','".$sData['params']."','".$sData['ret_func']."','".$sData['spec']."','".$sData['minfo']."','".$sData['dly_func']."','".$sData['props']."','".$sData['schema_id']."',".$sData['last_modify'].')';
             }
             $this->db->exec($sql);
         }
         $this->api_response('true',false,'succ');
     } 

     function create_supplier_image($data){
         @constant( "DEBUG_API" ) and $this->write_log($data,'image');
         $supplier_id = intval($data['supplier_id']);
         $time = time();
         if(!$data_struct = $this->decode_data_struct($data['data_struct'])) $this->api_response('fail','no data_struct');
         if('ZFZ'==$this->system->getConf('system.b2c_shop_type')){
             $this->_handle_pl_images($supplier_id, $data['data_struct']);
         }else{
            foreach($data_struct as $img_info){
                $xml_values = json_encode($img_info['xml_values']);
                if($id = $this->getSingleData('sync_image','id',' where supplier_goods_id = '.$img_info['goods_id'].' and supplier_id ='.$supplier_id)){
                    $sql = 'update '.DB_PREFIX.'sync_image set add_time= '.$time.',data_struct=\''.$xml_values.'\',status=\'hand\' where id ='.$id;
                }else{
                    $sql = "insert into ".DB_PREFIX."sync_image(type,supplier_id,supplier_goods_id,add_time,data_struct,status)values('gimage',".$supplier_id.",".$img_info['goods_id'].",".$time.",'".$xml_values."','hand')";
                }
                if(!$this->db->exec($sql)) $this->write_log($img_info, 'image_error');
            }
         }
         $this->api_response('true',false,'succ');
     }
     
     function _handle_pl_images($supplier_id,&$data_struct){
        $supplier_shop_url = $this->getSingleData('supplier','domain', ' where supplier_id = '.$supplier_id);
        $oPlatform = $this->system->loadModel('distribution/platformsync');
        $time = time();
        $gimageRelate = array();
        foreach($this->db->select('select gimage_id,supplier_gimage_id,goods_id from '.DB_PREFIX.'gimages where supplier_id = '.$supplier_id) as $k=>$v){
            $gimageRelate[$v['goods_id']][$v['supplier_gimage_id']] = $v['gimage_id'];
        }
        foreach(json_decode($data_struct,true) as $img_info){
            $supplier_goods_id = $img_info['goods_id'];
            if(!$goods_info = $this->db->selectrow('select goods_id,image_default from '.DB_PREFIX.'goods where supplier_id = '.$supplier_id.' and supplier_goods_id ='.$supplier_goods_id)) continue;
            $has_image_default = FALSE;
            foreach($img_info['xml_values'] as $pic_info){
                $pic_info['big'] = $oPlatform->get_remote_image($supplier_shop_url,$pic_info['big']);
                $pic_info['small'] = $oPlatform->get_remote_image($supplier_shop_url,$pic_info['small']);
                $pic_info['thumbnail'] = $oPlatform->get_remote_image($supplier_shop_url,$pic_info['thumbnail']);
                if(array_key_exists($pic_info['gimage_id'], $gimageRelate[$goods_info['goods_id']])){
                    $gimage_id =$gimageRelate[$goods_info['goods_id']][$pic_info['gimage_id']];
                    $sql = "update ".DB_PREFIX."gimages set thumbnail= '".$pic_info['thumbnail']."',small='".$pic_info['small']."',big='".$pic_info['big']."' where gimage_id = ". $gimage_id;
                    if(!$this->db->exec($sql)) $this->write_log(print_r($pic_info,true), 'gimage_error');
                }else{
                    $sql = "insert into ".DB_PREFIX."gimages(goods_id,is_remote,source,orderby,src_size_width,src_size_height,small,big,thumbnail,up_time,supplier_id,supplier_gimage_id,sync_time)
                    values(".$goods_info['goods_id'].",'true','N',".$pic_info['orderby'].",".$pic_info['src_size_width'].",".$pic_info['src_size_height'].",'".$pic_info['small']."','".$pic_info['big']."','".$pic_info['thumbnail']."',".$time.",".$supplier_id.",".$pic_info['gimage_id'].",".$time.") ";
                    if(!$this->db->exec($sql)) $this->write_log(print_r($pic_info,true), 'gimage_error');
                    $gimage_id = $this->db->lastInsertId();
                }
                if(!$has_image_default and ($pic_info['gimage_id']==$goods_info['image_default'])){
                    $sql = "update ".DB_PREFIX."goods set image_default = ".$gimage_id.",thumbnail_pic= '".$pic_info['thumbnail']."',small_pic='".$pic_info['small']."',big_pic='".$pic_info['big']."' where supplier_id = ".$supplier_id." and supplier_goods_id = ".$supplier_goods_id." ";
                    $this->db->exec($sql);
                    $has_image_default = true;
                }
            }
        }
         $this->api_response('true',false,'succ');
     }
     
     function create_supplier_rel($data){
         @constant( "DEBUG_API" ) and $this->write_log($data,'ref');
         $supplier_id = intval($data['supplier_id']);
         $oPlatform = $this->system->loadModel('distribution/platformsync');
         $localRelateData = $oPlatform->getRelationData($supplier_id);
         if(!$data_struct = $this->decode_data_struct($data['data_struct'])) $this->api_response('fail','no data_struct');
         foreach($data_struct as $info){
            if(!$local_type_id=$localRelateData['type'][$info['type_id']]) continue; 
            $this->set_supplier_goods_type_spec($local_type_id,$info['ts_xml_values'], $localRelateData);
            $this->set_supplier_type_brand($local_type_id,$info['tb_xml_values'], $localRelateData);
         }
         $this->api_response('true',false,'succ');
     }
     
     
     function set_supplier_goods_type_spec($local_type_id,&$tc_xml_values,&$localRelateData){
         foreach($tc_xml_values as $k=>$v){
               $bind_spec_id = $localRelateData['spec'][$v['spec_id']];
               if(!$this->db->selectrow("SELECT * FROM ".DB_PREFIX."goods_type_spec WHERE spec_id=".intval($bind_spec_id)." AND type_id=".intval($local_type_id))){
                    $rs = $this->db->query("SELECT * FROM ".DB_PREFIX."goods_type_spec WHERE 0=1");
                    $sql = $this->db->GetInsertSQL($rs,array('spec_id'=>$bind_spec_id,'type_id'=>$local_type_id,'spec_style'=>$v['spec_style']));
                    $this->db->exec($sql);
               }
         }
     }
     
     function set_supplier_type_brand($local_type_id,&$tb_xml_values,&$localRelateData){
         foreach($tb_xml_values as $k=>$v){
               $bind_brand_id = $localRelateData['brand'][$v['brand_id']];
               
               if(!$this->db->selectrow("SELECT * FROM ".DB_PREFIX."type_brand WHERE type_id=".intval($local_type_id)." AND brand_id=".intval($bind_brand_id))){
                       $rs = $this->db->query("SELECT * FROM ".DB_PREFIX."type_brand WHERE 0=1");
                        $sql = $this->db->GetInsertSQL($rs,array('type_id'=>$local_type_id,'brand_id'=>$bind_brand_id));
                        $this->db->exec($sql);
               }
         }
     }
     
     function create_supplier_prices($data){
         @constant( "DEBUG_API" ) and $this->write_log($data,'prices');
         $supplier_id = intval($data['supplier_id']);
         if(!$data_struct = $this->decode_data_struct($data['data_struct'])) $this->api_response('fail','no data_struct');
         foreach($data_struct as $price_info){
            $sync_goods = false;
            if(!$local_goods_id = $this->getSingleData('goods','goods_id',' where supplier_id = '.$supplier_id.' and supplier_goods_id = '.$price_info['goods_id'])) continue;
             foreach($price_info['price'] as $key => $val){
                  if(!$local_bn = $this->getSingleData('supplier_pdtbn','local_bn',' where supplier_id = '.$supplier_id.' and source_bn = \''.$key.'\' ')) continue;
                  $sql = "update ".DB_PREFIX."products set cost='".$val."' where bn = '".$local_bn."' ";
                  if(!$this->db->exec($sql)) $this->write_log(print_r($price_info['price'],true), 'price_error');
                  if(!$sync_goods){
                     $this->db->exec("update ".DB_PREFIX."goods set cost='".$val."' where goods_id = ".$local_goods_id);
                     $sync_goods = true;
                  }
             }
         }
         $this->api_response('true',false,'succ');
     }
     
      function set_sync_goods_store($data){
         @constant( "DEBUG_API" ) and $this->write_log($data,'store');
         $supplier_id = intval($data['supplier_id']);
         if(!$data_struct = $this->decode_data_struct($data['data_struct'])) $this->api_response('fail','no data_struct');
         foreach($data_struct as $store_info){
             if(!$goods_id = $this->getSingleData('goods','goods_id',' where supplier_id = '.$supplier_id.' and supplier_goods_id = '.trim($store_info['goods_id']))) continue;
             $goods_store=null;
             foreach($store_info['products'] as $bn=>$store){
                 if(!$local_bn = $this->getSingleData('supplier_pdtbn','local_bn',' where source_bn = \''.trim($bn).'\' ')) continue;
                 $sql = "update ".DB_PREFIX."products set store = '".$store."' where bn = '".$local_bn."' ";
                 if(!$this->db->exec($sql)) $this->write_log(print_r($store_info['products'],true), 'product_store_error');
                 if(is_null($store)){
                      $goods_store=null;
                      break;
                 }else{
                      $goods_store+=$store;
                 }
             }
              $usql = 'update '.DB_PREFIX.'goods set store = '.$goods_store.' where goods_id = '.$goods_id;

             $this->db->exec($usql);
             
         }
         $this->api_response('true',false,'succ');
     }
     
     function set_shop_type($data){
        $shop_type = trim($data['shop_type']);//ZFZ:批零店 DS:子店
        $shop_url  = trim($data['shop_url']);
        if(!$this->system->getConf('system.b2b_shop_url')) $this->db->exec("INSERT INTO `sdb_sitemaps` (p_node_id,node_type,depth,path,title,action,manual,item_id,p_order,hidden,child_count)VALUES ( '0', 'pageurl', '0', null, '代理商入驻', '".$shop_url."', '1', '1', '6', 'false', null)");
        $this->system->setConf('system.b2c_shop_type',$shop_type);
        $this->system->setConf('system.b2b_shop_url',$shop_url);
        $shop_type == 'DS' and $this->system->setConf('certificate.distribute',true);//子母店开启分销权限
        $this->api_response('true',false,'succ');
     }
     
     function get_b2b_products_price($data){
          if(!$supplier_goods_id = intval($data['supplier_goods_id'])) $this->api_response('fail','no goodsid');
          $supplier_id = intval($data['supplier_id']);
          $sql='select p.`name`,p.price from sdb_goods as g 
          LEFT JOIN sdb_products as p on g.goods_id = p.goods_id WHERE g.supplier_id = '.$supplier_id.' and g.supplier_goods_id = '.$supplier_goods_id.' order by p.price';
          
          if($row = $this->db->selectrow($sql)){
              $this->api_response('true',false,$row);
          }else{
              $this->api_response('fail','no data');
          }
     }
     
     /**
     * 检查是否是远程图片，判断前7位是否为imgget:，如果是则是本地图片，否则为远程图片
     *
     * @param string $image_path
     * @return boolean，是远程图片就返回true，不然返回false
     */
    function _checkRemoteImage($image_path){
        $check = substr($image_path,0,7);
        if($check == 'imgget:'){
            return false;
        }else{
            return true;
        }
    }
    
    function write_log($data,$log_name){
        $fold = HOME_DIR.'/logs/platform';
        !is_dir($fold) and mkdir_p($fold);
        $file_path = $fold.'/'.date('Ymd',time()).'_'.$log_name.'.log';
        error_log(date("Y-m-d H:i:s",time())."\n".print_r($data,true),3,$file_path);
    }
    
    function getSingleData($table,$params='*',$where=''){
        $sql= 'select '.$params.' from '.DB_PREFIX.$table.' '.$where;
        if(!$row=$this->db->selectrow($sql)) return '';
        return $params=='*'?$row:$row[$params];
    }
    
    function decode_data_struct($data_struct){
       $data_struct = json_decode($data_struct,true);
       $data_struct or $data_struct = json_decode(stripcslashes($data_struct),true);
       if($data_struct) return $data_struct;
       $this->write_log($data_struct, 'data_struct_error');
       return false;
    }
}