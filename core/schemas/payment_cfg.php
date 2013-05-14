<?php
/**
* @table payment_cfg;
*
* @package Schemas
* @version $
* @copyright 2003-2009 ShopEx
* @license Commercial
*/

$db['payment_cfg']=array (
  'columns' =>
  array (
    'id' =>
    array (
      'type' => 'mediumint',
      'required' => true,
      'pkey' => true,
      'extra' => 'auto_increment',
      'label' => __('支付方式ID'),
      'width' => 110,
      'editable' => false,
      'hidden'=>true,
    ),
    'custom_name' =>
    array (
      'type' => 'varchar(100)',
      'label' => __('支付方式名称'),
      'width' => 230,
      'editable' => true,
    ),
    'pay_type' =>
    array (
      'type' => 'varchar(30)',
      'required' => true,
      'default' => '',
      'editable' => false,
    ),
    'config' =>
    array (
      'type' => 'longtext',
      'editable' => false,
    ),
    'fee' =>
    array (
      'type' => 'decimal(9,5)',
      'default' => '0',
      'required' => true,
      'editable' => false,
    ),
    'des' =>
    array (
      'type' => 'longtext',
      'editable' => false,
    ),
    'order_num' =>
    array (
      'type' => 'smallint(3) unsigned',
      'default' => 0,
      'required' => true,
      'editable' => false,
    ),
    'disabled' =>
    array (
      'type' => 'bool',
      'default' => 'false',
      'editable' => false,
    ),
    'orderlist' =>
    array (
      'type' => 'number',
      'label' => __('排序'),
      'width' => 30,
      'editable' => true,
    ),
  ),

  'comment' => '支付插件实例表',
);

