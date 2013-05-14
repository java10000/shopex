<?php
/**
* @table articles;
*
* @package Schemas
* @version $
* @copyright 2003-2009 ShopEx
* @license Commercial
*/

$db['articles']=array (
  'columns' =>
  array (
    'article_id' =>
    array (
      'type' => 'number',
      'required' => true,
      'pkey' => true,
      'extra' => 'auto_increment',
      'comment' => __('文章ID'),
      'editable' => false,
    ),
    'node_id' =>
    array (
      'type' => 'table:sitemaps:node_id',
      'required' => true,
      'default' => 0,
      'label' => __('所属栏目'),
      'width' => 110,
      'comment' => __('所属栏目'),
      'editable' => false,
    ),
    'title' =>
    array (
      'type' => 'varchar(200)',
      'label' => __('文章标题'),
      'width' => 310,
      'searchtype' => 'has',
      'comment' => __('文章标题'),
      'editable' => true,
    ),
    'content' =>
    array (
      'type' => 'longtext',
      'label' => __('文章内容'),
      'comment' => __('文章内容'),
      'hidden'=>true,
      'searchtype' => 'has',
      'editable' => false,
    ),
    'uptime' =>
    array (
      'type' => 'time',
      'label' => __('更新时间'),
      'width' => 110,
      'comment' => __('更新时间'),
      'editable' => false,
    ),
    'ifpub' =>
    array (
      'type' => 'intbool',
      'label' => __('是否发布'),
      'width' => 110,
      'comment' => __('是否发布'),
      'editable' => true,
    ),
    'align' =>
    array (
      'type' => 'varchar(12)',
      'comment' => __('显示方式'),
      'editable' => false,
    ),
    'filetype' =>
    array (
      'type' => 'varchar(15)',
      'comment' => __('上传文件类型'),
      'editable' => false,
    ),
    'filename' =>
    array (
      'type' => 'varchar(80)',
      'comment' => __('文件名'),
      'editable' => false,
    ),
    'orderlist' =>
    array (
      'type' => 'mediumint(6)',
      'comment' => __('排序'),
      'editable' => true,
    ),
   //2011-8-16 新增 文章排序功能by zhangxuehui
   'ordernum'=>
    array (
      'type' => 'int(10)',
      'label' => __('排序'),
      'default' => '50',
      'width' => 50,
      'comment' => __('排序'),
      'editable' => true,
    ),
    'pubtime'=>
    array(
      'type' => 'time',
      'label' => __('创建时间'),
      'editable' => false,
    ),
    'disabled' =>
    array (
      'type' => 'bool',
      'default' => 'false',
      'comment' => __('失效'),
      'editable' => false,
    ),
    'goodsinfo'=>array(
      'type' => 'text',
      'editable' => false,
    )
  ),
  'comment' => __('文章表'),
  'index' =>
  array (
    'ind_disabled' =>
    array (
      'columns' =>
      array (
        0 => 'disabled',
      ),
    ),
    'ind_orderlist' =>
    array (
      'columns' =>
      array (
        0 => 'orderlist',
      ),
    ),
    'ind_ifpub' =>
    array (
      'columns' =>
      array (
        0 => 'ifpub',
      ),
    ),
    'ind_uptime' =>
    array (
      'columns' =>
      array (
        0 => 'uptime',
      ),
    ),
  ),
);