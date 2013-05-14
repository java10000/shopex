Element.extend({
	 amongTo:function(elp,opts){
		var el=this;
		var elSize=el.getSize(),
		    elpSize=elp.getSize();
		var options={width:2,height:2};
        
		if(opts){options=$merge(options,opts);}
        
		el.setStyles({
		'position':'absolute'
		});
        
                
		var pos={
		'top':Math.abs(((elpSize.size.y/options.height).toInt())-((elSize.size.y/options.height).toInt())+elp.getPosition().y+elpSize.scroll.y),
		'left':Math.abs(((elpSize.size.x/options.width).toInt())-((elSize.size.x/options.width).toInt())+elp.getPosition().x+elpSize.scroll.x)
		}
		el.setStyles(pos);
        
        if(el.getStyle('opacity')<1)el.setOpacity(1);
        if(el.getStyle('visibility')!='visible')el.setStyle('visibility','visible');
        if(el.getStyle('display')=='none')el.setStyle('display','');
        
        
		return this;	
	 },
	 zoomImg:function(maxwidth,maxheight,v){
	   if(this.getTag()!='img'||!this.width)return;
       var thisSize={'width':this.width,'height':this.height};
		   if (thisSize.width>maxwidth){
		      var overSize=thisSize.width-maxwidth;
			  var zoomSizeW=thisSize.width-overSize;
			  var zommC=(zoomSizeW/thisSize.width).toFloat();
			  var zoomSizeH=(thisSize.height*zommC).toInt();
			  $extend(thisSize,{'width':zoomSizeW,'height':zoomSizeH});
		   }
		   if (thisSize.height>maxheight){
		      var overSize=thisSize.height-maxheight;
			  var zoomSizeH=thisSize.height-overSize;
			  var zommC=(zoomSizeH/thisSize.height).toFloat();
			  var zoomSizeW=(thisSize.width*zommC).toInt();
			  $extend(thisSize,{'width':zoomSizeW,'height':zoomSizeH});
		   }
	   if(!v)return this.set(thisSize);
	   return thisSize;
	 },
	 subText:function(count){
	    var txt=this.get('text');
		if(!count||txt.length<=count)return txt;
		this.setText(txt.substring(0,count)+"...");
		if(!this.retrieve('tip:title'))
		this.set('title',txt);
		return txt;
	 },
	 getValues:function(){
		var values = {};
		this.getFormElements().each(function(el){
			var name = el.name;
			var value = el.getValue();
			if (value === false || !name || el.disabled) return;
			values[el.name] = value;
		});
		return values;
	 },
	 getCis:function(){
			return this.getCoordinates(arguments[0]);
		},
	 getContainer:function(){
		return this.getParent("*[container='true']")||$('main')||document.body;
		},
	 bindValidator:function(vipt_className){
               vipt_className=vipt_className||'x-input';
		        var status=true;
				var errorIndex=[];
				var f=this;
                var elements=f.getElements('.'+vipt_className);
				if(vipt_className!="_x_ipt"){
					   elements.combine(f.getElements('._x_ipt'));
					}
                if(!elements||!elements.length)return status;
				elements.each(function(el,idx){
                
                  if(el.get('emptyText')){
                              Element.clearEmptyText(el);
                         }
						var vv=validator.test(f,el);
                        
                        
                        
						if(!vv){
						  errorIndex.push(idx);
                          status=false;
						}
				});
				if(!status){
				     var el=elements[errorIndex[0]];
                     try{
                         if(el.isDisplay()){
                             el.focus();
                         }
                     }catch(e){}
				}
		        return status;
		},
	  show:function(){
          this.fireEvent('onshow',this);
          return 	$chk(this)?this.setStyle('display',''):this;
	  },
	  hide:function(){
           this.fireEvent('onhide',this);
           return 	$chk(this)?this.setStyle('display','none'):this;
	  },
      isDisplay:function(){
         if('none'==this.getStyle('display')||(this.offsetWidth+this.offsetHeight)==0){
            return false;
         }
         return true;
      },
	  toggleDisplay:function(){
		 return 	$chk(this)&&this.getStyle('display')=='none'?this.setStyle('display',''):this.setStyle('display','none');
	  },
	  getFormElementsPlus:function(ft){
	   var elements=[];
	   var nofilterEls=$$(this.getElements('input'), this.getElements('select'), this.getElements('textarea'));
       if(ft){
           nofilterEls=nofilterEls.filter(ft);
       }	   
       nofilterEls.each(function(el){
		  var name = el.name;
		  var value = el.getValue();
		  if(!name||!value)return;
		  if(el.getProperty('type')=='checkbox'||el.getProperty('type')=='radio'){
                 if(!!el.getProperty('checked'))
				 return elements.include($(el).toHiddenInput());
                 return;
			 }
		  elements.include(el);
		});
		return $$(elements);
	  },
	  toHiddenInput:function(){
	    return  new Element('input',{'type':'hidden','name':this.name,'value':this.value});
	  },
      clearEmptyText:function(){
          var et=$(this).retrieve('et',this.get('emptyText'));
          if(this.value==et){
              this.value='';
          }
          this.focus();
      }
	});
  String.extend({
	  format:function(){
        if(arguments.length == 0)
          return this;
         var reg = /{(\d+)?}/g;
         var args = arguments;
		 var string=this;
         var result = this.replace(reg,function($0, $1) {
           return  args[$1.toInt()]||"";
          }
     )
     return result;
  },
  toFormElements:function(){
    if(!this.contains('=')&&!this.contains('&'))return new Element('input',{type:'hidden'});
    var elements=[];
   
    var queryStringHash=this.split('&');
 
    $A(queryStringHash).each(function(item){

        if(item.contains('=')){
            item=$A(item.split('='));

            elements.push(new Element('input',{type:'hidden',name:item[0],value:decodeURIComponent(item[1])}));
        }else{
          elements.push(new Element('input',{type:'hidden',name:item}));
        }
    });
    return new Elements(elements);
  },
  getLength:function(charAt){      
      var str = this;
      len = 0; 
        for(i=0;i<str.length;i++){ 
            iCode = str.charCodeAt(i);
            if((iCode>=0 && iCode<=255)||(iCode>=0xff61 && iCode<=0xff9f)){
                len += 1;
            }else{
                len += charAt||3;
            }
        }
    return len;
  },
  limitLength:function(){
      var str=this;
      var limit=Array.flatten(arguments);
      var len=str.getLength();
      limit[0]=limit[0]||0;
      limit[1]=limit[1]||1;
      if(len>=limit[0]&&len<=limit[1]){
          return true;
      }      
      
      return false;
       
  }
});

