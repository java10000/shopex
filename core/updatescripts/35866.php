<?php
class UpgradeScript extends Upgrade{

    var $noticeMsg = array();
    function upgrade_checkdb(){
        $sql = "SELECT s_data FROM sdb_settings where s_name = 'system'";
        if($data = $this->db->selectrow($sql)){
            $data['s_data'] = unserialize($data['s_data']);
            $sql = "SELECT s_data FROM sdb_settings where s_name = 'site'";
            $d = $this->db->selectrow($sql);
            $d['s_data'] = unserialize($d['s_data']);
            if($data['s_data']['index_title']){
                $d['s_data']['homepage_title'] = $data['s_data']['index_title'];
            }
            $d['s_data']['goods_title'] = '{ENV_goods_name} - {ENV_goods_cat} {ENV_shopname}';
            $d['s_data']['list_title'] = '{ENV_goods_cat} {ENV_shopname}';
            $d['s_data']['brand_index_title'] = '品牌专区 {ENV_shopname}';
            $d['s_data']['brand_list_title'] = '{ENV_brand} {ENV_shopname}';
            $d['s_data']['article_list_title'] = '{ENV_article_cat} {ENV_shopname}';
            $d['s_data']['article_title'] = '{ENV_article_title} {ENV_shopname}';
            $this->db->exec("update `sdb_settings` set s_data = '".serialize($d['s_data'])."', s_time = ".time()." where s_name = 'site'");
        }

        $sql = "SELECT s_data FROM sdb_settings where s_name = 'messenger'";
        if($data = $this->db->selectrow($sql)){
            $data['s_data'] = unserialize($data['s_data']);
            if($data['s_data']['lostPw']){
                $data['s_data']['account-lostPw'] = $data['s_data']['lostPw'];
            }
            if($data['s_data']['order-delivery']){
                $data['s_data']['order-shipping'] = $data['s_data']['order-delivery'];
            }
            if($data['s_data']['order-pay']){
                $data['s_data']['order-payed'] = $data['s_data']['order-pay'];
            }
            if($data['s_data']['order-reship']){
                $data['s_data']['order-returned'] = $data['s_data']['order-reship'];
            }
            $this->db->exec("update `sdb_settings` set s_data = '".serialize($data['s_data'])."', s_time = ".time()." where s_name = 'messenger'");
        }

        $this->db->exec('INSERT INTO `sdb_triggers` (`trigger_id`, `filter_str`, `action_str`, `trigger_event`, `trigger_memo`, `trigger_define`, `trigger_order`, `active`, `disabled`) VALUES(1, \'所有\', \'增加积分100\', \'member/account:register\', \'注册会员即送100积分（可修改）\', \'a:2:{s:11:"filter_mode";s:5:"every";s:7:"actions";a:1:{i:0;a:2:{s:3:"act";s:15:"member:addPoint";s:4:"args";s:3:"100";}}}\', 5, \'false\', \'false\')');
        $this->db->exec('INSERT INTO `sdb_triggers` (`trigger_id`, `filter_str`, `action_str`, `trigger_event`, `trigger_memo`, `trigger_define`, `trigger_order`, `active`, `disabled`) VALUES(2, \'所有\', \'送优惠券3\', \'member/account:register\', \'注册会员即送10元优惠券（可修改）\', \'a:2:{s:11:"filter_mode";s:5:"every";s:7:"actions";a:1:{i:0;a:2:{s:3:"act";s:17:"member:sendcoupon";s:4:"args";s:1:"3";}}}\', 5, \'false\', \'false\')');
        $this->db->exec('INSERT INTO `sdb_triggers` (`trigger_id`, `filter_str`, `action_str`, `trigger_event`, `trigger_memo`, `trigger_define`, `trigger_order`, `active`, `disabled`) VALUES(3, \'留言被回复是\', \'增加积分10\', \'member/account:shopmessage\', \'会员留言被回复后即增加10积分（可修改）\', \'a:3:{s:11:"filter_mode";s:3:"all";s:6:"filter";a:1:{i:0;a:3:{s:3:"key";s:17:"shopmessage_reply";s:4:"test";s:4:"true";s:3:"val";s:0:"";}}s:7:"actions";a:1:{i:0;a:2:{s:3:"act";s:15:"member:addPoint";s:4:"args";s:2:"10";}}}\', 5, \'false\', \'false\')');
        $this->db->exec('INSERT INTO `sdb_triggers` (`trigger_id`, `filter_str`, `action_str`, `trigger_event`, `trigger_memo`, `trigger_define`, `trigger_order`, `active`, `disabled`) VALUES(4, \'评论被审核是\', \'增加积分10\', \'member/account:discuzz\', \'会员进行商品评论被审核后增加10积分（可修改）\', \'a:3:{s:11:"filter_mode";s:3:"all";s:6:"filter";a:1:{i:0;a:3:{s:3:"key";s:13:"discuzz_check";s:4:"test";s:4:"true";s:3:"val";s:0:"";}}s:7:"actions";a:1:{i:0;a:2:{s:3:"act";s:15:"member:addPoint";s:4:"args";s:2:"10";}}}\', 5, \'false\', \'false\')');

        $this->db->exec('INSERT INTO `sdb_magicvars` (`var_name`, `var_title`, `var_remark`, `var_value`, `var_type`, `disabled`) VALUES
(\'{register_message}\', \'会员注册页上方提示信息\', \'商店前台 > 用户注册\', \'欢迎来到我们网站，如果您是新用户，请填写下面的表单进行注册<br>（此为默认内容，具体内容可以在后台“页面管理-提示信息管理”中修改）\n<br>\', \'system\', \'false\'),
(\'{login_message}\', \'会员登陆页上方提示信息\', \'商店前台 > 用户登录\', \'如果您已是本站会员，请登录<br>（此为默认内容，具体内容可以在后台“页面管理-提示信息管理”中修改）\n<br>\', \'system\', \'false\'),
(\'{lost_password}\', \'忘记密码页上方提示信息\', \'商店前台 > 忘记密码\', \'如果忘记密码，请填写下面表单来重新获取密码<br>（此为默认内容，具体内容可以在后台“页面管理-提示信息管理”中修改）\n<br>\', \'system\', \'false\'),
(\'{buy_product}\', \'购物车商品列表上方提示信息\', \'商店前台 > 购物车\', \'请在此确认你要购买的商品<br>（此为默认内容，具体内容可以在后台“页面管理-提示信息管理”中修改）\n<br>\', \'system\', \'false\'),
(\'{nologin_buy}\', \'购物车登陆页直接购买提示信息\', \'商店前台 > 购物车登陆页\', \'无需注册会员，直接下订单购买商品<br>（此为默认内容，具体内容可以在后台“页面管理-提示信息管理”中修改）\n<br>\', \'system\', \'false\'),
(\'{reg_succ_mess}\', \'会员注册成功页上方提示信息\', \'商店前台 > 用户注册 > 注册成功\', \'<ul class="list"><li>请补充下列信息</li><li>本商店将最大限度保护您的隐私。</li><li>当然您不购买商品也可以成为本站用户，请填写下面的信息注册。</li><li>（此为默认内容，具体内容可以在后台“页面管理-提示信息管理”中修改）\n</li></ul>\', \'system\', \'false\'),
(\'{pay_message}\', \'订单付款页(线上付款)提示信息\', \'商店前台 > 购物车 > 下订单最后一步\', \'<h3>本网站支持的在线支付方式：</h3>\n（此为默认内容，具体内容可以在后台“页面管理-提示信息管理- 订单付款页(线上付款)提示信息”中修改）\n<table class="liststyle data" border="0" cellpadding="5" cellspacing="5" width="100%">\n  <tbody><tr>\n    <td><img src="statics/copyright_alipay.gif"></td>\n    <td>支付宝（中国）网络技术有限公司是国内领先的独立第三方支付平台，由阿里巴巴集团创办。支付宝致力于为中国电子商务提供“简单、安全、快速”的在线支付解决方案。\n<a href="https://www.alipay.com/static/utoj/utojindex.htm" target="_blank">如何使用支付宝支付？</a></td>\n  </tr>\n  <tr>\n    <td><img src="statics/copyright_tenpay.gif"></td>\n    <td>财付通是腾讯公司于2005年9月正式推出专业在线支付平台，致力于为互联网用户和企业提供安全、便捷、专业的在线支付服务。<a href="http://help.tenpay.com/helpcenter/guidelines.shtml" target="_blank">\n如何使用财务通付款？</a></td>\n  </tr>\n</tbody></table>\n为了更好的让用户完成购物流程，您可以根据您自己网站的情况来，以图文或者视频的方式来告知您的顾客如何进行在线付款。\', \'system\', \'false\'),
(\'{pay_offline}\', \'订单付款页(线下付款)提示信息\', \'商店前台 > 购物车 > 下订单最后一步\', \'<h3>我们目前支持的汇款方式，请根据您选择的支付方式来选择银行汇款。汇款以后，请立即通知我们。</h3>这里是默认内容，你可以在后台的“页面管理-提示信息管理-订单付款页(线下付款)提示信息”来修改这部分的内容<br> <table class="liststyle data" cellpadding="0" cellspacing="0"> <tbody> <tr> <th rowspan="3"><img alt="" src="statics/bank/zsyh.gif" border="0"></th> <td>持卡人：</td> <td>XXX</td> </tr> <tr> <td>卡号：</td> <td>6225800210121256 <strong>(推荐) </strong></td> </tr> <tr> <td align="left">开户行：</td> <td align="left">招行上海长乐支行</td> </tr> <tr> <th rowspan="3"><img alt="" src="statics/bank/gsyh.gif" border="0"></th> <td>持卡人：</td> <td>XXX</td> </tr> <tr> <td>卡号：</td> <td>9558801001142896791<strong>(推荐)</strong></td> </tr> <tr> <td align="left">开户行：</td> <td align="left">工行上海石城路支行</td> </tr> <tr> <th rowspan="3"><img alt="" src="statics/bank/jsyh.gif" border="0"></th> <td>持卡人：</td> <td>XXX</td> </tr> <tr> <td>卡号：</td> <td>4367421216884211030<strong>(推荐)</strong></td> </tr> <tr> <td align="left">开户行：</td> <td align="left">建行上海华池路支行</td> </tr> <tr> <th rowspan="3"><img alt="" src="statics/bank/nyyh.gif" border="0"></th> <td>持卡人：</td> <td>XXX</td> </tr> <tr> <td>卡号：</td> <td>9559980030143605514</td> </tr> <tr> <td align="left">开户行：</td> <td align="left">农行上海长宁区定西路支行</td> </tr> <tr> <th rowspan="3"><img alt="" src="statics/bank/zgyh.gif" border="0"></th> <td>持卡人：</td> <td>XXX</td> </tr> <tr> <td>卡号：</td> <td>4563510800013323984</td> </tr> <tr> <td align="left">开户行：</td> <td align="left">中行上海普陀分行长寿路支行</td> </tr> </tbody> </table>\', \'system\', \'false\'),
(\'{pay_succ}\', \'订单付款成功返回页提示信息\', \'商店前台 > 订单付款成功返回页\', \'<a href="index.php">返回首页</a><br>（此为默认内容，具体内容可以在后台“页面管理-提示信息管理”中修改）\n<br>\', \'system\', \'false\'),
(\'{pay_wait}\', \'订单付款页(货到付款)提示信息\', \'商店前台 > 购物车 > 下订单最后一步\', \'您选择的是货到付款的方式，您只需静待快递人员上门，然后把货款支付给快递人员就可以了，如果有问题请及时联系我们，我们的电话是：800-828-8888。<br>【此段为演示，您可以在“页面管理--提示信息管理中--订单付款页(货到付款)提示信息”中修改这段话。】\', \'system\', \'false\')');

        $this->db->exec('ALTER TABLE `sdb_products` ADD INDEX (`bn`( 30 ))');
        $this->db->exec('ALTER TABLE `sdb_supplier_pdtbn` ADD INDEX (`local_bn`( 30 ))');
        $this->db->exec('ALTER TABLE `sdb_supplier_pdtbn` ADD INDEX (`source_bn`( 30 ))');
        //SEO保存设置
        $seoSql = "SELECT goods_id,p_value FROM sdb_goods_memo where p_key = 'seo_info'";
        if($rs = $this->db->select($seoSql)){
             foreach ($rs as $key=>$val){
                 $p_value = unserialize($val['p_value']);
                 $source_id = $val['goods_id'];
                 $type = 'goods';
                 foreach ($p_value as $keys=>$vals){
                     if($keys == 'seo_title')
                     $keys = 'title';
                     if($keys == 'meta_keywords')
                     $keys = 'keywords';
                     if($keys == 'meta_description')
                     $keys = 'descript';
                     $store_key = $keys;
                     $value = $vals;
                     if(isset($value)){
                         $asql = "INSERT INTO sdb_seo (source_id,type,store_key,value) VALUES ('$source_id','$type','$store_key','$value')";
                         $this->db->exec($asql);
                     }
                 }
             }
        }


        $cachedir = HOME_DIR.'/cache/front_tmpl';
        if(($handle = opendir($cachedir))){
            while (false !==($file = readdir($handle))){
                if($file!='.' && $file!='..'){
                    @unlink($cachedir.'/'.$file);
                }
            }
            closedir($handle);
        }

        $addons = $this->system->loadModel('system/addons');
        $addons->refresh();

        return 'finish';
    }
}
?>