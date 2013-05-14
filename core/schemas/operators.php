<?php
/**
* @table operators;
*
* @package Schemas
* @version $
* @copyright 2003-2009 ShopEx
* @license Commercial
*/

$db['operators']=array (
  'columns' => 
  array (
    'op_id' => 
    array (
      'type' => 'number',
      'required' => true,
      'pkey' => true,
      'extra' => 'auto_increment',
      'label' => 'ID',
      'width' => 30,
      'editable' => false,
      'hidden'=>true,
    ),
    'username' => 
    array (
      'type' => 'varchar(20)',
      'required' => true,
      'default' => '',
      'label' => __('用户名'),
      'width' => 110,
      'editable' => false,
    ),
    'userpass' => 
    array (
      'type' => 'varchar(32)',
      'required' => true,
      'default' => '',
      'editable' => false,
    ),
    'name' => 
    array (
      'type' => 'varchar(30)',
      'label' => __('姓名'),
      'width' => 110,
      'editable' => true,
    ),
    'config' => 
    array (
      'type' => 'longtext',
      'editable' => false,
    ),
    'favorite' => 
    array (
      'type' => 'longtext',
      'editable' => false,
    ),
    'super' => 
    array (
      'type' => 'intbool',
      'default' => '0',
      'required' => true,
      'label' => __('超级管理员'),
      'width' => 75,
      'editable' => false,
    ),
    'lastip' => 
    array (
      'type' => 'varchar(20)',
      'editable' => false,
    ),
    'logincount' => 
    array (
      'type' => 'number',
      'default' => 0,
      'required' => true,
      'label' => __('登陆次数'),
      'width' => 110,
      'editable' => false,
    ),
    'lastlogin' => 
    array (
      'type' => 'time',
      'default' => 0,
      'required' => true,
      'label' => __('最后登陆时间'),
      'width' => 110,
      'editable' => false,
    ),
    'status' => 
    array (
      'type' => 'intbool',
      'default' => '1',
      'label' => __('启用'),
      'width' => 100,
      'required' => true,
      'editable' => true,
    ),
    'disabled' => 
    array (
      'type' => 'bool',
      'default' => 'false',
      'required' => true,
      'editable' => false,
    ),
    'op_no' => 
    array (
      'type' => 'varchar(50)',
      'label' => __('编号'),
      'width' => 30,
      'editable' => true,
    ),
    'department' => 
    array (
      'type' => 'varchar(50)',
      'label' => __('部门'),
      'width' => 75,
      'editable' => true,
    ),
    'memo' => 
    array (
      'type' => 'text',
      'label' => __('备注'),
      'width' => 270,
      'editable' => false,
    ),
  ),
  'comment' => '商店后台管理员表',
  'index' => 
  array (
    'uni_username' => 
    array (
      'columns' => 
      array (
        0 => 'username',
      ),
    ),
    'ind_disabled' => 
    array (
      'columns' => 
      array (
        0 => 'disabled',
      ),
    ),
  ),
);