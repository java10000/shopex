<?php
/**
* @table cachemgr;
*
* @package Schemas
* @version $
* @copyright 2003-2009 ShopEx
* @license Commercial
*/

$db['cachemgr']=array (
  'columns' =>
  array (
    'cname' =>
    array (
      'type' => 'varchar(30)',
      'required' => true,
      'default' => '',
      'pkey' => true,
      'comment' => __('缓存名称'),
      'editable' => false,
    ),
    'modified' =>
    array (
      'type' => 'int unsigned',
      'required' => true,
      'default' => 0,
      'comment' => __('最后更新时间'),
      'editable' => false,
    ),
  ),
  'engine' => 'heap',
  'comment' => __('缓存对象管理表'),
);