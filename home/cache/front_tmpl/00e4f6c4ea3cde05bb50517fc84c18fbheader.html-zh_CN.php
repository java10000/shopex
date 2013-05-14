<?php if(!function_exists('tpl_function_header')){ require(CORE_DIR.'/include_v5/smartyplugins/function.header.php'); } if(!function_exists('tpl_modifier_storager')){ require(CORE_DIR.'/include_v5/smartyplugins/modifier.storager.php'); } if(!function_exists('tpl_input_default')){ require(CORE_DIR.'/include_v5/smartyplugins/input.default.php'); } ?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"> <html xmlns="http://www.w3.org/1999/xhtml"> <head> <?php echo tpl_function_header(array(), $this);?> <link href="<?php echo $this->_plugins['function']['respath'][0]->_respath(array('type' => "user",'name' => "1354864820"), $this);?>images/css.css" rel="stylesheet" type="text/css" /> </head> <body> <div id="AllWrap"> <div id="Top"> <div id="user_box"> <table border="0" align="right" cellpadding="0" cellspacing="0"> <tr> <td class="user_td"><?php unset($this->_vars);$setting = array ( );$this->bundle_vars['setting'] = &$setting;if(!function_exists('widget_topbar')){require(PLUGIN_DIR.'/widgets/topbar/widget_topbar.php');}$this->_vars = array('data'=>widget_topbar($setting,$GLOBALS['system']),'widgets_id'=>'3');ob_start();?><span id="foobar_<?php echo $this->_vars['widgets_id']; ?>" style="position: relative;"> 您好,<span id="uname_<?php echo $this->_vars['widgets_id']; ?>"></span>&nbsp; <?php if( !$_COOKIE['MEMBER'] ){ ?> <span id="loginBar_<?php echo $this->_vars['widgets_id']; ?>"> <a href="<?php echo $this->_env_vars['base_url'],"passport",(((is_numeric(passport) && 'index'==login) || !login)?'':'-'.login),'.html';?>">[请登录]</a>&nbsp;&nbsp; <a href="<?php echo $this->_env_vars['base_url'],"passport",(((is_numeric(passport) && 'index'==signup) || !signup)?'':'-'.signup),'.html';?>">[免费注册]</a> </span> <?php }else{ ?> <span id="memberBar_<?php echo $this->_vars['widgets_id']; ?>"> <a href="<?php echo $this->_env_vars['base_url'],"member",(((is_numeric(member) && 'index'==index) || !index)?'':'-'.index),'.html';?>">[会员中心]</a>&nbsp;&nbsp; <a href="<?php echo $this->_env_vars['base_url'],"passport",(((is_numeric(passport) && 'index'==logout) || !logout)?'':'-'.logout),'.html';?>">[退出]</a> </span> <?php }  if( $this->_vars['setting']['show_cur'] ){ ?>&nbsp; <span id="Cur_sel_<?php echo $this->_vars['widgets_id']; ?>" style="cursor: default;"> <strong></strong> <img src="statics/transparent.gif" style="width:11px;height:11px;background-image:url(statics/bundle.gif);background-repeat:no-repeat;background-position:0 -0px;" /> </span> <?php }  if( $this->_vars['setting']['show_cart'] ){ ?>&nbsp; <a href="<?php echo $this->_env_vars['base_url'],"cart",(((is_numeric(cart) && 'index'==index) || !index)?'':'-'.index),'.html';?>" target="_blank" class="cart-container"> <span class="inlineblock CartIco">购物车</span> [<span id="Cart_<?php echo $this->_vars['widgets_id']; ?>" class="cart-number">0</span>] <img src="statics/transparent.gif" style="width:11px;height:11px;background-image:url(statics/bundle.gif);background-repeat:no-repeat;background-position:0 -0px;" /> </a> <?php } ?> </span> <style id='thridpartystyle'> .trustlogin { background:url(statics/icons/thridparty1.gif) no-repeat left; padding-left:18px; height:20px; line-height:20px; } #accountlogin{visibility:hidden;cursor:pointer;padding-top:0px; } </style> <script>