var ItemAgg=new Class({
 Implements:[Events,Options],
  options:{
   onActive:Class.empty,
   onBackground:Class.empty,
   show:0,
   eventName:'click',
   activeName:'cur',
   firstShow:true
  },
  initialize:function(tabs,items,options){
     this.setOptions(options);
	 this.tabs=$$(tabs);
	
	 this.items=$$(items);
	 this.items.setStyle('display','none');
     this.tempCurIndex=this.options.show||0;
     
	 if(this.options.firstShow){
	    this.show(this.tempCurIndex);
     }
	 this.tabs.each(function(item,index){
	    item.addEvent(this.options.eventName, function(e){
	        e=new Event(e).stop();
	        this.render(index);
	    }.bind(this));
	 },this);
  },
  render:function(index){
      this.tabs[index].blur();
      if(this.tempCurIndex!=index){
        this.show(index);
        this.hide(this.tempCurIndex);
        this.tempCurIndex=index;
	  }
  },
  show:function(index){
    this.items[index].show();
	this.tabs[index].addClass(this.options.activeName);
	this.fireEvent('onActive',[this.tabs[index],this.items[index]],this);
  },
  hide:function(index){
    this.items[index].hide();
	this.tabs[index].removeClass(this.options.activeName);
	this.fireEvent('onBackground',[this.tabs[index],this.items[index]],this);
  }
});

var _S=$,_SES=$ES,_SS=$$,_SE=$E;


/* clean null Object*/
function $cleanNull(){
	var obj=arguments[0];
	for(p in obj){
	  if(!obj[p])
	  delete(obj[p]);
	}
	return obj;
};


var tagInputer = new Class({
	initialize:function(input,btns){
		this.input = input;
		this.btns = btns.addEvent('click',this.toggle.bind(this));
	},
	setBtns:function(btns){
		this.btns = btns.addEvent('click',this.toggle.bind(this));
	},
	toggle:function(e){
		e = $(new Event(e).target);
		if(e.hasClass('checked')){
			this.removeTag(e.getText());
			e.removeClass('checked');
		}else{
			this.addTag(e.getText());
			e.addClass('checked');
		}
	},
	addTag:function(t){
		var tags = this.input.value.split(/\s+/);
		this.input.value = tags.include(t).join(' ').trim();
	},
	removeTag:function(t){
		var tags = this.input.value.split(/\s+/);
		this.input.value = tags.remove(t).join(' ').trim();
	},
	set:function(tags){
		if(!tags)return;
		this.input.value = tags.join(' ').trim();

		var tagHash = {};
		tags.each(function(k){
				this[k] = 1;
		}.bind(tagHash));
		
		this.btns.each(function(btn){
				if(this[btn.getText()]){
					btn.addClass('checked');
				}else{
					btn.removeClass('checked');
				}
		}.bind(tagHash));

	}
});


