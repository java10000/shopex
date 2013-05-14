<?php if(!function_exists('tpl_input_default')){ require(CORE_DIR.'/include_v5/smartyplugins/input.default.php'); } if(!function_exists('tpl_block_help')){ require(CORE_DIR.'/admin/smartyplugin/block.help.php'); } ?><div id="filter_<?php echo $this->_vars['_finder']['domid']; ?>" class='filter_panel'> <div class='filter_box'> <?php if( $this->_vars['_finder']['gtype'] ){ ?> <div class="division" style=" margin:0"> <table width="100%" class='filter_interzone'> <tr> <th>按类型筛选</th> <td> <select onchange="sel_<?php echo $this->_vars['_finder']['domid']; ?>($(this).value);"> <option style="font-weight:bold" value="_ANY_" >全部类型</option> <?php foreach ((array)$this->_vars['_finder']['gtype'] as $this->_vars['type']){ ?> <option value="<?php echo $this->_vars['type']['type_id']; ?>" <?php if( $this->_vars['type']['type_id'] == $this->_vars['_finder']['data']['type_id'] ){ ?>selected="selected"<?php } ?>><?php echo $this->_vars['type']['name']; ?> </option> <?php } ?> </select> </td> </tr> <tr> <th>按价格区间筛选</th> <td><?php echo tpl_input_default(array('type' => "unsigned",'name' => "pricefrom",'value' => $this->_vars['_finder']['data']['pricefrom'],'style' => "width:30px"), $this);?> - <?php echo tpl_input_default(array('type' => "unsigned",'name' => "priceto",'style' => "width:30px",'value' => $this->_vars['_finder']['data']['priceto']), $this);?> 元</td> </tr> <tr> <th>按商品关键词筛选</th> <td><?php echo tpl_input_default(array('type' => "text",'name' => "searchname",'style' => "width:100px",'value' => $this->_vars['_finder']['data']['searchname']), $this);?> &nbsp; <?php $this->_tag_stack[] = array('tpl_block_help', array()); tpl_block_help(array(), null, $this); ob_start(); ?>如果填写商品关键词，则只有符合该关键词搜索条件的商品才会出现在本虚拟分类，具体如下：<br />1、商品名称中包含该关键词<br />2、商品中的商品关键词中有任何一个等于该关键词<br />3、商品中的货号或商品编号等于该关键词<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_content = tpl_block_help($this->_tag_stack[count($this->_tag_stack) - 1][1], $_block_content, $this); echo $_block_content; array_pop($this->_tag_stack); $_block_content=''; ?></td> </tr> </table> </div> <?php } ?> <div id="filter_<?php echo $this->_vars['_finder']['domid']; ?>_body" style="margin:0;margin-top:10px"> <?php $_tpl_tpl_vars = $this->_vars; echo $this->_fetch_compile_include($this->_vars['_finder']['view'], array()); $this->_vars = $_tpl_tpl_vars; unset($_tpl_tpl_vars); ?> </div> </div> <input type="hidden" name="<?php echo $this->_vars['_finder']['name']; ?>" id="filter_<?php echo $this->_vars['_finder']['domid']; ?>_ipt" value="<?php echo $this->_vars['_finder']['from']; ?>" filterhidden="true" /> </div> <script>
    var sel_<?php echo $this->_vars['_finder']['domid']; ?>=function(val){
        var _data='view=<?php echo $this->_vars['_finder']['view']; ?>';
        var interzoneQS=$E('.filter_interzone','filter_<?php echo $this->_vars['_finder']['domid']; ?>').toQueryString();
        if(interzoneQS){
           _data+='&'+interzoneQS;
        }
        W.page('index.php?ctl=goods/product&act=showfilter&p[0]='+val,{update:'filter_<?php echo $this->_vars['_finder']['domid']; ?>_body',data:_data,'method':'post'});
    }
    void function(){
          
          /*根据服务器返回QueryString 勾选FilterBody select*/
          
          
          var filterHidden=$('filter_<?php echo $this->_vars['_finder']['domid']; ?>_ipt');
          var filterBody=$('filter_<?php echo $this->_vars['_finder']['domid']; ?>_body');
          
          var filterHiddenVHash=new Hash();
          
 
         filterHidden.value.replace(/([^&]+)\=([^&]+)/ig,function(){
             var arg=arguments;
             //console.info(arg[1],arg[2],filterHiddenVHash.get(arg[1]));
             var arr_v=(filterHiddenVHash.get(arg[1])||[]);
                 arr_v.push(arg[2]);    
             filterHiddenVHash.set(arg[1],arr_v);
         });
    
          
         // console.info(filterHiddenVHash);
          

          
          
          filterHiddenVHash.each(function(sv,snkey){
             
                var el_select=filterBody.getElement('select[name^='+snkey.slice(0,-1)+']');
             
                if(!el_select)return;
              
                $A(el_select.options).each(function(option){
              
                     if(sv.contains(option.value)){
                         
                         option.selected=true;
                      
                     }else{
                        
                        option.selected=false;
                        
                     }
                
                });
          
          });
          
    
    
    
    }();
</script> 