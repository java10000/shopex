<?php defined('CORE_DIR') || exit('入口错误'); ?>
<?php
class ctl_setting extends adminPage{

    var $workground ='setting';

    function welcome(){
        $this->page('setting/welcome.html');
    }

    function shoppingbasic(){
        $this->path[] = array('text'=>__('购物显示设置'));
        $this->page('setting/shopping_basic.html',true);
    }

    function guide_status($getable=false){
        if($getable){
            echo $this->system->getConf("system.guide",$_POST['check']);
        }
        else{
            return $this->system->setConf("system.guide",$_POST['check']);
        }
    }

    //商家信息
    function basicinfo(){
        $this->path[] = array('text'=>__('商家信息'));
        $this->pagedata['shopname'] = $this->system->getConf('system.shopname');
        $this->pagedata['shoptitle'] = $this->system->getConf('system.shopname').' - Powered By ShopEx';
        $this->page('setting/site_basic.html');
    }
    function basicinfoEdit(){
        $this->begin('index.php?ctl=system/setting&act=basicinfo');
        $this->end($this->settingEdit(),__('修改成功'));
    }
    //基本设置
    function siteBasic(){
        $this->siteinfors();
        $this->pagedata['auth_type']=$this->system->getConf('certificate.auth_type');
        $this->pagedata['n_shop_name'] = $this->system->getConf('system.shopname');
        $this->pagedata['n_shop_url'] = $this->system->getConf('store.shop_url');
        $this->pagedata['typeid'] = $this->system->getConf('store.shop_type');
        $this->pagedata['jyms'] = $this->system->getConf('store.sell_type');
        $this->pagedata['province'] = $this->system->getConf('store.province');
        $this->pagedata['city'] = $this->system->getConf('store.city');
        $this->path[] = array('text'=>__('基本设置'));
        $this->page('setting/site_basic.html');
    }




    function siteBasicEdit(){

        $this->begin('index.php?ctl=system/setting&act=siteBasic');
        if($_POST['setting']['system.shopname'] && !$this->system->getConf('system.index_title')){
            $this->system->setConf('system.index_title',$_POST['setting']['system.shopname']);
        }
        if($_POST['setting']['site.tax_ratio'])
            $_POST['setting']['site.tax_ratio'] = $_POST['setting']['site.tax_ratio']/100;
        $storager = &$this->system->loadModel('system/storager');
        if(!$_POST['setting']['site.logo']){
            unset($_POST['setting']['site.logo']);
        }
        if($_FILES ){
            foreach($_FILES['setting']['name'] as $k=>$name){
                $file = array(
                    'name'=>$name,
                    'type'=>$_FILES['setting']['type'][$k],
                    'tmp_name'=>$_FILES['setting']['tmp_name'][$k],
                    'error'=>$_FILES['setting']['error'][$k],
                    'size'=>$_FILES['setting']['size'][$k],
                );
                $_POST['setting'][$k] = $storager->save_upload($file,'default','logo');
                if(!$_POST['setting'][$k])unset($_POST['setting'][$k]);
            }
        }
        //$this->sendUpdateinfo($_POST['setting']);

        $this->end($this->settingEdit(),__('修改成功'));

        //$this->end($this->settingEdit(),__('修改成功'));
    }

    function _modified($src,$key){
        if(isset($src[$key]) && ($src[$key]!=$this->system->getConf($key))){
            return true;
        }else{
            return false;
        }
    }

    function shoppingBasicEdit(){
        $this->begin('index.php?ctl=system/setting&act=shoppingbasic');

        $_POST['setting']['site.tax_ratio'] = $_POST['setting']['site.tax_ratio']/100;
        $_POST['setting']['site.market_rate'] = $_POST['setting']['site.market_rate']?$_POST['setting']['site.market_rate']:0;
        $this->end($this->settingEdit(),__('修改成功'));
    }


