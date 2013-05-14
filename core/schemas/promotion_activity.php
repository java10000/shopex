<?php
/**
* @table promotion_activity;
*
* @package Schemas
* @version $
* @copyright 2003-2009 ShopEx
* @license Commercial
*/

$db['promotion_activity']=array (
  'columns' =>
  array (
    'pmta_id' =>
    array (
      'type' => 'number',
      'required' => true,
      'pkey' => true,
      'extra' => 'auto_increment',
      'label' => __('序号'),
      'width' => 30,
      'editable' => false,
    ),
    'pmta_name' =>
    array (
      'type' => 'varchar(200)',
      'label' => __('活动名称'),
      'width' => 230,
      'searchable' => true,
      'editable' => true,
    ),
    'pmta_enabled' =>
    array (
      'type' => 'bool',
      'label' => __('发布'),
      'width' => 30,
      'editable' => true,
    ),
    'pmta_time_begin' =>
    array (
      'type' => 'time',
      'label' => __('开始时间'),
      'width' => 75,
      'editable' => false,
    ),
    'pmta_time_end' =>
    array (
      'type' => 'time',
      'label' => __('结束时间'),
      'width' => 75,
      'editable' => false,
    ),
    'pmta_describe' =>
    array (
      'type' => 'longtext',
      'label' => __('详细描述'),
      'width' => 180,
      'editable' => false,
    ),
    'disabled' =>
    array (
      'type' => 'bool',
      'default' => 'false',
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