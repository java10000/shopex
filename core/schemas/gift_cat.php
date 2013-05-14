<?php
/**
* @table gift_cat;
*
* @package Schemas
* @version $
* @copyright 2003-2009 ShopEx
* @license Commercial
*/

$db['gift_cat']=array (
  'columns' => 
  array (
    'giftcat_id' => 
    array (
      'type' => 'number',
      'required' => true,
      'pkey' => true,
      'extra' => 'auto_increment',
      'label' => __('ID'),
      'width' => 110,
      'editable' => false,
    ),
    'cat' => 
    array (
      'type' => 'varchar(255)',
      'label' => __('分类名称'),
      'width' => 180,
      'searchname' => true,
      'editable' => true,
    ),
    'orderlist' => 
    array (
      'type' => 'mediumint(6) unsigned',
      'label' => __('排序'),
      'width' => 110,
      'editable' => true,
    ),
    'shop_iffb' => 
    array (
      'type' => 'intbool',
      'default' => '1',
      'label' => __('是否发布'),
      'width' => 110,
      'editable' => true,
    ),
    'disabled' => 
    array (
      'type' => 'bool',
      'default' => 'false',
      'editable' => false,
    ),
  ),
  'comment' => '赠品分类表',
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