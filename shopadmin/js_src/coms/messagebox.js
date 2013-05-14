var MessageBox=new Class({
    options:{
       delay:1,
       onFlee:$empty,
       FxOptions:{
       }
    },
    initialize:function(msg,type,options){
       $extend(this.options,options);
       this.createBox(msg,type);
       
    },
    flee:function(mb){
      if(window.MessageBoxOnShow){
                window.MessageBoxOnShow(mb,mb.hasClass('success'));
                window.MessageBoxOnShow=$empty();
         }
       var mbFx=new Fx.Styles(mb,this.options.FxOptions);
       var begin=false;
       var obj=this;
       mb.addEvent('click',function(){
           this.hide();
       });
       (function(){
           begin=true;
           
           mbFx.start({
              'left':0,
              'opacity':0
           }).chain(function(){
              this.element.remove();
              begin=false;
             
              if(obj.options.onFlee){
                obj.options.onFlee.apply(obj,[obj]);
              }
              if(window.MessageBoxOnFlee){
                    window.MessageBoxOnFlee(mb,mb.hasClass('success'));
                    window.MessageBoxOnFlee=$empty();
              }
           });
       }).delay(this.options.delay*1000);
       
    },
    createBox:function(msg,type){
      var msgCLIP=/<h4[^>]*>([\s\S]*?)<\/h4>/;
      var tempmsg=msg;
      if(tempmsg.test(msgCLIP)){
         tempmsg.replace(msgCLIP, function(){
            msg=arguments[1];
            return '';
        });
      }
      var box = new Element('div').setStyles({
          'position':'absolute',
          'visibility':'hidden',
          'width':200,
          'height':'auto',
          'opacity':0,
          'zIndex':65535
      }).inject(document.body);
      var obj=this;
      
      box.addClass(type).setHTML("<h4>",msg,"</h4>").amongTo(window).effect('opacity').start(1).chain(function(){
          obj.flee(this.element);
          
      }); 
      return box;
    }

});

MessageBox.success=function(msg,options){
    return new MessageBox(msg||"操作成功!",'success',options);
};
MessageBox.error=function(msg,options){
    options=options||{};
    if(!options.delay)options.delay=3;
    return new MessageBox(msg||"操作失败!",'error',options);
};
MessageBox.show=function(msg,options){
    if(msg.contains('failedSplash')){
        return new MessageBox(msg||"操作失败!",'error',options);
    }return new MessageBox(msg||"操作成功!",'success',options);
   
};