<?php
/**
* @table gnotify;
*
* @package Schemas
* @version $
* @copyright 2003-2009 ShopEx
* @license Commercial
*/

$db['gnotify']=array (
  'columns' =>
  array (
    'gnotify_id' =>
    array (
      'type' => 'number',
      'required' => true,
      'pkey' => true,
      'extra' => 'auto_increment',
      'comment' => __('会员id'),
      'width' => 110,
      'editable' => false,
    ),
    'goods_id' =>
    array (
      'type' => 'object:goods/products',
      'label' => __('缺货商品名称'),
      'width' => 270,
      'editable' => false,
    ),
    'member_id' =>
    array (
      'type' => 'object:member/member',
      'label' => __('会员用户名'),
      'width' => 75,
      'editable' => false,
    ),
    'product_id' =>
    array (
      'type' => 'object:goods/products',
      'label' => __('缺货状态'),
      'width' => 75,
      'editable' => false,
    ),
    'email' =>
    array (
      'type' => 'varchar(200)',
      'label' => 'Email',
      'width' => 150,
      'editable' => false,
    ),
    'status' =>
    array (
      'type' =>
      array (
        'ready' => __('准备发送'),
        'send' => __('已发送'),
        'progress' => __('发送中'),
      ),
      'default' => 'ready',
      'required' => true,
      'label' => __('通知状态'),
      'width' => 75,
      'editable' => false,
    ),
    'send_time' =>
    array (
      'type' => 'time',
      'label' => __('通知时间'),
      'width' => 75,
      'editable' => false,
    ),
    'creat_time' =>
    array (
      'type' => 'time',
      'label' => __('登记时间'),
      'width' => 75,
      'editable' => false,
    ),
    'disabled' =>
    array (
      'type' => 'bool',
      'default' => 'false',
      'required' => true,
      'editable' => false,
    ),
    'remark' =>
    array (
      'type' => 'longtext',
      'editable' => false,
    ),
  ),
  'comment' => '商品缺货通知表',
  'index' =>
  array (
    'ind_goods' =>
    array (
      'columns' =>
      array (
        0 => 'goods_id',
        1 => 'product_id',
        2 => 'member_id',
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