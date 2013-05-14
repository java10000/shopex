<?php
/**
* @table specification;
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

$db['specification']=array (
  'columns' =>
  array (
    'spec_id' =>
    array (
      'type' => 'number',
      'required' => true,
      'pkey' => true,
      'extra' => 'auto_increment',
      'label' => __('规格id'),
      'width' => 150,
      'editable' => false,
    ),
    'spec_name' =>
    array (
      'type' => 'varchar(50)',
      'default' => '',
      'required' => true,
      'label' => __('规格名称'),
      'width' => 180,
      'modifier' => 'row',
      'editable' => true,
    ),
    'alias' =>
    array (
      'type' => 'varchar(255)',
      'default' => '',
      'label' => __('规格别名'),
      'width' => 180,
    ),
    'spec_show_type' =>
    array (
      'type' =>
      array (
        'select' => __('下拉'),
        'flat' => __('平铺'),
      ),
      'default' => 'flat',
      'required' => true,
      'label' => __('显示方式'),
      'width' => 75,
      'editable' => true,
    ),
    'spec_type' =>
    array (
      'type' =>
      array (
        'text' => __('文字'),
        'image' => __('图片'),
      ),
      'default' => 'text',
      'required' => true,
      'label' => __('类型'),
      'width' => 75,
      'editable' => false,
    ),
    'spec_memo' =>
    array (
      'type' => 'varchar(50)',
      'default' => '',
      'required' => true,
      'label' => __('规格备注'),
      'width' => 350,
      'editable' => false,
    ),
    'p_order' =>
    array (
      'type' => 'number',
      'default' => 0,
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

    'supplier_spec_id' =>
    array (
      'type' => 'number',
      'hidden' => true,
    ),
    'supplier_id' =>
    array (
      'label' => __('供应商'),
      'width'=>100,
      'type' => 'int unsigned',
      'hidden' => $hidden,
    ),
    'lastmodify' =>
    array (
      'label' => __('供应商最后更新时间'),
      'width'=>150,
      'type' => 'time',
      'hidden' => $hidden,
    ),
  ),
  'comment' => '商店中商品规格',
);
