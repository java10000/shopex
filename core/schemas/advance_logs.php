<?php
/**
* @table advance_logs;
*
* @package Schemas
* @version $
* @copyright 2003-2009 ShopEx
* @license Commercial
*/

$db['advance_logs']=array (
  'columns' => 
  array (
    'log_id' => 
    array (
      'type' => 'number',
      'required' => true,
      'pkey' => true,
      'extra' => 'auto_increment',
      'label' => __('日志id'),
      'width' => 110,
      'comment' => __('日志id'),
      'editable' => false,
      'hidden'=>true,
    ),
    'member_id' => 
    array (
      'type' => 'object:member/member',
      'required' => true,
      'default' => 0,
      'label' => __('用户名'),
      'width' => 110,
      'comment' => __('用户id'),
      'editable' => false,
    ),
    'money' => 
    array (
      'type' => 'money',
      'required' => true,
      'default' => 0,
      'label' => __('出入金额'),
      'width' => 110,
      'comment' => __('出入金额'),
      'editable' => false,
      'hidden'=>true,
    ),
    'message' => 
    array (
      'type' => 'varchar(255)',
      'label' => __('管理备注'),
      'width' => 110,
      'comment' => __('管理备注'),
      'editable' => true,
    ),
    'mtime' => 
    array (
      'type' => 'time',
      'required' => true,
      'default' => 0,
      'label' => __('交易时间'),
      'width' => 75,
      'comment' => __('交易时间'),
      'editable' => false,
    ),
    'payment_id' => 
    array (
      'type' => 'varchar(20)',
      'label' => __('支付单号'),
      'width' => 110,
      'comment' => __('支付单号'),
      'editable' => false,
    ),
    'order_id' => 
    array (
      'type' => 'object:trading/order',
      'label' => __('订单号'),
      'width' => 110,
      'comment' => __('订单号'),
      'editable' => false,
    ),
    'paymethod' => 
    array (
      'type' => 'varchar(100)',
      'label' => __('支付方式'),
      'width' => 110,
      'comment' => __('支付方式'),
      'editable' => false,
    ),
    'memo' => 
    array (
      'type' => 'varchar(100)',
      'label' => __('业务摘要'),
      'width' => 110,
      'comment' => __('业务摘要'),
      'editable' => false,
    ),
    'import_money' => 
    array (
      'type' => 'money',
      'default' => '0',
      'required' => true,
      'label' => __('存入金额'),
      'width' => 110,
      'comment' => __('存入金额'),
      'editable' => false,
    ),
    'explode_money' => 
    array (
      'type' => 'money',
      'default' => '0',
      'required' => true,
      'label' => __('支出金额'),
      'width' => 110,
      'comment' => __('支出金额'),
      'editable' => false,
    ),
    'member_advance' => 
    array (
      'type' => 'money',
      'default' => '0',
      'required' => true,
      'label' => __('当前余额'),
      'width' => 110,
      'comment' => __('当前余额'),
      'editable' => false,
    ),
    'shop_advance' => 
    array (
      'type' => 'money',
      'default' => '0',
      'required' => true,
      'label' => __('商店余额'),
      'width' => 110,
      'comment' => __('商店余额'),
      'editable' => false,
      'hidden'=>true,
    ),
    'disabled' => 
    array (
      'type' => 'bool',
      'default' => 'false',
      'required' => true,
      'comment' => __('失效'),
      'editable' => false,
    ),
  ),
  'comment' => __('预存款历史记录'),
  'index' => 
  array (
    'ind_mtime' => 
    array (
      'columns' => 
      array (
        0 => 'mtime',
      ),
    ),
    'ind_disabled' => 
    array (
      'columns' => 
      array (
        0 => 'disabled',
      ),
    ),
  ),
);