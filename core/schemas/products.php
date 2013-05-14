<?php
/**
* @table products;
*
* @package Schemas
* @version $
* @copyright 2003-2009 ShopEx
* @license Commercial
*/

$db['products']=array (
  'columns' =>
  array (
    'product_id' =>
    array (
      'type' => 'number',
      'required' => true,
      'pkey' => true,
      'extra' => 'auto_increment',
      'label' => __('货品ID'),
      'width' => 110,
      'editable' => false,
    ),
    'goods_id' =>
    array (
      'type' => 'object:goods/products',
      'default' => 0,
      'required' => true,
      'label' => __('商品ID'),
      'width' => 110,
      'editable' => false,
    ),
    'barcode' =>
    array (
      'type' => 'varchar(128)',
      'label' => __('条码'),
      'width' => 110,
      'editable' => false,
    ),
    'title' =>
    array (
      'type' => 'varchar(255)',
      'label' => '',
      'width' => 110,
      'editable' => false,
    ),
    'bn' =>
    array (
      'type' => 'varchar(30)',
      'label' => __('货号'),
      'width' => 75,
      'fuzzySearch' => 1,
      'filtertype'=>'normal',
      'filterdefalut'=>true,
      'editable' => false,
    ),
    'price' =>
    array (
      'type' => 'money',
      'default' => '0',
      'required' => true,
      'label' => __('销售价格'),
      'width' => 75,
      'filtertype'=>'number',
      'filterdefalut'=>true,
      'editable' => false,
    ),
    'cost' =>
    array (
      'type' => 'money',
      'default' => '0',
      'label' => __('成本价'),
      'width' => 110,
      'filtertype'=>'number',
      'editable' => false,
    ),
    'mktprice' =>
    array (
      'type' => 'money',
      'label' => __('市场价'),
      'width' => 75,
      'vtype' => 'positive',
      'filtertype'=>'number',
      'editable' => false,
    ),
    'name' =>
    array (
      'type' => 'varchar(200)',
      'required' => true,
      'default' => '',
      'label' => __('货品名称'),
      'width' => 180,
      'fuzzySearch' => 1,
      'filtertype'=>'custom',
      'filtercustom'=>array('has'=>'包含','tequal'=>'等于','head'=>'开头等于','foot'=>'结尾等于'),
      'filterdefalut'=>true,
      'editable' => false,
    ),
    'weight' =>
    array (
      'type' => 'decimal(20,3)',
      'label' => __('单位重量'),
      'width' => 110,
      'filtertype'=>'number',
      'filterdefalut'=>true,
      'editable' => false,
    ),
    'unit' =>
    array (
      'type' => 'varchar(20)',
      'label' => __('单位'),
      'width' => 110,
      'filtertype'=>'normal',
      'editable' => false,
    ),
    'store' =>
    array (
      'type' => 'number',
      'label' => __('库存'),
      'width' => 30,
      'filtertype'=>'number',
      'filterdefalut'=>true,
      'editable' => false,
    ),
    'store_place' =>
    array (
      'type' => 'varchar(255)',
      'label' => __('货位'),
      'width' => 255,
      'hidden'=>true,
      'editable' => false,
    ),
    'freez' =>
    array (
      'type' => 'number',
      'label' => __('冻结库存'),
      'width' => 110,
      'hidden'=>true,
      'editable' => false,
    ),
    'pdt_desc' =>
    array (
      'type' => 'longtext',
      'label' => __('物品描述'),
      'width' => 110,
      'filtertype'=>'normal',
      'editable' => false,
    ),
    'props' =>
    array (
      'type' => 'longtext',
      'label' => __('规格值,序列化'),
      'width' => 110,
      'editable' => false,
    ),
    'uptime' =>
    array (
      'type' => 'time',
      'label' => __('录入时间'),
      'width' => 110,
      'editable' => false,
    ),
    'last_modify' =>
    array (
      'type' => 'time',
      'label' => __('最后修改时间'),
      'width' => 110,
      'editable' => false,
    ),
    'disabled' =>
    array (
      'type' => 'bool',
      'default' => 'false',
      'editable' => false,
    ),
    'marketable' =>
    array (
      'type' => 'bool',
      'default' => 'true',
      'required' => true,
      'label' => __('上架'),
      'width' => 30,
      'filtertype' => 'yes',
      'editable' => false,
    ),

    'is_local_stock' =>
    array (
      'type' => 'bool',
      'default' => 'true',
      'required' => true,
    ),

  ),
  'comment' => '货品表',
  'index' =>
       array (
           'ind_disabled' => array (
                                 'columns' => array (
                                                 0 => 'disabled',
                                               ),
            ),
            'ind_bn' => array (
                             'columns' => array (
                                                0 => 'bn',
                                           ),
                        ),
            'ind_goods_id' => array (
                             'columns' => array (
                                                0 => 'goods_id',
                                           ),
                        ),

       ),
);
