window.onerror=function(e){
  if(MODALPANEL){
    MODALPANEL.hide();
  }
}
if(!Setting)var Setting={};
Setting.get = function(k, b){
    return b;
}

var workgroundMarginX=function(){return $('workground').getStyle('margin-left').toInt()+$('workground').getStyle('margin-right').toInt()};

function updateSize(){
	var h =window.mainHeight=window.getSize().y - $('header').getSize().y;
	var w =window.mainWidth=$('header').getSize().x-(isNaN(workgroundMarginX())?0:workgroundMarginX());
    window.mainH=h-$('headBar').offsetHeight-$('footBar').offsetHeight;
    var _mainW=w.limit(1004-(isNaN(workgroundMarginX())?0:workgroundMarginX()),10000);
        $('main').setStyles({
            'width':_mainW,
           'height':mainH
        });
        $('footBar').setStyle('width',_mainW);
        $('headBar').setStyle('width',_mainW);
        if(fh=$E('#headBar .finder-header')){
           fh.setStyle('width',_mainW);
        }
        $('sidecontent').setStyle('height',h);
        $('leftToggler').setStyle('height',h);
}

var W,task;
var getW=function(){

    return (W&&$type(W)=='object')?W:new ShopEx_AdminXHR();
}
function showSide(){
	$(document.body).removeClass('closeLeft');
	updateSize();
}
function closeSide(){
	$(document.body).addClass('closeLeft');
	updateSize();
}






window.addEvent('domready', function(){
	

	$('leftToggler').addEvents({
		'click':function(e){
			var e = new Event(e).target;
			window.setSidePanel = true;
			if($(document.body).hasClass('closeLeft')){
				showSide();
			}else{
				closeSide();
			}
		}
	});
    
	/*updateSize();*/
	
	makeAjaksLink(document.body);
	/*
	  ShopEx_AdminXHR  define in Ajaks.js
	*/

	W=getW();

	new Ajax('index.php?ctl=dashboard&act=getcertInfo',{data:'t='+new Date().getTime(),update:'CertificateInfo'}).request();


	new DropMenu('msgRunner');

	window.addEvent('resize',updateSize);
});


var extra_validator = [];


function __(){
}

function toggleNode(e){
    e = new Event(e);
    var tgt= $(e.target);
    if (tgt.tagName == 'IMG') {
        var el = $(tgt.get('toggle'));
        if (el) {
            if (el.style.display == 'none') {
                tgt.removeClass('x-tnode-close');
                el.show();
            }
            else {
                tgt.addClass('x-tnode-close');
                el.hide();
            }
            e.stop();
        }
        else {
            var tree = tgt.get('tree');
            if (tree) {
                tgt.removeClass('x-tnode-close');
                var tid = tgt.getParent().get('tid');
                var el_id = tgt.parentNode.parentNode.id + '_' + tid;
                
                var box = new Element('div', {
                    'id': el_id,
					'class':'cateloadingbox'
                }).injectAfter(tgt.parentNode);
                var div=new Element('div').setText('loading...').setStyle('padding-left',tgt.parentNode.getStyle('padding-left').toInt() + 20);
                box.adopt(div);
                tgt.set('toggle', el_id);
                new Ajax('index.php?ctl=default&act=tnode&p[0]=' + tree + '&p[1]=' + tid + '&p[2]=' +tgt.parentNode.get('depth'), {
                    method: 'get',
                    update: el_id,
                    onComplete: function(){
                        makeAjaksLink(el_id);
                    }
                }).request();
            }
        }
    }
}