    function settingEdit(){
        $order_canceltime = $_POST['setting']['system.order_canceltime'];
        if($order_canceltime){
            $oOrder = $this->system->loadModel('trading/order');
            $oOrder->setModifiedtime($order_canceltime);
        }

        foreach($_POST['_set_'] as $key=>$type){
            if($type=='bool'){
                $_POST['setting'][$key] = $_POST['setting'][$key]?true:false;
            }
        }

        if($this->_modified($_POST['setting'],'site.stripHtml')){
            $frontend = &$this->system->loadModel('system/frontend');
            $frontend->clear_all_cache();
        }
        $this->system->setConf("readingGlass",$_POST['readingGlass']?1:0);

        if(isset($_POST['setting']['system.seo.emuStatic']) && $_POST['setting']['system.seo.emuStatic']){
            $svinfo = &$this->system->loadModel('utility/serverinfo');

            $url = parse_url($this->system->base_url());
            $code = substr(md5(time()),0,6);
            $content = $svinfo->doHttpQuery($url['path']."/_test_rewrite=1&s=".$code."&a.html");

            if(!strpos($content,'[*['.md5($code).']*]')){

                if(false===strpos(strtolower($_SERVER["SERVER_SOFTWARE"]),'apache')){
                    trigger_error(__('您的服务器不是apache,无法使用htaccess文件。请手动启用rewrite，否则无法启用伪静态'),E_USER_ERROR);
                }

                if(file_exists(BASE_DIR.'/'.ACCESSFILENAME)){
                    trigger_error(__('您的系统存在无效的').ACCESSFILENAME.__(', 无法启用伪静态'),E_USER_ERROR);
                }else{
                    if(($content = file_get_contents(BASE_DIR.'/root.htaccess'))){
                        $content = preg_replace('/RewriteBase\s+.*\//i','RewriteBase '.$url['path'],$content);
                        if(file_put_contents(BASE_DIR.'/'.ACCESSFILENAME,$content)){
                            $content = $svinfo->doHttpQuery($url['path']."/_test_rewrite=1&s=".$code."&a.html");
                            if(!strpos($content,'[*['.md5($code).']*]')){
                                unlink(BASE_DIR.'/'.ACCESSFILENAME);
                                trigger_error(__('您的系统不支持apache的').ACCESSFILENAME.__(',启用伪静态失败.'),E_USER_ERROR);
                            }
                        }else{
                            trigger_error(__('无法自动生成').ACCESSFILENAME.__(',可能是权限问题,启用伪静态失败'),E_USER_ERROR);
                        }
                    }else{
                        trigger_error(__('系统不支持rewrite,同时读取原始root.htaccess文件来生成目标').ACCESSFILENAME.__('文件,因此无法启用伪静态'),E_USER_ERROR);
                    }
                }
                trigger_error(__('不支持rewrite,放弃'),E_USER_ERROR);
            }
        }

        foreach($_POST['setting'] as $k=>$v){
            if(!$this->system->setConf($k,$v)){
                trigger_error($k.__('设置错误'),E_USER_ERROR);
                return false;
            }

        }

        return true;
    }

    //配送部分
    function deliverList(){
        $oDly = &$this->system->loadModel('trading/shipping');
        $aList['main'] = $oDly->getList();
        $aList['total'] = count($aList['main']);
        $this->system->output(json_encode($aList));
    }


