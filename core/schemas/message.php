<?php
/**
* @table message;
*
* @package Schemas
* @version $
* @copyright 2003-2009 ShopEx
* @license Commercial
*/

$db['message']=array (
  'columns' =>
  array (
    'msg_id' =>
    array (
      'label'=>__('序号'),
      'type' => 'number',
      'required' => true,
      'pkey' => true,
      'extra' => 'auto_increment',
      'editable' => false,
    ),
    'for_id' =>
    array (
      'type' => 'number',
      'default' => 0,
      'required' => true,
      'editable' => false,
    ),
    'msg_from' =>
    array (
      'label'=>'发送者',
      'type' => 'varchar(30)',
      'default' => '',
      'required' => true,
      'searchable' => true,
      'editable' => false,
      'filtertype'=>'yes',
      'filterdefalut'=>true,
    ),
    'from_id' =>
    array (
      'type' => 'number',
      'default' => 0,
      'editable' => false,
    ),
    'from_type' =>
    array (
      'type' => 'tinyint(1) unsigned',
      'map' =>
      array (
        0 => __('会员'),
        1 => __('管理员'),
        2 => __('非会员'),
      ),
      'default' => '0',
      'required' => true,
      'editable' => false,
    ),
    'to_id' =>
    array (
      'type' => 'number',
      'default' => 0,
      'required' => true,
      'editable' => false,
    ),
    'to_type' =>
    array (
      'type' => 'tinyint(1) unsigned',
      'default' => 0,
      'required' => true,
      'editable' => false,
    ),
    'unread' =>
    array (
      'type' => 'intbool',
      'default' => '0',
      'required' => true,
      'editable' => false,
    ),
    'folder' =>
    array (
      'type' =>
      array (
        'inbox' => __('收件箱'),
        'outbox' => __('发件箱'),
      ),
      'default' => 'inbox',
      'required' => true,
      'editable' => false,
    ),
    'email' =>
    array (
      'type' => 'varchar(255)',
      'editable' => false,
      'label'=>__('联系方式'),
      'filtertype'=>'normal',
      'hidden'=>true,
      'filterdefalut'=>true,
      'escape_html'=>true,
    ),
    'tel' =>
    array (
      'type' => 'varchar(30)',
      'editable' => false,
    ),
    'subject' =>
    array (
      'label'=>__("消息标题"),
      'type' => 'varchar(100)',
      'required' => true,
      'default' => '',
      'editable' => false,
      'filtertype'=>'normal',
      'filterdefalut'=>true,
      'escape_html'=>true,
    ),
    'message' =>
    array (
      'label'=>__("内容"),
      'type' => 'longtext',
      'required' => true,
      'default' => '',
      'editable' => false,
      'searchtype'=> 'has',
      'filtertype'=>'normal',
      'filterdefalut'=>true,
      'escape_html'=>true,
    ),
    'rel_order' =>
    array (
      'type' => 'bigint unsigned',
      'default' => 0,
      'editable' => false,
    ),
    'date_line' =>
    array (
      'label'=>__("时间"),
      'type' => 'time',
      'default' => 0,
      'required' => true,
      'editable' => false,
      'filtertype'=>'number',
      'filterdefalut'=>true,
    ),
    'is_sec' =>
    array (
      'type' => 'bool',
      'default' => 'true',
      'required' => true,
      'editable' => false,
      'label'=>__("公开"),
      'filtertype'=>'yes'
    ),
    'del_status' =>
    array (
      'type' =>
      array (
        0 => 0,
        1 => 1,
        2 => 2,
      ),
      'default' => '0',
      'editable' => false,
    ),
    'disabled' =>
    array (
      'type' => 'bool',
      'default' => 'false',
      'required' => true,
      'editable' => false,
    ),
    'msg_ip' =>
    array (
      'type' => 'varchar(20)',
      'default' => '',
      'required' => true,
      'editable' => false,
    ),
    'msg_type' =>
    array (
      'type' =>
      array (
        'default' => __('通常'),
        'payment' => __('支付'),
      ),
      'default' => 'default',
      'required' => true,
      'editable' => false,
    ),
  ),
  'comment' => '留言和短信表',
  'index' =>
  array (
    'ind_to_id' =>
    array (
      'columns' =>
      array (
        0 => 'to_id',
        1 => 'folder',
        2 => 'from_type',
        3 => 'unread',
      ),
    ),
    'ind_from_id' =>
    array (
      'columns' =>
      array (
        0 => 'from_id',
        1 => 'folder',
        2 => 'to_type',
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