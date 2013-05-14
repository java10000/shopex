<?php
/**
* @table dly_center;
*
* @package Schemas
* @version $
* @copyright 2003-2009 ShopEx
* @license Commercial
*/

$db['dly_center']=array (
  'columns' => 
  array (
    'dly_center_id' => 
    array (
      'type' => 'int unsigned',
      'required' => true,
      'pkey' => true,
      'extra' => 'auto_increment',
      'label' => __('ID'),
      'width' => 30,
      'editable' => false,
    ),
    'name' => 
    array (
      'type' => 'varchar(50)',
      'required' => true,
      'default' => '',
      'label' => __('发货点名称'),
      'width' => 150,
      'editable' => true,
    ),
    'address' => 
    array (
      'type' => 'varchar(200)',
      'label' => __('地址'),
      'width' => 180,
      'required' => true,
      'editable' => true,
    ),
    'region' => 
    array (
      'type' => 'region',
      'label' => __('地区'),
      'width' => 180,
      'editable' => false,
    ),
    'zip' => 
    array (
      'type' => 'varchar(20)',
      'label' => __('邮编'),
      'width' => 75,
      'editable' => true,
    ),
    'phone' => 
    array (
      'type' => 'varchar(100)',
      'label' => __('电话'),
      'width' => 110,
      'editable' => true,
    ),
    'uname' => 
    array (
      'type' => 'varchar(100)',
      'label' => __('发货人'),
      'width' => 110,
      'editable' => true,
    ),
    'cellphone' => 
    array (
      'type' => 'varchar(100)',
      'label' => __('手机'),
      'width' => 110,
      'editable' => true,
    ),
    'sex' => 
    array (
      'type' => 
      array (
        'female' => __('女士'),
        'male' => __('先生'),
      ),
      'label' => __('性别'),
      'width' => 30,
      'editable' => true,
    ),
    'memo' => 
    array (
      'type' => 'longtext',
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
  'comment' => '发货点表',
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