    function watermark(){
        $this->path[] = array('text'=>__('商品图片设置'));
        $gimage = &$this->system->loadModel('goods/gimage');
        $storager = &$this->system->loadModel('system/storager');
        $ib=&$this->system->loadModel('utility/magickwand');
        $this->system->setConf('system.watermark.lastcfg',time());
        if($ib->magickwand_loaded){
            $loaded = true;
        }else{
            $ib=&$this->system->loadModel('utility/gdimage');
            $loaded = $ib->gd_loaded;
        }
        $this->pagedata['readingGlass'] = $this->system->getConf('site.reading_glass');
        $this->pagedata['readingGlassWidth'] = $this->system->getConf('site.reading_glass_width');
        $this->pagedata['readingGlassHeight'] = $this->system->getConf('site.reading_glass_height');
        $this->pagedata['thumbnail_pic_height'] = $this->system->getConf('site.thumbnail_pic_height');
        $this->pagedata['thumbnail_pic_width'] = $this->system->getConf('site.thumbnail_pic_width');
        $this->pagedata['small_pic_height'] = $this->system->getConf('site.small_pic_height');
        $this->pagedata['small_pic_width'] = $this->system->getConf('site.small_pic_width');
        $this->pagedata['big_pic_height'] = $this->system->getConf('site.big_pic_height');
        $this->pagedata['big_pic_width'] = $this->system->getConf('site.big_pic_width');
        $this->pagedata['default_thumbnail_pic'] = $storager->getUrl($this->system->getConf('site.default_thumbnail_pic'));
        $this->pagedata['default_small_pic'] = $storager->getUrl($this->system->getConf('site.default_small_pic'));
        $this->pagedata['default_big_pic'] = $storager->getUrl($this->system->getConf('site.default_big_pic'));
        if($loaded){
            $this->pagedata['gd_loaded'] = true;
            $this->pagedata['thumbnail_pic_height'] = $this->system->getConf('site.thumbnail_pic_height');
            $this->pagedata['wm_small_enable'] = $this->system->getConf('site.watermark.wm_small_enable');
            $this->pagedata['wm_small_text'] = $this->system->getConf('site.watermark.wm_small_text');
            $this->pagedata['wm_small_font'] = $this->system->getConf('site.watermark.wm_small_font');
            $this->pagedata['wm_small_font_size'] = $this->system->getConf('site.watermark.wm_small_font_size');
            $this->pagedata['wm_small_font_color'] = $this->system->getConf('site.watermark.wm_small_font_color');
            $this->pagedata['wm_small_pic'] = $storager->getUrl($this->system->getConf('site.watermark.wm_small_pic'));
            $this->pagedata['wm_small_loc'] = $this->system->getConf('site.watermark.wm_small_loc');
            $this->pagedata['wm_small_transition'] = $this->system->getConf('site.watermark.wm_small_transition');
            $this->pagedata['wm_small_text_transition'] = $this->system->getConf('site.watermark.wm_small_text_transition');
            $this->pagedata['wm_big_enable'] = $this->system->getConf('site.watermark.wm_big_enable');
            $this->pagedata['wm_big_text'] = $this->system->getConf('site.watermark.wm_big_text');
            $this->pagedata['wm_big_font'] = $this->system->getConf('site.watermark.wm_big_font');
            $this->pagedata['wm_big_font_size'] = $this->system->getConf('site.watermark.wm_big_font_size');
            $this->pagedata['wm_big_font_color'] = $this->system->getConf('site.watermark.wm_big_font_color');
            $this->pagedata['wm_big_pic'] = $storager->getUrl($this->system->getConf('site.watermark.wm_big_pic'));
            $this->pagedata['wm_big_loc'] = $this->system->getConf('site.watermark.wm_big_loc');
            $this->pagedata['wm_big_transition'] = $this->system->getConf('site.watermark.wm_big_transition');
            $this->pagedata['wm_big_text_transition'] = $this->system->getConf('site.watermark.wm_big_text_transition');
            $this->pagedata['enable_options'] = array('0'=>__('关闭'),'1'=>__('开启图片水印'),'2'=>__('开启文字水印'));
            $this->pagedata['wm_pos_options'] = array('0' => __('居中'),'1' => __('顶部居左'),'2' => __('顶部居右'),'3' => __('底部居右'),'4' => __('底部居左'),'5' => __('顶部居中'),'6' => __('中部居右'),'7' => __('底部居中'),'8' => __('中部居左'));
            $this->pagedata['wm_font_options'] = $gimage->getFontFile();
            $this->pagedata['spec_image_width'] = $this->system->getConf('spec.image.width');
            $this->pagedata['spec_image_height'] = $this->system->getConf('spec.image.height');
            $this->pagedata['spec_default_pic'] = $this->system->getConf('spec.default.pic');

        }else{
            $this->pagedata['gd_loaded'] = false;
        }
        $this->page('setting/site_watermark.html');
    }

