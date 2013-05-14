var Tags_opt=new Class({
	initialize:function(options,sigId){
		this.finderTag=options.finderTag;
        if(!this.finderTag)return;		
		this.tagEvent();		
		this.sigId=sigId;
		this.applyObj=$E('.btn-apply',$('finder-tag'));
		this.createObj=$E('.btn-create-tag',$('finder-tag'));
		this.manage=$E('.btn-tag-manage',$('finder-tag'));
		this.taginput=$E('.tag-editor-value',$('finder-tag'));
		this.tagmain=$E('.tag-editor-group',$('finder-tag'));	
		this.url=options.url;
		this.bindEvent();	
	},
	filterUrl:function(act){	
		if(!this.url){return 'index.php'+location.search.split('&')[0]+"&act="+act;}
		return this.url+"&act="+act;			
	},
	bindEvent:function(){
        if(this.taginput){this.taginput.addEvent('click',function(e){e.stopPropagation();});}
        if(this.applyObj){this.applyObj.addEvent('click',function(e){this.finderTag.fireEvent('apply');}.bind(this));}		
		if(this.createObj){this.createObj.addEvent('click',function(e){this.newDialog();}.bind(this));}
	},
	bindTagEvent:function(obj){	   			
		obj.addEvent('click',function(e){
			e.stop();
		if(!this.hasClass('selected_all')){
			var cloneElement =$E('ul[class=theme_tag] li[class=selected_all]').clone();
			cloneElement.getFirst().replaces(this.getFirst());
			this.removeClass('selected_none');
			this.removeClass('selected_part');
			this.addClass('selected_all');						
		}else{
			var cloneElement =$E('ul[class=theme_tag] li[class=selected_none]').clone();
			cloneElement.getFirst().replaces(this.getFirst());
			this.removeClass('selected_all');
			this.addClass('selected_none');
		}
		});       
	},
	newDialog:function(){
	    var self=this;
		new Dialog(this.finderTag.getElement('.dialogTag').clone(),{width:350, height:200,title:'新建标签',				
		onLoad:function(){		
			this.dialog.getElement('.dialogTag').setStyle('display','block');
		 	this.dialog.getElement('.btnSmt').addEvent('click',function(){
				if($type(self.finderTag.fireEvent)!='function'){this.close();return};
				self.finderTag.fireEvent('createTag',this);					
			}.bind(this));
			this.dialog.getElement('.btnCancel').addEvent('click',function(){
				this.close();													 
			}.bind(this));
			this.dialog.getElement('.tag-editor-value').addEvent('keydown',function(e){
					 if(e.code==13){this.getElement('.btnSmt').fireEvent('click');}
			}.bind(this.dialog));
		}			   
		});		  
	},
	selectedRow:function(){		
		if($('headBar')&&$('headBar').getChildren('form')[0]&&$('headBar').getChildren('form')[0].retrieve('rowselected')){		
			this.key=$H($('headBar').getChildren('form')[0].retrieve('rowselected')).getKeys()[0];
			this.value=$H($('headBar').getChildren('form')[0].retrieve('rowselected')).getValues()[0];
		}
		if(this.value&&this.value.length<1){this.value=null;this.key=null;}
	},
	filterParam:function(){	
		var param=new String;	
		var paramName=/\w+(?=\/)/.exec(location.search);
		if(this.sigId){
			param='&'+paramName+'_id[]='+this.sigId;
		}else{	
			if(this.value&&this.value.contains('_ALL_'))
			return param='&'+this.key+'='+'_ALL_';			
			if(this.value)
			this.value.each(function(el){		
				param+='&'+this.key+'='+el;	
		     }.bind(this));
		}
		return param;
	},
	toQueryString:function(){
		var groupTag=this.finderTag.getElement('ul[class=tag-editor-group]').getElements('li');
		var selected_tag=[],part_tag=[];	
		groupTag.each(function(el){
			if(el.hasClass('selected_all')){selected_tag.include(el);}
			if(el.hasClass('selected_part')){part_tag.include(el);}
		});
		var tags='_SET_TAGS_=';				
		selected_tag.each(function(el){
			var t=el.get('text').trim();tags+='_S_ALL'+t+' ';	
		});						
		part_tag.each(function(el){
			var t=el.get('text').trim();tags+='_S_PAR'+t+' ';	
		});
		return tags;
	},
	tagEvent:function(){
		var self=this;	
		this.finderTag.addEvents({ 
			'show':function(){				
				self.tagmain.setStyle('overflow','hidden');				
				this.setStyle('overflow','hidden');	
				self.selectedRow();				
				var param=self.filterParam();			
				thistag=this;
				var url=self.filterUrl('tagList');
				
				if(!param.length)param=null;
				new Request({
					method: 'post', 
					url:url,
					onSuccess:function(response){	
						var json=JSON.decode(response);
						if(!json)return;						
						thistag.getElement('ul[class=tag-editor-group]').empty();
						if(json.length>10){self.tagmain.setStyles({'overflow-y':'auto','height':'200px'});}		
						for(var i=0;i<json.length;i++){
							var tagj=json[i]['status'];
							var creatElement =$E('ul[class=theme_tag] li[class=selected_'+tagj+']').clone();	
							creatElement.appendText(json[i]['tag_name']);						
							creatElement.inject(thistag.getElement('ul[class=tag-editor-group]'));			
						}
						var groupTag=thistag.getElement('ul[class=tag-editor-group]').getElements('li');		
						self.bindTagEvent(groupTag);									
					}
				}).send(param);									
			},
			'apply':function(sigId){
				var url=self.filterUrl('setTag');	
				self.selectedRow();
				if(!self.url&&!self.sigId&&!sigId)return;								
				if(!self.value&&self.url){MessageBox.error('请选择要操作的项');return;}
				var tagParam=self.toQueryString();			
				if(sigId)self.sigId=sigId;
				var param=self.filterParam()
				tagParam+=param;								
				W.page(url,{update:'messagebox',method: 'post', data:tagParam,
					onComplete:function(){							
						if(self.url){
							for(var f in finderGroup){
     						  		finderGroup[f].refresh();
   							} 
						}
						$('loadMask').hide();	
					}
				});				
			},
			
			'createTag':function(dialog){						
				var tagValue=dialog.dialog.getElement('.tag-editor-value').get('value').trim();			
				if(tagValue.contains(' ')||tagValue.length==0){MessageBox.error('标签名不能为空且其中不能有空格符号!');return;}
				var groupTag=this.getElement('ul[class=tag-editor-group]').getElements('li');
				if(groupTag.some(function(el){return el.get('text').trim()==tagValue})){
					MessageBox.error('此标签已存在');return;
				}
				var url=self.filterUrl('newTag');				
				thistag=this;
			
				W.page(url,{
				        update:'messagebox',
						method: 'post', 
						data:"tag_name="+tagValue,
						onComplete:function(value){								
							dialog.close();							
							self.selectedRow();											
							var creatElement =(self.sigId||self.value&&self.value.length>0)?$E('ul[class=theme_tag] li[class=selected_all]').clone():$E('ul[class=theme_tag] li[class=selected_none]').clone();	
							creatElement.appendText(tagValue);						
							creatElement.inject(thistag.getElement('ul[class=tag-editor-group]'));	
							self.bindTagEvent(creatElement);							
							if(self.value&&self.value.length||self.sigId)self.finderTag.fireEvent('apply');
						
						}
				});		
			}
		});
	}
});
