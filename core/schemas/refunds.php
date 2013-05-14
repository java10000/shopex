<?php
/**
* @table refunds;
*
* @package Schemas
* @version $
* @copyright 2003-2009 ShopEx
* @license Commercial
*/

$db['refunds']=array (
  'columns' => 
  array (
    'refund_id' => 
    array (
      'type' => 'bigint unsigned',
      'required' => true,
      'pkey' => true,
      'extra' => 'auto_increment',
      'label' => __('退款单号'),
      'width' => 110,
      'editable' => false,
      'searchtype'=>'has',
      'filtertype'=>'yes',
      'filterdefalut'=>true,
    ),
    'order_id' => 
    array (
      'type' => 'object:trading/order',
      'label' => __('订单号'),
      'width' => 110,
      'editable' => false,
      'searchtype'=>'tequal',
      'filtertype'=>'normal',
      'filterdefalut'=>true,
    ),
    'member_id' => 
    array (
      'type' => 'object:member/member',
      'label' => __('会员用户名'),
      'width' => 75,
      'editable' => false,
      'filtertype'=>'yes',
      'filterdefalut'=>true,
    ),
    'account' => 
    array (
      'type' => 'varchar(50)',
      'label' => __('退款帐号'),
      'width' => 110,
      'editable' => false,
      'searchtype'=>'tequal',
      'filtertype'=>'normal',
    ),
    'bank' => 
    array (
      'type' => 'varchar(50)',
      'label' => __('退款银行'),
      'width' => 110,
      'editable' => false,
      'filtertype'=>'normal',
      'filterdefalut'=>true,
    ),
    'pay_account' => 
    array (
      'type' => 'varchar(250)',
      'label' => __('退款人'),
      'width' => 75,
      'editable' => false,
      'filtertype'=>'normal',
      'filterdefalut'=>true,
    ),
    'currency' => 
    array (
      'type' => 'object:system/cur',
      'label' => __('货币'),
      'width' => 75,
      'editable' => false,
      'filtertype'=>'yes',
    ),
    'money' => 
    array (
      'type' => 'money',
      'default' => '0',
      'required' => true,
      'label' => __('金额'),
      'width' => 75,
      'editable' => false,
      'searchtype'=>'nequal',
      'filtertype'=>'number',
      'filterdefalut'=>true,
    ),
    'pay_type' => 
    array (
      'type' => 
      array (
        'online' => __('在线支付'),
        'offline' => __('线下支付'),
        'deposit' => __('预存款支付'),
        'recharge' => __('预存款充值'),
      ),
      'default' => 'offline',
      'label' => __('支付类型'),
      'width' => 110,
      'editable' => false,
      'filtertype'=>'yes',
    ),
    'payment' => 
    array (
      'type' => 'number',
      'required' => true,
      'default' => 0,
      'editable' => false,
    ),
    'paymethod' => 
    array (
      'type' => 'varchar(100)',
      'label' => __('支付方式'),
      'width' => 110,
      'editable' => false,
      'filtertype'=>'normal',
      'filterdefalut'=>true,
    ),
    'ip' => 
    array (
      'type' => 'varchar(20)',
      'editable' => false,
    ),
    't_ready' => 
    array (
      'type' => 'time',
      'required' => true,
      'default' => 0,
      'label' => __('单据创建时间'),
      'width' => 110,
      'editable' => false,
      'hidden'=>true,
    ),
    't_sent' => 
    array (
      'type' => 'time',
      'label' => __('退款时间'),
      'width' => 110,
      'editable' => false,
      'filtertype'=>'time',
    ),
    't_received' => 
    array (
      'type' => 'int unsigned',
      'editable' => false,
    ),
    'status' => 
    array (
      'type' => 
      array (
        'ready' => __('准备中'),
        'progress' => __('正在退款'),
        'sent' => __('款项已退'),
        'received' => __('用户收到退款'),
        'cancel' => __('已取消'),
      ),
      'default' => 'ready',
      'required' => true,
      'label' => __('状态'),
      'width' => 75,
      'editable' => false,
    ),
    'memo' => 
    array (
      'type' => 'longtext',
      'editable' => false,
    ),
    'title' => 
    array (
      'type' => 'varchar(255)',
      'required' => true,
      'default' => '',
      'editable' => false,
    ),
    'send_op_id' => 
    array (
      'type' => 'object:admin/operator',
      'label' => __('操作员'),
      'width' => 110,
      'editable' => false,
      'filtertype'=>'yes',
    ),
    'disabled' => 
    array (
      'type' => 'bool',
      'default' => 'false',
      'required' => true,
      'editable' => false,
    ),
    'trade_no' => 
    array (
      'type' => 'varchar(30)',
      'editable' => false,
    ),
  ),
  'comment' => '存放发给用户的款项记录',
  'index' => 
  array (
    'ind_disabled' => 
    array (
      'columns' => 
      array (
        0 => 'disabled',
      ),
    ),
  ),
);