    function watermarkEdit(){
        $this->begin('index.php?ctl=system/setting&act=watermark');
        $data = &$_POST;

        //图片处理
        $storager = &$this->system->loadModel('system/storager');
        $files = array();
        $procssing_files = array('default_thumbnail_pic'=>'site.default_thumbnail_pic',
                                'default_big_pic'=>'site.default_big_pic',
                                'default_small_pic'=>'site.default_small_pic',
                                'wm_small_pic'=>'site.watermark.wm_small_pic',
                                'wm_big_pic'=>'site.watermark.wm_big_pic',
                                'spec_default_pic'=>'spec.default.pic'
            );
        foreach($procssing_files as $k=>$v){
            if($_FILES[$k]['name']){
                $files[$k] = $storager->save_upload($_FILES[$k],'default',$k);
                if(!$files[$k]){
                    unset($files[$k]);
                }else{
                    $this->system->setConf($v,$files[$k]);
                }
            }
        }

        $this->system->setConf('site.reading_glass_width',$data['readingGlassWidth']);
        $this->system->setConf('site.reading_glass_height',$data['readingGlassHeight']);

        $this->system->setConf('site.reading_glass',$data['readingGlass']?1:0);
        $this->system->setConf('site.thumbnail_pic_width',$data['thumbnail_pic_width']);
        $this->system->setConf('site.thumbnail_pic_height',$data['thumbnail_pic_height']);
        $this->system->setConf('site.big_pic_width',$data['big_pic_width']);
        $this->system->setConf('site.big_pic_height',$data['big_pic_height']);
        $this->system->setConf('site.small_pic_width',$data['small_pic_width']);
        $this->system->setConf('site.small_pic_height',$data['small_pic_height']);

        $ib=&$this->system->loadModel('utility/magickwand');
        if($ib->magickwand_loaded){
            $loaded = true;
        }else{
            $ib=&$this->system->loadModel('utility/gdimage');
            $loaded = $ib->gd_loaded;
        }
        if($loaded){
            $watermark = array();
            $watermark['wm_small_enable']=$data['wm_small_enable'];
            if($watermark['wm_small_enable']){
                $watermark['wm_small_loc']=$data['wm_small_loc'];
                if($watermark['wm_small_enable']==1){

                    $watermark['wm_small_transition']=$data['wm_small_transition'];
                }elseif($watermark['wm_small_enable']==2){

                    $watermark['wm_small_text']=$data['wm_small_text'];
                    $watermark['wm_small_font']=$data['wm_small_font'];
                    $watermark['wm_small_font_size']=$data['wm_small_font_size'];
                    $watermark['wm_small_font_color']=$data['wm_small_font_color'];
                    $watermark['wm_small_text_transition']=$data['wm_small_text_transition'];

                }
            }
            $watermark['wm_big_enable']=$data['wm_big_enable'];
            if($watermark['wm_big_enable']){

                $watermark['wm_big_loc']=$data['wm_big_loc'];
                if($watermark['wm_big_enable']==1){

                    $watermark['wm_big_transition']=$data['wm_big_transition'];
                }elseif($watermark['wm_big_enable']==2){

                    $watermark['wm_big_text']=$data['wm_big_text'];
                    $watermark['wm_big_font']=$data['wm_big_font'];
                    $watermark['wm_big_font_size']=$data['wm_big_font_size'];
                    $watermark['wm_big_font_color']=$data['wm_big_font_color'];
                    $watermark['wm_big_text_transition']=$data['wm_big_text_transition'];

                }
            }
            foreach($watermark as $k=>$v)
                $this->system->setConf('site.watermark.'.$k,$v);
            $this->system->setConf('spec.image.width', $data['spec_image_width']);
            $this->system->setConf('spec.image.height', $data['spec_image_height']);
        }
        $this->end(true,__('商品图片设置完成'));
    }

    function watermarkPreview($tag){
        $ib=&$this->system->loadModel('utility/magickwand');
        if($ib->magickwand_loaded){
            $loaded = true;
        }else{
            $ib=&$this->system->loadModel('utility/gdimage');
            $loaded = $ib->gd_loaded;
        }
        $storager = &$this->system->loadModel('system/storager');
        $ib->src_image_name = '../images/default/wm_sample.jpg';
        $ib->wm_image_name = '../'.$storager->getFile($this->system->getConf('site.watermark.wm_'.$tag.'_pic'));
        $ib->wm_image_transition = $this->system->getConf('site.watermark.wm_'.$tag.'_transition');
        $ib->wm_text_transition = $this->system->getConf('site.watermark.wm_'.$tag.'_text_transition');
        $ib->wm_text =$this->system->getConf('site.watermark.wm_'.$tag.'_text');
        $ib->wm_text_size=$this->system->getConf('site.watermark.wm_'.$tag.'_font_size');
        $ib->wm_text_font = $this->system->getConf('site.watermark.wm_'.$tag.'_font');
        $ib->wm_text_color = $this->system->getConf('site.watermark.wm_'.$tag.'_font_color');
        $ib->wm_image_pos = $this->system->getConf('site.watermark.wm_'.$tag.'_loc');
        $enable = $this->system->getConf('site.watermark.wm_'.$tag.'_enable');
        $height = $this->system->getConf('site.'.$tag.'_pic_height');
        $width = $this->system->getConf('site.'.$tag.'_pic_width');

        switch($enable){
        case 0:
            header("Content-Type: text/html; charset=utf-8");
            exit(__('您还未设置水印，暂时无法查看效果。'));
        case 1:
            $ib->jpeg_quality = 90;             //jpeg图片质量
            $ib->wm_text = '';
            $ib->makeThumbWatermark($width,$height);
            break;
        case 2:
            $ib->jpeg_quality = 90;             //jpeg图片质量
            $ib->wm_image_name = "";
            $ib->makeThumbWatermark($width,$height);
            break;
        }
    }

