<?php if(!function_exists('tpl_modifier_escape')){ require(CORE_DIR.'/include_v5/smartyplugins/modifier.escape.php'); } ?><img src="index.php?<?php echo $this->_vars['stateString']; ?>" width="1" height="1" border="none" /> <div id='template-modal' style='display:none;'> <div class='dialog'> <div class='dialog-title'> <div class='title span-auto'>{title}</div> <div class='dialog-close-btn' >X</div> <div style="clear:both"></div> </div> <div class='dialog-content'> {content} </div> </div> </div> <?php if( $this->_vars['mini_cart'] ){ ?> <script>
/*
迷你购物车
@author litie[aita]shopex.cn
  [c] shopex.cn
*/
 window.addEvent('domready',function(){
     var miniCart={
           'show':function(target){
               var dialog  = this.dialog =$pick($('mini-cart-dialog'),new Element('div',{'class':'dialog mini-cart-dialog','id':'mini-cart-dialog'}).setStyles({width:300}).inject(document.body));
                this.dialog.setStyles({
                         top:target.getPosition().y+target.getSize().y,
                         left:target.getPosition().x
                    }).set('html',

                  $E('#template-modal .dialog').get('html').substitute({

                      title:'正在加入购物车',
                      content:'正在加入购物车...'
                  })

               ).show();



               document.addEvent('click',function(){
                  dialog.remove();
                  document.removeEvent('click',arguments.callee);
               });

           },
           'load':function(){
              var params=Array.flatten(arguments).link({
                  'remoteURL':String.type,
                  'options':Object.type
              });
              params.options.data = params.options.data ? (params.options.data.toQueryString ? params.options.data.toQueryString():params.options.data.toString())+'&mini_cart=true':'&mini_cart=true';
              var opts=params=$extend({
                 url:params.remoteURL,
                 method:'post',
                 evalScripts:true,
                 onRequest:function(){
                     this.dialog.getElement('.title').set('html','正在加入购物车');

                 }.bind(this),
                 onSuccess:function(re){
                     this.dialog.getElement('.title').set('html','<img src="statics/icon-success.gif" />成功加入购物车');
                     this.dialog.getElement('.dialog-content').set('html',re);
                     $$('.cart-number').set('text',Cookie.get('S[CART_COUNT]')||0);

                 }.bind(this),
                 onFailure:function(xhr){
                     this.dialog.remove();
                     MessageBox.error("加入购物车失败.<br /><ul><li>可能库存不足.</li><li>或提交信息不完整.</li></ul>");
                 }.bind(this)
              },params.options||{});
              if(!params.url)return false;
              miniCart.show(opts.target);
              new Request(opts).send();
           }
     };



   if(formtocart=$E('form[target=_dialog_minicart]')){
       formtocart.addEvent('submit',function(e){

           e.stop();
           miniCart.load([{
               url:this.action,
               method:this.method,
               data:this,
               target:this.getElement('input[value=加入购物车]')
           }]);

       });
   };
   /*for  goods which has specs*/
   if(btnbuy=$E('#goods-viewer form[target=_dialog_minicart] .btn-buy')){

      btnbuy.removeEvents('click').addEvent('click',function(e){
          e.stop();
          if(this.retrieve('tip:text'))return false;
          this.blur();
          this.form.fireEvent('submit',e);

      });

   };

    // 配件单独加入购物车
    $$('a[target=_dialog_minicart]').addEvent('click',function(e){
        e.stop();
        var product_id = this.getProperty('product') || 0, goods_id=this.getProperty('buy'),p_num = 1;
        if ($E('input[name="count_check_'+product_id+'"]')){
             p_num = $E('input[name="count_check_'+product_id+'"]').get('value');
        }

        miniCart.load([{
            url:this.href,target:this,
            data:new Object('goods[goods_id]='+goods_id+'&goods[product_id]='+product_id+'&goods[num]='+p_num),
        }]);
    }); 
    
});
</script> <?php }  if( $this->_vars['passport_login'] && $this->_vars['login_type']=='dialog' ){ ?> <script>
/*
解决：当登陆方式为弹出登陆框，论坛登陆成功后返回网店，登陆框依然存在。
*/

   function refresh(){
     if($('dialog1')!==null){
      if( $('dialog1').style.visibility=='visible' && Cookie.get('S[MEMBER]')){
          window.parent.location.reload();
       }
     }
   }
   setInterval("refresh()",2000);
</script> <?php }  if( $this->system->getConf('site.login_type')!='href'&&!$_COOKIE['MEMBER'] ){ ?> <script>
/*
快速 注册登陆
@author litie[aita]shopex.cn
  [c] shopex.cn
*/

   window.addEvent('domready',function(){
         var curLH = location.href;

         if(["-?login\.html","-?signup\.html","-?loginBuy\.html"].some(function(r){
                return curLH.test(new RegExp(r));
            })){return false;}
         var MiniPassport = new Object();
         var miniPassportDialog = new Element('div',{'class':'dialog mini-passport-dialog','id':'dialog1'}).set('html',$E('#template-modal .dialog').get('html').substitute({
                      title:'登录',
                      content:''
                  })).setStyles({
                      display:'none',
                      width:0,
                      height:'auto'
                  }).adopt(new Element('iframe',{src:'javascript:void(0);',styles:{position:'absolute',
                                                                                       zIndex:-1,
                                                                                       border:'none',
                                                                                       top:0,
                                                                                       left:0,
                                                                                       'filter':'alpha(opacity=0)'
                                                                                       },width:'100%',height:'100%'})).inject(document.body);

         var mpdSize = {
              loginBuy:{<?php if( $this->_vars['openid_open'] ){ ?>width:550,height:400<?php }else{ ?>width:570<?php } ?>},
              signup:{width:600,height:'auto'},
              login:{<?php if( $this->_vars['openid_open'] ){ ?>width:860,height:300<?php }else{ ?>width:430,height:300<?php } ?>},
              chain:{width:450}
         };

         $extend(MiniPassport,{

               show:function(from,options){

                  var handle = this.handle = from;

                  options = this.options = options ||{};

                var remoteURL = options.remoteURL||(handle?handle.get('href'):false);

                var act ="login";

                     act = remoteURL.match(/-([^-]*?)\.html/)[1];




                  if(miniPassportDialog.style.display=='none'){
                        var _styles  = {display:'block'};

                        miniPassportDialog.setStyles(_styles);
                  }
                  miniPassportDialog.getElement('.dialog-content').empty();


                  var fxValue  = mpdSize[act];
                  fxValue.opacity = 1;
                  miniPassportDialog.setStyles(fxValue).amongTo(window);



               // if(window.ie6) remoteURL=(remoteURL.substring(0,4)=='http')?remoteURL:remoteURL;

                  $pick(this.request,{cancel:$empty}).cancel();
                      this.request = new Request.HTML({update:miniPassportDialog.getElement('.dialog-content').set('html','&nbsp;&nbsp;正在加载...'),onComplete:function(){
                            MiniPassport.onload.call(MiniPassport);
                      }}).get(remoteURL,$H({mini_passport:1}));


               },
               hide:function(chain){

                  miniPassportDialog.getElement('.dialog-content').empty();

                       miniPassportDialog.hide();
                       if($type(chain)=='function'){chain.call(this)}
                       miniPassportDialog.eliminate('chain');
                       miniPassportDialog.eliminate('margedata');

               },
               onload:function(){

                   var dialogForm = miniPassportDialog.getElement('form');

                   miniPassportDialog.retrieve('margedata',[]).each(function(item){
                               item.t =  item.t||'hidden';

                               new Element('input',{type:item.t,name:item.n,value:item.v}).inject(dialogForm);
                       });


                   dialogForm.addEvent('submit',function(e){

                       e.stop();
                       var form = this;
                       if(!MiniPassport.checkForm.call(MiniPassport))return MessageBox.error('请完善必填信息!');


                       new Request({
                        method:form.get('method'),
                        url:form.get('action'),
                        onRequest:function(){

                           form.getElement('input[type=submit]').set({disabled:true,styles:{opacity:.4}});

                       },onComplete:function(re){


                              form.getElement('input[type=submit]').set({disabled:false,styles:{opacity:1}});
                              var _re = [];
                              re.replace(/\\?\{([^{}]+)\}/g, function(match){
                                        if (match.charAt(0) == '\\') return _re.push(JSON.decode(match.slice(1)));
                                        _re.push(JSON.decode(match));
                              });
                              var errormsg = [];
                              var plugin_url;
                              _re.each(function(item){

                                  if(item.status =='failed'){
                                      errormsg.push(item.msg);
                                  }
                                  if(item.status =='plugin_passport'){
                                      plugin_url = item.url;
                                  }
                              });


                              if(errormsg.length)return MessageBox.error(errormsg.join('<br/>'));

                              if(plugin_url){
                                  MiniPassport.hide.call(MiniPassport,$pick(miniPassportDialog.retrieve('chain'),function(){
                                       MessageBox.success('正在转向...');

                                       location.href = plugin_url;


                                  }));
                              }else{
                                  MiniPassport.hide.call(MiniPassport,$pick(miniPassportDialog.retrieve('chain'),function(){

                                       MessageBox.success('用户登录成功,正在转向...');
                                       location.reload();

                                  }));
                              }

                       }}).send(form);

                   });
                   miniPassportDialog.getElement('.close').addEvent('click',this.hide.bind(this));

                   miniPassportDialog.amongTo(window);


               },
               checkForm:function(){
                    var inputs = miniPassportDialog.getFormElements();
                    var ignoreIpts = $$(miniPassportDialog.getElements('form input[type=hidden]'),miniPassportDialog.getElements('form input[type=submit]'));
                    ignoreIpts.each(inputs.erase.bind(inputs));

                    if(inputs.some(function(ipt){
                        if(ipt.value.trim()==''){

                           ipt.focus();
                          return true;
                        }

                    })){

                       return false;
                    }
                    return true;

               }

         });


     /*统一拦截*/
     $(document.body).addEvent('click',function(e){

            if(Cookie.get('S[MEMBER]'))return true;

            var tgt = $(e.target);

            if(!tgt.match('a'))tgt = tgt.getParent('a');

            if((!tgt)||!tgt.match('a'))return;

            if(tgt.href.test(/-?login\.html/)||tgt.href.test(/-?signup\.html/)){
                e.stop();
                return MiniPassport.show(tgt);

            }
            if((tgt.href.test(/\/[\?]member/i))&&(tgt.href.test(/\.html$/i))){
              e.stop();
              MiniPassport.show(tgt,{remoteURL:'<?php echo $this->_env_vars['base_url'],"passport",(((is_numeric("passport") && 'index'=="login")  || !"login")?'':'-'."login"),'.html';?>'});
              miniPassportDialog.store('chain',function(){

                    MessageBox.success('会员认证成功,正在进入...');
                    location.href= '<?php echo $this->_env_vars['base_url'],"member",(((is_numeric("member") && 'index'=="index")  || !"index")?'':'-'."index"),'.html';?>';

              });
            }
     });



     /*checkout*/
     $$('form[action$=checkout.html]').addEvent('submit',function(e){
            if(Cookie.get('S[MEMBER]'))return this.submit();
            e.stop();
            var form = this;
            MiniPassport.show(this,{remoteURL:'<?php echo $this->_env_vars['base_url'],"cart",(((is_numeric("cart") && 'index'=="loginBuy")  || !"loginBuy")?'':'-'."loginBuy"),'.html';?>'});
            if(this.get('extra') == 'cart'){
                miniPassportDialog.store('margedata',[{t:'hidden',n:'regType',v:'buy'}]);
            }
            miniPassportDialog.store('chain',function(){
                    MessageBox.success('正在转入...');
                    form.submit();
            });
     });

   });
</script> <?php }  if( $this->_vars['im_setting']['alignselect'] =='left' or $this->_vars['im_setting']['alignselect'] =='right' ){ ?> <div id="siderIMchat" style="<?php echo $this->_vars['im_setting']['alignselect']; ?>:0"><div id="siderIMchat_hiddenbar"></div><div id="siderIMchat_main"><div class="top"></div><div class="infobox"><?php echo $this->_vars['im_setting']['titleexp']; ?></div><div class="bg "><ul class="clearfix"> <?php foreach ((array)$this->_vars['im_setting']['im'] as $this->_vars['key'] => $this->_vars['data']){ ?> <li> <?php if( $this->_vars['data']['type']==1 ){ ?> <a target="_blank" href="tencent://message/?uin=<?php echo $this->_vars['data']['link']; ?>&&Site=LICENSE_ShopEx&&Menu=yes"><img border="0" src="http://wpa.qq.com/pa?p=1:<?php echo $this->_vars['data']['link']; ?>:1"/><?php echo $this->_vars['setting']['plug']; ?></a><br /><?php echo $this->_vars['data']['info'];  }elseif( $this->_vars['data']['type']==2 ){ ?> <a href="msnim:chat?contact=<?php echo $this->_vars['data']['link']; ?>"><img width="30" height="30" border="0" src="http://im.live.com/Messenger/IM/Images/Icons/Messenger.Logo.gif"/><?php echo $this->_vars['setting']['plug']; ?></a><br /><?php echo $this->_vars['data']['info'];  }elseif( $this->_vars['data']['type']==3 ){ ?> <a target="_blank" href="http://amos1.taobao.com/msg.ww?v=2&uid=<?php echo tpl_modifier_escape($this->_vars['data']['link'],'url'); ?>&s=1"><img border="0" src="http://amos1.taobao.com/online.ww?v=2&uid=<?php echo tpl_modifier_escape($this->_vars['data']['link'],'url'); ?>&s=1"/><?php echo $this->_vars['setting']['plug']; ?></a><br /><?php echo $this->_vars['data']['info'];  }elseif( $this->_vars['data']['type']==4 ){ ?> <a target="_blank" href="http://scs1.sh1.china.alibaba.com/msg.atc?v=1&uid=<?php echo $this->_vars['data']['link']; ?>"><img border="0" src="http://scs1.sh1.china.alibaba.com/online.atc?v=1&uid=<?php echo $this->_vars['data']['link']; ?>&s=102"/><?php echo $this->_vars['setting']['plug']; ?></a><br /><?php echo $this->_vars['data']['info'];  }elseif( $this->_vars['data']['type']==5 ){ ?> <a href="callto://<?php echo $this->_vars['data']['link']; ?>"><img border="0" src="http://goodies.skype.com/graphics/skypeme_btn_small_green.gif"/><?php echo $this->_vars['setting']['plug']; ?></a><br /><?php echo $this->_vars['data']['info'];  } ?> </li> <?php } ?> </ul><div class="textcenter pushdown-2"><span id="closeSiderIMchat" class="lnk">关闭在线客服</span></div></div><div class="bottom"></div></div></div> <script type="text/javascript">
window.addEvent('domready', function() {
   $('siderIMchat_hiddenbar').addEvent('mouseover',function(){
                         this.setStyle('display','none');
                         $('siderIMchat_main').setStyle('display','block')
                         });

   $('closeSiderIMchat').addEvent('click',function(){
                         $('siderIMchat_main').setStyle('display','none');
                         $('siderIMchat_hiddenbar').setStyle('display','block')
                         })    ;
    siderIMchatsetGoTop();


});

function siderIMchatsetGoTop(){
    $('siderIMchat').tween('top',$E('body').getScroll().y+100)
}
window.addEvent('scroll',function(){
     siderIMchatsetGoTop();
})

</script> <?php }  if( $this->_vars['preview_theme'] ){ ?> <script>
(function(){
        if(Cookie.read("S[PREVIEW_THEME]")){
            var themeName=Cookie.read("S[PREVIEW_THEME]");
            var themeTip=new Element('div').inject(document.body,'top');
            themeTip.setStyles({position:'absolute',top:0,right:0, 'white-space':'nowrap', padding:'0 10px', background:'green', color:'#fff'});
            themeTip.setText('模板预览：'+themeName);
            var themeBtn=new Element('span',{ styles:{'cursor':'pointer', 'margin-left':'10px'}}).inject(themeTip);
            themeBtn.setText('[退出预览]');
            themeBtn.addEvent('click',function(){Cookie.dispose('S[PREVIEW_THEME]');window.close();});
        }
})();
</script> <?php } ?> <style id="thridpartystyle"> .thridpartyicon { background:url(statics/icons/thridparty0.gif) no-repeat left center; height:30px; line-height:30px; text-indent:35px;} #accountlogin { width:180px; border:2px solid #badbf2; position:absolute; background:#fff; padding:5px;} #accountlogin h5 { border-bottom:1px solid #e2e2e2; margin:0px 5px 10px 5px;padding:0; height:22px; line-height:22px; color:#333333; font-weight:normal;} #accountlogin .logoimg { float:left; margin-left:5px;_margin-left:3px;} #accountlogin .logoimg span img { margin:6px 3px 0 3px;_margin:6px 2px 0 0; } #accountlogin .more { text-align:right; float:right;} #accountlogin .more a { text-decoration:underline;} .trustlogos{overflow:hidden;} .trustlogos li{float:left; padding:2px;height:39px;overflow:hidden;} .trustlogos .login-beli{float:none;clear:left;padding-left:10px;} .belive-login{margin-top:10px;} .trust__login{margin:0;border:none;cursor:pointer;} .trust_log{text-indent:-9999px;} .btn-trustlogin {background:url(statics/btn-trustlogin.gif); width:87px; height:30px; margin-bottom:35px;} .trustdialog .dialog-content { padding:0px; height:320px;} .RegisterWrap {} .RegisterWrap h4 { height:30px; line-height:30px;} .RegisterWrap .more { height:30px; line-height:30px; text-align:right; font-size:14px; color:#333333;} .RegisterWrap .more a { text-decoration:underline;} .RegisterWrap .form { } .RegisterWrap #formlogin,.RegisterWrap #formthridlogin { height:160px; border:1px solid #CCCCCC; margin:10px 0; padding:15px;} .RegisterWrap .customMessages { height:40px;} .dialog-title { margin:0 5px;} .dialog-title .title { padding:10px 0 2px 10px;} .dialog-title .dialog-close-btn {font-family:Arial Black;color:#fff;background:#FF9955;border:1px #FA6400 solid;font-size:14px;cursor:pointer; width:21px; margin-top:5px; text-align:center;} .dialog-title .dialog-close-btn:hover {background:#ff6655;border-color:#ff6655;} </style> <script>
$('thridpartystyle').inject(document.head);
</script> 