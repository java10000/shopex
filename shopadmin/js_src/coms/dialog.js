/**
 * Dialog.js--弹出窗口
 * @param {String} url or {element} Element
 * @param {Object} options 
 * @author litie@shopex.cn
 * @copyright (c) ShopEx Dev Team. 
 *
 *此js 依赖mootools1.2,依赖   .dialog css定义 ,依赖shopeadmin/index.html 下$('dialogProtoType').innerHTML;
 *
 *
 *
 *e.g.
 *       var dialog=new Dialog('url.php',{
                               title:'弹出窗口',
                               width:300,
                               height:300
                        });
         
         
						
		//dialog.close();   will close the dialog Instance.
		
		//you can addEvent
            var dialog2=new Dialog('url.php',{
               title:'弹出窗口',
			   width:300,
			   height:300,
			   modal:true,
			   onShow:function( dialogins){
			      dialogins.dialog_title.set('text','dialog');
			   }
                        });
                        
                        
          
          ############Dialog 小特性":#############
          、Dialog 可以多实例共存。
          、关闭Dialog会销毁掉Dialog和其内部已被加载的DOM元素.
          、Dialog 在弹出的时候,会向Dialog的ClassName 为'dialog' Dom元素仓库里帮上当前的Dialog实例:{
              
              例如你在一个弹出窗口里加载了一个页面，想通过页面里的某个事件来关闭Dialog。
              
              你可以取到Dialog页面里的一个元素后向外查找.dialog的元素.找到的第一个一定是当前的dialog Dom Element对象。
              
              例如页面里有一个 ID为  'closeBtn'的元素.
              
              你可以：
              
              $('closeBtn').getParent('.dialog').retrieve('instance').close();
              //$('closeBtn').getParent('.dialog');取到当前dialog Dom Element对象.
              //$('closeBtn').getParent('.dialog').retrieve('instance'); 取到了当前的dialog实例.
              
         
              
          }
          、你可以在new Dialog的时候为其附加一些状态事件：{
              
              例如你想通过Dialog重用一个你之前在mian区域应用的表单，但只是想重用表单而不想重用控制器(action) ;
              
              你可以这样:
              
              new Dialog('index.php?ctl=#',{
              
                  onLoad:function(){
                      
                      this.dialog.getElement('form').action='index.php?######';
                      
                      
                      甚至颠覆这个表单本来在框架里的提交规则：
                      
                        this.dialog.getElement('form').removeEvents('submit').addEvent('submit',function(){
                             
                             var form=this;
                             
                                //up2you..!!!                        
                        }); 
                         
                      
                  }
                
              });
          
          
          }
          #########################
          
          
          
          
          
          
                		
 */


