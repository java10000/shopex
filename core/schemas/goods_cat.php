<?php
/**
* @table goods_cat;
*
* @package Schemas
* @version $
* @copyright 2003-2009 ShopEx
* @license Commercial
*/

$db['goods_cat']=array (
  'columns' => 
  array (
    'cat_id' => 
    array (
      'type' => 'int(10)',
      'required' => true,
      'pkey' => true,
      'extra' => 'auto_increment',
      'label' => __('分类ID'),
      'width' => 110,
      'editable' => false,
    ),
    'parent_id' => 
    array (
      'type' => 'int(10)',
      'label' => __('分类ID'),
      'width' => 110,
      'editable' => false,
    ),
    'supplier_id' => 
    array (
      'type' => 'int unsigned',
      'editable' => false,
    ),
    'supplier_cat_id' => 
    array (
      'type' => 'number',
      'editable' => false,
    ),
    'cat_path' => 
    array (
      'type' => 'varchar(100)',
      'default' => ',',
      'label' => __('分类路径(从根至本结点的路径,逗号分隔,首部有逗号)'),
      'width' => 110,
      'editable' => false,
    ),
    'is_leaf' => 
    array (
      'type' => 'bool',
      'required' => true,
      'default' => 'false',
      'label' => __('是否叶子结点（true：是；false：否）'),
      'width' => 110,
      'editable' => false,
    ),
    'type_id' => 
    array (
      'type' => 'int(10)',
      'label' => __('类型序号'),
      'width' => 110,
      'editable' => false,
    ),
    'cat_name' => 
    array (
      'type' => 'varchar(100)',
      'required' => true,
      'default' => '',
      'label' => __('分类名称'),
      'width' => 110,
      'editable' => false,
    ),
    'disabled' => 
    array (
      'type' => 'bool',
      'default' => 'false',
      'required' => true,
      'label' => __('是否屏蔽（true：是；false：否）'),
      'width' => 110,
      'editable' => false,
    ),
    'p_order' => 
    array (
      'type' => 'number',
      'label' => __('排序'),
      'width' => 110,
      'editable' => false,
    ),
    'goods_count' => 
    array (
      'type' => 'number',
      'label' => __('商品数'),
      'width' => 110,
      'editable' => false,
    ),
    'tabs' => 
    array (
      'type' => 'longtext',
      'editable' => false,
    ),
    'finder' => 
    array (
      'type' => 'longtext',
      'label' => __('渐进式筛选容器'),
      'width' => 110,
      'editable' => false,
    ),
    'addon' => 
    array (
      'type' => 'longtext',
      'editable' => false,
    ),
    'child_count' => 
    array (
      'type' => 'number',
      'default' => 0,
      'required' => true,
      'editable' => false,
    ),
  ),
  'comment' => '类别属性值有限表',
  'index' => 
  array (
    'ind_cat_path' => 
    array (
      'columns' => 
      array (
        0 => 'cat_path',
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