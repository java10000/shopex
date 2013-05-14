<?php
/**
* @table goods;
*
* @package Schemas
* @version $
* @copyright 2003-2009 ShopEx
* @license Commercial
*/

if($this->system->getConf('certificate.distribute')){
    //是否开启分销权限
    $hidden = false;
}else{
    $hidden = true;
}

$db['goods']=array (
  'columns' =>
  array (
    'goods_id' =>
    array (
      'type' => 'number',
      'required' => true,
      'pkey' => true,
      'extra' => 'auto_increment',
      'label' => __('ID'),
      'width' => 110,
      'hidden' => true,
      'editable' => false,
    ),
    'cat_id' =>
    array (
      'type' => 'object:goods/productCat',
      'required' => true,
      'default' => 0,
      'label' => __('分类'),
      'width' => 75,
      'editable' => true,
      'filtertype'=>'yes',
      'filterdefalut'=>true,
    ),
    'type_id' =>
    array (
      'type' => 'object:goods/gtype',
      'label' => __('类型'),
      'width' => 75,
      'editable' => false,
      'filtertype'=>'yes',
    ),
    'goods_type' =>
    array (
      'type' =>
      array (
        'normal' => __('普通商品'),
        'bind' => __('捆绑商品'),
      ),
      'default' => 'normal',
      'required' => true,
      'label' => __('销售类型'),
      'width' => 110,
      'editable' => false,
      'hidden'=>true,
    ),
    'brand_id' =>
    array (
      'type' => 'object:goods/brand',
      'label' => __('品牌'),
      'width' => 75,
      'editable' => true,
      'hidden'=>true,
      'filtertype'=>'yes',
      'filterdefalut'=>true,
    ),
    'brand' =>
    array (
      'type' => 'varchar(100)',
      'label' => __('品牌'),
      'width' => 75,
      'editable' => false,
    ),
    'supplier_id' =>
    array (
      'label' => __('供应商'),
      'width'=>100,
      'type' => 'int unsigned',
      'editable' => false,
      'hidden' => $hidden,
    ),
    'supplier_goods_id' =>
    array (
      'type' => 'number',
      'editable' => false,
      'hidden' => true,
    ),
    'wss_params' =>
    array (
      'type' => 'longtext',
      'editable' => false,
    ),
    'image_default' =>
    array (
      'type' => 'longtext',
      'label' => __('默认图片'),
      'width' => 75,
      'hidden' => true,
      'editable' => false,
    ),
    'udfimg' =>
    array (
      'type' => 'bool',
      'default' => 'false',
      'label' => __('是否用户自定义图'),
      'width' => 110,
      'hidden' => true,
      'editable' => false,
    ),
    'thumbnail_pic' =>
    array (
      'type' => 'varchar(255)',
      'label' => __('缩略图'),
      'width' => 110,
      'hidden' => true,
      'editable' => false,
    ),
    'small_pic' =>
    array (
      'type' => 'varchar(255)',
      'editable' => false,
    ),
    'big_pic' =>
    array (
      'type' => 'varchar(255)',
      'editable' => false,
    ),
    'image_file' =>
    array (
      'type' => 'longtext',
      'label' => __('图片文件'),
      'width' => 110,
      'hidden' => true,
      'editable' => false,
    ),
    'brief' =>
    array (
      'type' => 'varchar(255)',
      'label' => __('商品简介'),
      'width' => 110,
      'hidden' => false,
      'editable' => false,
      'filtertype'=>'normal',
    ),
    'intro' =>
    array (
      'type' => 'longtext',
      'label' => __('详细介绍'),
      'width' => 110,
      'hidden' => true,
      'editable' => false,
      'filtertype'=>'normal',
    ),
    'mktprice' =>
    array (
      'type' => 'money',
      'label' => __('市场价'),
      'width' => 75,
      'vtype' => 'positive',
      'editable' => false,
      'filtertype'=>'number',
    ),
    'cost' =>
    array (
      'type' => 'money',
      'default' => '0',
      'required' => true,
      'label' => __('成本价'),
      'width' => 75,
      'editable' => false,
      'filtertype'=>'number',
    ),
    'price' =>
    array (
      'type' => 'money',
      'default' => '0',
      'required' => true,
      'label' => __('销售价'),
      'width' => 75,
      'vtype' => 'bbsales',
      'editable' => false,
      'filtertype'=>'number',
      'filterdefalut'=>true,
    ),
    'bn' =>
    array (
      'type' => 'varchar(200)',
      'label' => __('商品编号'),
      'width' => 110,
      'fuzzySearch' => 1,
      'primary' => true,
      'searchtype' => 'head',
      'editable' => true,
      'filtertype'=>'yes',
      'filterdefalut'=>true,
    ),
    'name' =>
    array (
      'type' => 'varchar(200)',
      'required' => true,
      'default' => '',
      'label' => __('商品名称'),
      'width' => 310,
      'fuzzySearch' => 1,
      'primary' => true,
      'locked' => 1,
      'searchtype' => 'has',
      'editable' => true,
      'filtertype'=>'custom',
      'filterdefalut'=>true,
      'filtercustom'=>array('has'=>'包含','tequal'=>'等于','head'=>'开头等于','foot'=>'结尾等于')
    ),
    'marketable' =>
    array (
      'type' => 'bool',
      'default' => 'true',
      'required' => true,
      'label' => __('上架'),
      'width' => 30,
      'editable' => true,
      'filtertype' => 'yes',
      'filterdefalut'=>true,
    ),
    'weight' =>
    array (
      'type' => 'decimal(20,3)',
      'label' => __('重量'),
      'width' => 75,
      'editable' => false,
    ),
    'unit' =>
    array (
      'type' => 'varchar(20)',
      'label' => __('单位'),
      'width' => 30,
      'editable' => false,
      'filtertype'=>'normal',
    ),
    'store' =>
    array (
      'type' => 'number',
      'label' => __('库存'),
      'width' => 30,
      'editable' => false,
      'filtertype'=>'number',
      'filterdefalut'=>true,
    ),
    'store_place' =>
    array (
      'type' => 'varchar(255)',
      'label' => __('货位'),
      'width' => 255,
      'editable' => false,
      'hidden'=>true,
    ),
    'score_setting' =>
    array (
      'type' =>
      array (
        'percent' => __('百分比'),
        'number' => __('实际值'),
      ),
      'default' => 'number',
      'editable' => false,
    ),
    'score' =>
    array (
      'type' => 'number',
      'label' => __('积分'),
      'width' => 30,
      'editable' => false,
    ),
    'spec' =>
    array (
      'type' => 'longtext',
      'label' => __('规格'),
      'width' => 110,
      'hidden' => true,
      'editable' => false,
    ),
    'pdt_desc' =>
    array (
      'type' => 'longtext',
      'label' => __('物品'),
      'width' => 110,
      'hidden' => true,
      'editable' => false,
    ),
    'spec_desc' =>
    array (
      'type' => 'longtext',
      'label' => __('物品'),
      'width' => 110,
      'hidden' => true,
      'editable' => false,
    ),
    'params' =>
    array (
      'type' => 'longtext',
      'editable' => false,
    ),
    'uptime' =>
    array (
      'type' => 'time',
      'label' => __('上架时间'),
      'width' => 110,
      'editable' => false,
    ),
    'downtime' =>
    array (
      'type' => 'time',
      'label' => __('下架时间'),
      'width' => 110,
      'editable' => false,
    ),
    'last_modify' =>
    array (
      'type' => 'time',
      'label' => __('更新时间'),
      'width' => 110,
      'editable' => false,
    ),
    'disabled' =>
    array (
      'type' => 'bool',
      'default' => 'false',
      'required' => true,
      'editable' => false,
    ),
    'notify_num' =>
    array (
      'type' => 'number',
      'default' => 0,
      'required' => true,
      'label' => __('缺货登记'),
      'width' => 110,
      'editable' => false,
    ),
    'rank' =>
    array (
      'type' => 'decimal(5,3)',
      'default' => '5',
      'editable' => false,
    ),
    'rank_count' =>
    array (
      'type' => 'int unsigned',
      'default' => 0,
      'editable' => false,
    ),
    'comments_count' =>
    array (
      'type' => 'int unsigned',
      'default' => 0,
      'required' => true,
      'editable' => false,
    ),
    'view_w_count' =>
    array (
      'type' => 'int unsigned',
      'default' => 0,
      'required' => true,
      'editable' => false,
    ),
    'view_count' =>
    array (
      'type' => 'int unsigned',
      'default' => 0,
      'required' => true,
      'editable' => false,
    ),
    'buy_count' =>
    array (
      'type' => 'int unsigned',
      'default' => 0,
      'required' => true,
      'editable' => false,
    ),
    'buy_w_count' =>
    array (
      'type' => 'int unsigned',
      'default' => 0,
      'required' => true,
      'editable' => false,
    ),
    'count_stat' =>
    array (
      'type' => 'longtext',
      'editable' => false,
    ),
    'p_order' =>
    array (
      'type' => 'number',
      'default' => 30,
      'required' => true,
      'label' => __('排序'),
      'width' => 110,
      'editable' => false,
      'hidden' => true,
    ),
    'd_order' =>
    array (
      'type' => 'number',
      'default' => 30,
      'required' => true,
      'label' => __('排序'),
      'width' => 30,
      'editable' => true,
    ),
    'p_1' =>
    array (
      'type' => 'varchar(255)',
      'editable' => false,
    ),
    'p_2' =>
    array (
      'type' => 'varchar(255)',
      'editable' => false,
    ),
    'p_3' =>
    array (
      'type' => 'varchar(255)',
      'editable' => false,
    ),
    'p_4' =>
    array (
      'type' => 'varchar(255)',
      'editable' => false,
    ),
    'p_5' =>
    array (
      'type' => 'varchar(255)',
      'editable' => false,
    ),
    'p_6' =>
    array (
      'type' => 'varchar(255)',
      'editable' => false,
    ),
    'p_7' =>
    array (
      'type' => 'varchar(255)',
      'editable' => false,
    ),
    'p_8' =>
    array (
      'type' => 'varchar(255)',
      'editable' => false,
    ),
    'p_9' =>
    array (
      'type' => 'varchar(255)',
      'editable' => false,
    ),
    'p_10' =>
    array (
      'type' => 'varchar(255)',
      'editable' => false,
    ),
    'p_11' =>
    array (
      'type' => 'varchar(255)',
      'editable' => false,
    ),
    'p_12' =>
    array (
      'type' => 'varchar(255)',
      'editable' => false,
    ),
    'p_13' =>
    array (
      'type' => 'varchar(255)',
      'editable' => false,
    ),
    'p_14' =>
    array (
      'type' => 'varchar(255)',
      'editable' => false,
    ),
    'p_15' =>
    array (
      'type' => 'varchar(255)',
      'editable' => false,
    ),
    'p_16' =>
    array (
      'type' => 'varchar(255)',
      'editable' => false,
    ),
    'p_17' =>
    array (
      'type' => 'varchar(255)',
      'editable' => false,
    ),
    'p_18' =>
    array (
      'type' => 'varchar(255)',
      'editable' => false,
    ),
    'p_19' =>
    array (
      'type' => 'varchar(255)',
      'editable' => false,
    ),
    'p_20' =>
    array (
      'type' => 'varchar(255)',
      'editable' => false,
    ),
    'p_21' =>
    array (
      'type' => 'varchar(255)',
      'editable' => false,
    ),
    'p_22' =>
    array (
      'type' => 'varchar(255)',
      'editable' => false,
    ),
    'p_23' =>
    array (
      'type' => 'varchar(255)',
      'editable' => false,
    ),
    'p_24' =>
    array (
      'type' => 'varchar(255)',
      'editable' => false,
    ),
    'p_25' =>
    array (
      'type' => 'varchar(255)',
      'editable' => false,
    ),
    'p_26' =>
    array (
      'type' => 'varchar(255)',
      'editable' => false,
    ),
    'p_27' =>
    array (
      'type' => 'varchar(255)',
      'editable' => false,
    ),
    'p_28' =>
    array (
      'type' => 'varchar(255)',
      'editable' => false,
    ),
    'goods_info_update_status' =>
    array (
      'type' => 'bool',
      'default' => 'false',
      'editable' => false,
    ),
    'stock_update_status' =>
    array (
      'type' => 'bool',
      'default' => 'false',
      'editable' => false,
    ),
    'marketable_update_status' =>
    array (
      'type' => 'bool',
      'default' => 'false',
      'editable' => false,
    ),
    'img_update_status' =>
    array (
      'type' => 'bool',
      'default' => 'false',
      'editable' => false,
    ),
  ),
  'comment' => '商品表',
  'index' =>
  array (
    'uni_bn' =>
    array (
      'columns' =>
      array (
        0 => 'bn',
      ),
    ),
    'ind_p_1' =>
    array (
      'columns' =>
      array (
        0 => 'p_1',
      ),
    ),
    'ind_p_2' =>
    array (
      'columns' =>
      array (
        0 => 'p_2',
      ),
    ),
    'ind_p_3' =>
    array (
      'columns' =>
      array (
        0 => 'p_3',
      ),
    ),
    'ind_p_4' =>
    array (
      'columns' =>
      array (
        0 => 'p_4',
      ),
    ),
    'ind_p_23' =>
    array (
      'columns' =>
      array (
        0 => 'p_23',
      ),
    ),
    'ind_p_22' =>
    array (
      'columns' =>
      array (
        0 => 'p_22',
      ),
    ),
    'ind_p_21' =>
    array (
      'columns' =>
      array (
        0 => 'p_21',
      ),
    ),
    'ind_frontend' =>
    array (
      'columns' =>
      array (
        0 => 'disabled',
        1 => 'goods_type',
        2 => 'marketable',
      ),
    ),
    'supplier_goods' =>
    array (
      'columns' =>
      array (
        0 => 'supplier_id',
        1 => 'supplier_goods_id'
      )
    ),
  ),
);
