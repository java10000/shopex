<?php

$db['job_data_sync']=array (
    'columns' => 
    array(
      'job_id' => 
      array (
        'type' => 'int unsigned',
      'required' => true,
      'pkey' => true,
        'extra' => 'auto_increment',
      ),
      'from_time' => 
      array (
        'type' => 'int unsigned',
            'required' => true,
            'default' => 0,
      ),
      'to_time' => 
      array (
        'type' => 'int unsigned',
            'required' => true,
            'default' => 0,
      ),
      'page' => 
      array (
        'type' => 'number',
            'required' => true,
            'default' => 0,
      ),
      'limit' => 
      array (
        'type' => 'number',
            'required' => true,
            'default' => 0,
      ),
      'supplier_id' => 
      array (
        'type' => 'int unsigned',
            'required' => true,
            'default' => 0,
      ),
      'supplier_pline' => 
      array (
        'type' => 'longtext',
      ),
      'auto_download' => 
      array (
        'type' => array(
            'true'=>__(''),
            'false'=>__(''),
        ),
        'default' => 'false',
            'required' => true,
      ),
      'to_cat_id' => 
      array (
        'type' => 'number',
        'default' => null,
      ),
    )
);
