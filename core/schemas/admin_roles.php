<?php
/**
* @table admin_roles;
*
* @package Schemas
* @version $
* @copyright 2003-2009 ShopEx
* @license Commercial
*/

$db['admin_roles']=array (
  'columns' =>
  array (
    'role_id' =>
    array (
      'type' => 'int unsigned',
      'required' => true,
      'pkey' => true,
      'extra' => 'auto_increment',
      'label' => __('角色id'),
      'width' => 75,
      'comment' => __('角色id'),
      'editable' => false,
      'hidden'=>true,
    ),
    'role_name' =>
    array (
      'type' => 'varchar(100)',
      'required' => true,
      'default' => '',
      'label' => __('角色名称'),
      'width' => 150,
      'comment' => __('角色名称'),
      'editable' => true,
    ),
    'role_memo' =>
    array (
      'type' => 'text',
      'default' => '',
      'label' => __('角色备注'),
      'width' => 180,
      'comment' => __('角色备注'),
      'editable' => false,
    ),
    'disabled' =>
    array (
      'type' => 'bool',
      'default' => 'false',
      'required' => true,
      'comment' => __('无效'),
      'editable' => false,
    ),
  ),

  'index' =>array(
  'ind_disabled'=>array (
    'name' => 'ind_disabled',
    'columns' =>
    array (
      0 => 'disabled',
    ),
  ),
  ),
  'comment' => __('管理员角色表'),
);