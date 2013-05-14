function selectArea(sel,path,depth){
    var sel=$(sel);
    if(!sel)return;
    var sel_value=sel.value;
    var sel_panel=sel.getParent();
    var selNext=sel.getNext();
    var areaPanel= sel.getParent('*[package]');
    var hid=areaPanel.getElement('input[type=hidden]');

    var setHidden=function(sel){
        var rst=[];
		var sel_break = true;
        var sels=$ES('select',areaPanel);
		sels.each(function(s){
		  if(s.getValue()!= '_NULL_' && sel_break){
			  rst.push($(s.options[s.selectedIndex]).get('text'));
		  }else{
		    sel_break = false;
		  }
		});
        if(sel.value != '_NULL_'){
		    $E('input',areaPanel).value = areaPanel.get('package')+':'+rst.join('/')+':'+sel.value;
		}else{
		    $E('input',areaPanel).value =function(sel){
                          var s=sels.indexOf(sel)-1;
                          if(s>=0){
                             return areaPanel.get('package')+':'+rst.join('/')+':'+sels[s].value;
                          }
                          return '';
            }(sel);
		}
        
    };
	if(sel_value=='_NULL_'&&selNext&& 
			(selNext.getTag()=='span' && selNext.hasClass('x-areaSelect'))){
		sel.nextSibling.empty();
        setHidden(sel);
	}else{
		
		/*nextDepth*/
		var curOption=$(sel.options[sel.selectedIndex]);
		if(curOption.get('has_c')){
		  new Request({
				onSuccess:function(response){
					if(selNext && 
						(selNext.getTag()=='span'&& selNext.hasClass('x-region-child'))){
						var e = selNext;
					}else{
						var e = new Element('span',{'class':'x-region-child'}).inject(sel_panel);
					}
                    setHidden(sel);
					if(response){
						e.set('html',response);
	                    if(hid){
                           hid.retrieve('sel'+depth,$empty)();
                           hid.retrieve('onsuc',$empty)();
                        }
					}else{
						sel.getAllNext().remove();
						setHidden(sel);
						hid.retrieve('lastsel',$empty)(sel);
					}
				}
			}).get('index.php?ctl=default&act=sel_region&p[0]='+path+'&p[1]='+depth);
            if($('shipping')){
			$('shipping').setText('');
            }
		}else{
		    sel.getAllNext().remove();
            setHidden(sel);
            if(!curOption.get('has_c')&&curOption.value!='_NULL_')
            hid.retrieve('lastsel',$empty)(sel);
		}
	}
}