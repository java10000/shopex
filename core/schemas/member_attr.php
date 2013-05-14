<?php
/**
* @table member_attr;
*
* @package Schemas
* @version $
* @copyright 2003-2009 ShopEx
* @license Commercial
*/

$db['member_attr']=array (
  'columns' => 
  array (
    'attr_id' => 
    array (
      'type' => 'int unsigned',
      'required' => true,
      'pkey' => true,
      'extra' => 'auto_increment',
      'label' => __('选项ID'),
      'width' => 110,
      'editable' => false,
    ),
    'attr_name' => 
    array (
      'type' => 'varchar(20)',
      'default' => '',
      'required' => true,
      'label' => __('选项名称'),
      'width' => 110,
      'editable' => false,
    ),
    'attr_type' => 
    array (
      'type' => 'varchar(20)',
      'default' => '',
      'required' => true,
      'editable' => false,
    ),
    'attr_required' => 
    array (
      'type' => 'bool',
      'default' => 'false',
      'required' => true,
      'editable' => false,
    ),
    'attr_search' => 
    array (
      'type' => 'bool',
      'default' => 'false',
      'required' => true,
      'label' => __('搜索'),
      'width' => 110,
      'editable' => false,
    ),
    'attr_option' => 
    array (
      'type' => 'text',
      'editable' => false,
    ),
    'attr_valtype' => 
    array (
      'type' => 'varchar(20)',
      'default' => '',
      'required' => true,
      'editable' => false,
    ),
    'disabled' => 
    array (
      'type' => 'bool',
      'default' => 'false',
      'required' => true,
      'editable' => false,
    ),
    'attr_tyname' => 
    array (
      'type' => 'varchar(20)',
      'default' => '',
      'required' => true,
      'label' => __('选项类型'),
      'width' => 110,
      'editable' => false,
    ),
    'attr_group' => 
    array (
      'type' => 'varchar(20)',
      'default' => '',
      'required' => true,
      'label' => __('选项类别'),
      'width' => 110,
      'editable' => false,
    ),
    'attr_show' => 
    array (
      'type' => 'bool',
      'default' => 'true',
      'required' => true,
      'label' => __('显示'),
      'width' => 110,
      'editable' => false,
    ),
    'attr_order' => 
    array (
      'type' => 'int unsigned',
      'default' => 0,
      'required' => true,
      'label' => __('排序'),
      'width' => 110,
      'editable' => false,
    ),
  ),
);