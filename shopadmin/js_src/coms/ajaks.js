/** ajaks.js

 * (c) ShopEx Team < www.shopex.cn >

 *本JS基于Mootools JS Framework构建.
 *
 *
 * 这是一个用于shopadmin 后台异步交互的类.
 *
 *
 *
 *
 *已经在/init.js中被实例化:
                                             var getW=function(){
                                                                     return (W&&$type(W)=='object')?W:new ShopEx_AdminXHR();
                                                                            }

在做交互的时候一般用到
              "  W.page(url,options); "

    由于Ajaks类继承于Mootools中的Ajax.
    所以options可以参看Mootools帮助文档中的Ajax
            或Mootools1.2doc 的Request.HTML中的options
 *
 *
 *
 */

/* if(window.gecko){
Element.implement({

    empty: function(){
        var garbage=window.frames['download'];

        $A(this.childNodes).each(function(node){
           try{
              garbage.document.body.appendChild(node);
           }catch(e){
              var docIns=garbage.document.implementation.createDocument("", "", null);
               docIns.importNode(node);
               docIns=null;
           }
        });

        $each(garbage.document.body.childNodes,Element.dispose)
        return this;
    }
}) ;
}
 */





void function(){


var xhrList=new Hash();

try{xhrList.empty.periodical(60*1000,xhrList);}catch(e){}

var Ajaks=new Class({
        Extends:Ajax,
        initialize:function(url,options){
         xhrList.set(url,'init');
         this.addEvent('onCancel',function(){xhrList.set(url,'cancel');});
         this.parent(url,options);
   },
   send:function(url, options){

    xhrList.set(this.url,'request');

       if (!this.check(arguments.callee, options)) return this;
        this.running = true;
        var type = $type(options);

        if (type == 'string' || type == 'element') options = {data: options};
        var old = this.options;
        options = $extend({data: old.data, url: url, method: old.method}, options);
        var data = options.data, url = options.url, method = options.method;

        switch ($type(data)){
            case 'element': data = $(data).toQueryString(); break;
            case 'object': case 'hash': case 'array': data = Hash.toQueryString(data);
        }

        if (this.options.urlEncoded && 'post'==method.toLowerCase()){
            var encoding = (this.options.encoding) ? '; charset=' + this.options.encoding : '';
            this.headers.set('Content-type', 'application/x-www-form-urlencoded' + encoding);
        }
        if (data &&'get'== method.toLowerCase()){
            url = url + (url.contains('?') ? '&' : '?') + data;
            data = null;
        }
        url+='&_ajax=true';
        if(W){
               url+='&_ss='+W.sideStore;
            }
        this.xhr.open(method.toUpperCase(), url, this.options.async);
        this.xhr.onreadystatechange = this.onStateChange.bind(this);
        this.headers.each(function(value, key){
            if (!$try(function(){
                this.xhr.setRequestHeader(key, value);
                return true;
            }.bind(this))) this.fireEvent('exception', [key, value]);
        }, this);
         this.loadMask('onrequest', $(this.options.update));

        /*(function(){
            if (['init','request'].contains(xhrList.get(this.url)))

        }).delay(500, this);*/


        if(window.ie && this.options.update&& $(this.options.update) ){
            try{
                $ES('iframe',this.options.update).each(function(e){
                    e.remove();
                });
            }catch(e){}
        }

        this.fireEvent('request');
        this.xhr.send(data);
        if (!this.options.async) this.onStateChange();
        return this;
   },
   onFailure: function(){
            if ($chk($('loadMask')))this.loadMask('onfailure');
               /* if(this.options.elMap)*/

                switch(this.transport.status){
                    case 404:
                        MessageBox.error('页面未找到.');
                        break;
                    case 401:
                        new Dialog('401.html',{ajaksable:false,modal:true,width:300,height:150,resizeable:false});
                        break;
                    case 403:
                        MessageBox.error('您无此操作的权限.');
                        break;
                }

        xhrList.set(this.url,'failure');
        this.fireEvent('onFailure', this.transport);
    },
   onException:function(k,v){
       if ($chk($('loadMask'))){
         this.loadMask('onException');
       }
       MessageBox.error('出现错误:'+k+':'+v);
    },
   success:function(text,xml){

       var options = this.options
       var response = this.response;
       var updatePlace=$(options.update);

        response.html = text.stripScripts(function(script){
           response.javascript = script;
        });
        response.html = this.processHTML(response.html);






           if(!updatePlace){
                  MessageBox.show(response.html);
                  this.onSuccess(response.html);
                  this.loadMask('complete');
                  return;
            }

           if(options.elMap){
                    $each(options.elMap,function(value,key){
                        if(key=='.sideContent')return;

                        $(value).empty().setStyle('fontSize',0);


                        /*$(value).inject();

                         $(value).getElements('*').set({'id':null,'class':null}).inject('elTempBox');

                         (function(){

                           $('elTempBox').empty();

                         }).delay(5000);*/
                    });


                  response.html= response.html.replace(/<\!-{5}(.*?)-{5}([\s\S]*?)-{5}(.*?)-{5}>/g,function(){
                               if(options.elMap[arguments[1]]){
                                   if(arguments[1]=='.sideContent'&&arguments[2]){
                                      options.elMap[arguments[1]].adopt(new Element('div').setHTML(arguments[2]).getFirst().setStyle('display','none'));
                                   }else{
                                      options.elMap[arguments[1]].set('html',arguments[2]).setStyle('fontSize','');;
                                   }
                               }
                               return '';
                        });

               updateSize();
            }

            updatePlace.empty().setHTML(response.html);

            if (options.evalScripts) {
                    $exec(response.javascript);
            }

            makeAjaksLink(updatePlace);

        this.onSuccess(response.html,null,options,null,response.javascript);
        this.loadMask('oncomplete');

        /*fix document title*/
        document.title=$('shop_title_block').getText()+' - Powered By ShopEx';


   },
    loadMask: function(state, update){
        update = $(update);
        var amongSpace = update || window;
        switch (state) {
            case 'onrequest':
                return $('loadMask').amongTo(window).show();
            default:
               $('loadMask').hide();
               xhrList.set(this.url,'success');
        }
    }


});

/**
* #请求加工厂
*  让请求的URL进入historyManger,以及对options参数进行一些处理.
*  其中page方法为请求分发方法.
*/
ShopEx_AdminXHR=new Class({
   options:{
        evalScripts: true,
        autoCancel:true,
        method:'get',
        elMap: false,
        data:null,
        link:'cancel'
   },
   initialize:function(){
      this.historyMangerInit.call(this);
      $$('#mainMenus a[name]').addEvent('click',function(e){
          e.stop();
          document.body.fireEvent('click',{target:this,stop:$empty});
      });
   },
   historyMangerInit:function(){
        /*HistoryManager init*/

        try{

        this.history =
        historyManager.register('page', ['ctl=dashboard&act=getInfo'], function(values, dft){
            var options = this.options;
            if($('main')){
            var url='index.php?' + (values.input ? values.input : dft[0]);
            return this.page(url);
            }
        }.bind(this), function(values){
            return values[0];
        }, '.*');

        historyManager.start();
        }catch(e){MessageBox.error(e.message)}
        /*HistoryManager init end*/
   },
   page:function(){

    var params=Array.flatten(arguments).link({
            'url':String.type,/*请求地址*/
            'options':Object.type,/*请求配置*/
            'sponsor':Element.type/*发起请求事件的元素*/
        });

    if(['request','init'].indexOf(xhrList.get(url))>-1)return $('loadMask').amongTo(window).show();

    var options=$merge(this.options,params.options);
    var url=params.url;
    var el=params.sponsor;

    if(options.autoCancel&&this.curAjax)this.curAjax.cancel();



    if($type(options.data)=='element'){
           if(!$(options.data).bindValidator('x-input')){

               return $('loadMask').hide();
           }
    }

/*else{
        if(!!el){
             var container=$(el).getContainer();

             alert(container.tagName);
             if(container){
               if(!container.bindValidator('x-input')){


                    return $('loadMask').hide();
               }
             }
        }
    }*/


    options.update=options.update||$($chk(el)?$(el).getContainer():false)||'main';


    if (options.update=='main'||($type(options.update)=='element'&&options.update.id=='main')) {

           var match = url.match('index\\.php\\?(.*)');

            if (match)this.history.setValue(0, match[1]);
           if(!options.elMap)
            options.elMap = {
                '.mainHead':$('headBar'),
                '.mainFoot':$('footBar')
            }
        }
        $extend(options.elMap,{'.sideContent':$('sidecontent')});
       this.curAjax=new Ajaks(url,options);

       this.curAjax.request();

   }
});


/**
用于处理左侧菜单
如果符合要求,则为元素点击,提交事件,增加拦截,并进入ShopEx_AdminXHR流程.
**/
var navHieghtLine=function(sidekey){


   var curAction=location.hash.slice(1);

   if(!$('sidecontent').retrieve('info'))return;
   var curSide=$('sidecontent').retrieve('info').cur;

   var sideAc=$('sidecontent').retrieve('curAction');
   var mainMenuAc=$('mainMenus').retrieve('curAction');
   if(sideAc)sideAc.removeClass('cur');

   if(mainMenuAc)mainMenuAc.removeClass('current');

   var curActMenu=$('submenu_'+curSide).getElement('a[href$='+curAction+']');
   if(curActMenu)
   $('sidecontent').store('curAction',curActMenu.addClass('cur'));

         var curCtlMainMenu=$('mainMenus').getElement('a[name='+sidekey+']');
         if(curCtlMainMenu)
         $('mainMenus').store('curAction',curCtlMainMenu.addClass('current'));

};

SideRender=function(sidekey){

        if(!sidekey)return;

        var sideContent=$('sidecontent');
        var sideContentInfo=sideContent.retrieve('info',{'cur':sidekey,'store':[]});

        var sideStore=sideContentInfo.store;
        var curSide=sideContentInfo.cur;

            $('submenu_'+curSide).hide();

            $('submenu_'+sidekey).show();

            sideContentInfo.cur=sidekey;
             navHieghtLine(sidekey);
            if(sideStore.indexOf(sidekey)>-1)return;
            sideStore.include(sidekey);
            if(W)W.sideStore=sideStore.join(',');
            showSide();

    }


void function(){


      var isAJAXLink=function(item){

          if(item.getTag()!='a'){
              item=item.getParent('a');
          }

          if(!item){
            return false;
          }

         /* if(item.href.match(/\.(?!(php|html))/i)){

             return false;

          }*/

          if((!$chk($(item).target) || item.target.test('{', 'i')) && !item.href.match(/^javascript.+/i) && !item.onclick){
            return item;
          }

          if(item.get('target')=='_blank'&&item.href.contains(SHOPADMINDIR))return item;

          return false;
      };
      var isAJAXForm=function(item){
          if(item.getTag()!='form')return false;
         return $chk($(item).getProperty('action')) && (!$chk(item.target) || item.target.test('{', 'i')) && !item.onsubmit;
      }

       $(document.body).addEvents({
              'click':function(e){



                var item=isAJAXLink($(e.target));

                     if(!item)return;
                       e.stop();

                     if(item.get('target') == '_blank'){

                         return _open(item.href);
                     }


                      var a_target;

                      try{
                         a_target=Json.evaluate(item.target)
                      }catch(e){
                       if($chk(item.href))return W.page(item.href,item);
                       return false;
                      }

                      if(dlg=item.get('dialog')){

                          var opt={

                          title:$pick(item.title,item.get('text')),
                          width:window.getSize().x*0.7,
                          height:window.getSize().y*0.7

                          };
                          dlg=JSON.decode(dlg);
                          dlg=$type(dlg)=='object'?dlg:{};
                          opt=$extend(opt,dlg);
                          return new Dialog(item.href,opt);

                      }


                      if('fm' in a_target&&$chk(a_target.fm)){
                       var titleHook = false;
                       a_target.update = (a_target.update||'main');
                       if (a_target.update == 'main'||(a_target.update.id&&a_target.update.id=='main')) {
                                    [$('footBar'),$('headBar')].each(Element.empty);
                                    updateSize();
                                }
                        var ifm = new Element('iframe', {
                                    'name': a_target.fm,
                                    'styles': {
                                        'width': '100%',
                                        'height': '98%',
                                        'border': 'none',
                                        'overflow-y':'scroll'
                                    }
                                }).inject($(a_target.update).empty());
                        ifm.setProperty('src', item.href);
                      }else{
                         if(e.shift){return open(item.href.replace('?','#'));}
                        W.page(item.href,a_target,item);
                      }
              },
              'submit':function(e){
                   var item=$(e.target);

                  if(isAJAXForm(item)){
                     e.stop();
                     new Element('input', {
                        'type': 'hidden',
                        'value': 1,
                        'name': '__'
                    }).inject(item);



              if (!item.bindValidator('x-input')){return $('loadMask').hide();}

              $ES('textarea[ishtml=true]',item).getValue();

              var action = item.getProperty('action');

              var f_target=f_target||{};

              if(item.target.test('{', 'i')){


                  try {f_target = Json.evaluate(item.target);}catch (e) {}


                }

              if(f_target_obj=item.retrieve('target')){

                  $extend(f_target,f_target_obj);

              }


              if(e.requestOptions){
                  $extend(f_target,e.requestOptions);
              }



              var iptFiles = $ES('input[type="file"]', item);
              if (iptFiles.length>0 && iptFiles.some(function(el){
                            return el.value;
                        })){
                            MODALPANEL.show();
                            $('loadMask').amongTo(window).show();
                            if(!$('upload_iframe')){
                                new Element('iframe',{'id':'upload_iframe','name':'upload_iframe','styles':{'display':'none'}}).inject(document.body);
                            }
                            //if(item.target) item.store('reset_target',item.target);
                            item.setProperty('target', 'upload_iframe');
                            item.set({'enctype':'multipart/form-data',
                                      'encoding':'multipart/form-data'
                            });
                            item.setProperty('action',item.getProperty('action')+'&_upload=true');
                            window.upload_rs_el =f_target&&f_target.ure?f_target.ure:$(e.target).getContainer();
                            item.submit();
                            /*if(item.retrieve('reset_target')){
                                item.target=item.get('target');
                            }
                            else{
                                item.target='';
                            }*/
                            return item;
                    }
                    if (f_target) {
                         if('fm' in f_target){
                            item.setProperty('target', ftarget.fm);
                            return item.submit();
                         }
                         return W.page(action, $extend(f_target, {
                            'method': item.getProperty('method'),
                            'data': item.toQueryString()
                         }),item);
                    }

                    W.page(action,{
                        'method': item.getProperty('method'),
                        'data': item.toQueryString()
                    },item);

                 }

              }


       });


    }();
makeAjaksLink=function(scope){

        $(scope).getElements('form').addEvent('submit',function(e){
            var stopObj=e?(e.stop?e.stop:$empty):$empty;
            document.body.fireEvent('submit',$extend((e||{}),{target:this,stop:stopObj}));

        });
};


Splash={
        go:function(to,timeout,options){
         $$($('loadMaks'),MODALPANEL).hide();
         (function(){
            var splashPanels=$$($('successSplash'),$('noticeSplash'),$('failedSplash'));
            var sp;
            var hassp=splashPanels.some(function(_sp){
                 if($chk(_sp)){
                    sp=_sp;
                    return true;
                 }
            });
            if(sp)
            W.page(to,JSON.decode(options),sp);
         }).delay(timeout.toInt());
    }

}


 }();
