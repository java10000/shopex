<?php
/**
* @table magicvars;
*
* @package Schemas
* @version $
* @copyright 2003-2009 ShopEx
* @license Commercial
*/

$db['magicvars']=array (
  'columns' => 
  array (
    'var_name' => 
    array (
      'type' => 'varchar(20)',
      'required' => true,
      'pkey' => true,
      'label' => __('变量名'),
      'class' => 'span-3',
      'editable' => false,
    ),
    'var_title' => 
    array (
      'type' => 'varchar(100)',
      'label' => __('名称'),
      'class' => 'span-3',
      'editable' => false,
    ),
    'var_remark' => 
    array (
      'type' => 'varchar(100)',
      'required' => true,
      'label' => __('备注'),
      'class' => 'span-3',
      'editable' => false,
    ),
    'var_value' => 
    array (
      'type' => 'text',
      'hidden' => true,
      'label' => __('变量值'),
      'class' => 'span-4',
      'editable' => false,
    ),
    'var_type' => 
    array (
      'type' => 
      array (
        'system' => __('系统'),
        'custom' => __('自定义'),
      ),
      'default' => 'custom',
      'required' => true,
      'label' => __('变量类型'),
      'class' => 'span-2',
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
);