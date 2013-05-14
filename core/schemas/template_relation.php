<?php
/**
* @table template_relation;
*
* @package Schemas
* @version $
* @copyright 2003-2009 ShopEx
* @license Commercial
*/

$db['template_relation']=array (
  'columns' =>
  array (
    'template_relation_id' =>
    array (
      'type' => 'int',
      'required' => true,
      'pkey' => true,
      'extra' => 'auto_increment',
      'label' => __('网店机器人id'),
      'hidden' => 1,
      'editable' => false,
    ),
    'source_type' =>
    array (
      'type' => 'varchar(20)',
      'required' => true,
      'default' => '',
      'label' => __('条件'),
      'width' => 300,
      'editable' => false,
    ),
    'source_id' =>
    array (
      'type' => 'int',
      'required' => true,
      'label' => __('动作'),
      'width' => 300,
      'default'=>0,
      'editable' => false,
    ),
    'template_name' =>
    array (
      'type' => 'varchar(100)',
      'required' => true,
      'default' => '',
      'width' => 80,
      'editable' => false,
    ),
    'template_type' =>
    array (
      'type' => 'varchar(100)',
      'width' => 200,
      'editable' => false,
    ),
  ),
);