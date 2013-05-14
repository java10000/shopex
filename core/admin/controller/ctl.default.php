<?php defined('CORE_DIR') || exit('入口错误'); ?>
<?php
class ctl_default extends adminPage{

    function index(){
        $mdl = $this->system->loadModel('service/apiclient');
        $certid = $this->system->loadModel('service/certificate')->getCerti();
        $mdl->key = '371e6dceb2c34cdfb489b8537477ee1c';
        $mdl->url = 'http://esb.shopex.cn/api.php';
        $s = $mdl->native_svc('service.get_appkey',array('certificate_id'=>$certid));
        
        $this->pagedata['statusId'] = $this->system->getConf('shopex.wss.enable');
        if(!IN_AJAX){
            foreach($_GET as $k=>$v){
                if(substr($k,0,1)=='_' && strlen($k)>1){
                    $setting[substr($k,1)] = $v;
                }
            }
            if(constant('SAAS_MODE')){
                $saas = &$this->system->loadModel('service/saas');
                if($shopinfo = $saas->native_svc('host.getinfo',array('host_id'=>HOST_ID))){
                    if($shopinfo['response_code']>0){
                        $this->pagedata['shop_service_info'] = $shopinfo['response_error'];
                    }else{
                        $this->pagedata['shop_service_info'] .= $shopinfo['service_name'];
                        $this->pagedata['shop_service_info'] .= $shopinfo['status']=='tryout'?__('(试用)'):'';
                        $this->pagedata['shop_service_info'] .= '['.date('y/m/d',$shopinfo['add_time'])
                                                        .'-'.date('y/m/d',$shopinfo['finish_time']).']';
                    }
                }
            }

            $titlename = $this->system->getConf('system.shopname');
            $this->pagedata['title'] = $titlename.' - Powered By ShopEx';
            $this->pagedata['shopname'] = (empty($titlename) ? __("点此设置商店名称") : $titlename);
            $this->pagedata['session_id'] = $this->system->sess_id;
            $this->pagedata['status_url'] = urlencode(PHP_SELF.'?ctl=default&act=status&sess_id='.$this->system->sess_id);
            $this->pagedata['shopadmin_dir']= '/'.SHOPADMIN_PATH.'/';
            $this->pagedata['shop_base']=$this->system->base_url();
            $this->pagedata['uname'] = $this->system->op_name;
            $this->pagedata['shop_type_info'] = '1to1' == $this->system->getConf('system.b2c_shop_type')?'批零店':'加盟连锁店';


            if(!function_exists('admin_menu_filter')){
                require(CORE_INCLUDE_DIR.'/shop/admin.menu_filter.php');
            }
            $this->pagedata['menu'] = &admin_menu_filter($this->system,null);
            $this->_fetchM($this->pagedata['menu'],$menus,array());

            $menus = array_values($menus);
            foreach($menus as $i=>$m){
                foreach($menus[$i]['key'] as $k=>$v){
                    $mkey[]=array($k,$i);
                }
                unset($menus[$i]['key']);
            }

            $i = count($menus);
            foreach($mlist as $k=>$v){
                $menus[$i] = $v;
                $mkey[] = array($k,$i);
                $i++;
            }
            $this->pagedata['guide']=$this->system->getConf('system.guide');

            $this->pagedata['scripts'] = find(dirname($_SERVER['SCRIPT_FILENAME']).'/js_src','js');
            $this->pagedata['mlist'] =array('menus'=>&$menus,'key'=>&$mkey);
            $this->display('index.html');
        }else{
            $this->system->error(401);
        }
    }

    function getAppChange(){
        $center = $this->system->loadModel('service/app_center');
        $data = $center->get_tools_status();
        $appmgr = $this->system->loadModel('system/appmgr');
        $app_data = $appmgr->getList();
        $output['update_count'] = $app_data['update_count'];
        $output['status'] = $data['result'];
        echo json_encode($output);
    }

