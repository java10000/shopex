<?php
/**
* @table gimages;
*
* @package Schemas
* @version $
* @copyright 2003-2009 ShopEx
* @license Commercial
*/

$db['gimages']=array (
  'columns' =>
  array (
    'gimage_id' =>
    array (
      'type' => 'number',
      'required' => true,
      'pkey' => true,
      'extra' => 'auto_increment',
      'editable' => false,
    ),
    'goods_id' =>
    array (
      'type' => 'object:goods/products',
      'editable' => false,
    ),
    'is_remote' =>
    array (
      'type' => 'bool',
      'default' => 'false',
      'required' => true,
      'editable' => false,
    ),
    'source' =>
    array (
      'type' => 'varchar(255)',
      'required' => true,
      'default' => '',
      'editable' => false,
    ),
    'orderby' =>
    array (
      'type' => 'tinyint unsigned',
      'default' => 0,
      'required' => true,
      'editable' => true,
    ),
    'src_size_width' =>
    array (
      'type' => 'int unsigned',
      'required' => true,
      'default' => 0,
      'editable' => false,
    ),
    'src_size_height' =>
    array (
      'type' => 'int unsigned',
      'required' => true,
      'default' => 0,
      'editable' => false,
    ),
    'small' =>
    array (
      'type' => 'varchar(255)',
      'editable' => false,
    ),
    'big' =>
    array (
      'type' => 'varchar(255)',
      'editable' => false,
    ),
    'thumbnail' =>
    array (
      'type' => 'varchar(255)',
      'editable' => false,
    ),
    'up_time' =>
    array (
      'type' => 'int unsigned',
      'required' => true,
      'default' => 0,
      'editable' => false,
    ),
    
    'supplier_id' =>
    array (
      'type' => 'int unsigned',
    ),
    
    'supplier_gimage_id' =>
    array (
      'type' => 'number',
    ),
    'sync_time' =>
    array (
      'type' => 'int unsigned',
      'default' => 0,
    ),

  ),
  'index' =>
  array (
    'ind_up_time' =>
    array (
      'columns' =>
      array (
        0 => 'up_time',
      ),
    ),
  ),
);