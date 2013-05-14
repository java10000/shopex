<?php

$db['image_sync'] = array (
    'columns' => 
    array(
        'img_sync_id' => 
        array (
            'type' => 'int unsigned',
            'required' => true,
            'pkey' => true,
            'extra' => 'auto_increment',
        ),
        'type' => 
        array (
            'type' => array(
                'gimage' => __(''),
                'spec_value' => __(''),
                'udfimg' => __(''),
                'brand_logo' => __(''),
            ),
            'required' => true,
            'default' => 'gimage',
        ),
        'supplier_id' => 
        array (
            'type' => 'int unsigned',
            'required' => true,
            'default' => 0,
        ),
        'supplier_object_id' => 
        array (
            'type' => 'number',
            'required' => true,
            'default' => 0,
        ),
        'add_time' => 
        array (
            'type' => 'int unsigned',
            'required' => true,
            'default' => 0,
        ),
        'command_id' => 
        array (
            'type' => 'int unsigned',
            'required' => true,
            'default' => 0,
        ),
        'failed' => 
        array (
            'type' => array(
                'true' => __(''),
                'false' => __(''),
            ),
            'required' => true,
            'default' => 'false',
        ),
    ),
);