    function _fetchM($menu,&$arr,$p){
        foreach($menu as $m){
            if($m['link']){
                if(isset($arr[$m['link']])){
                    $arr[$m['link']]['key'][$m['label']]=1;
                }else{
                    $arr[$m['link']] = array('link'=>$m['link'],'path'=>((count($p)>0?implode('/',$p).'/':'').$m['label']),'key'=>array($m['label']=>1));
                }
                if($m['keywords']){
                    foreach($m['keywords'] as $k){
                        $arr[$m['link']]['key'][$k]=1;
                    }
                }
            }
            if($m['items']){
                $np = array_slice($p,0);
                $np[]=$m['label'];
                $this->_fetchM($m['items'],$arr,$np);
            }
        }
    }

    function tnode($model,$id,$depth){
        $o = &$this->system->loadModel($model);
        $this->pagedata['item'] = $options = $o->treeOptions();
        $this->pagedata['item']['items']=$o->getNodes($id);
        $this->pagedata['item']['model']=$model;
        $this->pagedata['depth'] = $depth+1;
        $this->display('treeNode.html');
    }

    function uploadSplash(){
        foreach($_POST as $k=>$v) {
            if ($v=='null') {
                unset($_POST[$k]);
            }
        }
        echo '<script>top.$("loadMask").hide();top.MODALPANEL.hide();</script>';
        call_user_func_array(array(&$this,'splash'),$_POST);
    }

    function status(){
        header('Content-type: text/html; charset=UTF-8');
        $status = &$this->system->loadModel('system/status');
        $storeless = intval($this->system->getConf('system.product.alert.num'));
        $this->pagedata['allstatus'] = array(
            'ORDER_NEW'=>array('label'=>__('未处理订单'),'url'=>'index.php?ctl=order/order&act=index&view=1&filter='.urlencode(serialize(array('pay_status'=>array(
                            'v'=>'0',
                            't'=>'未处理'
                        ))))),
            'GOODS_ALERT'=>array('label'=>__('库存报警'),'url'=>'index.php?ctl=goods/product&act=index&filter='.urlencode(serialize(array('storeless'=>array(
                            'v'=>$storeless,
                            't'=>'库存小于等于'.$storeless
                        ))))),
            'GNOTIFY'=>array('label'=>__('缺货通知'),'url'=>'index.php?ctl=goods/gnotify&act=index&filter='.urlencode(serialize(array('status'=>array(
                            'v'=>'ready',
                            't'=>'未发送'
                        ))))),
            'GDISCUSS'=>array('label'=>__('商品评论'),'url'=>'index.php?ctl=goods/discuss&act=index&filter='.urlencode(serialize(array('adm_read_status'=>array(
                            'v'=>'false',
                            't'=>'未阅读'
                        ))))),
            //'GASK'=>array('label'=>__('购买咨询'),'url'=>'index.php?ctl=member/gask&act=index'),
            'GASK'=>array('label'=>__('购买咨询'),'url'=>'index.php?ctl=member/gask&act=index&filter='.urlencode(
            serialize(array('adm_read_status'=>array(
                            'v'=>'false',
                            't'=>'未阅读',
                        ),
                            'for_comment_id'=>array(
                            'v'=>NULL,
                            't'=>'用户',
                        ))))),
            'GOODS_ONLINE'=>array('label'=>__('上架商品'),'url'=>'index.php?ctl=goods/product&act=index'),
            'ORDER_MESSAGE'=>array('label'=>__('新留言订单'),'url'=>'index.php?ctl=order/order&act=new_order_message_list'),
            );
        $status_data = $status->getList();
        $oBbs = $this->system->loadModel('resources/shopbbs');
        $status_data['ORDER_MESSAGE'] = $oBbs->getNewOrderMessage();

        $oProduct = $this->system->loadModel('goods/finderPdt');
        $filter_p['store_alarm'] = $this->system->getConf('system.product.alert.num');
        foreach($oProduct->getList('goods_id', $filter_p, 0, 1000) as $row){
            $filter['goods_id'][] = $row['goods_id'];
        }
        
        if(empty($filter['goods_id'])) $filter['goods_id'][] = -1;
        unset($filter_p);

        $oGoods = &$this->system->loadModel('goods/products');
        $alert_count = $oGoods->count($filter);
        $status_data['GOODS_ALERT'] = $alert_count;
        
        // 插件注册定时任务
        $appmgr = &$this->system->loadModel('system/appmgr');
        foreach(unserialize($this->system->getConf("system.crontab_queue")) as $k =>$v ){
            list($objCtl,$act_method) = $appmgr->get_func($v);
            if(method_exists($objCtl,$act_method)){
                $objCtl->$act_method();
            }
        }
        
        // 系统消息队列
        $messenger = &$this->system->loadModel('system/messenger');
        $messenger->runQueue();

        // csm信息
        $this->_action_site_info();
        
        $this->pagedata['status'] = $status_data;
        echo $this->fetch('status.html');
        flush();
        set_time_limit(0);
        
        // 保存自定义列宽
        foreach($_POST['events'] as $event=>$detail){
            if(method_exists($this,$action = '_action_'.$event)){
                $this->$action($detail);
            }
        }
        $this->system->__session_close(1);
    }

