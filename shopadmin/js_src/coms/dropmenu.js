void function(){
var dropedMenu;

DropMenu = new Class({
	options: {
		periodical: false,
		delay: 500,
		postvar:'finderItems',
		varname:'items',
		height:250,
        relative:window,
        offset:{x:0,y:0}
	},initialize: function(el, options){
        var el=$(el);
        if(!el)return;
        options=$extend(this.options,options);
        
        var menu=$(options.menu)||$(el.get('dropmenu'));
        
        if(!menu)return;
       
		
        el.addEvent('click',function(e){
           if($(e.target).get('dropfor')){
             e.stopPropagation();
           }
          
			if('x_btn_finder-tag'==this.get('id')){
				$('finder-tag').fireEvent('show');
			}			
			
            e=$(this.get('dropfor'))||this;
            e.blur();
            
            if(e.hasClass('droping')){
               e.removeClass('droping');
               return menu.hide();
            }
            
            
            e.addClass('droping');
            
            e.fireEvent('dropmenu',{type:'dropmenu',target:e,'menu':menu});
         
			var p = e.getPosition(options.relative);
            
			if(!menu.get('initEd')){
				menu.setStyles({'display':'block','visibility':'hidden','overflow-y':'auto'});
				menu.set('initEd','1');
				if(menu.offsetHeight>options.height){
					menu.setStyle('height',options.height);
				}
				menu.setStyle('visibility','visible');
			 }
			
			menu.setStyles({
							 left:p.x+options.offset.x,
							 top:p.y+e.getSize().size.y+options.offset.y,
							 'display':'block'
						   });
			
			menu.store('drophandle',e);
           
           (function(){
           
                document.body.addEvent('click',function(){
                        menu.retrieve('drophandle').removeClass('droping');
                        if(menu.setStyle){
                            menu.setStyle('display','none');
                        }
                        this.removeEvent('click',arguments.callee);
                    });
                }).delay(100);
                
                
                
			});
	}
});
}();