/*checkbox划选

  @params scope checkbox所在容器
  @params match 从容器去取得所有checkbox的selector
*/
var attachEsayCheck=function(scope,match,callback){
        callback=callback||$empty;
        var scope=$(scope);
        if(!scope)return;
        var checks=scope.getElements(match);

        if(!checks.length)return;       
       var targetRoot;
        
        scope.addEvents({
              'mousedown':function(e){
                 scope.store('eventState',e.type);
                 targetRoot=false;
              },
              'mouseup':function(e){
                scope.store('eventState',e.type);
              }
        });
        checks.addEvent('mouseover',function(){
                if(scope.retrieve('eventState')!='mousedown')return;
                var _target=$pick(this.match('input')?this:null,this.getElement('input'));
               
                if(!_target)return;
                if(_target.get('disabled'))return;
                   if(!targetRoot){
                     targetRoot=_target.set('checked',!_target.get('checked'));
                      targetRoot.fireEvent('change');
                      callback(targetRoot);
                       return;
                   }
                   _target.set('checked',targetRoot.get('checked'));
                   _target.fireEvent('change');
                   callback(_target);
             
                      
        });
};

/*viewIMG*/
function viewIMG(src,eventEl){
   var el=$(eventEl);
   el.addClass('viewIMG_Loading');
   var imgViewBox=($('imgViewBox')||new Element('div',{'id':'imgViewBox'}).setStyles({
    'border':'4px #000 solid',
	'padding':2,
	'background':'#ffffff',
	'position':'absolute',
	'zIndex':65535,
	'top':-2000
   }).inject(document.body))
   .empty().setHTML("<div style='padding:5px;'><span style='display:block;text-align:right;cursor:pointer;border-top:1px #ccc solid;' onclick='$(\"imgViewBox\").hide()'>关闭</span></div>");
    var _IMG= new Asset.image(src,{onload:function(){
				  var img=$(this);
				  if(img.$e)return;
			      img.injectTop(imgViewBox); 
				  imgViewBox.show().amongTo(window);
				  el.removeClass('viewIMG_Loading');
				  img.$e=true;
			},onerror:function(){alert("加载图片失败");el.removeClass('viewIMG_Loading');}});
};



_open = function(url,options){
   
   options = $extend({
     width:window.getSize().x*0.8,
     height:window.getSize().y*0.8
   },options||{})
   var params = 'toolbar=no,location=no,status=no,menubar=no,scrollbars=yes,resizable=yes,width={width},height={height}';
       params = params.substitute(options);
       
   window.open(url||'about:blank','_blank',params);

};


window.init_select = function(e){
		this.empty();
		new Request.JSON({secure:1,onComplete:function(p){
				this.removeEvent('focus',window.init_select);
				$H(p).each(function(k,v){
					new Element('option',{value:k}).setText(v).inject(this)}.bind(this))
				}.bind(this)
		}).get(this.get('remote_url'));
	}

window.obj_finder_call_back = function(dialog){
	if(!this.getAttribute('multiple')){
		var ipt = dialog.getElement('.dialog-content-body').getElement('input[checked]');
		if(!ipt){return}
		if(this.getElement('input'))this.getElement('input').value = ipt.value;
		if(this.getElement('.label'))this.getElement('.label').setText(ipt.getNext('label').getText());
	}else{
		var table = this.getParent('div[params]');
		var toadd = [];
		dialog.getElement('.dialog-content-body').getElements('input[checked]').each(function(ipt){
			if(!$E('input[item_id='+ipt.value+']',table)){
				toadd.push(ipt.value);
			}
		});
	    var myRequest=new Request({
		   onComplete:function(retext){
				table.getElement('.rows-body').innerHTML+=retext;
		   }
		 });
		var params = JSON.decode(table.get('params'));
		myRequest.post('index.php?ctl=editor&act=object_rows',
			{data:toadd,iptname:params.name,
			object:params.object,cols:params.cols,view:params.view});
	}
}