/*
*foobar update:2009-9-8 13:46:55 modify by rocky@shopex 2013.1.27
*@author litie[aita]shopex.cn
*-----------------*/
window.addEvent('domready',function(){
    var coinBar,cartCountBar;
    
    var barId ="<?php echo $this->_vars['widgets_id']; ?>",bar = $('foobar_'+barId), barOptions = {
        MID:Cookie.get('S[MEMBER]'),
        uname:Cookie.get('S[UNAME]'),
        name:Cookie.get('S[NAME]'),
        coin:<?php echo ((isset($this->_vars['data']['cur']) && ''!==$this->_vars['data']['cur'])?$this->_vars['data']['cur']:'null'); ?>,
        curCoin:Cookie.get('S[CUR]'),
        cartViewURl:'<?php echo $this->_env_vars['base_url'],"cart",(((is_numeric("cart") && 'index'=="view")  || !"view")?'':'-'."view"),'.html';?>',
        stick:<?php if( $this->_vars['setting']['stick'] ){ ?>true<?php }else{ ?>false<?php } ?>
    };

    /* 调取cookie显示登陆用户名*/
    if(barOptions.MID){
        $('uname_'+barId).setText(barOptions.name ? barOptions.name : barOptions.uname);
    }
    
    // 选择货币
    if (coinBar = $('Cur_sel_'+barId)) {
        var coinMenu = new Element('div',{'class':'coinmenu fmenu','styles':{'display':'none'}}).inject(document.body);

        barOptions.coin.each(function(item){
            if(item['cur_code']==barOptions['curCoin']){
                coinBar.getElement('strong').set('text',[item.cur_sign,item.cur_name].join(''));
            }
            coinMenu.adopt(new Element('div',{'class':'item',text:[item.cur_sign,item.cur_name].join(''),events:{
                click:function(){
                    Cookie.set('S[CUR]',item.cur_code);
                    window.location.reload();
                }
            }}));
        });

        coinBar.addEvents({
            'mouseenter':function(){
                coinMenu.setStyles({
                    top:coinBar.getPosition().y+coinBar.getSize().y,
                    left:coinBar.getPosition().x,
                    display:'block',
                    visibility:'visible'
                });
            }
        });
        
        new QMenu(coinBar,coinMenu);
    }
    
    // 购物车
    if( cartCountBar = $('Cart_'+barId) ) {
        cartCountBar.setText(Cookie.get('S[CART_COUNT]')?Cookie.get('S[CART_COUNT]'):0);
        var cartViewMenu = new Element('div',{'class':'cartviewmenu fmenu','styles':{'display':'none'}}).inject(document.body);
        cartCountBar.addEvents({
            'mouseenter': function(){
                cartViewMenu.setStyles({
                    top:bar.getPosition().y+bar.getSize().y,
                    left:bar.getPosition().x,
                    width:bar.getSize().x,
                    display:'block',
                    visibility:'visible'
                }).set('html','<div class="note">加载购物车信息...</div>');
                
                this.retrieve('request',{cancel:$empty}).cancel();
                this.store('request',new Request.HTML({update:cartViewMenu}).get(barOptions.cartViewURl));
            }
        });
        new QMenu(cartCountBar,cartViewMenu);
     }
    
});
</script> <script>
if((null!=Cookie.get('S[NAME]'))||(null!=Cookie.get('S[UNAME]'))){
    $('uname_<?php echo $this->_vars['widgets_id']; ?>').setText('：'+(Cookie.get('S[NAME]') ? Cookie.get('S[NAME]'):Cookie.get('S[UNAME]')));
}
</script> <?php $body = str_replace('%THEME%','{ENV_theme_dir}',ob_get_contents());ob_end_clean();echo $body;unset($body);$setting=null;$this->_vars = &$this->pagedata;?></td> <td class="car_td"><?php unset($this->_vars);$setting = array ( );$this->bundle_vars['setting'] = &$setting;$this->_vars = array('widgets_id'=>'4');ob_start();?><div class="ShopCartWrap"> <a href="<?php echo $this->_env_vars['base_url'],"cart",(((is_numeric("cart") && 'index'=="index") || !"index")?'':'-'."index"),'.html';?>" class="cart-container">购物车中有<b class="cart-number"> <script>document.write(Cookie.get('S[CART_NUMBER]')?Cookie.get('S[CART_NUMBER]'):0);</script></b>件商品</a> </div> <?php $body = str_replace('%THEME%','{ENV_theme_dir}',ob_get_contents());ob_end_clean();echo $body;unset($body);$setting=null;$this->_vars = &$this->pagedata;?></td> <td class="kf_td"><?php unset($this->_vars);$setting = array ( 'usercustom' => '400-800-9999', );$this->bundle_vars['setting'] = &$setting;$this->_vars = array('widgets_id'=>'5');ob_start();?>400-800-9999<?php $body = str_replace('%THEME%','{ENV_theme_dir}',ob_get_contents());ob_end_clean();echo $body;unset($body);$setting=null;$this->_vars = &$this->pagedata;?></td> </tr> </table> </div> <div class="Logo"><?php unset($this->_vars);$setting = array ( );$this->bundle_vars['setting'] = &$setting;$this->_vars = array('widgets_id'=>'6');ob_start();?><a href="./"><img src="<?php echo tpl_modifier_storager($this->system->getConf('site.logo')); ?>" border="0"/></a><?php $body = str_replace('%THEME%','{ENV_theme_dir}',ob_get_contents());ob_end_clean();echo $body;unset($body);$setting=null;$this->_vars = &$this->pagedata;?></div> <div id="TopMenu"><?php unset($this->_vars);$setting = array ( 'treenum' => '3', 'treelistnum' => '90', );$this->bundle_vars['setting'] = &$setting;if(!function_exists('widget_treelist')){require(PLUGIN_DIR.'/widgets/treelist/widget_treelist.php');}$this->_vars = array('data'=>widget_treelist($setting,$GLOBALS['system']),'widgets_id'=>'7');ob_start();?><div class="TreeList"> <?php echo $this->_vars['data']; ?> </div><?php $body = str_replace('%THEME%','{ENV_theme_dir}',ob_get_contents());ob_end_clean();echo $body;unset($body);$setting=null;$this->_vars = &$this->pagedata;?></div> </div> <div id="Menu"><?php unset($this->_vars);$setting = array ( 'max_leng' => '', 'showinfo' => '', );$this->bundle_vars['setting'] = &$setting;if(!function_exists('widget_menu_lv1')){require(PLUGIN_DIR.'/widgets/menu_lv1/widget_menu_lv1.php');}$this->_vars = array('data'=>widget_menu_lv1($setting,$GLOBALS['system']),'widgets_id'=>'8');ob_start();?><ul class="MenuList"> <?php $this->_env_vars['foreach'][wgtmenu]=array('total'=>count($this->_vars['data']),'iteration'=>0);foreach ((array)$this->_vars['data'] as $this->_vars['key'] => $this->_vars['item']){ $this->_env_vars['foreach'][wgtmenu]['first'] = ($this->_env_vars['foreach'][wgtmenu]['iteration']==0); $this->_env_vars['foreach'][wgtmenu]['iteration']++; $this->_env_vars['foreach'][wgtmenu]['last'] = ($this->_env_vars['foreach'][wgtmenu]['iteration']==$this->_env_vars['foreach'][wgtmenu]['total']);  if( $this->_vars['key']>$this->bundle_vars['setting']['max_leng'] && $this->bundle_vars['setting']['max_leng'] ){  if( $this->_vars['item']['node_type']=='pageurl' ){ ?> <div><a href="<?php echo $this->_vars['item']['action']; ?>" <?php if( $this->_vars['item']['item_id']=='1' ){ ?>target="_blank"<?php } ?>><?php echo $this->_vars['item']['title']; ?></a></div> <?php }else{ ?> <div><a href="<?php echo $this->_vars['item']['link']; ?>"><?php echo $this->_vars['item']['title']; ?></a></div> <?php }  }elseif( $this->_vars['key']==$this->bundle_vars['setting']['max_leng'] && $this->bundle_vars['setting']['max_leng'] ){  $this->_vars["page"]="true"; ?> <li style="position:relative;z-index:65535;" class="wgt-menu-more" id="<?php echo $this->_vars['widgets_id']; ?>_menu_base" onClick="if($('<?php echo $this->_vars['widgets_id']; ?>_showMore').style.display=='none'){$('<?php echo $this->_vars['widgets_id']; ?>_showMore').style.display='';}else{ $('<?php echo $this->_vars['widgets_id']; ?>_showMore').style.display='none';}"><a class="wgt-menu-view-more" href="JavaScript:void(0)"></a> <div class="v-m-page" style="display:none;position:absolute; top:25px; left:0" id="<?php echo $this->_vars['widgets_id']; ?>_showMore"> <?php if( $this->_vars['item']['node_type']=='pageurl' ){ ?> <div><a href="<?php echo $this->_vars['item']['action']; ?>" <?php if( $this->_vars['item']['item_id']=='1' ){ ?>target="_blank"<?php } ?>><?php echo $this->_vars['item']['title']; ?></a></div> <?php }else{ ?> <div><a href="<?php echo $this->_vars['item']['link']; ?>"><?php echo $this->_vars['item']['title']; ?></a></div> <?php }  }else{  if( $this->_vars['item']['node_type']==pageurl ){ ?> <li><a <?php if( $this->_env_vars['foreach']['menu']['last'] ){ ?>class="last"<?php } ?> href="<?php echo $this->_vars['item']['action']; ?>" <?php if( $this->_vars['item']['item_id']=='1' ){ ?>target="_blank"<?php } ?>><?php echo $this->_vars['item']['title']; ?></a></li> <?php }else{ ?> <li><a <?php if( $this->_env_vars['foreach']['menu']['last'] ){ ?>class="last"<?php } ?> href="<?php echo $this->_vars['item']['link']; ?>"><?php echo $this->_vars['item']['title']; ?></a></li> <?php }  }  } unset($this->_env_vars['foreach'][wgtmenu]);  if( $this->_vars['page']=="true" ){ ?> </div> </li> <?php } ?> </ul> <script>
if($('<?php echo $this->_vars['widgets_id']; ?>_showMore')){
	$('<?php echo $this->_vars['widgets_id']; ?>_showMore').setOpacity(.8);
}
</script> <?php $body = str_replace('%THEME%','{ENV_theme_dir}',ob_get_contents());ob_end_clean();echo $body;unset($body);$setting=null;$this->_vars = &$this->pagedata;?></div> <div class="secmenu"> <div id="hot_sear"><?php unset($this->_vars);$setting = array ( 'usercustom' => '热门关键词：<a href="#">耐克编织休闲鞋</a> <a href="#">詹姆斯战靴</a> <a href="#">耐克Nike空军系列</a> <a href="#">匡威板鞋</a> <a href="#">篮球鞋</a>', );$this->bundle_vars['setting'] = &$setting;$this->_vars = array('widgets_id'=>'9');ob_start();?>热门关键词：<a href="#">耐克编织休闲鞋</a> <a href="#">詹姆斯战靴</a> <a href="#">耐克Nike空军系列</a> <a href="#">匡威板鞋</a> <a href="#">篮球鞋</a><?php $body = str_replace('%THEME%','{ENV_theme_dir}',ob_get_contents());ob_end_clean();echo $body;unset($body);$setting=null;$this->_vars = &$this->pagedata;?></div> <div id="Search"><?php unset($this->_vars);$setting = array ( );$this->bundle_vars['setting'] = &$setting;if(!function_exists('widget_search')){require(PLUGIN_DIR.'/widgets/search/widget_search.php');}$this->_vars = array('data'=>widget_search($setting,$GLOBALS['system']),'widgets_id'=>'10');ob_start();?><form action="<?php echo $this->_env_vars['base_url'],"search",(((is_numeric(search) && 'index'==result) || !result)?'':'-'.result),'.html';?>" method="post" class="SearchBar"> <table cellpadding="0" cellspacing="0"> <tr> <td class="search_label"> <span>关键字：</span> <input name="name[]" size="10" class="inputstyle keywords" value="<?php echo $this->_vars['setting']['search']; ?>" x-webkit-speech/> </td> <?php if( $this->_vars['setting']['searchopen'] ){ ?> <td class="search_price1">价格从 <?php echo tpl_input_default(array('name' => "price[0]",'type' => "number",'size' => "4",'class' => "inputstyle gprice_from"), $this);?></td> <td class="search_price2">到<?php echo tpl_input_default(array('name' => "price[1]",'type' => "number",'size' => "4",'class' => "inputstyle gprice_to"), $this);?></td> <?php } ?> <td><input type="submit" value="搜索" class="btn_search" onfocus='this.blur();'/> </td> <td><a href="<?php echo $this->_env_vars['base_url'],"search",(((is_numeric("search") && 'index'=="index") || !"index")?'':'-'."index"),'.html';?>" class="btn_advsearch">高级搜索</a> </td> </tr> </table> </form> <?php $body = str_replace('%THEME%','{ENV_theme_dir}',ob_get_contents());ob_end_clean();echo $body;unset($body);$setting=null;$this->_vars = &$this->pagedata;?></div> </div> </div>