    function _action_finder_colset($params){
        foreach($params as $ctl=>$list){
            echo 'colwith.'.$ctl."\n";
            if($set = $this->system->get_op_conf('colwith.'.$ctl)){
                $this->system->set_op_conf('colwith.'.$ctl,array_merge($set,$list));
            }else{
                $this->system->set_op_conf('colwith.'.$ctl,$list);
            }
        }
    }

    function sel_region($path,$depth){
         header('Content-type:text/html;charset=utf-8');
        $local = &$this->system->loadModel('system/local');
        if($ret = $local->get_area_select($path)){
            echo '&nbsp;-&nbsp;'.$local->get_area_select($path,array('depth'=>$depth));
        }else{
            echo '';
        }
    }

    function get_menulist($searchPanel){
       header('Content-type:text/html;charset=utf-8');
      require('adminSchema.php');
      if (is_array($menu)){
        foreach($menu as $key => $val){
            foreach($val as $skey => $sval){
                foreach($sval as $sskey=>$ssval){
                    if ($ssval['type']=="group"){
                        foreach($ssval['items'] as $ssskey =>$sssval){
                            if ($sssval['type'] == "menu"){
                                $tmpMenu[]=array(
                                    "label"=>$sssval['label'],
                                    "link"=>$sssval['link']
                                );
                            }
                        }
                    }
                }
            }
        }
      }

      if($searchPanel){
         $this->display('menuSearch.html');
         exit;
      }
    }

    function check_api_maintenance(){
        $notice = get_http(PLATFORM_HOST,PLATFORM_PORT,SERVER_PLATFORM_NOTICE);
        if(strlen($notice) == 0){   //没有维护
            $this->system->setConf('site.api.maintenance.is_maintenance',false,true);
            $this->system->setConf('site.api.maintenance.notify_msg','',true);
        }else{
            $this->system->setConf('site.api.maintenance.is_maintenance',true,true);
            $this->system->setConf('site.api.maintenance.notify_msg',$notice,true);
        }
        //加入解锁下载列表中锁定的任务sdb_job_goods_download
        /*$mdl_sync = $this->system->loadModel('distribution/syncjob');
        $mdl_sync->unlock_goods_download();*/
        echo $notice;
    }


    function shownewtools(){
        $this->display('appTaobaoIntro.html');
    }

    function getcertidandurl(){
        $cet_ping = ping_url("http://guide.ecos.shopex.cn/index.php");
        if(!strstr($cet_ping,'HTTP/1.1 200 OK')){
            echo $this->system->base_url().'error.html';
        }else{
            $certi_model = $this->system->loadModel("service/certificate");
            $cert_id = $this->system->getConf("certificate.id");
            $base_url = urldecode($this->system->base_url());
            $sess_id = $certi_model->get_sess();
            $confirmkey = md5($sess_id.'ShopEx@License'.$cert_id);
            $center_url = "http://guide.ecos.shopex.cn/index.php?certi_id=".$cert_id.'&url='.urlencode($base_url).'&confirmkey='.$confirmkey.'&sess_id='.$sess_id;
            echo $center_url;
        }
    }

