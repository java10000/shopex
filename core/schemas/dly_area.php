<?php
/**
* @table dly_area;
*
* @package Schemas
* @version $
* @copyright 2003-2009 ShopEx
* @license Commercial
*/

$db['dly_area']=array (
  'columns' => 
  array (
    'area_id' => 
    array (
      'type' => 'mediumint(6) unsigned',
      'required' => true,
      'pkey' => true,
      'extra' => 'auto_increment',
      'label' => 'ID',
      'width' => 110,
      'editable' => false,
    ),
    'name' => 
    array (
      'type' => 'varchar(80)',
      'required' => true,
      'default' => '',
      'label' => __('配送地区'),
      'width' => 180,
      'editable' => false,
    ),
    'disabled' => 
    array (
      'type' => 'bool',
      'default' => 'false',
      'editable' => false,
    ),
    'ordernum' => 
    array (
      'type' => 'smallint(4) unsigned',
      'label' => __('排序'),
      'width' => 180,
      'editable' => true,
    ),
  ),
  'comment' => '配送地区表',
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