<?php if(!function_exists('tpl_block_role')){ require(CORE_DIR.'/admin/smartyplugin/block.role.php'); } ?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"> <html xmlns="http://www.w3.org/1999/xhtml"> <head> <meta http-equiv="Content-Type" content="text/html; charset=utf-8"> <meta http-equiv="X-UA-Compatible" content="IE=7" > <title><?php echo $this->_vars['title']; ?></title> <link rel="shortcut icon" href="../favicon.gif" type="image/gif" /> <link rel="stylesheet" href="css/shopadmin.css" type="text/css" media="screen, projection"/> <script>
var SHOPVERSION = 'ShopEx48.5';
var SHOPADMINDIR='<?php echo $this->_vars['shopadmin_dir']; ?>';
var SHOPBASE='<?php echo $this->_vars['shop_base']; ?>';
var SHOPMENUS = 'json from=$mlist';
var DEBUG_JS=true;
var Setting = {};
var sess_id = '<?php echo $this->_vars['session_id']; ?>';
<?php echo $this->_vars['script'];  if( $this->_vars['statusId'] ){ ?>
window.statusId = <?php echo $this->_vars['statusId']; ?>;
<?php } ?>
</script> </head> <body class="closeLeft" id='shopadmin'> <span id='version-shopex485' style='display:none;'></span> <div class="container" style='visibility:hidden'> <div class="header" id='header'> <div class="hd1"> <h1 id="logo" class="shopname"> <a href="http://www.shopex.cn" target="_blank"><img src="images/admin_logo.png" class="imgbundle" style="background-image: none;" /></a> <a href="index.php?ctl=system/setting&act=basicinfo" id="shop_title_block"><?php echo $this->_vars['shopname']; ?></a> <?php if( !constant('SAAS_MODE') ){ ?> <span class="certiinfo" id="CertificateInfo">&nbsp;</span> <?php }else{ ?> <span class="certiinfo" id="CertificateInfo" style="display:none">&nbsp;</span> <span class="certiinfo"><?php echo $this->_vars['shop_service_info']; ?></span> <?php } ?> </h1> <div id="msgRunner" dropMenu='msgStorage' style='visibility:hidden;'><div class='runner'></div></div> <!-- 顶部消息提示 --> <ul class="x-drop-menu" id="msgStorage" style='display:none;'></ul> <div id="head_sync_download_tip_msg" style="float:left;text-align:left;margin-top:15px;padding-left:15px;display:none" > <img src="images/transparent.gif" class="imgbundle" style="width:13px;height:14px;background-position:0 -256px;" /><span id="downmsg"></span> </div> <!-- 最顶部菜单 --> <div class="top-link-menu"> <div> <span class="btn-appstore" id="btn-appstore" onclick="W.page('index.php?ctl=system/appmgr&act=index'); closeSide();"> <img src="images/transparent.gif" class="imgbundle" style="width:16px;height:15px;background-position:0 -102px;" />应用中心 </span> <a href='http://www.shopex.cn/help/ShopEx48' target="_blank" > <img src="images/transparent.gif" class="imgbundle" style="width:16px;height:16px;background-position:0 -951px;" />帮助 </a>&nbsp;&nbsp; <a href='javascript:void(0);' class='user_guide'> <img src="images/transparent.gif" class="imgbundle" style="width:16px;height:16px;background-position:0 -1859px;" />开店向导 </a>&nbsp;&nbsp; <a href="http://www.ekaidian.cn" target="_blank"> <img src="images/transparent.gif" class="imgbundle" style="width:16px;height:16px;background-position:0 -935px;" />客户服务 </a>&nbsp;&nbsp; <a href="http://www.ekaidian.cn" target="_blank"> <img src="images/transparent.gif" class="imgbundle" style="width:14px;height:18px;background-position:0 -917px;" />购买咨询 </a> </div> </div> </div> <!-- 次顶部菜单 --> <div class="hd2"> <div id="mainMenus" class="top-menu"> <?php foreach ((array)$this->_vars['menu'] as $this->_vars['key'] => $this->_vars['item']){  if( !$this->_vars['item']['hidden'] ){ ?> <a name="<?php echo $this->_vars['key']; ?>" href="<?php echo $this->_vars['item']['link']; ?>" id="top-tab-<?php echo $this->_vars['key']; ?>" class="tab"><span><span><?php echo $this->_vars['item']['label']; ?></span></span></a> <?php }  }  $this->_tag_stack[] = array('tpl_block_role', array('require' => "7,8",'mode' => "or")); tpl_block_role(array('require' => "7,8",'mode' => "or"), null, $this); ob_start(); ?><div class="tab blankbutton"><span><span><?php $this->_tag_stack[] = array('tpl_block_role', array('require' => "7")); tpl_block_role(array('require' => "7"), null, $this); ob_start(); ?><a href="index.php?ctl=system/setting&act=welcome" id="top-tab-setting">商店配置</a><?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_content = tpl_block_role($this->_tag_stack[count($this->_tag_stack) - 1][1], $_block_content, $this); echo $_block_content; array_pop($this->_tag_stack); $_block_content='';  $this->_tag_stack[] = array('tpl_block_role', array('require' => "8")); tpl_block_role(array('require' => "8"), null, $this); ob_start(); ?><a href="index.php?ctl=system/tools&act=welcome" id="top-tab-tools">工具箱</a><?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_content = tpl_block_role($this->_tag_stack[count($this->_tag_stack) - 1][1], $_block_content, $this); echo $_block_content; array_pop($this->_tag_stack); $_block_content=''; ?></span></span></div><?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_content = tpl_block_role($this->_tag_stack[count($this->_tag_stack) - 1][1], $_block_content, $this); echo $_block_content; array_pop($this->_tag_stack); $_block_content=''; ?> <a class="tab greenbutton" href="../" target="_blank" class="tab"><span><span>浏览商店</span></span></a> </div> <div class="top-link clearfix"> <div class='span-auto user-name'>您好, <?php echo $this->_vars['uname']; ?></div> <div class='span-auto'>[&nbsp;<a href='index.php?ctl=admin/profile&act=operator' dialog='{title:"帐号设置"}'>帐号设置</a>&nbsp;]</div> <div class='span-auto'>[&nbsp;<a href='index.php?ctl=passport&act=logout' target='_top'>退出</a>&nbsp;]</div> <div class='span-auto'><a href='index.php?ctl=dashboard&act=index'>桌面</a></div> <div class='span-auto' id='authinfo'></div> <div class='span-auto'><a href='index.php?ctl=system/about&act=index' dialog='{title:"关于"}'>关于</a></div> </div> </div> </div> <!-- 侧边栏 --> <div id="sidecontent"></div> <div id="leftToggler" class="toggle-bar" title="开启或关闭侧边栏"><div class="left-toggler">&nbsp;</div></div> <div id="workground"> <div id="headBar" class='head_content'></div> <div id="main" class='main_content'></div> <div id="footBar" class='foot_content'>&nbsp;</div> </div> </div> <div id="shopkits-pannel" class="close"> <div class="ctlTools">&nbsp;</div> </div> <!--begin LoadMask--> <div id='loadMask' class='loading' style='z-index:65535;display:block;'> <span class='loading_msg'>正在加载...</span> </div> <!--END loadMask--> <!--JAVASCRIPTS SRC--> <script type="text/javascript" src="js/package/tools.js"></script> <script type="text/javascript" src="js/package/component.js"></script> <script type="text/javascript" src="js/package/admin.js"></script> <!--JAVASCRIPTS SRC END--> <script type="text/javascript" src="js/fixie6.js"></script> <iframe name="download" style="display:none" src='about:blank'></iframe> <div id='elTempBox' style='display:none'></div> <div id='tabernacle'></div> <div id="dialogProtoType" style='display:none'> <div class="dialog-box"> <div class="dialog-head"> <div class="dialog-title"></div> <img src="images/transparent.gif" class="imgbundle dialog-btn-close" style="width:17px;height:17px;background-position:0 -197px;" /> </div> <div class="dialog-content-head"></div> <div class="dialog-content-body" container='true'>正在加载...</div> <div class="dialog-content-foot"></div> <img class="dialog-btn-resize" src='images/resize-btn.gif'/> </div> </div> <div id="frameDialogProtoType" style='display:none'> <div class="dialog-box"> <div class="dialog-head"> <div class="dialog-title"></div> <img src="images/transparent.gif" class="imgbundle dialog-btn-close" style="width:17px;height:17px;background-position:0 -197px;" /> </div> <div class="dialog-content-head"></div> <iframe class="dialog-content-body dialog-frame-body" container='true' frameborder="0" src='about:blank'></iframe> <div class="dialog-content-foot"></div> <img class="dialog-btn-resize" src='images/resize-btn.gif'/> </div> </div> <script>

