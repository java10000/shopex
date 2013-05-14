<?php
/**
* @table gift;
*
* @package Schemas
* @version $
* @copyright 2003-2009 ShopEx
* @license Commercial
*/

$db['gift']=array (
  'columns' =>
  array (
    'gift_id' =>
    array (
      'type' => 'number',
      'required' => true,
      'pkey' => true,
      'extra' => 'auto_increment',
      'label' => __('ID'),
      'width' => 110,
      'editable' => false,
    ),
    'gift_bn' =>
    array(
       'type' => 'varchar(255)',
       'default' => 0,
       'label' => __('赠品编号'),
       'width' => 110,
       'editable' => false,
    ),
    'giftcat_id' =>
    array (
      'type' => 'object:trading/giftcat',
      'label' => __('分类'),
      'width' => 110,
      'editable' => true,
    ),
    'insert_time' =>
    array (
      'type' => 'time',
      'default' => 0,
      'required' => true,
      'label' => __('插入时间'),
      'width' => 110,
      'editable' => false,
      'hidden'=>true,
    ),
    'update_time' =>
    array (
      'type' => 'time',
      'default' => 0,
      'required' => true,
      'label' => __('更新时间'),
      'width' => 110,
      'editable' => false,
      'hidden'=>true,
    ),
    'name' =>
    array (
      'type' => 'varchar(255)',
      'label' => __('赠品名称'),
      'searchtype' => 'has',
      'width' => 230,
      'required' => true,
      'editable' => true,
    ),
    'thumbnail_pic' =>
    array (
      'type' => 'varchar(255)',
      'label' => __('列表页缩略图'),
      'width' => 110,
      'editable' => false,
      'hidden'=>true,
    ),
    'small_pic' =>
    array (
      'type' => 'varchar(255)',
      'label' => __('缩略图'),
      'width' => 110,
      'editable' => false,
      'hidden'=>true,
    ),
    'big_pic' =>
    array (
      'type' => 'varchar(255)',
      'label' => __('详细图'),
      'width' => 110,
      'editable' => false,
      'hidden'=>true,
    ),
    'image_file' =>
    array (
      'type' => 'longtext',
      'editable' => false,
    ),
    'intro' =>
    array (
      'type' => 'varchar(255)',
      'label' => __('简介'),
      'width' => 110,
      'editable' => false,
    ),
    'gift_describe' =>
    array (
      'type' => 'longtext',
      'label' => __('详细描述'),
      'width' => 110,
      'editable' => false,
      'hidden'=>true,
    ),
    'weight' =>
    array (
      'type' => 'int',
      'label' => __('重量'),
      'width' => 110,
      'required' => true,
      'default' => 0,
      'editable' => false,
    ),
    'storage' =>
    array (
      'type' => 'number',
      'default' => 0,
      'label' => __('库存'),
      'width' => 30,
      'required' => true,
      'editable' => true,
    ),
    'price' =>
    array (
      'type' => 'varchar(255)',
      'label' => __('价格'),
      'width' => 110,
      'editable' => false,
      'hidden'=>true,
    ),
    'orderlist' =>
    array (
      'type' => 'number',
      'default' => 0,
      'label' => __('排序'),
      'width' => 30,
      'editable' => true,
    ),
    'shop_iffb' =>
    array (
      'type' => 'intbool',
      'default' => '1',
      'required' => true,
      'label' => __('发布'),
      'width' => 30,
      'editable' => false,
    ),
    'limit_num' =>
    array (
      'type' => 'number',
      'default' => 0,
      'label' => __('限购数量'),
      'width' => 110,
      'editable' => true,
    ),
    'limit_start_time' =>
    array (
      'type' => 'time',
      'label' => __('开始时间'),
      'width' => 75,
      'inputType' => 'date',
      'required' => true,
      'default' => 0,
      'editable' => false,
    ),
    'limit_end_time' =>
    array (
      'type' => 'time',
      'label' => __('结束时间'),
      'width' => 75,
      'inputType' => 'date',
      'required' => true,
      'default' => 0,
      'editable' => false,
    ),
    'limit_level' =>
    array (
      'type' => 'varchar(255)',
      'label' => __('允许兑换等级'),
      'width' => 110,
      'editable' => false,
      'hidden'=>true,
    ),
    'ifrecommend' =>
    array (
      'type' => 'intbool',
      'default' => '0',
      'required' => true,
      'label' => __('推荐'),
      'width' => 30,
      'bool' => 'number',
      'editable' => true,
    ),
    'point' =>
    array (
      'type' => 'number',
      'default' => 0,
      'label' => __('兑换所需积分'),
      'width' => 30,
      'required' => true,
      'editable' => true,
    ),
    'freez' =>
    array (
      'type' => 'number',
      'default' => 0,
      'label' => __('冻结库存'),
      'width' => 110,
      'editable' => false,
      'hidden'=>true,
    ),
    'disabled' =>
    array (
      'type' => 'bool',
      'default' => 'false',
      'editable' => false,
    ),
  ),
  'comment' => '赠品关系表',
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