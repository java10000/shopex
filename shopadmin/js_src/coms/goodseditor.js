var ShopExGoodsEditor = new Class({
    Implements:[Options],
    options: {
        periodical: false,
        delay: 500,
        postvar:'finderItems',
        varname:'items',
        width:500,
        height:400
    },
    initialize: function(el, options){
   
        this.el = $(el);
        this.setOptions(options);
        this.cat_id = $('gEditor-GCat-input').getValue();
        this.type_id = $('gEditor-GType-input').getValue();
        this.goods_id = $('gEditor-GId-input').getValue();
        this.initEditorBody.call(this);
        
    },
    initEditorBody:function(){
         
        var _this=this;
        var gcatSelect=$('gEditor-GCat-input');
        var gtypeSelect=$('gEditor-GType-input');
        
        gcatSelect.addEvent('change',function(e){
            var selectedOption=$(this.options[this.selectedIndex]);
            var typeid=selectedOption.get('type_id')||1;
            
            if(typeid!=gtypeSelect.getValue()){
            
                checkTypeTransform(
                    'cat',
                    (function(){
                    
											if(gtypeSelect.getValue() == 1){
													gtypeSelect.getElement('option[value='+typeid+']').set('selected',true);
													MODALPANEL.show();
													_this.updateEditorBody.call(_this);
											}else{
													if(confirm('\t是否根据所选分类的默认类型重新设定商品类型？\n\n如果重设，可能丢失当前所输入的类型属性、关联品牌、参数表等类型相关数据。')||this.getValue()<0){
															gtypeSelect.getElement('option[value='+typeid+']').set('selected',true);
															MODALPANEL.show();
															_this.updateEditorBody.call(_this);
													}
											}
                    }).bind(this),
                    (function(e){
                    alert(e);
                    }).bind(this)
                );
            }
             _this.cat_id = this.getValue();
           
        });
        gtypeSelect.addEvent('click',function(){
           this.store('tempvalue',this.getValue());
        });
        gtypeSelect.addEvent('change',function(e){
            var tmpTypeValue = this.retrieve('tempvalue');
            checkTypeTransform(
                'type',
                (function(e){
                    if(this.getValue()&&confirm('是否根据所选类型的默认类型重新设定商品类型？\n如果重设，可能丢失当前所输入的类型属性，关联品牌，参数表等类型的相关数据')){
                        MODALPANEL.show();
                        _this.updateEditorBody.call(_this);
                        _this.type_id=this.getValue();
                    }else{
                        this.getElement('option[value='+tmpTypeValue+']').set('selected',true);
                    }
                }).bind(this),
                (function(e){
                    alert(e);
                    this.getElement('option[value='+tmpTypeValue+']').set('selected',true);
                }).bind(this)
            );
        });
        
       var checkTypeTransform = function(ctype,succfunc,failfunc){
           new Request({
                data:$('x-g-basic').toQueryString(),
                onRequest:function(){
                    $('loadMask').show();
                },
                onComplete:function(ers){
                    $('loadMask').hide();
                    if(ers == '1')
                        succfunc();
                    else
                        failfunc(ers);
                }
            }).post('index.php?ctl=goods/gtype&act=typeTransformCheck&p[0]='+ctype);
       }
       
       MODALPANEL.hide();
       
       
    },
    updateEditorBody:function(){
       cb = $defined(arguments[0])?arguments[0]:function(){void(0);};
       $ES('#gEditor textarea[ishtml=true]').getValue();
       W.page('index.php?ctl=goods/product&act=update',{update:'gEditor-Body',
                                                        data:$('gEditor').toQueryString()+'&pic_bar='+encodeURIComponent( $E('#action-pic-bar .pic-area').get('html') ),
                                                        method:'post',
                                                        onComplete:function(){
                                                            this.initEditorBody.call(this);
                                                            var curGimg=$E('#gEditor .gpic-box .current');
                                                            if(curGimg){
                                                              curGimg.onclick();
                                                            }
                                                            cb();
                                                        }.bind(this)
                                                        }
                                                       );
    },
    mprice:function(e){
        for(var dom=e.parentNode; dom.tagName!='TR';dom=dom.parentNode){;}
        var info = {};
        $ES('input',dom).each(function(el){
            if(el.name == 'price[]')
                info['price']=el.value;
            else if(el.name == 'goods[price]')
                info['price']=el.value;
            else if(el.getAttribute('level'))
                info['level['+el.getAttribute('level')+']']=el.value;
        });
        window.fbox = new Dialog('index.php?ctl=goods/product&act=mprice',{ajaxoptions:{data:info,method:'post'},modal:true});
        window.fbox.onSelect = goodsEditor.setMprice.bind({base:goodsEditor,'el':dom});
    },
    setMprice:function(arr){
        var parr={};
        arr.each(function(p){
            parr[p.name] = p.value;
        });
        $ES('input',this.el).each(function(d){
            var level = d.getAttribute('level');
            if(level && parr[level]!=undefined){
                d.value = parr[level];
            }
        });
    },
    spec:{
        addCol:function(s,typeid){
            this.dialog = new Dialog('index.php?ctl=goods/spec&act=addCol&_form='+(s?s:'goods-spec')+'&type_id='+typeid,{ajaxoptions:{data:$('goods-spec').toQueryString()+($('nospec_body')?'&'+$('nospec_body').toQueryString():''),method:'post'},title:'规格'});
        },
        addRow:function(){
            this.dialog = new Dialog('index.php?ctl=goods/spec&act=addRow',{ajaxoptions:{data:$('goods-spec'),method:'post'}});
        }
    },
    adj:{
        addGrp:function(s){
            this.dialog = new Dialog('index.php?ctl=goods/adjunct&act=addGrp&_form='+(s?s:'goods-adj'));
        }
    },
    pic:{
        del:function(ident,obj){
            if(confirm('确认删除本图片吗?')){
                obj = $(obj);
                var pic_box=obj.getParent('.gpic-box');
                try{
                if(ident){
                    new Request({url:'index.php?ctl=goods/product&act=removePic',onSuccess:function(){
                       pic_box.remove();
                       
                       if($E('#all-pics .gpic-box .current'))return;
                       if($$('#all-pics .gpic-box').length&&$$('#all-pics .gpic-box').length>0){
                         $('x-main-pic').empty().set('html','<div class="notice" style="margin:0 auto">请重新选择默认商品图片.</div>');
                       }else{
                         $('x-main-pic').empty().set('html','<div class="notice" style="margin:0 auto">您还未上传商品图片.</div>');
                       }
                       
                    }}).send({ident:ident});
                }}catch(e){
                   pic_box.remove();
                }                
            }
        },
        setDefault:function(id,imgstr){
            if(isNaN(id))return;
			if($('x-main-pic').retrieve('cururl')==imgstr)return;
            $('x-main-pic').store('cururl','loading');
            var lastdft = this.getDefault();
            if(lastdft){
               lastdft=$E('#all-pics img[sn=_img_'+lastdft+']');
                if(lastdft)lastdft.getParent('span').removeClass('current');
            }
            if($E('#x-main-pic input[name=image_default]')){
               $E('#x-main-pic input[name=image_default]').set('value',id);
            }else{
               $('x-main-pic').empty();
               new Element('input',{type:'hidden',name:'image_default'}).set('value',id).inject('x-main-pic');
            }
            if(_temimg=$('x-main-pic').getElements('img')){
               if(_temimg.length){
               _temimg.remove();
               }
            }
           
            
            $('x-main-pic').addClass('x-main-pic-loading');
           
            new Asset.image(imgstr,{
                onload:function($1){
                
                   var _img=$($1.zoomImg(290,220));    
                       _img.inject($('x-main-pic'));  
                       _img.setStyle('margin-top',Math.abs((220-_img.height.toInt())/2));      
                       $('x-main-pic').removeClass('x-main-pic-loading');
                       $('x-main-pic').store('cururl',imgstr);
                },onerror:function(){
                        $('x-main-pic').removeClass('x-main-pic-loading');
                        $('x-main-pic').store('cururl','');
                }
            }); 
            
			var fff = $E('#all-pics img[sn=_img_'+id+']');
			if ( fff ) {
				fff.getParent('span').addClass('current');
			}
        },
        getDefault:function(){
            var o = $E('#x-main-pic input[name=image_default]');
            if(o){
                return o.value;
            }else{return false};
        },
        viewSource:function(act){
           return new Dialog(act,{title:'查看图片信息',singlon:false,'width':650,'height':300});
        }
    },
    rateGoods:{
        add:function(){
            window.fbox = new Dialog('index.php?ctl=goods/product&act=select',{modal:true,ajaxoptions:{data:{onfinish:'goodsEditor.rateGoods.insert(data)'},method:'post'}});
        },
        del:function(){

        },
        insert:function(data){
            $ES('div.rate-goods').each(function(e){
                data['has['+e.getAttribute('goods_id')+']'] = 1;
            });
            new Ajax('index.php?ctl=goods/product&act=ratelist',{data:data,onComplete:function(s){$('x-rate-goods').innerHTML+=s}}).request();
        }
    }
});