/*向导弹出*/
<?php if( $this->_vars['guide']=='' or $this->_vars['guide']=='false' ){ ?>
    window.addEvent('load',function(){
        $E('#header .user_guide').store('checked',false).fireEvent('click');
    });
<?php }else{ ?>
    $E('#header .user_guide').store('checked',true);
<?php } ?>

window.addEvent('load',function(){
    new Request.JSON({
        url: 'index.php?ctl=default&act=getAppChange', 
        onComplete: function(response){
            if(response.update_count > 0){
                if ($('app-num-header')) {
                    $('app-num-header').set('html', response.update_count);
                } else {
                    new Element('span', {html: '('+response.update_count+')', id:'app-num-update', title:'应用有更新'}).inject($('btn-appstore'));
                };
            }
            if(response.status == 'succ' && !Cookie.read('newTip:tb')){
                /*cookie write in appmgr/index*/
                var newTip = new Element('div', {
                    'class': 'tip-new',
                    'html': '新功能',
                    'title': '新功能：整合淘宝功能使用向导'
                }).addEvent('click', function(e){
                    new Dialog('index.php?ctl=default&act=shownewtools', {width:690, height:410, title:'新功能使用向导'});	
                }).inject($E('#header .blankbutton'));
            }
        }
    }).send();

     // F5 页面刷新 js 截获
    if( !window.ie ) {
         window.addEvent('keydown',function(e){
            if(e.code==116 && !e.control){ // 116 == F5
                e.stop();
                var url = historyManager.getHash();
                W.page('index.php?'+(url?url:'ctl=dashboard&act=index'));
            }
        });
    }

    /*顶部滚动提示*/        
    var getEvents=function(){return $('msgStorage').retrieve('events');};

    var synMsgRemoete = new Request({
        onSuccess:function(re){
            (function(){
                synMsgRemoete.post("index.php?ctl=default&act=status",{
                    events:getEvents()
                });
            }).delay(60000);

            var msgStorage=$('msgStorage').store('events',{finder_colset:{}}).set('html',re);

            var msgRunner=$E('#msgRunner .runner').empty().scrollTo(0,0);
            $clear(msgRunner.retrieve('timer'));
            var els=msgStorage.getElements('li');
            if(!els||!els.length){
                return msgRunner.setStyle('visibility','hidden');
            }else{
                msgRunner.setStyle('visibility','visible');
            }
            
            els.each(function(el){
                new Element('span').set('text',el.get('text')).inject(msgRunner);
            });
            
            var runnerSpans=msgRunner.getElements('span');
            if ( runnerSpans && runnerSpans.length>0 ) {
                runnerSpans.addEvents({
                    'mouseenter':function(){
                        this.getParent().store('stopScroll',true);
                    },
                    'mouseleave':function(){
                        this.getParent().store('stopScroll',false);
                    }
                });
            }

            try{
                if( !runnerSpans || runnerSpans.length<2)return;
                var msgRunnerFxScroll= new Fx.Scroll(msgRunner);
                msgRunner.store('timer',(function(){
                    if(!this.element.retrieve('stopScroll')){
                        this.start(0,this.element.getScrollTop()+this.element.getFirst().offsetHeight);
                        if(this.element.getScrollSize().y<=(this.element.getScrollTop()+this.element.getSize().y)){
                            this.stop();
                            this.set(0,0);
                        }
                    }
                }).periodical(2500, msgRunnerFxScroll));
            }catch(e){}
        },
        onCancel:function(){
            synMsgRemoete.post("index.php?ctl=default&act=status",{
                events:getEvents()
            });
        },
        onFailure:function(){
            (function(){
                synMsgRemoete.post("index.php?ctl=default&act=status",{
                    events:getEvents()
                });
            }).delay(90000);
        }
    });

   synMsgRemoete.post("index.php?ctl=default&act=status");
});

// 遮罩层
var MODALPANEL=(function(){
  var mp=$pick($('MODALPANEL'),new Element('div',{'id':'MODALPANEL'}).inject(document.body)).setStyle('display','none');
    mp.setStyles({
        'position':'absolute',
        'background':'#333333',
        'width':'100%',
        'height':window.getScrollSize().y,
        'top':0,
        'left':0,
        'zIndex':65500,
        'opacity':.4
    });
    mp.addEvent('onshow',function(el){
        el.setStyles({
            'width':'100%',
            'height':window.getScrollSize().y
        });
    });
    return $(mp);
})();

window.addEvent('resize',function(){
    MODALPANEL.setStyles({
        'width':'100%',
        'height':window.getScrollSize().y
    });
});

</script> </body> </html> 