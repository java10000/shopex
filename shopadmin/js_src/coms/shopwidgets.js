/*shopAdmin Widgets
    Extends by   JS/coms/DragDropPlus.js
*/
var Widgets=new Class({
   Extends:DragDropPlus,
   initialize:function(drags,drops,options){
      this.parent(drags,drops,options);
        this.drags.each(function(drag){
               if(drag.getProperty('ishtml')){
                    $E('.content-html',drag).setHTML($E('.content-textarea',drag).getValue());
                }

        });

   },
   inject:function(widget,theme){
        this.addWidget(this.curEl,widget,theme?theme:this.theme);
    },
    newWidget:function(el){
        this.curDrop = el;
        this.curdialog=new parent.Dialog(top.SHOPADMINDIR+'index.php?ctl=content/pages&act=widgets',{width:800,height:500,title:'增加页面版块',modal:true,resizeable:true,onShow:function(e){
        this.dialog_body.id='dialogContent';
    }});
    },
    htmlarea:function(el){
       
    },
    ghostDrop:function(widget,theme){
        var widget=widget;
        var theme=theme;
        this.drag_operate_box.setStyle('visibility','hidden').store('lock',true);
        $('tempDropBox')?$('tempDropBox').empty().remove():'';
        parent._showWidgets_tip('在您需要放入版块的蓝色区域点击鼠标左键即可添加版块。点击鼠标右键则取消添加版块操作。');
        this.tempDropBox=new Element('div',{'id':'tempDropBox'}).inject(document.body);
        try{
        this.drops.each(function(item,index){
            if(!item)return;
            var _this=this;
            var cis=item.getCis();
            if(cis.height>5&&cis.width>5){
            cis.height-=5;
            cis.width-=5;
            }
            var dropghost=new Element('div',{
                    'class':'widgets_drop_ghost',
                    'styles':$merge(cis,{'opacity':.3}),
                    'text':'['+(index+1)+']',
                    'title':'点击此处，将['+widget.name+']版块添加到此区域'
                 }).addEvents({
                    'mouseover':function(){this.addClass('widgets_drop_ghost_on')},
                    'mouseleave':function(){this.removeClass('widgets_drop_ghost_on')},
                    'click':function(){
                        _this.tempDropBox.empty();
                        _this.addWidget(item,widget,theme);
                        _this.drag_operate_box.store('lock',false);
                        parent._hideWidgets_tip();
                    }
                 }).inject(_this.tempDropBox);
        },this);
        }catch(e){alert(JSON.encode(e));}
        document.body.addEvent('contextmenu',function(e){
           e.stop();
           $('tempDropBox')?$('tempDropBox').empty().remove():'';
           parent._hideWidgets_tip();
            this.drag_operate_box.store('lock',false);
           document.body.removeEvent('contextmenu',arguments.callee);
        }.bind(this));
    },
    copyWidgets:function(el){
          /*var elsource=el.source;
             new Ajax('index.php?ctl=system/template&act=copyWg&p[0]='+elsource.getProperty('id')+'&p[1]='+elsource.getProperty('widgets_id'),{onComplete:function(re){
                 //console.info(re);
                var widgets_id=Json.evaluate(re).widgetid;
                elsource.setProperty('widgets_id',widgets_id);
             }}).request();
             elsource.setStyle('border','1px dashed #333');*/
    },
    addWidget:function(drop,widget,theme){
        var dialogSetting={
            modal:true,
            title:'增加版块:['+widget.label+']',
            ajaksable:false
        };
        this.curdialog=new top.Dialog(top.SHOPADMINDIR+'index.php?ctl=system/template&act=doAddWidgets&p[0]='+widget.name+'&p[1]='+theme,dialogSetting);
        this.curDrop=drop;
    },
    editWidget:function(widget){
        var dialogSetting={
        modal:true,
        title:'修改版块:['+widget.label+']',
        ajaksable:false
        };
        this.curWidget=$(widget);
        if(widget.get('ishtml'))return this.curdialog=new top.Dialog(top.SHOPADMINDIR+'index.php?ctl=content/pages&act=editHtml',
        $merge(dialogSetting,{ajaksable:true,
                              ajaxoptions:{method:'post',
                                            data:'htmls='+encodeURIComponent($E('.content-html',widget).getHTML().clean().trim())
                                            },title:'编辑HTML'}));
                                            
        this.curdialog=new top.Dialog(top.SHOPADMINDIR+'index.php?ctl=system/template&act=editWidgets&p[0]='+widget.get('widgets_id')+'&p[1]='+widget.get('widgets_theme'),dialogSetting);
       
    },
    saveWidgets:function(){
        var params=[];
        var wpanels=this.drops;
        var file={};
        wpanels.each(function(item,index){
            var widgets =$ES('.shopWidgets_box',item);
            widgets.each(function(widgetbox){
                params.push(("widgets[{widgetsId}]={baseFile}:{baseSlot}:{baseId}").substitute({
                                                       widgetsId:widgetbox.get('widgets_id'),
                                                       baseFile:this.bf,
                                                       baseSlot:this.bs,
                                                       baseId:this.bi
                                                    }));
                                                    
                                                    
                             
               if(widgetbox.get('ishtml')){
                    var ch=$E('.content-html',widgetbox);
                    params.push(('html[{widgetsId}]={htmls}').substitute({
                        widgetsId:widgetbox.get('widgets_id'),
                        htmls:encodeURIComponent(ch.getHTML())
                    }));
                }
            },{mce:this.mce,bf:item.get('base_file'),bs:item.get('base_slot'),bi:item.get('base_id')});
            file[item.get('base_file')]=1;
          }.bind(this));

        
        for(f in file){params.push(("files[]={file}").substitute({file:f}));}

        new XHR({method:'post',
                    onRequest:function(){
                       top.MODALPANEL.show();
                       top.$('loadMask').amongTo(top.window).show();
                    },
                    onSuccess:function(re){
                        try{
                            var re=Json.evaluate(re);
                            for (dom in re) {
                                    if($chk($(dom))&&$chk(re[dom]))
                                    $(dom).setProperty('widgets_id', re[dom]);
                                }
                            top.MessageBox.success('保存成功!');
                        }catch(e){
                             top.MessageBox.error("保存失败"+e.message);
                        }finally{
                            top.MODALPANEL.hide();
                            top.$('loadMask').hide();
                        }
                    }
        }).send(top.SHOPADMINDIR+'index.php?ctl=system/template&act=widgetsSave',params.join('&'));
    }
});


window.addEvent('domready',function(){

if(parent.$('loadMask'))parent.$('loadMask').hide().getElement('.loading_msg').set('text','正在加载...');
if(parent.MODALPANEL)parent.MODALPANEL.hide();


    this.shopWidgets=new Widgets('.shopWidgets_box','.shopWidgets_panel',{
        onEdit:function(widget,widget_panel){
           shopWidgets.editWidget(widget);
        },
        onDelete:function(widget){
           if(!confirm('确定删除此版块?'))return;
           var dob=this.drag_operate_box;
           var _this=this;
           dob.setStyle('visibility','hidden').store('lock',true);
           var drop=widget.getParent();
             widget.effect('opacity').start(0).chain(function(){
                   widget.remove();
                   dob.store('lock',false);
                   _this.checkEmptyDropPanel(drop);
           });
        }
    });
    


});



