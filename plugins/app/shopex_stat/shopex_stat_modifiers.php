<?php

include dirname(__FILE__).'/config.php';

class shopex_stat_modifiers extends pageFactory{

    function modify(&$content, $ctl){
        if ( defined('IN_AJAX') && IN_AJAX ) {
            return $content;
        }
        
        if ( !$ctl ) {
            $ctl = $this->system->ctl;
        }
        
        //判断用户是否同意分享数据 添加数据采集探头
        $certid = $this->system->getConf('certificate.id');
        $tencentuid = $this->system->getConf('app.umaylike.tencentuid');
        $content .= "\r\n<script>(function(){try{var shopexjs = document.createElement('script');
            shopexjs.type = 'text/javascript';
            shopexjs.async = true;
            shopexjs.src = '".STAT_JS_ADDR."';
            shopexjs.onload = shopexjs.onreadystatechange = function() {
            if (!this.readyState || this.readyState === 'loaded' || this.readyState === 'complete') {
            _ecq.push(['\$setSiteId','".$certid."']);\r\n";

        $content .= "_ecq.push(['\$setTencent','".$tencentuid."']);\r\n";
        $content .= $ctl->pagedata['shopex_stat_js'].
            "\r\nshopexjs.onload = shopexjs.onreadystatechange = null;
            }
            };
            var s = document.getElementsByTagName('script')[0];
            s.parentNode.insertBefore(shopexjs, s);}";
        $content .= "\r\ncatch(e){}
            })();</script>";
        return $content;
    }
}
