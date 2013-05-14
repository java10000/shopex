<?php
/**
* @table payments;
*
* @package Schemas
* @version $
* @copyright 2003-2009 ShopEx
* @license Commercial
*/

$db['payments']=array (
  'columns' => 
  array (
    'payment_id' => 
    array (
      'type' => 'varchar(20)',
      'required' => true,
      'default' => '',
      'pkey' => true,
      'label' => __('支付单号'),
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
      'searchtype'=>'nequal',
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
      'label' => __('收款账号'),
      'width' => 110,
      'searchtype'=>'tequal',
      'editable' => false,
      'filtertype'=>'normal',
      'filterdefalut'=>true,
    ),
    'bank' => 
    array (
      'type' => 'varchar(50)',
      'label' => __('收款银行'),
      'width' => 110,
      'editable' => false,
      'filtertype'=>'normal',
      'filterdefalut'=>true,
    ),
    'pay_account' => 
    array (
      'type' => 'varchar(50)',
      'label' => __('支付账户'),
      'width' => 110,
      'editable' => false,
      'filtertype'=>'normal',
    ),
    'currency' => 
    array (
      'type' => 'varchar(10)',
      'label' => __('货币'),
      'width' => 75,
      'editable' => false,
    ),
    'money' => 
    array (
      'type' => 'money',
      'default' => '0',
      'required' => true,
      'label' => __('支付金额'),
      'width' => 75,
      'searchtype'=>'nequal',
      'editable' => false,
    ),
    'paycost' => 
    array (
      'type' => 'money',
      'label' => __('支付网关费用'),
      'width' => 110,
      'editable' => false,
    ),
    'cur_money' => 
    array (
      'type' => 'money',
      'default' => '0',
      'required' => true,
      'editable' => false,
    ),
    'pay_type' => 
    array (
      'type' => 
      array (
        'online' => __('在线支付'),
        'offline' => __('线下支付'),
        'deposit' => __('预存款支付'),
        'recharge' => __('预存款充值'),
        'joinfee' => __('加盟费'),
      ),
      'default' => 'online',
      'required' => true,
      'label' => __('支付类型'),
      'width' => 110,
      'editable' => false,
      'filtertype'=>'yes',
      'filterdefalut'=>true,
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
    'op_id' => 
    array (
      'type' => 'object:admin/operator',
      'label' => __('操作员'),
      'width' => 110,
      'editable' => false,
      'filtertype'=>'normal',
    ),
    'ip' => 
    array (
      'type' => 'ipaddr',
      'label' => __('支付IP'),
      'width' => 110,
      'editable' => false,
    ),
    't_begin' => 
    array (
      'type' => 'time',
      'label' => __('支付开始时间'),
      'width' => 110,
      'editable' => false,
      'filtertype'=>'time',
      'filterdefalut'=>true,
    ),
    't_end' => 
    array (
      'type' => 'time',
      'label' => __('支付完成时间'),
      'width' => 110,
      'editable' => false,
    ),
    'status' => 
    array (
      'type' => 
      array (
        'succ' => __('支付成功'),
        'failed' => __('支付失败'),
        'cancel' => __('未支付'),
        'error' => __('处理异常'),
        'invalid' => __('非法参数'),
        'progress' => __('处理中'),
        'timeout' => __('超时'),
        'ready' => __('准备中'),
      ),
      'default' => 'ready',
      'required' => true,
      'label' => __('支付状态'),
      'width' => 75,
      'editable' => false,
      'filtertype'=>'yes',
      'hidden'=>true,
      'filterdefalut'=>true,
    ),
    'memo' => 
    array (
      'type' => 'longtext',
      'editable' => false,
    ),
    'disabled' => 
    array (
      'type' => 'bool',
      'default' => 'false',
      'editable' => false,
    ),
    'trade_no' => 
    array (
      'type' => 'varchar(30)',
      'editable' => false,
    ),
  ),
  'comment' => '支付记录',
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