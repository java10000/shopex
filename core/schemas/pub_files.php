<?php
/**
* @table pub_files;
*
* @package Schemas
* @version $
* @copyright 2003-2009 ShopEx
* @license Commercial
*/

$db['pub_files']=array (
  'columns' => 
  array (
    'file_id' => 
    array (
      'type' => 'int',
      'required' => true,
      'pkey' => true,
      'extra' => 'auto_increment',
      'editable' => false,
    ),
    'file_name' => 
    array (
      'type' => 'varchar(50)',
      'label' => __('文件名'),
      'width' => 110,
      'editable' => false,
    ),
    'file_ident' => 
    array (
      'type' => 'varchar(100)',
      'required' => true,
      'default' => '',
      'label' => __('文件'),
      'width' => 110,
      'editable' => false,
    ),
    'cdate' => 
    array (
      'type' => 'int unsigned',
      'required' => true,
      'default' => 0,
      'label' => __('日期'),
      'width' => 110,
      'editable' => false,
    ),
    'memo' => 
    array (
      'type' => 'varchar(250)',
      'label' => __('描述'),
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