<?php
/**
* @table goods_keywords;
*
* @package Schemas
* @version $
* @copyright 2003-2009 ShopEx
* @license Commercial
*/

$db['goods_keywords']=array (
  'columns' => 
  array (
    'goods_id' => 
    array (
      'type' => 'object:goods/products',
      'required' => true,
      'default' => 0,
      'pkey' => true,
      'editable' => false,
    ),
    'keyword' => 
    array (
      'type' => 'varchar(40)',
      'default' => '',
      'required' => true,
      'pkey' => true,
      'editable' => false,
    ),
    'refer'=>array(
      'type' => 'varchar(255)',
      'default' => '',
      'required' => false,
      'editable' => false
    ),
    'res_type'=>array(
      'type' => 'enum(\'goods\',\'article\')',
      'default' => 'goods',
      'required' => true,
      'pkey' => true,
      'editable' => false
    )
  ),
  'pkeys'=>array(
      'keyword','goods_id','res_type'
  )
);