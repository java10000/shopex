<?php defined('CORE_DIR') || exit('入口错误'); ?>
<?php
    class ctl_cent_save{
        function ctl_cent_save(){
            $this->system = &$GLOBALS['system'];
        }
        function receive_infor(){
            if($_POST['certi_ac']&&($_POST['certi_ac'] == $this->make_shopex_ac($_POST,$this->system->getConf('certificate.token')))){
                $cen_data = $_POST;
                $this->system->setConf("system.shopname",$cen_data['shop_name']);
                $this->system->setConf("store.mobile",$cen_data['mobile']);
                $this->system->setConf("store.email",$cen_data['email']);    
                $this->system->setConf("store.contact",$cen_data['contact']);
                $this->system->setConf("store.qq",$cen_data['qq']);    
                $this->system->setConf("store.shop_type",$cen_data['shop_type']);    
                $this->system->setConf('store.sell_type',$cen_data['sell_type']);
                $this->system->setConf('store.city',$cen_data['city']);
                $this->system->setConf('store.province',$cen_data['province'],1);
            }
        }

        function make_shopex_ac($post_params,$token){
            ksort($post_params);
            $str = '';
            foreach($post_params as $key=>$value){
                if($key!='certi_ac') {
                    $str.=$value;
                }
            }

            return md5($str.$token);
        }



    }



?>