<?php
/**
* @table brand;
*
* @package Schemas
* @version $
* @copyright 2003-2009 ShopEx
* @license Commercial
*/

$db['brand']=array (
  'columns' => 
  array (
    'brand_id' => 
    array (
      'type' => 'number',
      'required' => true,
      'pkey' => true,
      'extra' => 'auto_increment',
      'label' => __('品牌id'),
      'width' => 150,
      'comment' => __('品牌id'),
      'editable' => false,
    ),
    'supplier_id' => 
    array (
      'type' => 'int unsigned',
      'comment' => __('供应商id'),
      'editable' => false,
    ),
    'supplier_brand_id' => 
    array (
      'type' => 'number',
      'comment' => __('供应商品牌id'),
      'editable' => false,
    ),
    'brand_name' => 
    array (
      'type' => 'varchar(50)',
      'label' => __('品牌名称'),
      'width' => 180,
      'required' => true,
      'comment' => __('品牌名称'),
      'editable' => true,
      'searchtype'=>'has',
    ),
    'brand_url' => 
    array (
      'type' => 'varchar(255)',
      'label' => __('品牌网址'),
      'width' => 350,
      'comment' => __('品牌网址'),
      'editable' => true,
      'searchtype' => 'has',
    ),
    'brand_desc' => 
    array (
      'type' => 'longtext',
      'comment' => __('品牌介绍'),
      'editable' => false,
    ),
    'brand_logo' => 
    array (
      'type' => 'varchar(255)',
      'comment' => __('品牌图片标识'),
      'editable' => false,
    ),
    'brand_keywords' => 
    array (
      'type' => 'longtext',
      'label' => __('品牌别名'),
      'width' => 150,
      'comment' => __('品牌别名'),
      'editable' => false,
      'searchtype'=>'has',
    ),
    'disabled' => 
    array (
      'type' => 'bool',
      'default' => 'false',
      'comment' => __('失效'),
      'editable' => false,
    ),
    'ordernum' => 
    array (
      'type' => 'number',
      'label' => __('排序'),
      'width' => 150,
      'comment' => __('排序'),
      'editable' => true,
    ),
  ),
  'comment' => __('品牌表'),
  'index' => 
  array (
    'ind_disabled' => 
    array (
      'columns' => 
      array (
        0 => 'disabled',
      ),
    ),
    'ind_ordernum' => 
    array (
      'columns' => 
      array (
        0 => 'ordernum',
      ),
    ),
    'ind_supplier_brand' => 
    array (
      'columns' => 
      array (
        0 => 'supplier_id',
        1 => 'supplier_brand_id',
      ),
    ),
  ),
);