(function(){
    var dialogTemplete,dialogTempleteWithFrame;
    var getDialogTemplete=function(frame){
       
       if(!frame){
           if(undefined!=dialogTemplete){
                return dialogTemplete;
           }
           dialogTemplete=$('dialogProtoType').innerHTML;
           $('dialogProtoType').remove();
           
           return dialogTemplete;
       }
        if(undefined!=dialogTempleteWithFrame){
                return dialogTempleteWithFrame;
           }
            dialogTempleteWithFrame=$('frameDialogProtoType').innerHTML;
           $('frameDialogProtoType').remove();
           
           return dialogTempleteWithFrame;
    };
	
	
	//Dialog scpoe : window
    Dialog=new Class({
    Implements:[Options,Events]
	,options:{
	   onShow:$empty,//显示时的事件
	   onHide:$empty,//关闭时的事件注册
	   onClose:$empty,//关闭时的事件注册
	   onLoad:$empty,
       callback:false,
       frameable:false,
	 	width:750,/*窗口宽度*/
		height:400,/*窗口高度*/
        dialogBoxWidth:10,
		title:'',/*窗口标题*/
		dragable:true,/*是否允许拖拽*/
		resizeable:true,/*是否允许改变尺寸*/
		ajaksable:true,/*是否允许被框架[Ajaks]动态注册异步事件*/
		singlon:true,/*是否仅允许单独实例*/
		modal:false,/*是否在弹出时候其他区域不可操作*/
		ajaxoptions: {/*ajax请求参数对象*/
			update:false,
			evalScripts: true,
            method: 'get',
		    autoCancel:true
		}
	 },
	 initialize:function(url,options){
	    
        var currentRegionDialogs=this.currentRegionDialogs=$$('.dialog');
         
		 if(currentRegionDialogs.some(function(item,idx){
			 if(item.retrieve('serial')==url.toString().trim()&&item.style.display!='none'){
			    item.inject(document.body)
			   return true;
			 }
		}) )return;
		
	     this.setOptions(options);
		 options=this.options;
		 this.UID = (Native.UID)++;
		 
         var _dialogTemplete=getDialogTemplete(this.options.frameable);
      
		 this.dialog = new Element('div',{id:'dialog_'+this.UID,'class':'dialog','styles':{'visibility':'hidden','zoom':1,'opacity':0,'zIndex':65534}})
		           .setHTML(_dialogTemplete).inject(document.body)
				   .store('serial',url.toString().trim());		
		
		if(this.options.callback){
           this.dialog.store('callback',this.options.callback);
        }
		this.dialog_head=$E('.dialog-head',this.dialog)
		           .addEvent('click',function(e){
				           if($$('.dialog').length>1)
						   this.inject(document.body);
				     }.bind(this.dialog));
		this.dialog_body=$E('.dialog-content-body',this.dialog);
		//this.dialog_foot=$E('.foot',this.dialog);	
		
		$E('.dialog-title',this.dialog_head).setText(options.title||"Dialog");
		
		$E('.dialog-btn-close',this.dialog_head).addEvents({'click':function(e){
		    if(e)
			e=new Event(e).stop();
			this.close();
		}.bind(this),'mousedown':function(e){new Event(e).stop()}});
		
		if(options.dragable){
		  this.dragDialog();
		}
		
		if (options.resizeable) {
			this.dialog_body.makeResizable({
				handle: $E('.dialog-btn-resize',this.dialog),
				limit: {
					x: [200,window.getSize().x*0.9],
					y:[100,Math.max(window.getSize().y,window.getScrollSize().y)]
				},
				onDrag: function(){
				    this.setDialogWidth();
				}.bind(this)
			});
			//this.dialog_foot.setStyle('display','');
			
			//this.dialog_body.setStyle('overflow','scroll');
		}else{
			$E('.dialog-btn-resize',this.dialog).hide();
			//this.dialog_body.setStyle('overflow','hidden');
		}
		
		//this.dialogShowEffect=this.dialog.effect('opacity');
		
		$extend(options.ajaxoptions,{
		 'update':this.dialog_body,
		 'elMap':{
				'.mainHead':$E('.dialog-content-head',this.dialog),
				'.mainFoot':$E('.dialog-content-foot',this.dialog)
			},
		  'onRequest':function(){
		    this.setDialog_bodySize();
		  }.bind(this),
		  'onFailure':function(){
		    this.close();
			alert("加载弹出内容失败!");
		  }.bind(this),
		  'onComplete':function(re){
		    if('onComplete' in options)options.onComplete(re);
		    this.onLoad.call(this,re);
		  }.bind(this)
		});
		
		 this.popup(url,options);
	 },
	  onLoad:function(re){
	      var closebtn=$E('*[isCloseDialogBtn]',this.dialog);
		  if(closebtn){
			    closebtn.addEvent('click',this.close.bind(this));
			}
          this.dialog.store('instance',this);
	      this.show();
	 },
	 initContent:function(url,options,isreload){
	     options=options||this.options;
         var _this=this;
         if(!isreload){
             var ic=arguments.callee;   
             this.reload=function(){
               ic(url,options,true);
             }
         }
         
         if(options.frameable){
           
           this.dialog_body.set('src',url).addEvent('load',function(){
                   
                _this.onLoad.call(_this,this);   
                
           });
         
         
         
           return;
         }
         
         
         
	     if($type(url)=='string'){
		    if(options.ajaksable)
	 		W.page(url,options.ajaxoptions);
	    	else
			new Ajax(url,options.ajaxoptions).request();
		 }else{
		    try{
		       this.dialog_body.empty().adopt(url);
			}catch(e){
			   this.dialog_body.setHTML('内容加载失败.!');
			}
            if(!isreload){
			    this.onLoad.call(this);
             }
		 }		
	 },
	 popup:function(url,options){
	  if(options.modal||options.singlon)MODALPANEL.show();
	  $('loadMask').amongTo(window).show();
	  this.fireEvent('onShow',this);
	  this.initContent(url,options);
	 },
	 show:function(){
	   this.setDialog_bodySize();
	    $('loadMask').hide();
        
        var crd=this.currentRegionDialogs;
        var crd_length=crd?crd.length:0;
        
	    if(crd_length&&crd_length>0){
            this.dialog.position($H(crd[crd_length-1].getPosition()).map(function(v){
                return v+=20;
            })).setOpacity(1);
        }else{
           this.dialog.amongTo(window);
        }
        
     
        this.fireEvent('onLoad',this);
	 },
	 close:function(){
         try{
         this.fireEvent('onClose',this.dialog);
		 }catch(e){}
         this.dialog.style.display='none';
		
         if(window.ie){
            $ES('iframe',this.dialog_body).remove();
         }
		 this.dialog.empty().remove();
		 $('dialogdragghost_'+this.UID)?$('dialogdragghost_'+this.UID).remove():'';
		 if(this.currentRegionDialogs.length>0)return false;
		   MODALPANEL.hide();
		  
		 return 'nodialog';
	 },
	 hide:function(){
	 	this.fireEvent('onHide');
		this.close.call(this);
	 },
	 setDialog_bodySize:function(){
	  this.dialog_body.setStyles({
			'height':this.options.height-this.dialog_head.getSize().size.y-this.options.dialogBoxWidth*2,
			'width':this.options.width-this.options.dialogBoxWidth*2
		});
	  this.setDialogWidth();
	 },
	 setDialogWidth:function(){
	   this.dialog.setStyle('width',this.dialog_body.getSize().size.x+this.options.dialogBoxWidth*2);
	 },
	 dragDialog:function(){
            var dialog=this.dialog;
			var dragGhost=new Element('div',{'id':'dialogdragghost_'+this.UID});
			    dragGhost.setStyles({
				'position':'absolute',
				'border':'2px #333333 dashed',
				'cursor':'move',
				'background':'#66CCFF',
				'display':'none',
				'opacity':0.3,
				'zIndex':65535
				}).inject(document.body);	
			this.addEvent('load',function(e){
				dragGhost.setStyles(dialog.getCis());
			});
			    new Drag(dragGhost,{
				    'handle':this.dialog_head,
					'limit':{'x':[0,window.getSize().x],'y':[0,window.getSize().y]},
					 'onStart':function(){
					     dragGhost.setStyles(dialog.getCis());
					     dragGhost.show();
					 },
					 'onComplete':function(){
					    var pos=dragGhost.getPosition();
						dialog.setStyles({
							'top': pos.y,
							'left':pos.x
						});
						dragGhost.hide();
					}
				});	
	 }
});

})();	