    function cert() {
        $this->path[] = array('text'=>__('备案证书'));
        $this->page('setting/cert.html');
    }

    function doCert() {
        $this->begin('index.php?ctl=system/setting&act=cert');
        $this->system->setConf('site.certtext', $_POST['setting']['site.certtext']);
        $this->end(true ,__('修改成功'));
    }

    function previewImg(){
        echo '<iframe name="_previewImg" id="previewImg" width="100%" height="100%" scrolling="yes" frameborder="0" border="0" marginwidth="0" marginheight="0"></iframe>';
    }

    function createPreviewImg($tag, $upload=false){
        header('Content-type: text/html;charset=utf-8');
        if($upload){
            if($_FILES['default_preview_pic']['tmp_name']){
                $pic = MEDIA_DIR.'/default/default_preview_pic.jpg';
                $upTemp=move_uploaded_file($_FILES['default_preview_pic']['tmp_name'],$pic);
                chmod($pic, 0755);
            }
echo '<form action="" method="post" enctype="multipart/form-data">';
echo __('<div style="border:1px solid #bec6ce; font-size:12px; margin:5px;"><div style="margin:5px; margin-top:5px; padding:10px; background:#e2e8eb;"><div align=center style="height:30px; line-height:30px;">上传一张自己的商品图片查看效果：<input autocomplete="off" name="default_preview_pic" size="10" style="" class="_x_ipt file" vtype="file" type="file"><input type="submit" value="上传"/></div>');
echo '<input type="hidden" name="'.$tag.'_pic_width" value="'.$_POST[$tag.'_pic_width'].'"/>';
echo '<input type="hidden" name="'.$tag.'_pic_height" value="'.$_POST[$tag.'_pic_height'].'"/>';
echo __('<div align=center style="font-weight:bold;height:30px; line-height:30px;">预览：</div>');
echo '<div align=center style="padding:10px;"><img border=1 src="../images/default/shopexstorelogo.jpg?'.time().'" width='.$_POST[$tag.'_pic_width'].' height='.$_POST[$tag.'_pic_height'].'/></div></div></div>';
echo '</form>';
        }
        else{
            $ib=&$this->system->loadModel('utility/magickwand');
            if($ib->magickwand_loaded){
                $loaded = true;
            }else{
                $ib=&$this->system->loadModel('utility/gdimage');
                $loaded = $ib->gd_loaded;
            }
            $storager = &$this->system->loadModel('system/storager');
            $ib->src_image_name = BASE_DIR.'/images/default/wm_sample.jpg';
            $ib->wm_image_name = ($_FILES['wm_'.$tag.'_pic']['tmp_name']?$_FILES['wm_'.$tag.'_pic']['tmp_name']:$storager->getFile($this->system->getConf('site.watermark.wm_'.$tag.'_pic')));                                                         // '../'.$storager->getFile($this->system->getConf('site.watermark.wm_'.$tag.'_pic'));
            $ib->wm_image_transition = $_POST['wm_'.$tag.'_transition'];     // $this->system->getConf('site.watermark.wm_'.$tag.'_transition');

            $ib->wm_text_transition = $_POST['wm_'.$tag.'_text_transition'];

            $ib->wm_text = $_POST['wm_'.$tag.'_text'];                              //$this->system->getConf('site.watermark.wm_'.$tag.'_text');
            $ib->wm_text_size=$_POST['wm_'.$tag.'_font_size'];                 //$this->system->getConf('site.watermark.wm_'.$tag.'_font_size');
            $ib->wm_text_font = $_POST['wm_'.$tag.'_font'];                      // $this->system->getConf('site.watermark.wm_'.$tag.'_font');
            $ib->wm_text_color = $_POST['wm_'.$tag.'_font_color'];            // $this->system->getConf('site.watermark.wm_'.$tag.'_font_color');
            $ib->wm_image_pos = $_POST['wm_'.$tag.'_loc'];         //$this->system->getConf('site.watermark.wm_'.$tag.'_loc');
            $enable = $_POST['wm_'.$tag.'_enable'];                                    // $this->system->getConf('site.watermark.wm_'.$tag.'_enable');
            $height = $_POST[$tag.'_pic_height'];                                       //$this->system->getConf('site.'.$tag.'_pic_height');
            $width = $_POST[$tag.'_pic_width'];                                         //$this->system->getConf('site.'.$tag.'_pic_width');

            switch($enable){
            case 0:
                header("Content-Type: text/html; charset=utf-8");
                exit(__('您还未设置水印，暂时无法查看效果。'));
            case 1:
                $ib->jpeg_quality = 90;             //jpeg图片质量
                $ib->wm_text = '';
                $ib->makeThumbWatermark($width,$height);
                break;
            case 2:
                $ib->jpeg_quality = 90;             //jpeg图片质量
                $ib->wm_image_name = "";
                $ib->makeThumbWatermark($width,$height);
                break;
            }
        }

    }


