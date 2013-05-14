<?php
/**
* @table link;
*
* @package Schemas
* @version $
* @copyright 2003-2009 ShopEx
* @license Commercial
*/

$db['link']=array (
  'columns' =>
  array (
    'link_id' =>
    array (
      'type' => 'number',
      'required' => true,
      'pkey' => true,
      'extra' => 'auto_increment',
      'label' => __('友情链接id'),
      'width' => 150,
      'editable' => false,
      'hidden'=>true,
    ),
    'link_name' =>
    array (
      'type' => 'varchar(128)',
      'label' => __('友情链接名称'),
      'width' => 180,
      'required' => true,
      'editable' => true,
    ),
    'href' =>
    array (
      'type' => 'varchar(255)',
      'label' => __('友情链接地址'),
      'width' => 230,
      'required' => true,
      'editable' => true,
    ),
    'image_url' =>
    array (
      'type' => 'varchar(255)',
      'editable' => true,
    ),
    'orderlist' =>
    array (
      'type' => 'number',
      'label' => __('排序'),
      'width' => 270,
      'required' => true,
      'default' => 0,
      'editable' => true,
    ),
    'disabled' =>
    array (
      'type' => 'bool',
      'default' => 'false',
      'editable' => false,
    ),
  ),
);