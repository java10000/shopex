<?php if(!function_exists('tpl_block_area')){ require(CORE_DIR.'/admin/smartyplugin/block.area.php'); } ?><form action="index.php?ctl=goods/virtualcat&act=update" id="catEditor" method="post"> <?php $this->_tag_stack[] = array('tpl_block_area', array('inject' => ".mainHead")); tpl_block_area(array('inject' => ".mainHead"), null, $this); ob_start(); ?> <div class="action-bar mainHead"> <span class="sysiconBtn addorder" onclick="W.page('index.php?ctl=goods/virtualcat&act=addNew')">添加分类</span>(共有<?php echo $this->_vars['tree_number']; ?>个虚拟分类) <?php if( $this->_vars['tree_number']<=500 ){ ?> <span id="showCat-handle" class="sysiconBtn btn-show-cate">全部展开</span> <span id="hideCat-handle" class="sysiconBtn btn-hide-cate">全部收起</span> <?php } ?> </div> <div class='gridlist-head mainHead' style='text-align:center!important'> <div class='span-8' >虚拟分类名称</div> <div class='span-2'>添加子类</div> <div class='span-2'>编辑</div> <div class='span-2'>删除</div> <div class='span-2'>查看商品</div> <div class='span-2'>预览</div> </div> <?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_content = tpl_block_area($this->_tag_stack[count($this->_tag_stack) - 1][1], $_block_content, $this); echo $_block_content; array_pop($this->_tag_stack); $_block_content=''; ?> <div class='gridlist'> <div id="cat_tree" class='gridlist-list' style='text-align:center!important'> <?php $this->_env_vars['foreach']["item"]=array('total'=>count($this->_vars['tree']),'iteration'=>0);foreach ((array)$this->_vars['tree'] as $this->_vars['item']){ $this->_env_vars['foreach']["item"]['first'] = ($this->_env_vars['foreach']["item"]['iteration']==0); $this->_env_vars['foreach']["item"]['iteration']++; $this->_env_vars['foreach']["item"]['last'] = ($this->_env_vars['foreach']["item"]['iteration']==$this->_env_vars['foreach']["item"]['total']); ?> <div depath="<?php echo $this->_vars['item']['step']; ?>" class="clear_cat row" cid="<?php echo $this->_vars['item']['cat_id']; ?>" pid="<?php echo $this->_vars['item']['pid']; ?>"> <div class='row-line'> <div class='span-8' style='text-align:left!important'> <div style="margin-left:<?php echo $this->_vars['item']['step']*25; ?>px;"> <?php if( $this->_vars['tree_number']<=500 ){  if( $this->_vars['item']['cls']=='true' ){ ?> <span style='width:12px;line-height:12px;margin:auto 4px;overflow:hidden;display:inline-block;padding:0;cursor:pointer'> <img src="images/transparent.gif" alt="收起子分类" title="收起子分类" class="imgbundle handle-hide" style="width:12px;height:12px;background-position:0 -861px;" /> <img src="images/transparent.gif" alt="收起子分类" title="展开子分类" class="imgbundle handle-show" style="width:12px;height:12px;background-position:0 -873px;" /> </span> <?php }else{ ?> <img src="images/transparent.gif" class="imgbundle" style="width:12px;height:12px;background-position:0 -76px;" /> <?php }  } ?> 排序 <input class="orderTextStyle _x_ipt" vtype="unsigned" type="number" size="2" name="p_order[<?php echo $this->_vars['item']['cat_id']; ?>]" value="<?php echo $this->_vars['item']['p_order']; ?>" style="width:40px"> <span class="lnk" onClick='W.page("index.php?ctl=goods/virtualcat&act=edit&p[0]=<?php echo $this->_vars['item']['cat_id']; ?>")'><?php echo $this->_vars['item']['cat_name']; ?></span> </div> </div> <div class='span-2'> <?php $this->_vars["cat_id"]=$this->_vars['item']['cat_id']; ?> <span class="opt" onClick='W.page("index.php?ctl=goods/virtualcat&act=addNew&p[0]=<?php echo $this->_vars['item']['cat_id']; ?>")'><img src="images/transparent.gif" alt="添加子分类" class="imgbundle" style="width:16px;height:16px;background-position:0 -0px;" /></span> </div> <div class='span-2'><span class="opt" onclick="W.page('index.php?ctl=goods/virtualcat&act=edit&p[0]=<?php echo $this->_vars['item']['cat_id']; ?>')"><img src="images/transparent.gif" alt="编辑" class="imgbundle" style="width:16px;height:16px;background-position:0 -284px;" /></span></div> <div class='span-2'><span class="opt" onclick="deleteRow('index.php?ctl=goods/virtualcat&act=toRemove&p[0]=<?php echo $this->_vars['item']['cat_id']; ?>',event)"> <img src="images/transparent.gif" alt="删除" class="imgbundle" style="width:16px;height:16px;background-position:0 -214px;" /></span> </div> <div class='span-2'> <a href='index.php?ctl=goods/product&act=index&filter=<?php echo urlencode(serialize($this->_vars['item']['link'])); ?>&vfilter=hidden'><img src="images/transparent.gif" border="0" alt="查看此分类下商品" class="imgbundle" style="width:16px;height:16px;background-position:0 -2025px;" /></a> </div> <div class='span-2'><span class="opt" onclick="window.open('<?php echo $this->_vars['item']['url']; ?>')"><img src="images/transparent.gif" alt="跳转前台查看该" class="imgbundle" style="width:18px;height:19px;background-position:0 -2535px;" /></span></div> </div> </div> <?php } unset($this->_env_vars['foreach']["item"]); ?> </div> </div> <?php $this->_tag_stack[] = array('tpl_block_area', array('inject' => ".mainFoot")); tpl_block_area(array('inject' => ".mainFoot"), null, $this); ob_start(); ?> <div class="mainFoot"> <div class="footer"> <table cellspacing="0" cellpadding="0" align="center" class="table-action"> <tr> <td><b class="submitBtn"><input onclick="$('catEditor').fireEvent('submit',{type:'submit',target:$('catEditor')})" type="submit" value="保存排序" /></b></td> </tr> </table> </div> </div> <?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_content = tpl_block_area($this->_tag_stack[count($this->_tag_stack) - 1][1], $_block_content, $this); echo $_block_content; array_pop($this->_tag_stack); $_block_content=''; ?> </form> <script>