    function imsetting(){
        $this->path[] = array('text'=>__('在线客服设置'));
        $this->pagedata['setting'] = unserialize($this->system->getConf('im.setting'));
        $this->page('setting/im_setting.html');
    }

    function saveimsetting(){
        $this->begin('index.php?ctl=system/setting&act=imsetting');
        $data = serialize($_POST);
        $this->system->setConf('im.setting',$data,true);
        $this->end(true,__('保存成功'));
    }

    function siteinfors(){
        $cer = &$this->system->loadModel('service/certificate');
        $data = $cer->center_send('category.get_category_info');
        $this->pagedata['category_list'] = $data['info'];
    }


    function save_siteinfor(){
        $this->begin('index.php?ctl=system/setting&act=siteBasic');

        if($_FILES ){
            $storager = &$this->system->loadModel('system/storager');
            foreach($_FILES['setting']['name'] as $k=>$name){
                $file = array(
                    'name'=>$name,
                    'type'=>$_FILES['setting']['type'][$k],
                    'tmp_name'=>$_FILES['setting']['tmp_name'][$k],
                    'error'=>$_FILES['setting']['error'][$k],
                    'size'=>$_FILES['setting']['size'][$k],
                );
                $_POST['setting'][$k] = $storager->save_upload($file,'default','logo');
                if(!$_POST['setting'][$k])unset($_POST['setting'][$k]);
            }
        }
        
        foreach($_POST['setting'] as $k=>$v){
            $tmp = explode('.',$k);
            if('shop_url' == $tmp[1] && substr($v,-1) != '/'){
                $v = $v.'/';
            }
            
            $this->system->setConf($k,$v,true);
        }
        $this->system->setConf('store.city',$_POST['city'],true);
        $this->system->setConf('store.province',$_POST['province'],true);

        $this->end(true,__('修改成功'),'index.php?ctl=system/setting&act=siteBasic');
    }

    function centerSend($info,$params){
       $params = $_POST['setting'];
       $cer = &$this->system->loadModel('service/certificate');
       foreach($params as $key=>$value){
            $value = trim($value);
            if($key =='system.shopname'){
                $gprams['shop_name'] = $value;
            }
            if(substr($key,0,5)=='store'){
                //商店网址后缀必须"/"结束,防止与中心通信错误
                if(substr($key,6) == 'shop_url' && substr($value,-1) != '/'){
                    $value = $value.'/';
                }
                $gprams[substr($key,6)] = $value;
            }
       }
       $gprams['province'] = $_POST['province'];
       $gprams['city'] = $_POST['city'];
       $data = $cer->center_send('certi.update_info',$gprams);
       return $data;
    }

    function sendUpdateinfo($setting_info){
        $setting_info = $_POST['setting'];

        $httpd=$this->system->loadModel('utility/http_client');
        $url = constant('LICENSE_CENTER');
        $oCertificate = $this->system->loadModel("service/certificate");
        $app_id = VERIFY_APP_ID;

        $cat_params = $oCertificate->get_category_info($app_id);
        $results = $httpd->post($url,$cat_params);
        
        $data = json_decode($results,true);
        $setting_info['typeid'] = $data['info']['typeid'];
        $params = $oCertificate->update_info($app_id, $setting_info);

        $httpd->post($url,$params);

        return true;
    }

    function greencard(){
        $this->path[] = array('text'=>__('绿卡专享服务'));

        $cert = $this->system->loadModel('service/certificate');
        $url_arr = parse_url($this->system->base_url());
        $this->pagedata['url'] = $url_arr['host'];
        $this->pagedata['sess_id'] = $cert->get_sess();
        $this->pagedata['certi_id'] = $this->system->getConf('certificate.id');

        $this->page('setting/green_card.html');
    }
}
