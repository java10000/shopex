<?php
$db['job_apilist'] = array (
    'columns' => 
    array(
      'job_id' => 
      array (
        'type' => 'int unsigned',
        'required' => true,
        'pkey' => true,
        'extra' => 'auto_increment',
      ),
      'supplier_id' => 
      array (
        'type' => 'int unsigned',
            'required' => true,
            'default' => 0,
      ),
      'api_name' => 
      array (
        'type' => 'varchar(100)',
            'required' => true,
      ),
      'api_params' => 
      array (
        'type' => 'longtext',
      ),
      'api_version' => 
      array (
        'type' => 'varchar(10)',
            'required' => true,
      ),
      'api_action' => 
      array (
        'type' => 'varchar(100)',
            'required' => true,
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
    )
);

