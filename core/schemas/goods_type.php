<?php
/**
* @table goods_type;
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

$db['goods_type']=array (
  'columns' =>
  array (
    'type_id' =>
    array (
      'type' => 'int(10)',
      'required' => true,
      'pkey' => true,
      'extra' => 'auto_increment',
      'label' => __('类型序号'),
      'width' => 110,
      'editable' => false,
    ),
    'name' =>
    array (
      'type' => 'varchar(100)',
      'required' => true,
      'default' => '',
      'label' => __('类型名称'),
      'width' => 150,
      'editable' => true,
    ),
    'alias' =>
    array (
      'type' => 'longtext',
      'editable' => false,
    ),
    'is_physical' =>
    array (
      'type' => 'intbool',
      'default' => '1',
      'required' => true,
      'label' => __('实体商品'),
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
    'supplier_type_id' =>
    array (
      'type' => 'number',
      'editable' => false,
      'hidden' => true,
    ),
    'schema_id' =>
    array (
      'type' => 'varchar(30)',
      'required' => true,
      'default' => 'custom',
//      'label' => __('类型标识'),
      'hidden'=>1,
      'width' => 110,
      'editable' => false,
    ),
    'props' =>
    array (
      'type' => 'longtext',
      'editable' => false,
    ),
    'spec' =>
    array (
      'type' => 'longtext',
      'editable' => false,
    ),
    'setting' =>
    array (
      'type' => 'longtext',
      'comment' => '类型设置',
      'width' => 110,
      'editable' => false,
    ),
    'minfo' =>
    array (
      'type' => 'longtext',
      'editable' => false,
    ),
    'params' =>
    array (
      'type' => 'longtext',
      'editable' => false,
    ),
    'dly_func' =>
    array (
      'type' => 'intbool',
      'default' =>'0',
      'required' => true,
      'editable' => false,
    ),
    'ret_func' =>
    array (
      'type' => 'intbool',
      'default' => '0',
      'required' => true,
      'editable' => false,
    ),
    'reship' =>
    array (
      'default' => 'normal',
      'required' => true,
      'type' =>
      array (
        'disabled' => __('不支持退货'),
        'func' => __('通过函数退货'),
        'normal' => __('物流退货'),
        'mixed' => __('物流退货+函数式动作'),
      ),
      'editable' => false,
    ),
    'disabled' =>
    array (
      'type' => 'bool',
      'default' => 'false',
      'editable' => false,
    ),
    'is_def' =>
    array (
      'type' => 'bool',
      'default' => 'false',
      'required' => true,
      'label' => __('类型标示'),
      'width' => 110,
      'editable' => false,
    ),
    'lastmodify' =>
    array (
      'label' => __('供应商最后更新时间'),
      'width'=>150,
      'type' => 'time',
      'hidden' => $hidden,
    ),
  ),
  'comment' => '商品类型表',
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