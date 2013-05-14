<?php
include_once(CORE_DIR.'/api/shop_api_object.php');
class api_b2b_3_0_order extends shop_api_object {
    var $app_error=array(
            
    );
    
    /**
     * 获取订单设置
     * 
     * cur_sign: 货币符号
     * decimals: 前台商品价格精确到几位
     * carryset: 价格进位方式
     * dec_point: 小数符号
     * thousands_sep: 千位符号
     * decimal_digit: 0:整数取整,1:取整到1位小数,2:取整到2位小数,取整到3位小数....
     * decimal_type: 0:四舍五入,1:向上取整,2:向下取整
     * trigger_tax: 是否设置含税价格,0/1
     * tax_ratio: 税率(去除%后的数字)
     */
    function get_order_setting(){
        $obj_payments = $this->load_api_instance('search_payments_by_order','2.0');
        $cursign = $obj_payments->getcur('CNY');
        $return = array(
            'cur_sign' =>$cursign['cur_sign'],
            'decimals' => $this->system->getConf('system.money.operation.decimals'),
            'carryset' => $this->system->getConf('system.money.operation.carryset'),
            'dec_point' => $this->system->getConf('system.money.dec_point'),
            'thousands_sep' => $this->system->getConf('system.money.thousands_sep'),
            'decimal_digit' => $this->system->getConf('site.decimal_digit'),
            'decimal_type' => $this->system->getConf('site.decimal_type'),
            'trigger_tax' => $this->system->getConf('site.trigger_tax'),
            'tax_ratio' => $this->system->getConf('site.tax_ratio')
        );
        
        $this->api_response('true',false,$return);
    }
}