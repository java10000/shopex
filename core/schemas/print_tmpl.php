<?php
/**
* @table print_tmpl;
*
* @package Schemas
* @version $
* @copyright 2003-2009 ShopEx
* @license Commercial
*/

$db['print_tmpl']=array (
  'columns' =>
  array (
    'prt_tmpl_id' =>
    array (
      'type' => 'int unsigned',
      'required' => true,
      'pkey' => true,
      'extra' => 'auto_increment',
      'label' => __('ID'),
      'width' => 75,
      'editable' => false,
    ),
    'prt_tmpl_title' =>
    array (
      'type' => 'varchar(100)',
      'required' => true,
      'default' => '',
      'label' => __('单据名称'),
      'width' => 390,
      'unique' => true,
      'editable' => true,
    ),
    'shortcut' =>
    array (
      'type' => 'bool',
      'default' => 'false',
      'label' => __('是否启用'),
      'width' => 110,
      'editable' => true,
    ),
    'disabled' =>
    array (
      'type' => 'bool',
      'default' => 'false',
      'editable' => false,
    ),
    'prt_tmpl_width' =>
    array (
      'type' => 'tinyint unsigned',
      'default' => 100,
      'required' => true,
      'editable' => false,
    ),
    'prt_tmpl_height' =>
    array (
      'type' => 'tinyint unsigned',
      'default' => 100,
      'required' => true,
      'editable' => false,
    ),
    'prt_tmpl_data' =>
    array (
      'type' => 'longtext',
      'editable' => false,
    ),
  ),
);