window.setTab = function(c,a){
	var url = $('_'+c).getAttribute('url');
	if(url && !$(c).getAttribute('url')){
		W.page(url,{update:c});
		$(c).setAttribute('url',url);
	}
	$(c).style.display='';
	$('_'+c).addClass('t-handle-current');
	$('_'+c).removeClass('t-handle');
	a.each(function(a){
		$(a).style.display='none';
		$('_'+a).addClass('t-handle');
		$('_'+a).removeClass('t-handle-current');
	});
}
/*window.addEvent('keyup', function(event){
	event = new Event(event);
	if(event.alt){
		if (event.key == 'up'){
			this.movePrev();
			event.stopPropagation();
		}else if(event.key == 'down'){
			this.moveNext();
			event.stopPropagation();
		}
	}
});*/


    /*
 
 *@ShopAdmin 向导功能
 
 *@litie@shopex.cn
 
向导内页以下情况的A标签会触发后台异步请求：
1）A标签target不为'_blank',内部没有子节点。例如:<a href='xxx'>links</a>;
2）A标签target不为'_blank',内部有子节点,但A标签的className='userguidelink'例如:<a href='xxx' class='userguidelink'><p>sadasdsadsada</p></a>;

除了上述情况以外,A标签都不会进入异步请求框架.

向导相关的JS在shopadmin/init.js里.

现在在step5和step6里面点了完成按钮后。向导状态自动转为第一步.

 */
 $E('#header .user_guide').addEvent('click',function(){
     var  btn=this;
  //   var urlstep=btn.retrieve('step','user_guide/step1.html');
	var cen_url;
	 new Request({method:'post',onComplete:function(rs){
		cen_url = rs;
      var urlstep=btn.retrieve('step',cen_url);
 
     var iframe=new Element('iframe',{width:710,height:520,src:urlstep,
         frameborder:0,
         scrolling:"no",
		 id:"user_guide_iframe",
         styles:{border:'none',margin:0}
     }).addEvent('load',function(){
		  return;
          var _win=this.contentWindow;
          var _doc=this.contentWindow.document;
          var _href=_win.location.href;
		  if(!_href.contains("user_guide"))return;
          $E('#header .user_guide').store('step',_win.location.href);
          $('loadMask').hide();
          var links=new Elements(_doc.getElementsByTagName('a'));
          links=links.filter(function(link){
              return (!link.getFirst()||link.hasClass('userguidelink'))&&link.get('target')!='_blank';
          }).addEvent('click',function(e){
             e.stop();
             btn.store('goUrl',this.href.split('user_guide/')[1]).retrieve('dialog').close();
          });
     });
     var ugDialog= new Dialog(iframe,{
              title:'ShopEx使用向导',
              height:570,
              ajaksable:false,
              onLoad:function(){
               if(!window.gecko){
                 iframe.set('src',urlstep);
               }
             },
             onClose:function(){
               new Element('div',{styles:{background:'#66CCFF',position:'absolute',overflow:'hidden',zIndex:65535,opacity:.3,border:'2px #333 dashed'}})
               .inject(document.body)
               .setStyles(this.dialog.getCis())
               .effects({duration:800,fps:90})
               .start(btn.getCis()).chain(function(){
                  this.element.remove();
                  if(btn.retrieve('goUrl')){
                       W.page(btn.retrieve('goUrl'));
                  }
                  btn.store('goUrl',false);
               });
             }
       });
     
     btn.store('dialog',ugDialog);
	 }}).post('index.php?ctl=default&act=getcertidandurl');;

	 
     
    });



//一些判断
window.addEvent('load',function(){
     //判断flash player
    
   //if($('loadMask'))$('loadMask').hide();

   $E('.container').setStyle('visibility','visible');

    if(!Browser.Plugins.Flash.version||Browser.Plugins.Flash.version<9){
	   var curfl;
	   if(Browser.Plugins.Flash.version){
	     curfl="当前的Flash Player 版本:<strong>"+Browser.Plugins.Flash.version+"."+Browser.Plugins.Flash.build+"</strong>";
	   }else{
	     curfl="<strong>未安装Flash player 插件.</strong>"
	   }
	   new Dialog(
	   new Element('div').setHTML("<h3 class='error'>",
	   curfl,
	   "<br/><br/>可能会影响到正常使用.你是否要手动升级安装?</h3>",
	   "<a href='http://www.macromedia.com/go/getflashplayer' class='sysiconBtnNoIcon'><strong>是</strong>,我要去下载升级包</a>",
	   "<span isCloseDialogBtn='true' class='sysiconBtnNoIcon'><strong>否</strong>,我不想升级</span>"),
	   {title:'Flash Player 可能会引起问题.',resizeable:false,width:450,height:150});
	}
    
    if(!DEBUG_JS&&$('_firebugConsole')){
         (function(){
            new Dialog(
            new Element('div').setHTML("<h3 class='error'>在已知情况下，除非正确配置 Firebug，否则它会使 ShopEx后台 运行缓慢</h3><span class='lnk' isCloseDialogBtn='true'>我知道了!</span>"),
           {title:'FireBug 使程序变慢.',resizeable:false,width:450,height:150});
           }).delay(5000);
    }
     
    
});




