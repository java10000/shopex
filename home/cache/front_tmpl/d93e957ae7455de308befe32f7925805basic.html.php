<?php if(!function_exists('tpl_modifier_paddingleft')){ require(CORE_DIR.'/include_v5/smartyplugins/modifier.paddingleft.php'); } if(!function_exists('tpl_function_uploader')){ require(CORE_DIR.'/admin/smartyplugin/function.uploader.php'); } if(!function_exists('tpl_input_default')){ require(CORE_DIR.'/include_v5/smartyplugins/input.default.php'); } if(!function_exists('tpl_block_help')){ require(CORE_DIR.'/admin/smartyplugin/block.help.php'); } if(!function_exists('tpl_input_select')){ require(CORE_DIR.'/include_v5/smartyplugins/input.select.php'); } if(!function_exists('tpl_input_textarea')){ require(CORE_DIR.'/include_v5/smartyplugins/input.textarea.php'); } if(!function_exists('tpl_modifier_cdate')){ require(CORE_DIR.'/include_v5/smartyplugins/modifier.cdate.php'); } ?><script>

new Swiff.Uploader({
  allowDuplicates:true,
  verbose: true,
  url:'<?php echo $this->_vars['url']; ?>/index.php?ctl=goods/product&act=newPic&p[0]=<?php echo $this->_vars['goods']['id']; ?>&p[1]=add&sess_id='+sess_id,
  path: 'images/uploader.swf',
  typeFilter: {
      'Images (*.jpg, *.jpeg, *.gif, *.png, *.bmp)': '*.jpg; *.jpeg; *.gif; *.png; *.bmp'
  },
  fileSizeMax:<?php echo $this->_vars['max_upload']['size']; ?>,
  target:'fix_fl10upload_panel',
  onSelectFail:function(rs){
      rs.each(function(v){
          if(v.validationError=='sizeLimitMax'){
              alert(v.name+'\n\n文件超出大小');
          };
      });            
  },
  onSelectSuccess:function(rs){
      var PID='up_';
      var _this=this;
      rs.each(function(v,i){
           new Element('div',{'class':'gpic-box','id':PID+v.id}).inject($('all-pics'));
      });
      this.start();        
  },
  onFileOpen:function(e){
      $('up_'+e.id).setHTML('<span><em style="font-size:13px;font-family:Georgia;">0%</em></span>',
                          '<div class="picdelete"><div class="progress-bar" style="width:45px"><i>&nbsp;</i></div></div>');
  },
  onFileProgress:function(e){
      var pt=Number(e.progress.bytesLoaded*100/e.size).toFixed()+'%';  
      $('up_'+e.id).getElement('i').setStyle('width',pt);
      $('up_'+e.id).getElement('em').set('text',pt);
  },        
  onFileComplete: function(res){        
      if(res.response.error){
          return  new MessageBox('文件'+res.name+'上传失败',{type:'error',autohide:true});
      }
      $('up_'+res.id).setHTML(res.response.text);
      if(!$('x-main-pic').retrieve('cururl')){
        $E('#all-pics .gpic-box span').onclick();
      }
  }
});

     
$('gEditor').retrieve('setTabs',function(){
         var gtabs=$ES('#single-page-sidebar .spage-side-nav li.l-handle');
          
        var SpageItemAgg = new Class({
            Extends: ItemAgg,
            initialize: function(tabs, items, options){
                this.setOptions(options);
                 this.items=$$(items);
                 this.tabs=$$(tabs);
                
                 var tempTabs = items.map(function(item,index){
                    if(index == 0) return this.tabs[index];
                    return this.tabs.filter(function(tab){
                        if(tab.getElement('span').get('text') == item.getElement('h3').get('text')) return tab;
                    })[0];
                }, this);
                this.tabs = tempTabs;
                 this.items.setStyle('display','none');
                 this.tempCurIndex=this.options.show||0;

                 if(this.options.firstShow){
                    this.show(this.tempCurIndex);
                 }
                 this.tabs.each(function(item,index){
                    if(!item) return;
                    item.addEvent(this.options.eventName,function(e){
                      e=new Event(e).stop();
                      this.tabs[index].blur();
                      if(this.tempCurIndex!=index){
                          this.show(index);
                          this.hide(this.tempCurIndex);
                          this.tempCurIndex=index;
                      }
                    }.bind(this));
                 },this);
            }
        });
                
         new SpageItemAgg(gtabs,$ES('#gEditor .spage-main-box'),{
            activeName:'cur',
            onActive:function(tab,item){    
                     var anotherItems=$$($A(this.items).remove(item));
                  if(tab.hasClass('all')){
                     anotherItems.show();
                  }else{
                     anotherItems.hide();
                  }
            },
            onBackground:function(tab){
                 tab.style.cssText='';
                 tab.removeClass('');
            }
    });
}());

