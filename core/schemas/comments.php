<?php
/**
* @table comments;
*
* @package Schemas
* @version $
* @copyright 2003-2009 ShopEx
* @license Commercial
*/

$db['comments']=array (
  'columns' =>
  array (
    'comment_id' =>
    array (
      'type' => 'number',
      'required' => true,
      'pkey' => true,
      'extra' => 'auto_increment',
      'label' => __('序号'),
      'editable' => false,
    ),
    'for_comment_id' =>
    array (
      'type' => 'number',
      'label' => __('对comments的回复'),
      'editable' => false,
      'hidden'=>true,
    ),
    'goods_id' =>
    array (
      'type' => 'object:goods/products',
      'required' => true,
      'default' => 0,
      'label' => __('咨询商品'),
      'editable' => false,
    ),
    'object_type' =>
    array (
      'type' =>
      array (
        'ask' => 'ask',
        'discuss' => 'discuss',
        'buy' => 'buy',
      ),
      'default' => 'ask',
      'required' => true,
      'label' => __('评论类型'),
      'editable' => false,
      'hidden'=>true,
    ),
    'author_id' =>
    array (
      'type' => 'number',
      'label' => __('会员(后台管理员)id'),
      'editable' => false,
      'hidden'=>true,
    ),
    'author' =>
    array (
      'type' => 'varchar(100)',
      'label' => __('咨询人'),
      'editable' => false,
      'searchtype'=>'tequal',
      'filtertype' => 'normal',
      'filterdefalut'=>true,
    ),
    'levelname' =>
    array (
      'type' => 'varchar(50)',
      'label' => __('会员等级'),
      'editable' => false,
      'filtertype' => 'bool',
    ),
    'contact' =>
    array (
      'type' => 'varchar(255)',
      'label' => __('联系方式'),
      'editable' => false,
      'filtertype' => 'normal',
      'filterdefalut'=>true,
      'escape_html'=>true,
    ),
    'mem_read_status' =>
    array (
      'type' => 'bool',
      'default' => 'false',
      'required' => true,
      'label' => __('会员阅读标识'),
      'editable' => false,
      'hidden'=>true,
    ),
    'adm_read_status' =>
    array (
      'type' => 'bool',
      'default' => 'false',
      'required' => true,
      'label' => __('已阅'),
      'editable' => false,
      'filtertype' => 'yes',
    ),
    'time' =>
    array (
      'type' => 'time',
      'required' => true,
      'default' => 0,
      'label' => __('咨询时间'),
      'editable' => false,
      'filtertype' => 'time',
      'filterdefalut'=>true,
    ),
    'lastreply' =>
    array (
      'type' => 'time',
      'required' => true,
      'default' => 0,
      'label' => __('回复时间'),
      'editable' => false,
      'filtertype' => 'time',
    ),
    'reply_name' =>
    array (
      'type' => 'varchar(100)',
      'label' => __('最后回复人'),
      'editable' => false,
    ),
    'title' =>
    array (
      'type' => 'varchar(255)',
      'label' => __('标题'),
      'editable' => false,
      'searchtype'=>'has',
      'filtertype' => 'normal',
      'filterdefalut'=>true,
      'escape_html'=>true,
    ),
    'comment' =>
    array (
      'type' => 'longtext',
      'label' => __('内容'),
      'editable' => false,
      'searchtype'=>'has',
      'filtertype' => 'normal',
      'filterdefalut'=>true,
      'escape_html'=>true,
    ),
    'ip' =>
    array (
      'type' => 'varchar(15)',
      'label' => __('咨询人IP'),
      'editable' => false,
    ),
    'display' =>
    array (
      'type' => 'bool',
      'default' => 'false',
      'required' => true,
      'label' => __('前台显示'),
      'editable' => false,
      'filtertype' => 'yes',
    ),
    'p_index' =>
    array (
      'type' =>
      array (
        1 => __('已置顶'),
        0 => __('无'),
      ),
      'default' => '0',
      'label' => __('置顶'),
      'editable' => false,
      'filtertype'=>'yes',
    ),
    'disabled' =>
    array (
      'type' => 'bool',
      'default' => 'false',
      'label' => __('失效'),
      'editable' => false,
      'hidden'=>true,
    ),
  ),
  'comment' => '商品评论表',
  'index' =>
  array (
    'ind_goods' =>
    array (
      'columns' =>
      array (
        0 => 'goods_id',
      ),
    ),
    'ind_member' =>
    array (
      'columns' =>
      array (
        0 => 'author_id',
      ),
    ),
    'ind_disabled' =>
    array (
      'columns' =>
      array (
        0 => 'disabled',
      ),
    ),
    'ind_pindex' =>
    array (
      'columns' =>
      array (
        0 => 'p_index',
      ),
    ),
  ),
);