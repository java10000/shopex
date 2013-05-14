<?php
/**
* @table return_product;
*
* @package Schemas
* @version $
* @copyright 2003-2009 ShopEx
* @license Commercial
*/

$db['return_product']=array (
  'columns' => 
  array (
    'order_id' => 
    array (
      'type' => 'object:trading/order',
      'default' => 0,
      'required' => true,
      'searchable' => true,
      'label' => __('订单号'),
      'width' => 110,
      'editable' => false,
    ),
    'member_id' => 
    array (
      'type' => 'object:member/member',
      'default' => 0,
      'required' => true,
      'searchable' => true,
      'label' => __('申请人'),
      'width' => 110,
      'editable' => false,
    ),
    'return_id' => 
    array (
      'type' => 'bigint unsigned',
      'required' => true,
      'pkey' => true,
      'extra' => 'auto_increment',
      'label' => 'ID',
      'width' => 150,
      'editable' => false,
    ),
    'title' => 
    array (
      'type' => 'varchar(200)',
      'default' => '',
      'required' => true,
      'label' => __('售后服务标题'),
      'width' => 310,
      'fuzzySearch' => 1,
      'editable' => false,
    ),
    'content' => 
    array (
      'type' => 'longtext',
      'editable' => false,
    ),
    'status' => 
    array (
      'type' => 
      array (
        1 => __('申请中'),
        2 => __('审核中'),
        3 => __('接受申请'),
        4 => __('完成'),
        5 => __('拒绝'),
      ),
      'default' => 1,
      'required' => true,
      'label' => __('处理状态'),
      'width' => 75,
      'editable' => false,
    ),
    'image_file' => 
    array (
      'type' => 'varchar(255)',
      'default' => '',
      'required' => true,
      'editable' => false,
    ),
    'product_data' => 
    array (
      'type' => 'longtext',
      'editable' => false,
    ),
    'comment' => 
    array (
      'type' => 'longtext',
      'editable' => false,
    ),
    'add_time' => 
    array (
      'type' => 'time',
      'default' => 0,
      'required' => true,
      'label' => __('售后处理时间'),
      'width' => 110,
      'editable' => false,
    ),
    'disabled' => 
    array (
      'type' => 'bool',
      'default' => 'false',
      'required' => true,
      'editable' => false,
    ),
  ),
  'comment' => '退货记录表',
);