function finish_gtask_edit(){
    if($('g_taskman_box_end')){
        $('goods_task_area').empty();
        var ipts = $ES('input','g_taskman_box');
        if(ipts.length>0){
            ipts.inject($('goods_task_area'));
            var a = $E('tr.row','g_taskman_box').getElements('td');
            $('goods_task_area').innerHTML += '下次'+a[0].getText().trim() + a[1].getText().trim();
            $('goods_task_btn').setText('定时器管理');
        }else{
            $('goods_task_btn').setText('定时上下架');
        }
    }
}
</script> <h3>基本信息</h3> <div id="x-g-basic" class="goods-detail"> <?php if( $this->_vars['commandtype'] ){ ?> <input type='hidden' name='commandType' value='<?php echo $this->_vars['commandtype']; ?>'/> <?php }  if( $this->_vars['supplier_id'] ){ ?> <input type='hidden' name='command_id' value='<?php echo $this->_vars['command_id']; ?>'/> <input type='hidden' name='object_id' value='<?php echo $this->_vars['object_id']; ?>'/> <input type='hidden' name='supplier_id' value='<?php echo $this->_vars['supplier_id']; ?>'/> <?php } ?> <div class="division"> <table border="0" cellpadding="0" cellspacing="0"> <tr> <th>所属分类：</th> <td> <select name="goods[cat_id]" id="gEditor-GCat-input" class="x-input"> <?php if( $this->_vars['goods']['cat_id'] < 1 ){ ?> <option value="0" >请选择所属分类</option> <?php }  foreach ((array)$this->_vars['cats'] as $this->_vars['cat']){ ?> <option class="optionlevels optionlevel_<?php echo ((((isset($this->_vars['cat']['step']) && ''!==$this->_vars['cat']['step'])?$this->_vars['cat']['step']:1)));?> " value="<?php echo $this->_vars['cat']['cat_id']; ?>" depth="<?php echo $this->_vars['cat']['step']; ?>" type_id="<?php echo $this->_vars['cat']['type']; ?>" <?php if( $this->_vars['cat']['cat_id']==$this->_vars['goods']['cat_id'] ){ ?>selected<?php } ?>> <?php echo tpl_modifier_paddingleft($this->_vars['space'],$this->_vars['cat']['step'],'　');  echo $this->_vars['cat']['cat_name']; ?> </option> <?php } ?> </select> </td> <td rowspan="10" style="width:300px;"><div class="goods-pic-area division" id="action-pic-bar"> <div class="action-bar clearfix"> <?php echo tpl_function_uploader(array('button_text' => "上传新图片"), $this);?> <div class="span-4 last" style="*padding:5px 0;"><input type="checkbox" id="pic-up-btn" style="vertical-align:middle;" onclick="$('imageuparea').toggleDisplay();$('udfimg').value = ($('udfimg').value=='false')?'true':'false'" <?php if( $this->_vars['goods']['udfimg']=='true' ){ ?>checked="checked"<?php } ?>/> <label for="pic-up-btn" style="color:#000;"><span><span><img src="images/transparent.gif" class="imgbundle icon" style="width:15px;height:16px;background-position:0 -133px;" />商品列表页图片设置</span></span></label> </div> </div> <div class="pic-area"><?php $_tpl_tpl_vars = $this->_vars; echo $this->_fetch_compile_include("product/gimage_goods.html", array()); $this->_vars = $_tpl_tpl_vars; unset($_tpl_tpl_vars); ?></div> </div> </td> </tr> <tr> <th>所属类型：</th> <td><select name="goods[type_id]" id="gEditor-GType-input"> <!--<option value='1'>通用商品类型</option>--> <?php foreach ((array)$this->_vars['gtype'] as $this->_vars['type']){  if( $this->_vars['type']['type_id']==$this->_vars['goods']['type_id'] ){ ?>selected<?php } ?>><?php echo $this->_vars['type']['name'];  if( $this->_vars['type']['type_id']==$this->_vars['goods']['type_id'] ){ ?>selected<?php } ?>><?php echo $this->_vars['type']['name']; ?> <option class="optionlevels" value='<?php echo $this->_vars['type']['type_id']; ?>' <?php if( $this->_vars['type']['type_id']==$this->_vars['goods']['type_id'] ){ ?>selected<?php } ?>><?php echo $this->_vars['type']['name']; ?> </option> <?php } ?> </select> <input type="hidden" name="oldTypeId" value="<?php echo $this->_vars['goods']['type_id']; ?>"/> </td> </tr> <tr> <th>商品名称：</th> <td> <?php echo tpl_input_default(array('type' => "text",'id' => "id_gname",'name' => "goods[name]",'required' => "true",'size' => 70,'value' => $this->_vars['goods']['name']), $this);?> <em><font color="red">*</font></em> </td> </tr> <?php if( $this->_vars['goodsbn_display_switch'] ){ ?> <tr> <th>商品编号：</th> <td><?php echo tpl_input_default(array('type' => "text",'name' => "goods[bn]",'value' => $this->_vars['goods']['bn']), $this);?></td> </tr> <?php }else{ ?> <input type='hidden' name="goods[bn]" value="<?php echo $this->_vars['goods']['bn']; ?>"> <?php } ?> <tr> <th>商品关键词：</th> <td><?php echo tpl_input_default(array('type' => "text",'name' => "keywords[keyword]",'value' => $this->_vars['goods']['keywords'],'size' => 70), $this);?> <span class="notice-inline "> 仅用于在前台、后台筛选商品，多个关键词用半角竖线"|"分开 </span> <?php $this->_tag_stack[] = array('tpl_block_help', array('docid' => "73",'type' => "link-small")); tpl_block_help(array('docid' => "73",'type' => "link-small"), null, $this); ob_start(); ?>点击查看帮助<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_content = tpl_block_help($this->_tag_stack[count($this->_tag_stack) - 1][1], $_block_content, $this); echo $_block_content; array_pop($this->_tag_stack); $_block_content=''; ?> </td> </tr> <?php if( $this->_vars['prototype']['setting']['use_brand'] ){ ?> <tr> <th>品牌：</th> <td><?php echo tpl_input_select(array('name' => "goods[brand_id]",'nulloption' => "1",'rows' => $this->_vars['brandList'],'valueColumn' => "brand_id",'labelColumn' => "brand_name",'value' => $this->_vars['goods']['brand_id']), $this);?></td> </tr> <?php }  if( $this->_vars['prototype']['is_physical'] ){ ?> <tr> <th>计量单位：</th> <td><?php echo tpl_input_default(array('type' => "text",'value' => $this->_vars['goods']['unit'],'name' => "goods[unit]",'maxlength' => "25"), $this);?></td> </tr> <?php } ?> <tr> <th>简介：</th> <td><?php echo tpl_input_textarea(array('value' => $this->_vars['goods']['brief'],'name' => "goods[brief]",'cols' => "50",'rows' => "2",'maxth' => "255"), $this);?><br /> <span class="info">简短的商品介绍,请不要超过255字节</span></td> </tr> <tr> <th>是否上架销售：</th> <td> <table><tr><td width="80px" style="border:none"> <input type="radio" name="goods[marketable]"<?php if( $this->_vars['goods']['marketable'] == 'true' or !$this->_vars['goods']['marketable'] ){ ?> checked="checked"<?php } ?> value="true" >是 <input type="radio" name="goods[marketable]"<?php if( $this->_vars['goods']['marketable'] == "false" ){ ?> checked="checked"<?php } ?> value="false" >否 </td> <td style="border:none"> <?php if( $this->_vars['goods']['scheduled'] ){ ?> <span id="goods_task_area" style="paddin-right:5px">下次<?php if( $this->_vars['goods']['scheduled']['0']['action']=='offline' ){ ?>下架<?php }else{ ?>下架<?php }  echo tpl_modifier_cdate($this->_vars['goods']['scheduled']['0']['tasktime'],'FDATE_FTIME');  foreach ((array)$this->_vars['goods']['scheduled'] as $this->_vars['task']){ ?> <input type="hidden" name="scheduled[<?php echo $this->_vars['task']['tasktime']; ?>]" value="<?php echo $this->_vars['task']['action']; ?>" /> <?php } ?> </span> <input id="goods_task_btn" type="button" class="sysiconBtnNoIcon" value="定时器管理" onclick="new Dialog('index.php?ctl=goods/product&act=taskman',{title:'定时器管理',width:450,height:300,ajaxoptions:{method:'post',data:_S('goods_task_area')},onClose:finish_gtask_edit})"> <?php }else{ ?> <span id="goods_task_area" style="paddin-right:5px"></span> <input id="goods_task_btn" type="button" class="sysiconBtnNoIcon" value="定时上下架" onclick="new Dialog('index.php?ctl=goods/product&act=taskman',{title:'定时上下架管理',width:450,height:300,ajaxoptions:{method:'post',data:_S('goods_task_area')},onClose:finish_gtask_edit})"> <?php } ?> </td></tr></table> </td> </tr> <?php if( $this->_vars['point_setting'] == 2 ){ ?> <tr> <th>积分：</th> <td><?php echo tpl_input_default(array('type' => "digits",'value' => $this->_vars['goods']['score'],'name' => "goods[score]",'maxlength' => "25",'required' => "true"), $this);?></td> </tr> <?php } ?> </table> <div id="goods-spec"> <?php if( $this->_vars['prototype']['setting']['use_spec']&& count($this->_vars['goods']['products'])>0 ){  $_tpl_tpl_vars = $this->_vars; echo $this->_fetch_compile_include("product/spec.html", array()); $this->_vars = $_tpl_tpl_vars; unset($_tpl_tpl_vars);  }else{  $_tpl_tpl_vars = $this->_vars; echo $this->_fetch_compile_include("product/nospec.html", array()); $this->_vars = $_tpl_tpl_vars; unset($_tpl_tpl_vars);  }  if( $this->_vars['goods']['supplier_id'] ){  foreach ((array)$this->_vars['old_bn'] as $this->_vars['oldBnItem']){ ?> <input type='hidden' name='src_bn[<?php echo $this->_vars['oldBnItem']; ?>]' value='1'/> <?php }  } ?> </div> <?php if( $this->_vars['prototype']['setting']['use_props'] ){ ?> <h4>扩展属性：</h4> <table border="0" cellpadding="0" cellspacing="0"> <?php if( $this->_vars['prototype']['setting']['use_props'] ){ ?> <tbody id='goods_type_props'> <?php foreach ((array)$this->_vars['prototype']['props'] as $this->_vars['key'] => $this->_vars['aProp']){ ?> <tr class="prop"> <th><?php echo $this->_vars['aProp']['name']; ?>：</th> <td><?php $this->_vars["p_col"]="p_{$this->_vars['key']}";  if( $this->_vars['aProp']['type'] == 'select' ){  echo tpl_input_select(array('name' => "goods[p_{$this->_vars['key']}]",'nulloption' => "1",'options' => $this->_vars['aProp']['options'],'value' => $this->_vars['goods'][$this->_vars['p_col']]), $this); }else{  echo tpl_input_default(array('type' => "text",'name' => "goods[p_{$this->_vars['key']}]",'maxlength' => "25",'value' => $this->_vars['goods'][$this->_vars['p_col']]), $this); } ?></td> </tr> <?php } ?> </tbody> <?php } ?> </table> <?php } ?> </div> </div> 