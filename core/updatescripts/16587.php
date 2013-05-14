<?php
$system = &$GLOBALS['system'];
$o = &$system->loadModel('system/local');
$defaultlocal = defined('DEFAULT_LOCAL')?DEFAULT_LOCAL:'mainland';
if($o->get_default()){
    echo update_message('无需重新设置本地包数据.');
}elseif(!$o->use_package($defaultlocal)){
    echo update_message('无法设置默认本地包数据.',E_WARNING);
}else{
    echo update_message('已设置本地包数据');
}

if($address = $system->getConf('store.address')){
    if(substr($address,-1,1)!='/'){
        $address .= '/';
    }
    if(substr($address,0,4)!='http'){
        $address = 'http://'.$address;
    }
    $system->setConf('store.address',$address);
}

$db = &$system->database();
$advanceList = $db->select("SELECT * FROM sdb_advance_logs");
$advanceCheck = true;
foreach( $advanceList as $adv ){
    $importMoney = $adv['money']>0?$adv['money']:0;
    $explodeMoney = $adv['money']<0?-$adv['money']:0;
    $memo = '';
    if( strstr($adv['message'],'预存款支付') )
        $memo = '购物消费';
    else if( strstr($adv['message'],'预存款退款') )
        $memo = '预存款退款';
    else if( strstr($adv['message'],'预存款充值') )
        $memo = '预存款充值';
    else{
        if( $adv['money']>0 )
            $memo = '管理员代充值';
        else
            $memo = '管理员代扣费';
    }
    $paymentId = strstr( $adv['message'], '#P{' ) ;
    if( $paymentId )
        $paymentId = substr( $paymentId, 3,14 );
    $orderId = strstr( $adv['message'], '#O{' );
    if( $orderId )
        $orderId = substr( $orderId, 3 , 14 );
    $paymethod = '';
    if($paymentId){
        $row = $db->selectrow('SELECT `paymethod` FROM `sdb_payments` WHERE `payment_id` = '.$paymentId);
        $paymethod = $row['paymethod'];
    }
    $memberAdvance = $db->selectrow('SELECT SUM(`money`) as msum FROM sdb_advance_logs WHERE `mtime` <= "'.$adv['mtime'].'" AND `member_id` = '.$adv['member_id']);
    $memberAdvance = $memberAdvance['msum'];
    $shopAdvance = $db->selectrow('SELECT SUM(`money`) as ssum FROM sdb_advance_logs WHERE `mtime` <= "'.$adv['mtime'].'" ');
    $shopAdvance = $shopAdvance['ssum'];
    if ( !$db->exec('UPDATE `sdb_advance_logs` SET `import_money`= '.$importMoney.', `explode_money` = '.$explodeMoney.' , `memo`= "'.$memo.'" , `payment_id`= "'.$paymentId.'" , `order_id`= "'.$orderId.'" , `paymethod`= "'.$paymethod.'" , `member_advance` = '.$memberAdvance.', `shop_advance` = "'.$shopAdvance.'" WHERE log_id = '.$adv['log_id']) ){
        $advanceCheck = false;
        break;
    }
}
if($advanceCheck)
    echo update_message('已更新预存款日志');
else
    echo update_message('更新预存款日志失败',E_WARNING);

?>
