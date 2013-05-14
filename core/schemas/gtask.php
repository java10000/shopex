<?php
/**
* @table plugins;
*
* @package Schemas
* @version $
* @copyright 2003-2009 ShopEx
* @license Commercial
*/

$db['gtask']=array (
  'columns' =>
      array (
        'goods_id' =>
        array (
          'type' => 'object:goods/products',
          'comment'=>'商品id',
        ),
        'tasktime' =>
        array (
          'type' => 'time',
          'comment'=>'时间',
          'required' => true,
          'default' => 0,
        ),
        'action' =>
        array (
          'type' => array('online'=>'上架','offline'=>'下架'),
          'comment'=>'动作',
          'required' => true,
          'default' => 'online',
        ),
   ),
  'index'=>array (
    'tasktime' => array (
          'columns' =>array ('tasktime'),
       ),
    'goods' => array (
          'columns' =>array ('goods_id'),
       ),
    ),
);