window.addEvent('domready',function(){

 try{
     //shopadmin Xtip 
     window.Xtip=new Tips($('shop_title_block'),{className:'x-tip'});
    $('shop_title_block').store('tip:title','网店名称:-(点击进行设置)');
    $('shop_title_block').store('tip:text',$('shop_title_block').subText(25));
     setInterval("if($('elTempBox')&&$('elTempBox').getElements('*').length)$('elTempBox').empty()",2*60000);
     var forAddKeyDwonEvent;
     var KonamiCode=[];     
     if(window.ie){forAddKeyDwonEvent=document.body}else{forAddKeyDwonEvent=window;}
     forAddKeyDwonEvent.focus();
     forAddKeyDwonEvent.addEvent('keydown',function(e){
       if(e.code==27){
       
          $E('.finder-search-input')?$E('.finder-search-input').blur():false;
          forAddKeyDwonEvent.focus();
       }	  
       if(['INPUT','TEXTAREA'].contains(e.target.tagName))return;
	   
      /*快捷组合键*/ 
      if(e.shift&&e.code==191){
       return  new Dialog('shortcut_key_help.html',{title:'快捷键',ajaksable:false,width:window.getSize().x*0.7,height:window.getSize().y*0.7});
      }
      
       var shopAdminTabs=new Hash({
           t49:'top-tab-goods',
           t50:'top-tab-order',
           t51:'top-tab-member',
           t52:'top-tab-sale',
           t53:'top-tab-site',
           t54:'top-tab-analytics'
        });
        var tkey='t'+e.code;
        
        if(e.alt&&shopAdminTabs.getKeys().contains(tkey)){
             
            return $(shopAdminTabs[tkey]).fireEvent('click',{stop:$empty});
        
        }
      
      
     
      /*快捷单键*/

            switch (e.code){
                case 27:
                var dialogs=$$('.dialog');
                if(dialogs.length){
                 return dialogs.getLast().retrieve('instance').close();
                }
                if(MODALPANEL.isDisplay()){
                  return MODALPANEL.hide();
                }
                for(cf in finderGroup){
                   return finderGroup[cf].detail.fireEvent('slideOut');
                }
                
                break;
               case 82://R
                 if(e.control)return;
                 var url = historyManager.getHash();
                  W.page('index.php?'+(url?url:'ctl=dashboard&act=index'));
                 break;
               case 85://U
                  $(document.body).toggleClass('closeLeft');
                  updateSize();
                 break;
               case 191:
                       e.stop();
               return  $E('.finder-search-input')?$E('.finder-search-input').focus():false;
               case 72:
                return $E('#header .user_guide').fireEvent('click',{stop:$empty});
               
                  
            }


      /*KonamiCode 键*/  
        KonamiCode.push(e.code);
        
        if(KonamiCode.toString().indexOf("38,38,40,40,37,37,39,39,66,65")>-1){
            new Dialog('google_gears.html',{title:'利用Gears加速ShopEx后台',ajaksable:false});
            KonamiCode=[];
        }
       
     });
     

 }catch(e){alert(e)}


});

/*gears init*/
(function() {
  if (window.google && google.gears) {
    return;
  }
  var factory = null;
  if (typeof GearsFactory != 'undefined') {
    factory = new GearsFactory();
  } else {
    try {
      factory = new ActiveXObject('Gears.Factory');
      if (factory.getBuildInfo().indexOf('ie_mobile') != -1) {
        factory.privateSetGlobalObject(this);
      }
    } catch (e) {
      if ((typeof navigator.mimeTypes != 'undefined')
           && navigator.mimeTypes["application/x-googlegears"]) {
        factory = document.createElement("object");
        factory.style.display = "none";
        factory.width = 0;
        factory.height = 0;
        factory.type = "application/x-googlegears";
        document.documentElement.appendChild(factory);
      }
    }
  }
  if (!factory) {
    return;
  }
  if (!window.google) {
    google = {};
  }

  if (!google.gears) {
    google.gears = {factory: factory};
  }
  /*gears init end*/
})();


/*insert Gears Ico into top menu*/
void function(){
    if(window.google&&google.gears){
        var tlm = $E('#header .top-link-menu').setStyle('width',320);
        var gearsIco = tlm.getElement('.gearsIco').show();
       
        gearsIco.store('tip:text','当前浏览器支持<b>Gears</b><sup>Powered By Google</sup><br/><br/><b>Gears</b>可以同步远程资源到本地，使程序加载运行更迅速。');
        new Tips(gearsIco,{'className':'x-tip'});
        gearsIco.addEvent('click',function(e){
           e.stop();
           new Dialog('google_gears.html',{title:'利用Gears加速ShopEx后台',ajaksable:false});
        });
    }
}();

/*APP by Gears
void function(){

       if(window.google&&google.gears){
           
           $exec=function(text){
            
                if (!text) return text;
           
                var workerPool = google.gears.factory.create('beta.workerpool');
                
                workerPool.onmessage = function(a, b, message) {
                 // alert('Received message from worker ' + message.sender + ': \n' + message.body);
                  //console.info(message.body.scripts);
                  var text=message.body.scripts;
                  	if (window.execScript){
                        window.execScript(text);
                    } else {
                        var script = document.createElement('script');
                        script.setAttribute('type', 'text/javascript');
                        script[(Browser.Engine.webkit && Browser.Engine.version < 420) ? 'innerText' : 'text'] = text;
                        document.head.appendChild(script);
                        document.head.removeChild(script);
                    }
                  
                };
                var childWorkerId = workerPool.createWorkerFromUrl('worker.js');
                console.info(childWorkerId);
             
                workerPool.sendMessage([{scripts:''+text}], childWorkerId);

                
                return text;
           }
           
   
     }

}();*/