function deleteRow(act,event){
e=$(new Event(event).stop().target);
     var row=e.getParent('.row');
    
    if(confirm('您确定要删除该分类？')){ 
        W.page(act,{
        method:'get',
        update:'messagebox',
        onComplete:function(re){
            
            if(re.contains('successSplash')){row.remove();}
        
            }
        });
    }
}
<?php if( $this->_vars['tree_number']<=500 ){ ?>
void function(){
   
   $E('#hideCat-handle').addEvent('click',function(){
    $ES('#cat_tree .clear_cat').each(function(e){
        if(e.get('depath')>1){
            e.setStyles({'display':'none'});
        }
    });
    $ES('#cat_tree .handle-hide').hide();
  });
    $E('#showCat-handle').addEvent('click',function(){

        $ES('#cat_tree .clear_cat').each(function(e){
            if(e.get('depath')>1){
                e.setStyles({'display':''});
            }
        });
        $ES('#cat_tree .handle-hide').show();
    });

    $('cat_tree').addEvent('click',function(e){
       
       if(!e.target.className.match(/handle-/i))return;
    

      var handle=$(e.stop().target);
			var eventRow=handle.getParent('.row');
			var visible=handle.hasClass('handle-show')?'':'none';	
				if(visible=='none'){	   
					 	 handle.hide().getNext().show();						  			 				   			  			   				    	
					}else{																   
					 	 handle.hide().getPrevious().show(); 						 
									    
					}	
			flode(eventRow,visible);

    });
   
	function flode(eventRow,visible){			
			var cid=eventRow.get('cid');
			var pid=eventRow.get('pid');		
			
			eventRow.getAllNext('div[pid='+cid+']').each(function(row){								
				if(visible=='none'){						
					row.hide();
					var obj=row.getElements('.span-8 img');							
					if(obj.length>1){					
						flode(row,visible);	
					}											
				}else{						
					row.show();		
					var obj=row.getElements('.span-8 img');							
					if(obj.length>1){			   
						var vis=(obj[0].getStyle('display')=='none'?'none':'inline');			
						flode(row,vis);	
					}
				}	
						
			});
	}

}();
<?php } ?>
</script>