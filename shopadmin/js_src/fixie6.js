/*此文件将只会在7以下版本的IE浏览器加载运行
  
  在core/admin/view/index.html中引入 
   
*/

if(window.ie6){



Element.implement({
    empty: function(){
        $A(this.childNodes).each(function(node){
            if ((/select/i).test(node.tagName)){
               node.parentNode.removeChild(node);
            }
            Element.destroy(node);
        });
        return this;
    }
});


void function(){



var _fixSelectFrame=function(panel){
      
      
       var fsf=$(panel).retrieve('iframe',new Element('iframe',{src:'javascript:void(0);',styles:{position:'absolute',
                                                                                       zIndex:-1,
                                                                                       border:'none',
                                                                                       'filter':'alpha(opacity=0)'
                                                                                       }}).inject(panel));
                                                                                       
              fsf.setStyles({'top':0,'left':0,width:panel.offsetWidth,height:panel.offsetHeight});
              
              return fsf;
         

};




    Dialog=Class.refactor(Dialog||$empty,{

             show:function(){
              var dialog=this.dialog;
              var FixSelectFrame=_fixSelectFrame(dialog);
              
              dialog.addEvent('resize',function(){
                   FixSelectFrame.setStyles({'top':0,'left':0,width:dialog.offsetWidth,height:dialog.offsetHeight});     
              
              });
              
              
               this.previous();
           }

    });



    GoogColorPicker=Class.refactor(GoogColorPicker,{
         
             
        initialize:function(el,options){
          this.addEvent('show',function(){_fixSelectFrame(this.gcp_panel)});
          
             this.previous(el,options);
        
        }

    });


try{

    DatePickers=Class.refactor(DatePickers,{
        
        initialize:function(els,options){
         
          this.addEvent('show',_fixSelectFrame);
          
             this.previous(els,options);
        
        }

    });

}catch(e){};

    Tips=Class.refactor(Tips,{
        
       initialize:function(els,options){
          this.addEvent('show',_fixSelectFrame);
          this.previous(els,options);
        }

    });


  
}();


}


