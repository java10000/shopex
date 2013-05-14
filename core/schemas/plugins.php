<?php
/**
* @table plugins;
*
* @package Schemas
* @version $
* @copyright 2003-2009 ShopEx
* @license Commercial
*/

$db['plugins']=array (
  'columns' =>
  array (
    'plugin_id' =>
    array (
      'type' => 'number',
      'required' => true,
      'pkey' => true,
      'extra' => 'auto_increment',
      'label' => __('插件id'),
      'width' => 50,
      'hidden' => 1,
      'editable' => false,
    ),
    'plugin_ident' =>
    array (
      'type' => 'varchar(100)',
      'required' => true,
      'default' => '',
      'label' => __('标识'),
      'width' => 50,
      'hidden' => 1,
      'editable' => false,
    ),
    'plugin_name' =>
    array (
      'type' => 'varchar(100)',
      'required' => true,
      'default' => '',
      'label' => __('插件名称'),
      'width' => 310,
      'editable' => false,
    ),
    'plugin_type' =>
    array (
      'type' =>
      array (
        'io' => __('输入输出'),
        'schema' => __('商品插件'),
        'hook' => __('事件处理'),
        'pmt' => __('优惠规则'),
        'local' => __('地区插件'),
        'messenger' => __('消息发送'),
        'pay' => __('支付插件'),
        'passport' => __('登陆插件'),
        'admin' => __('后台功能插件'),
        'shop' => __('后台功能插件'),
        'action' => __('网店机器人动作'),
        'app' => __('网店应用程序'),
        'mdl' => __('模型'),
      ),
      'required' => true,
      'default' => 'io',
      'label' => __('类型'),
      'width' => 110,
      'editable' => false,
    ),
   'app_type' =>
    array (
      'type' =>'varchar(100)',
      'required' => true,
      'default' => '',
      'label' => __('类型'),
      'width' => 110,
      'editable' => false,
    ),
    'plugin_base' =>
    array (
      'type' =>
      array (
        0 => __('系统'),
        9 => __('模板'),
      ),
      'default' => '0',
      'required' => true,
      'label' => __('所属'),
      'width' => 110,
      'editable' => false,
    ),
    'plugin_version' =>
    array (
      'type' => 'varchar(100)',
      'default' => '1.0',
      'editable' => false,
    ),
    'plugin_author' =>
    array (
      'type' => 'varchar(100)',
      'editable' => false,
    ),
    'plugin_package' =>
    array (
      'type' => 'varchar(100)',
      'editable' => false,
    ),
    'plugin_website' =>
    array (
      'type' => 'varchar(200)',
      'editable' => false,
    ),
    'plugin_updatechec' =>
    array (
      'type' => 'varchar(200)',
      'editable' => false,
    ),
    'plugin_desc' =>
    array (
      'type' => 'text',
      'label' => __('描述'),
      'width' => 310,
      'editable' => false,
    ),
    'plugin_hasopts' =>
    array (
      'type' => 'bool',
      'default' => 'false',
      'required' => true,
      'editable' => false,
    ),
    'plugin_struct' =>
    array (
      'type' => 'text',
      'editable' => false,
    ),
    'plugin_config' =>
    array (
      'type' => 'text',
      'editable' => false,
    ),
    'plugin_path' =>
    array (
      'type' => 'varchar(255)',
      'required' => true,
      'default' => '',
      'editable' => false,
    ),
    'plugin_mode' =>
    array (
      'type' =>
      array (
        'file' => __('文件型'),
        'dir' => __('目录型'),
      ),
      'default' => 'file',
      'required' => true,
      'editable' => false,
    ),
    'status' =>
    array (
      'type' =>
      array (
        'unused' => __('未使用'),
        'used' => __('使用过'),
        'broken' => __('已损坏'),
      ),
      'default' => 'unused',
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
    'plugin_mtime' =>
    array (
      'type' => 'int',
      'required' => true,
      'default' => 0,
      'editable' => false,
    ),
  ),
);