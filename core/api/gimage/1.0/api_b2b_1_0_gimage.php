<?php
include_once(CORE_DIR.'/api/shop_api_object.php');
class api_b2b_1_0_gimage extends shop_api_object {
    var $max_number=100;
    function getColumns(){
        $columns=array(
         'source'=>array('type'=>'string')
        );
        return $columns;
    }

    /**
     * 获取gimage信息
     *
     * @param array $data 必须包含gimage_id
     *
     * @return 输出图片信息
     */
    function search_gimage_info($data){
        $gimage = $this->db->selectrow('select source,big from sdb_gimages where gimage_id='.intval($data['gimage_id']));
        if(!$gimage || (empty($gimage['source']) && empty($gimage['big'])) ){
           echo '';
           exit;
        }
        /* 修正远程图片平台无法获取图片数据BUG by yangbing 2010-3-30 */
        if($gimage['source'] != 'N' && !strstr($gimage['source'],'http') && !strstr($gimage['source'],'https')){
            $return_img = file_get_contents(HOME_DIR.'/upload/'.$gimage['source']);
            $image_filename = basename($gimage['source']);
        }else if(!empty($gimage['big']) && !strstr($gimage['big'],'http') && !strstr($gimage['big'],'https')){
            $objStorager = &$this->system->loadModel('system/storager');
            $gimage_url = $objStorager->getUrl($gimage['big']);
            $return_img = file_get_contents($gimage_url);
        }else{
            if($gimage['source'] != 'N'){
                $gimage_img = $gimage['source'];
            }else{
                $gimage_img = $gimage['big'];
            }
            $objStorager = &$this->system->loadModel('system/storager');
            $obj_tools = $this->load_api_instance('get_http','1.0');
            
            $gimage_url = $objStorager->getUrl($gimage_img);
            
            $arr_http = $obj_tools->get_http_var($gimage_url); 
            $return_img = $obj_tools->get_http($arr_http['host'],$arr_http['port'],$arr_http['path'],'',5);
            $image_filename = basename($gimage_url);
        }  

        if(!empty($return_img)){
            header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
            header('Content-type: application/octet-stream');
            header('Content-type: application/force-download');
            header('Content-Disposition: attachment; filename="'.$image_filename.'"');
            echo $return_img;
            exit;
        }else{
           echo '';
           exit;
        }     
    }


    /**
    * 图片的应用级错误
    * @param string 错误原码
    * @author DreamDream
    * @return array 错误信息
    */
    function application_error($code){
        $error=array(

        );
        return $error[$code];
    }

}