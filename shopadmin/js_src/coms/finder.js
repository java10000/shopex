/*
   Finder Class
   @autohr liitie@shopex.cn
   @copyright www.shopex.cn
*/
void function(){
     
       /*全局 finderGroup HASH*/
        finderGroup={};
   
        finderDestory=function(){
            for(var key in finderGroup){                   
                   delete finderGroup[key];
            }
       };
    /*
      自定义列表横向拖动事件
    */
    Element.Events.ListDrag = {
        base: 'mousedown', 
        condition: function(event){ 
            
            return (event.control == true); 
        }
     };

    var FinderListDrag=new Class({
             Extends:Drag,
              attach: function(){
                    this.handles.addEvent('ListDrag', this.bound.start);
                    
                    return this;
                },
                detach: function(){
                    this.handles.removeEvent('ListDrag', this.bound.start);
                   
                    return this;
                }
    });
    
    var FinderColDrag=new Class({
             Extends:Drag,
               start:function(event){
      
                    if(this.element.getParent('.finder-header').scrollLeft>0)return;
                    this.parent(event);
                }
    });

    /*
      单元格编辑定位函数。
    */
    var _position=function(panel,event,rela,offsets){
         offsets=offsets||{x:0,y:0};
         
         var size = (rela||window).getSize(), scroll = (rela||window).getScroll();
         
        var tip = {x: panel.offsetWidth, y: panel.offsetHeight};
        var props = {x: 'left', y: 'top'};
        for (var z in props){
            var pos = event.page[z] + offsets[z];
            if ((pos + tip[z] - scroll[z]) > size[z]){

                 pos = event.page[z] - offsets[z] - tip[z];
            
            }
            panel.setStyle(props[z], pos);
        }         
        
        
  
     
    };
    
    /*列表状态保存函数*/
    var _pushColState=function(ctl,colinfo){
       var  events=$('msgStorage').retrieve('events',{finder_colset:{}});
       
            (events['finder_colset'][ctl]=events['finder_colset'][ctl]||{})[colinfo[0]]=colinfo[1];
           
    };
    
    



   
   /*Finder 类定义*/
    Finder=new Class({
          Implements:[Events],
          options:{
             
          
          },
          initialize:function(finderId,options){
        
             $extend(this.options,options);
             /*init finder area*/
            this.id=finderId;
            this.initStaticView();
            this.initView();
            
            this.attachStaticEvents();
           
            this.attachEvents();  
           
          },
          /*初始化 Finder 静态Elements*/
          initStaticView:function(){
          
                  $each(['action','form','search','tip','detail','header','footer'],function(p){
                 
                     this[p]=$('finder-'+p+'-'+this.id);
                   
                 },this);
             
          
          },
          /*初始化 Finder 动态Elements*/
          initView:function(){
          
                /* $each(['header','list','footer'],function(p){
                 
                     this[p]=$('finder-'+p+'-'+this.id);
                     if('list'==p){
                           this[p].store('visibility',true);
                         }                   
                 },this);
*/                 
                 this.list = $('finder-list-'+this.id).store('visibility',true);
                 
                 this.listContainer=this.list.getContainer();
                 
          },
          isVisibile:function(){
                 if(!this.list['retrieve'])return false;
                 return $chk(this.list.retrieve('visibility'));
           },
          attachStaticEvents:function(){
          var finder=this;
          
          if(finder.search){
             finder.search.addEvent('submit',function(e){
                  e.stop();
                  var f = this;
                  var v=f.getElement('.finder-search-input');
                  finder.filter.reset().push({
                                              value:v.value,
                                              name:f.get('current_key'),
                                              label:f.getElement('label').innerHTML+'为:<b>'+v.value+'&nbsp;</b>'
                                              });
                  v.value='';
              });
            new DropMenu($E('.finder-search-select',finder.action),{offset:{y:12,x:-5},relative:finder.action});
          }
          
           if(finder.tip){
                  
                  finder.tip.addEvents({
                      
                      'show':function(className,selectedCount){
                        
                        $$(this.childNodes).hide();
                        var el=this.getElement('.'+className);
                      
                        el.innerHTML=el.innerHTML.replace(/<em>([\s\S]*?)<\/em>/ig,function(){
                            return '<em>'+selectedCount+'</em>';
                        });
                        
                        
                    
                        el.setStyle('display','block');
                        el.getElement('span').set('html',$$('.finder-footer .textright i').get('html'));
                                            
                        if(this.retrieve('show'))return;
                        
                                          
                        this.store('show',true);
                        
                        /*var fx=this.retrieve('showfx',new Fx.Style(this,'height',{link:'cancel',duration:300}));
                        fx.start(25).chain(function(){
                            window.fireEvent('resize');
                        });*/
                        this.show();
                        window.fireEvent('resize');
                      },
                      'hide':function(){
                        if(!this.retrieve('show'))return;
                        this.store('show',false);
                        $$(this.childNodes).hide();
                        
                        this.hide();
                        window.fireEvent('resize');
                        /*this.retrieve('showfx').start(0).chain(function(){
                           
                           window.fireEvent('resize');
                        
                        });*/  
                      }
                  });
               
               }
            if(finder.action){
                
                finder.action.getElements('*[submit]').addEvent('click',function(e){
                     $list = $$('ul.finder-filter-info li.finder-filter-item input[type=hidden]');
                     $list.each(function(item){
                        item.set('disabled',true);
                     });
                     var target=this.get('target');
                     var actionUrl=this.get('submit');
                     var itemSelected=finder.form.retrieve('rowselected');
                     var tmpForm=new Element('div'); 

					 var isAll;
                     for(n in itemSelected){
                         if(n){
                           $A(itemSelected[n]).each(function(v){
				               if(v=='_ALL_')isAll=true;
                               tmpForm.adopt(new Element('input',{type:'hidden','name':n,value:v}))  
                           })  
                         }                        
                      }
				
                      var filter= finder.form.getElement('.finder-filter');
					  if(filter && isAll){
						filter.getElements('input').each(function(f){
							tmpForm.adopt(new Element('input',{type:'hidden','name':f.name,value:f.value})); 
						});
					  }

                      var targetType=target,targetOptions={};
                      if(target){
                          if(target.contains('::')){
                              targetType=target.split('::')[0];
                              targetOptions=JSON.decode(target.split('::')[1]);
                              if($type(targetOptions)!='object'){
                                 targetOptions={};
                              }
                          }
                      }else{
                          targetType = 'refresh';
                      }
                    
                     if(!tmpForm.getFirst())return MessageBox.error('请选择要操作的数据项.');
                     var con = this.getProperty('confirm');
                     if(con){if(!window.confirm(con))return;};                  
                     if(!$(e.target).get('only_id')){
                         tmpForm.adopt(finder.form.toQueryString().toFormElements());
                     }

                     switch (targetType){
                          case 'refresh':
                                W.page(actionUrl,$extend({data:tmpForm,method:'post',update:'messagebox',onComplete:finder.refresh.bind(finder)},targetOptions));
                                break;
                          case 'dialog':
                               var _options=targetType.contains('::')?JSON.decode(targetType.split('::')[1]):{};
                               window.finderDialog = new Dialog(actionUrl,$extend({title:this.get('dialogtitle')||this.get('text'),ajaxoptions:{
                                    data:tmpForm,
                                    method:'post'
                               },onClose:finder.refresh.bind(finder)},targetOptions));
                                break;
                          case '_blank':
                               new Element('form',{action:actionUrl,name:targetType,target:'_blank',method:'post'}).adopt(tmpForm).inject('elTempBox').submit();
                               break;                              
                          default:
		
                              new Element('form',{action:actionUrl,name:targetType,method:'post'}).adopt(tmpForm).inject('elTempBox').submit();
                              /*elTempBox define in shopadmin/index.html*/
                              break;
                     }
                     $list.each(function(item){
                        item.set('disabled',false);
                     });

                });
            
            }

            if(finder.header){
               
         
                  finder.header.addEvent('click',function(e){
                               var target=$(e.target);
                               if(!target.hasClass('orderable')){
                                 target=target.getParent('.orderable');
                               }
                               if(!target)return;
                               var forFill=[('desc'==target.get('order'))?'asc':'desc',target.get('key')]
                                            .link({'_finder[orderType]':String.type,'_finder[orderBy]':String.type});
                                   finder.fillForm(forFill).refresh();
                                     e.stopPropagation();
                               return ;
                     });

             }
             
             
             if(finder.form){
                       
                  if(finder.options.filterInit){
                      new Filter(finder.form.getElement('.finder-filter')).attach(finder.options.filterInit);
                      updateSize();
                  }
            
                  finder.filter=new Filter(finder.form.getElement('.finder-filter'),{onChange:function(){
                                     
                                     finder.unselectAll();
                                     finder.refresh();
                                     
                  
                    }
                  
                  
                  
                  });
             }
             
             if(finder.detail){
             
             finder.detail.retrieve('content',finder.detail.getElement('.finder-detail-content'));
             
             /*if(finder.footer){
                 
                  new Drag(finder.detail,{
                                modifiers:{'y':'height'},
                                invert:true,
                                handle:finder.footer,
                                onDrag:function(el){

                                     if(!finder.detail.retrieve('slideIn')){
                                        return this.stop();
                                     }
                                    // console.info(finder.listContainer.getStyle('height'),this.mouse.now.y,this.mouse.start.y);
                                    
                                     finder.listContainer.setStyle('height',(finder.listContainer.getStyle('height').toInt()+(this.mouse.now.y-this.mouse.start.y)));
                                }
                         });
                 
                 }*/
   
            
      
             finder.detail.addEvents({
              
                 'slideIn':function(){
          
                     var args=Array.flatten(arguments);
                     if(this.retrieve('slideIn')){this.store('slideIn',args);return this.fireEvent('load',args);}
                     this.store('slideIn',args);
                     this.fireEvent('load',args);
                     this.setStyle('height',finder.listContainer.getSize().y*0.5);
                     this.setStyle('width',finder.listContainer.getSize().x);
                     updateSize();
                    // upfinderHeaderSize();
                 },
                 'slideOut':function(){
                        if(!this.retrieve('slideIn'))return;
                        this.store('slideIn',false);

                        this.retrieve('content').empty();
                        
                        this.setStyle('height',0);
                        
                        var curRow=this.retrieve('currow');
                        if(curRow.removeClass){
                          curRow.removeClass('view-detail');
                        }
                        
                        this.store('item-id',null);
                        updateSize();
                        
                        // upfinderHeaderSize();
                 },
                 'load':function(url,options,row,refresh){
                
                     if(!refresh){                     
                     
                         if(!$chk(row)||row.hasClass('view-detail'))return;
                         
                         if($type(this.retrieve('currow'))=='element'){
                          $(this.retrieve('currow')).removeClass('view-detail');
                         }
                         this.store('currow',row.addClass('view-detail'));
                         this.store('item-id',row.get('item-id'));
                     
                     }
                     
                     
                     this.addClass('loading-content');
                     var _finderDetail=this;
                     var _options={
                         update:_finderDetail.retrieve('content'),
                         onComplete:function(){
                            _finderDetail.fireEvent('complete',row);
                         }
                       };
                       $extend(_options,options);
                       /*var _request=new Request.HTML(_options);
                           
                       this.store('request',_request);
                        _request.get(url+'&_ajax=true');*/

                        W.page(url+'&_finder_name='+finder.id,_options);
                 },
                 'complete':function(row){
                    if(row&&(row.getPosition().y>this.getPosition().y)){
                       finder.listContainer.retrieve('fxscroll',new Fx.Scroll(finder.listContainer,{link:'cancel'})).toElement(row);
                    }
                    this.removeClass('loading-content');
                    
                 }
             });
             
              
             
             }
             
         
             
           /* window.addEvent('resize',function(e){
                 try{
                     if(finder.detail.retrieve('slideIn')){
                           finder.detail.setStyle('height',finder.listContainer.getSize().y*0.4);
                           updateSize();                          
                       }
                      // upfinderHeaderSize();
                 }catch(e){                
                    window.removeEvent('resize',arguments.callee);
                 }
             });*/
             
             
          
          },
          selectAll:function(){
             var sellist=this.header.getElement('.sellist');
             sellist.set('checked',true).fireEvent('change').set('checked',false);
            (this.form.retrieve('rowselected')[sellist.name]||[]).empty().push('_ALL_');
             this.tip.fireEvent('show','selectedall');
          },
          unselectAll:function(){
             var sellist=this.header.getElement('.sellist');
             sellist.set('checked',false).fireEvent('change');
              (this.form.retrieve('rowselected')[sellist.name]||[]).empty();
             this.tip.fireEvent('hide');
          },
          attachEvents:function(){
          
               var finder=this;

              
               
               /*finder drag col*/
               if(finder.header){
                   
              
                   finder.header.getElements('col').each(function(col,index){
                  
                   var index=index.toInt();
                   var nth=index+1;
                   var resizeHandle=finder.header.getElement('td:nth-child('+nth+')').getElement('.finder-col-resizer');
                   
                   if(!resizeHandle)return;
                   
                   
                      new FinderColDrag(col,{
                           modifiers:{'x':'width'},
                           limit:{'x':[15,1000]},
                           handle:resizeHandle,
                           unit:0,
                           onBeforeStart:function(el){
                             el.store('lc',finder.list.getElements('col')[index]);

                           },
                           onDrag:function(el){
                   
                              if(!finder.header.hasClass('col-resizing'))finder.header.addClass('col-resizing');
                              var w=el.getStyle('width').toInt();
                              
                        
                              $(el.retrieve('lc')).setStyle('width',w);
                              $(el).addClass('resizing');
                              if(window.webkit){
                                 finder.header.getElement('td:nth-child('+nth+')').setStyle('width',w);
                                 finder.list.getElement('td:nth-child('+nth+')').setStyle('width',w);
                              }
                           },
                           onComplete:function(el){
                              finder.header.removeClass('col-resizing');
                              $(el).removeClass('resizing');
                              var key=finder.list.getElement('td:nth-child('+nth+')').get('key');
                              _pushColState(finder.options.controller,[key,el.getStyle('width').toInt()]);
                           }
                        });
                   
                   });

               }
               
               
           
               
               
               /*finderListEventInfo*/
               var fleinfo=finder.list.retrieve('eventInfo',{});
               
               
               
               
               /*finder form hash*/
               var frowselected=finder.form.retrieve('rowselected',{});
               
               
          
               
               
               /*finder.list.addEvent('selectstart',function(e){
                   e.stop();
               });*/
        
           
               finder.list.addEvents({
                     'selectrow':function(ckbox){           
                       ckbox.getParent('.row').addClass('selected');
                     },
                     'unselectrow':function(ckbox){
                       ckbox.getParent('.row').removeClass('selected');
                     }
                  });
               
               var selectHandles=finder.list.getElements('.sel');
               
                  finder.rowCount=selectHandles.length;
                 
                 if(finder.header && finder.header.getElement('.sellist')){
                      finder.header.getElement('.sellist').addEvent('change',function(){                               
                           selectHandles.set('checked',this.checked)
                           selectHandles.fireEvent('change');                            
                   
                      });
                  }
                  
                   selectHandles.each(function(sel){
                   if(window.ie){
                       sel.addEvent('click',function(){this.fireEvent('change');});
                       sel.addEvent('focus',function(){this.blur();})
                     }
                     sel.addEvent('change',function(){
                     
                             frowselected[this.name]=frowselected[this.name]||[]
                           
                             frowselected[this.name][this.checked?'include':'erase'](this.value);
                             if(!this.checked&&frowselected[this.name].contains('_ALL_')){
                                frowselected[this.name].erase('_ALL_');
                                return finder.unselectAll();
                             }
                           
                           var selectedLength=frowselected[this.name].length;                           
                          
                           if(selectedLength>1){
                              finder.tip.fireEvent('show',['selected',selectedLength]);
                              if(selectedLength==finder.tip.get('count').toInt()||frowselected[sel.name].contains('_ALL_')){
                                  
                                  finder.tip.fireEvent('show',['selectedall',selectedLength]);
                              
                              }
                           }else{
                              finder.tip.fireEvent('hide');
                           }
                           
                           
                           
                           finder.list.fireEvent(this.checked?'selectrow':'unselectrow',this);
                           
               
                     });
                     
                     
                     
                     
                      if(frowselected[sel.name]&&frowselected[sel.name].push&&frowselected[sel.name].contains(sel.value)){
                    
                         sel.set('checked',true).fireEvent('change');
                         
                      }else if(frowselected[sel.name]&&frowselected[sel.name].contains('_ALL_')){
                         sel.set('checked',true).fireEvent('change');
                      }
                      
                      
                      /*保持 showDetails 状态*/
             
                      if(row=sel.getParent('.row')){
                      
                          if(row.get('item-id')==finder.detail.retrieve('item-id')){
                              row.addClass('view-detail');
                              finder.detail.store('currow',row);
                              finder.listContainer.retrieve('fxscroll',new Fx.Scroll(finder.listContainer,{link:'cancel'})).toElement(row);
                          }
                      
                      }
                      
                      
                   
                   });
              
               
             
               
               
               /*单元格编辑弹出 .cell-edit-action*/
               var editPanel=finder.list.getLast();
               if(editPanel)
               editPanel.addEvents({
                   'show':function(cell){
                   
                      if(!cell)return;
                      var epsize=editPanel.getSize().size;
                      editPanel.setStyles({
                          visibility:'visible',
                          opacity:0                        
                      });
                     
                      editPanel.retrieve('fx',new Fx.Styles(editPanel,{link:'cancel',duration:400,transition:Fx.Transitions.Quint.easeOut})).start({
                           opacity:1
                      });
                     
                      var actionPanel=editPanel.getElement('.cell-edit-action');
                      
                      actionPanel.addEvent('submet',function(){
                           this.addClass('cell-edit-action-remote');
                            editPanel.store('requesting',true);
                           var _request=new Request({onSuccess:function(re){
                              
                              if(re.substring(0,3)=='ok:'){
                                (cell.getElement('.cell')||cell).innerHTML=re.substring(3);
                              }else{
                                 MessageBox.error(re);
                              }
                               
                              editPanel.fireEvent('hide',cell);                                                         
                               editPanel.store('requesting',false);
                           }});
                           
                           _request.post(this.get('action'),this);
                           
                           this.store('request',_request);
                           
                        });
                        
                     actionPanel.getElements('button').addEvent('click',function(e){
                        // e.stop();
                        
                        if(this.hasClass('x-select-btn'))return;
                         if(this.get('type')=='submit'){
                                    
                          return actionPanel.fireEvent('submet',cell);      
                         
                         }
                         
                         editPanel.fireEvent('hide',cell);      
                     
                     });   
                     
                    /* actionPanel.getElement('input').focus();
                     actionPanel.getElement('input').addEvent('keyup',function(e){
                            e.stop();
                            if(e.code!=13)return;
                            actionPanel.fireEvent('submet',cell);
                     });*/
                      
                   },
                   'hide':function(cell){
                      this.setStyle('visibility','hidden');
                      var _actionRequest=this.getElement('.cell-edit-action')?this.getElement('.cell-edit-action').retrieve('request'):null;
                      if(_actionRequest&&'cancel' in _actionRequest){
                         _actionRequest.cancel();
                      }
                      if(!cell)return;
                      ['edit-ready','edit-begin','edit-ing'].each(cell.removeClass,cell);
                      this.empty();
                   }
               
               });

               finder.list.addEvent('click',function(e){
                   
                   var target=$(e.target);
                   if(!target)return;
                   
                   if(target.match('img')){target=$(target.parentNode)}
                   
                   /*新窗口查看，选中行*/
                  if(target.match('a')&&target.get('target')=='_blank'){
                        var selfSelected=target.getParent('.row').getElement('input[class=sel]');
                        selfSelected.set('checked',true);
                        finder.list.fireEvent('selectrow',selfSelected);
                        selfSelected.fireEvent('change');  
                        
                        return;
                   }        

                   /*view detail*/
                   if(_detail=target.get('detail')){
                     e.stopPropagation();

                       if(target.hasClass('btn-detail-open')&&target.getParent('.view-detail')){   /*ba 判断详情是否展开 */ 
                            return finder.hideDetail(_detail,{},target.getParent('.row'));
                       }else{   
                            return finder.showDetail(_detail,{},target.getParent('.row'));
                        }
                        
                   }
                   /*enter edit*/
                   if(target.hasClass('cell')||target.hasClass('cell-inside')){target=target.getParent('td');}
                   
                   if(target.match('td')){
                   
                     if(finder.detail.retrieve('slideIn')){
                       
                       if((detailbtn=target.getParent('.row').getElement('*[detail]'))&&!target.getParent('.row').hasClass('view-detail')){
             
            

                         return finder.showDetail(detailbtn.get('detail'),{},target.getParent('.row'));
                       }
                     
                     }
                      if(target.hasClass('editable')){
                          
                         
                          if(target.hasClass('edit-ing')||target.hasClass('edit-begin'))return;   
                         
                          if(target.hasClass('edit-ready')){
                             target.addClass('edit-begin');
                             finder.editCell(target,e,function(editPanel){
                                    target.addClass('edit-ing');
                              });    
                              return;
                          }
                          
                          if(fleinfo.curcell){
                          
                              if(_editPanel=fleinfo.curcell.retrieve('editPanel')){
                                  
                                  if(_editPanel.retrieve('requesting'))return;
                                  
                                  _editPanel.fireEvent('hide');
                              
                              }
                              
                              fleinfo.curcell.removeClass('edit-ready').removeClass('edit-begin').removeClass('edit-ing');
          
                          }
   
                          target.addClass('edit-ready');
                          
                          fleinfo.curcell=target;
                      }
                      
                   }

               });
               
               attachEsayCheck(finder.list,'td:nth-child(first)');
               
    
               

         

             
             
             var container=finder.list.getContainer();
             
             if(finder.header.getStyle('overflow')!='visible'){
                    new FinderListDrag(finder.header,{
                                        handle:finder.list,
                                        style:false,
                                        modifiers: {x: 'scrollLeft'},
                                        invert:true,
                                        preventDefault:true,
                                        onDrag:function(){
                                         
                                            container.scrollTo(finder.header.getScrollLeft());
                                        },
                                        onStart:function(el){
                                          el.setStyle('cursor','move');
                                     
                                           finder.list.addEvent('contextmenu',function(e){e.stop();});
                                        },
                                        onComplete:function(el){
                                          el.setStyle('cursor','default');
                                          (function(){
                                              finder.list.removeEvents('contextmenu');
                                          }).delay(200);
                                        }
                                        });
             
             }
              
          
          
          },
          editCell:function(cell,event,popCall){
             if(!cell)return;
             var finder=this;
              cell.addClass('edit-begin');
             
             var editPanel=finder.list.getLast().setStyle('visibility','hidden');
             
             var rurl='index.php?ctl='+this.options.controller+'&act=cell_editor';
             new Request({onSuccess:function(re){
                    editPanel.set('html',re);
                    _position(editPanel,event);
                    editPanel.fireEvent('show',cell);
                    cell.store('editPanel',editPanel);
                    (popCall||$empty)(editPanel);
            
            }}).post(rurl,{
                 'id':cell.getParent('.row').get('item-id'),
                 'key':cell.get('key').trim()
             });
                 
          },
          fillForm:function(hash){
              if(!hash||'object'!=$type(hash))return;
              hash=$H(hash);
              var finder=this;
              hash.each(function(v,k){
                
                 var focusHInput=(finder.form.getElement('input[name^='+k.slice(0,-1)+']')||new Element('input',{type:'hidden',name:k}).inject(finder.form))
                 focusHInput.set('value',v);
              });
              
              return finder;
               
          },
          eraseFormElement:function(){
               var readyNames=Array.flatten(arguments);
               var finder=this;
               $each(readyNames,function(name){
                  finder.form.getElement('input[name='+name+']').remove();
               });
               
               return finder;
          },
          showDetail:function(url,options,row){
             
              this.detail.fireEvent('slideIn',Array.flatten(arguments));
          
          },    /*ba 隐藏 详情展开区域 */
          hideDetail:function(url,options,row){
                    
              this.detail.fireEvent('slideOut',Array.flatten(arguments));        
          },
          getQueryStringByForm:function(){
            return this.form.toQueryString();
          },
          page:function(num){
              this.form.store('page',num||1);
              
              this.request({
                  method:this.form.method||'post'
              });
          
          },
          refresh:function(){
          
             this.request({
                  method:this.form.method||'post',
                  onComplete:function(){
                     
                     if(sinargs=this.detail.retrieve('slideIn')){
                         sinargs.push('refresh');
                         sinargs[2]=false;//refresh detail, params[2]: row = false;
                         this.detail.fireEvent('load',sinargs);
                     }
                     
                  }
              });
          
          },
          request:function(){
       
             var params=Array.flatten(arguments);
             var p=params.link({                 
                 'options':Object.type,
                 'action':String.type
             });
             //if(!p.action&&!$(this.form.id))return;           
             p.action=p.action||this.form.action+'&page='+(this.form.retrieve('page')||1);
             p.options=p.options||{};
             var _onComplete=p.options.onComplete;
             if($type(_onComplete)!='function'){
                _onComplete=$empty;
             }
             
             p.options=$extend(p.options,{
             
                elMap:{
                     '.mainHead':this.header,
                     '.mainFoot':this.footer
                },onComplete:function(){
                    this.initView();
                    this.attachEvents();
                    _onComplete.apply(this,Array.flatten(arguments));
                }.bind(this)
             
             });
             
             
             var _data=this.getQueryStringByForm();
             var optData=p.options.data;
             
             switch ($type(optData)){
                     
                     case 'string':
                     p.options.data=[_data,optData].join('&');
                     break;
                     case 'object':case 'hash':
                     p.options.data=[_data,Hash.toQueryString(optData)].join('&');
                     break;
                     case 'element':
                     p.options.data=[_data,$(optData).toQueryString()].join('&');
                     break;
                     default:
                     p.options.data=_data;
                 
                 }
             
             
             var finderColStateEvent=$('msgStorage').retrieve('events');
             if($H(finderColStateEvent['finder_colset']).getValues().length){
                 //finder 状态同步.
               new Request().post('index.php?ctl=default&act=status',{'events':$('msgStorage').retrieve('events')}).chain(function(){
                   
                   finderColStateEvent['finder_colset']={};
                    W.page(p.action,p.options);
                   
               }.bind(this));
               
               return;
             }      
             
             
             W.page(p.action,p.options);
          
          }  
    });
    
    
    
    var Filter =new Class({
         Implements:[Events,Options],
         options:{
             onPush:$empty,
             onRemove:$empty,
             onChange:$empty
         },
         initialize:function(filterPanel,options){
        
             var _this=this;
             this.fp=$(filterPanel);
             this.fpInfo=this.fp.getElement('.finder-filter-info');
             this.fp.removeEvents('click').addEvent('click',function(e){
                   var target=$(e.target);
                   if(target&&target.hasClass('remove')){
                         _this.remove(target.getParent('.finder-filter-item'));
                         return e.stopPropagation();
                   }      
             });
             this.setOptions(options);
         },
         push:function(data,isbatch){
   
             if(this.fpInfo.getElement('input[name='+data.name+']')){
             
                 //item.getParent('.finder-filter-item').injectBottom(this.fpInfo);
                 
                 return this;
             }
            
             
             var tpl=this.fp.getElement('.ffitpl').clone();
             
    
          
       
             if('array'==typeof(data.value)){
                 data.name+='[]';
                 data.each(function(v){
                     new Element('input',{type:'hidden'}).set({
                         name:data.name,
                         value:data.value
                     }).inject(tpl);
                 });
             }else{

                     new Element('input',{type:'hidden'}).set({
                         name:data.name,
                         value:data.value
                     }).inject(tpl);
             }
             
             
                      
            if(mg=data.merge){
             
                  $splat(mg).each(function(item){
                    var forAd;
                    if($type(item)=='object'){
                      forAd =new Element('input',{type:'hidden'}).set({      
                          
                                name:item.name,
                                value:item.value
                            });
                    }
                    if($type(item)=='string'){
                    
                      forAd=item.toFormElements();
                    }
                    
                    tpl.adopt(forAd);
                  
                  });
                  
               
                  delete(data.merge);
             
             }
             
        
             
             
             //tpl.getElement('input[type=hidden]').set(data);
             
             tpl.getElement('span').set('html',data.label);
             tpl.className='finder-filter-item';
             this.fpInfo.adopt(tpl);
             
             if(isbatch)return this;
             
             this.change();
             return this;
         },
         attach:function(datas){
       
             /*datas=Array.flatten([datas]);*/
             
             datas.each(function(data,idx){
             
                   this.push(data,true);
                   if(idx==(datas.length-1)){
                       this.change();
                   }
                    
             },this);
         
         },
         remove:function(filterItem){
        
              $(filterItem).remove();
             this.change();
             return this;
         },
         reset:function(){
             this.fpInfo.getElements('.finder-filter-item').each(Element.remove);
             return this;
         },
         change:function(){
            
            if(!this.fpInfo.getElements('.finder-filter-item').length){
                   
                   this.fp.hide();
              }else{
                 
                 this.fp.show();
              
              }
              
              this.fireEvent('change');
         
         }         
    });
    
 
    
    
}();
