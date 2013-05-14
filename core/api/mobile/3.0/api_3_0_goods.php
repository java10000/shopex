<?php
include_once(CORE_DIR.'/api/shop_api_object.php');
/**
 * API 模块部份
 * @package
 * @version 3.0: 
 * @copyright 2003-2011 ShopEx
 * @author 
 * @license Commercial
 */

class api_3_0_goods extends shop_api_object {
    function get_brand(){
    /*$data=array(
        '0'=>array(
             'brand_id'=>'品牌id',
             'brand_name'=>'品牌名称',
             'brand_logo'=>'图片地址',
             'brand_url'=>'品牌网址',
             'brand_desc'=>'详细说明'
         ),
        '1'=>array(
             'brand_id'=>'品牌id',
             'brand_name'=>'品牌名称',
             'brand_logo'=>'图片地址',
             'brand_url'=>'品牌网址',
             'brand_desc'=>'详细说明'
         ),
         .........
       );*/
        $sql="SELECT brand_id,brand_name,brand_logo,brand_url,brand_desc FROM sdb_brand WHERE disabled = 'false'";
        $return_data = $this->db->select($sql);
        $this->api_response('true','',$return_data);
    }

    function get_classification(){
        $file = MEDIA_DIR.'/goods_cat.data';
        $contents = file_get_contents($file);
        $arr=json_decode($contents,true);
        foreach($arr as $val){
            $value['cid']=$val['cat_id'];
            $value['name']=$val['cat_name'];
            $value['parent_cid']=$val['pid'];
            $value['sort_order']=$val['p_order'];
            $value['path']=$val['cat_path'];
            $data[]=$value;
        }
        if(!$data){
            $sql='SELECT cat_id as cid,cat_name as name,parent_id as parent_cid,cat_path as path,p_order as sort_order FROM sdb_goods_cat';
            $data=$this->db->select($sql);
        }
        $this->api_response('true','',$data);
    }

}