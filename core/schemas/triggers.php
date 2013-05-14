<?php
/**
* @table triggers;
*
* @package Schemas
* @version $
* @copyright 2003-2009 ShopEx
* @license Commercial
*/

$db['triggers']=array (
  'columns' =>
  array (
    'trigger_id' =>
    array (
      'type' => 'int',
      'required' => true,
      'pkey' => true,
      'extra' => 'auto_increment',
      'label' => __('网店机器人id'),
      'hidden' => 1,
      'editable' => false,
    ),
    'filter_str' =>
    array (
      'type' => 'varchar(255)',
      'required' => true,
      'default' => '',
      'label' => __('条件'),
      'width' => 300,
      'editable' => false,
    ),
    'action_str' =>
    array (
      'type' => 'varchar(255)',
      'required' => true,
      'default' => '',
      'label' => __('动作'),
      'width' => 300,
      'editable' => false,
    ),
    'trigger_event' =>
    array (
      'type' => 'varchar(100)',
      'required' => true,
      'default' => '',
      'label' => __('事件'),
      'width' => 80,
      'editable' => false,
    ),
    'trigger_memo' =>
    array (
      'type' => 'varchar(100)',
      'label' => __('备注'),
      'width' => 200,
      'editable' => false,
    ),
    'trigger_define' =>
    array (
      'type' => 'text',
      'required' => true,
      'default' => '',
      'editable' => false,
    ),
    'trigger_order' =>
    array (
      'type' => 'tinyint',
      'default' => 5,
      'required' => true,
      'editable' => false,
    ),
    'active' =>
    array (
      'type' => array(
        'true' =>'启用',
        'false' => '停用'
        ),
      'default' => 'false',
      'label' => __('状态'),
      'width' => 100,
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