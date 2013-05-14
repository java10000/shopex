<?php
class commodity_radar_default_modifiers extends pageFactory{
    var $script_url = 'http://js.sradar.cn/RadarForMootools.js';

    function commodity_radar_default_modifiers(){
        parent::pageFactory();
        $this->system = &$GLOBALS['system'];
    }

    function index(&$content){
        $find = '<script type="text/javascript" src="js/fixie6.js"></script>';
        $replace = $find.'<script type="text/javascript" src="'.$this->script_url.'"></script>'."\n";
        $content = str_replace($find,$replace,$content);
        return $content;
    }
    
    function product_list(&$content){
        if (!class_exists('commodity_radar_public')) require_once 'commodity_radar_public.php';
        $radar = new commodity_radar_public();
        
        $license_id     = $radar->certi_id;
        $product_key    = $radar->product_key;
        $data = array(
            'radar_lincense_id' =>  $license_id,
            'radar_product_key' =>  $product_key,
        );
        $sign_key       = $radar->get_sign($data);
        
        $public_data = ' <div style="display:none"><input type="hidden" id="radar_lincense_id" value="'.$license_id.'" />
                        <input type="hidden" id="radar_product_key" value="'.$product_key.'" />
                        <input type="hidden" id="radar_taobao_cat_id" value="" />
                        <input type="hidden" id="radar_sign_key" value="'.$sign_key.'" /></div> ';
        
        $find = '<!-----.mainHead-----';
        $replace = '<script>(function(){radar_point_extract();})()</script>'."\n".$find.$public_data;
        $content = str_replace($find,$replace,$content);
        return $content;
    }

}
?>