    function frame_include(){
        echo "<script>
        (function getHash(){
          var url=decodeURIComponent(location.hash);
        
          var param=url.substr(1).split('=');
          switch (param[1]){
            case 'close':
                top.$('user_guide_iframe').getParent('.dialog').retrieve('instance').close();
                break;
            case 'checked':
                new top.XHR({method:'post',data:'check='+param[0]}).send('index.php?ctl=system/setting&act=guide_status');
            break;    
            default:
              if(url=='#../') url = '../'
              top.location.href='".$this->system->base_url().SHOPADMIN_PATH."/'+url;     
              top.$('user_guide_iframe').getParent('.dialog').retrieve('instance').close();
            break;
    }    
        })();
        
        </script>";
    }
    
    // 今天推送昨天的数据；一天一次推送
    function _action_site_info() {
        $today = strtotime('today')-1;
        $yesterday = strtotime('yesterday')+1;// yesterday
        
        // 判断今天有没有推送过
        if ( $today < $this->system->loadModel('system/status')->get('CSM_LAST_PUSH') ) return;
        
        // 整理数据
        $member_count = $this->system->db->select("SELECT COUNT(1) c FROM sdb_members WHERE regtime>$yesterday AND regtime<$today");
        $order_count = $this->system->db->select("SELECT COUNT(1) c FROM sdb_orders WHERE createtime>$yesterday AND createtime<$today");
        $order_amount = $this->system->db->select("SELECT SUM(total_amount) s FROM sdb_orders WHERE createtime>$yesterday AND createtime<$today");
        $order_amount = $order_amount[0]['s'];
        
        $admin_login = $this->system->db->select("SELECT SUM(logincount) as s,MAX(lastlogin) as last FROM sdb_operators");
        
        $activity = $this->system->db->count("SELECT COUNT(1) FROM sdb_promotion_activity");
        $cate = $this->system->db->count("SELECT COUNT(1) FROM sdb_goods_cat");
        $goods = $this->system->db->count("SELECT COUNT(1) FROM sdb_goods");
        
        $sitebegintme = $this->system->db->select("SELECT s_time FROM sdb_settings");
        
        $arr = array(
            'data.date'=>date('Y-m-d',strtotime('yesterday')+1),
            'data.currenttime'=>date('Y-m-d H:i:s',NOW),
            'name'=>$this->system->getConf('system.shopname'), // 店铺名称
            'domain'=>$this->system->getConf('store.shop_url'), // 店铺域名
            'starttime'=> $sitebegintme[0]['s_time'], // 店铺开始时间
            'site.customname'=>$this->system->getConf('store.contact'), // 联系人,api文档上没有
            'new_user'=> +$member_count[0]['c'],// 今日新增会员数量
            'new_order'=> +$order_count[0]['c'],// 今日新增订单数量
            'new_orderamount'=> +$order_amount, // 今日新增订单总额
            'login_times'=> +$admin_login[0]['s'], //管理员总登陆次数
            'login_datetime'=> date('Y-m-d H:i:s',$admin_login[0]['last']), // 管理员最后登陆时间
            'cate.stat'=> +$cate, // 总类目数 api文档： new_category
            'activity.stat'=> +$activity, // 当前活动总数 api文档： new_activity
            'goods.stat'=> +$goods, // 当前商品总数 api文档： new_goods
            'level'=>'', // 店铺等级
        );
        
        $certiMdl = $this->system->loadModel('service/certificate');
        
        // 推送
        $arr['format'] = 'json';
        $arr['version'] = '1.0';
        $arr['call_times'] = 'day';
        $arr['return'] = 'has';
        $arr['datetime'] = date('Y-m-d H:i:s',NOW);
        $arr['entid'] = $certiMdl->getent_id();
        $arr['method'] = CSM_PRODUCT_NAME;
        
        $arr['sign'] = $certiMdl->make_shopex_ac($arr, CSM_PRODUCT_KEY);
        $resp = $this->system->loadModel('utility/http_client')->post(CSM_API_URL,$arr);

        if ( !($resp = json_decode($resp, 1)) || 'succ' != $resp['res'] ) { // 推送失败
            return false;
        }
        
        // 推送成功 记录本次推送时间
        $this->system->loadModel('system/status')->set('CSM_LAST_PUSH',NOW);
    }
}
