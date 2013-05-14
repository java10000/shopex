<?php
$system = &$GLOBALS['system'];

$db = &$system->database();
$advanceCheck = true;
$memattr = &$system->loadModel('member/memberattr');
$tmpdata = $db->select("select attr_id from sdb_member_attr");
if(count($tmpdata)==0){
    $attrarray = array(
                        array('attr_id'=>'1','attr_name'=>'地区','attr_type'=>'area','attr_required'=>'false','attr_search'=>'false','attr_option'=>'','attr_show'=>'true','attr_valtype'=>'','attr_tyname'=>'系统默认','attr_order'=>'4','attr_group'=>'defalut'),
                        array('attr_id'=>'2','attr_name'=>'联系地址','attr_type'=>'addr','attr_required'=>'false','attr_search'=>'false','attr_option'=>'','attr_show'=>'true','attr_valtype'=>'','attr_tyname'=>'系统默认','attr_order'=>'5','attr_group'=>'defalut'),
                        array('attr_id'=>'3','attr_name'=>'姓名','attr_type'=>'name','attr_required'=>'false','attr_search'=>'false','attr_option'=>'','attr_show'=>'true','attr_valtype'=>'','attr_tyname'=>'系统默认','attr_order'=>'1','attr_group'=>'defalut'),
                        array('attr_id'=>'4','attr_name'=>'移动电话','attr_type'=>'mobile','attr_required'=>'false','attr_search'=>'false','attr_option'=>'','attr_show'=>'true','attr_valtype'=>'number','attr_tyname'=>'系统默认','attr_order'=>'7','attr_group'=>'defalut'),
                        array('attr_id'=>'5','attr_name'=>'固定电话','attr_type'=>'tel','attr_required'=>'false','attr_search'=>'false','attr_option'=>'','attr_show'=>'true','attr_valtype'=>'number','attr_tyname'=>'系统默认','attr_order'=>'8','attr_group'=>'defalut'),
                        array('attr_id'=>'6','attr_name'=>'邮编','attr_type'=>'zip','attr_required'=>'false','attr_search'=>'false','attr_option'=>'','attr_show'=>'true','attr_valtype'=>'number','attr_tyname'=>'系统默认','attr_order'=>'6','attr_group'=>'defalut'),    
                        array('attr_id'=>'7','attr_name'=>'性别','attr_type'=>'sex','attr_required'=>'false','attr_search'=>'false','attr_option'=>'','attr_show'=>'true','attr_valtype'=>'','attr_tyname'=>'系统默认','attr_order'=>'2','attr_group'=>'defalut'),
                        array('attr_id'=>'8','attr_name'=>'出生日期','attr_type'=>'date','attr_required'=>'false','attr_search'=>'false','attr_option'=>'','attr_show'=>'true','attr_valtype'=>'','attr_tyname'=>'系统默认','attr_order'=>'3','attr_group'=>'defalut'),
                        array('attr_id'=>'9','attr_name'=>'安全问题','attr_type'=>'pw_question','attr_required'=>'false','attr_search'=>'false','attr_option'=>'','attr_show'=>'true','attr_valtype'=>'','attr_tyname'=>'系统默认','attr_order'=>'9','attr_group'=>'defalut'),
                        array('attr_id'=>'10','attr_name'=>'回答','attr_type'=>'pw_answer','attr_required'=>'false','attr_search'=>'false','attr_option'=>'','attr_show'=>'true','attr_valtype'=>'','attr_tyname'=>'系统默认','attr_order'=>'10','attr_group'=>'defalut'),
                        array('attr_id'=>'11','attr_name'=>'QQ','attr_type'=>'text','attr_required'=>'false','attr_search'=>'false','attr_option'=>'','attr_show'=>'false','attr_valtype'=>'','attr_tyname'=>'QQ','attr_order'=>'11','attr_group'=>'contact'),
                        array('attr_id'=>'12','attr_name'=>'MSN','attr_type'=>'text','attr_required'=>'false','attr_search'=>'false','attr_option'=>'','attr_show'=>'false','attr_valtype'=>'email','attr_tyname'=>'MSN','attr_order'=>'12','attr_group'=>'contact'),
                        array('attr_id'=>'13','attr_name'=>'Skype','attr_type'=>'text','attr_required'=>'false','attr_search'=>'false','attr_option'=>'','attr_show'=>'false','attr_valtype'=>'alphaint','attr_tyname'=>'Skype','attr_order'=>'13','attr_group'=>'contact'),
                        array('attr_id'=>'14','attr_name'=>'旺旺','attr_type'=>'text','attr_required'=>'false','attr_search'=>'false','attr_option'=>'','attr_show'=>'false','attr_valtype'=>'','attr_tyname'=>'旺旺','attr_order'=>'14','attr_group'=>'contact'),
                        );
    foreach($attrarray as $k => $v){
        $memattr->save($v);    
    }

    echo update_message('已增加会员自定义注册项');
}

$oCat = &$system->loadModel('goods/productCat');
$aCat = $oCat->getList('cat_id','',0,-1);
foreach($aCat as $row){
    $oCat->updateChildCount($row['cat_id']);
}


$system = &$GLOBALS['system'];

$db = &$system->database();

$db->exec('DELETE FROM sdb_sell_logs');

$itemCount = 100;
$itemstart = 0;
while( $itemCount == 100 ){

    $items = $db->select('SELECT di.product_id , p.name, p.pdt_desc , di.number , d.member_id , o.ship_email , d.t_begin , o.member_id, p.price , p.goods_id ,m.uname
                                        FROM sdb_delivery_item di 
                                        LEFT JOIN sdb_delivery d ON d.delivery_id = di.delivery_id 
                                        LEFT JOIN sdb_orders o ON d.order_id = o.order_id 
                                        LEFT JOIN sdb_products p ON p.product_id = di.product_id
                                        LEFT JOIN sdb_members m ON d.member_id = m.member_id
                                        WHERE d.type = "delivery" AND di.item_type = "goods" AND o.disabled = "false"  LIMIT '.$itemstart.','.$itemCount);

    $sql = 'INSERT INTO sdb_sell_logs ( member_id , name , price , goods_id , product_id , product_name, pdt_desc , number , createtime ) VALUES ';
    $addSql = array();
    $itemi = 0;
    foreach( $items as $v ){
        $itemdata = array(
            $v['member_id']?$v['member_id']:0,
            $v['uname']?$v['uname']:$v['ship_email'],
            $v['price'],
            $v['goods_id'],
            $v['product_id'],
            $v['name'],
            $v['pdt_desc'],
            $v['number'],
            $v['t_begin']
        );
        $addSql[] = ' ( "'.implode('" , "', $itemdata).'" ) ';
        $itemi++;
    }

    if( !empty($addSql) ){
        $sql .= implode(' , ', $addSql);
        $db->exec($sql);
    }

    if( $itemi < 100 )
        $itemCount = $itemi;
    $itemstart += 100;

}

echo update_message('已更新销售记录');

?>