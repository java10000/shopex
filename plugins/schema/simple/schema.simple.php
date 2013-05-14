<?php
class schema_simple{

    var $name='通用商品类型';
    var $version='$Id: schema.php 11548 2008-06-26 08:14:24Z ever $';
    var $use_brand = true;
    var $use_params = false;
    var $use_props = false;
    var $use_spec = true;
    var $is_def = true;

/*    function pre_commit(&$post,&$message,&$pagedata,&$errmsg){
        $post = array(
                            'is_physical'=>true,
                            'params'=>false,
                            'setting'=>$post['bbs'],
                            'props'=>false,
                            'name'=>$post['name']
                        );
            $post['is_physical'] = true;
            $post['params'] = false;
            $post['setting'] = $post['bbs'];
            $post['props'] = false;
            $post['name'] = $post['name'];
        return true;
    }*/
}
?>