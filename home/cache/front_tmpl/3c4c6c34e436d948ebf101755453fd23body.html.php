<?php if(!function_exists('tpl_modifier_escape')){ require(CORE_DIR.'/include_v5/smartyplugins/modifier.escape.php'); } ?><div id="mce_body_<?php echo $this->_vars['id']; ?>" class='wysiwyg_body' style="height:<?php echo ((isset($this->_vars['height']) && ''!==$this->_vars['height'])?$this->_vars['height']:'300px'); ?>;<?php echo $this->_vars['style']; ?>;"> <?php if( $this->_vars['editor_type']=='textarea' ){ ?> <textarea name="<?php echo $this->_vars['name']; ?>"  style="height:<?php echo ((isset($this->_vars['height']) && ''!==$this->_vars['height'])?$this->_vars['height']:'300px'); ?>;"><?php echo tpl_modifier_escape($this->_vars['value'],'html'); ?></textarea> <?php }else{ ?> <textarea name="<?php echo $this->_vars['name']; ?>" style="display:none" style="height:<?php echo ((isset($this->_vars['height']) && ''!==$this->_vars['height'])?$this->_vars['height']:'300px'); ?>;"><?php echo tpl_modifier_escape($this->_vars['value'],'html'); ?></textarea> <div id='mce_body_<?php echo $this->_vars['id']; ?>_frm_container' style="height:100%;"> </div> <?php } ?> </div> <div align='left' style='font-size:14px;font-weight:bold;'> <script>
 var mce_body_<?php echo $this->_vars['id']; ?>_Height=function(v){
     v=v?$('mce_body_<?php echo $this->_vars['id']; ?>').getStyle('height').toInt()+100:$('mce_body_<?php echo $this->_vars['id']; ?>').getStyle('height').toInt()-100;
   if(v<100)return MessageBox.error("不能再小");
   $('mce_body_<?php echo $this->_vars['id']; ?>').setStyle('height',(v));
   if($E('iframe','mce_body_<?php echo $this->_vars['id']; ?>'))
   $E('iframe','mce_body_<?php echo $this->_vars['id']; ?>').setProperty('height',v);
   if($E('textarea','mce_body_<?php echo $this->_vars['id']; ?>'))
   $E('textarea','mce_body_<?php echo $this->_vars['id']; ?>').setStyle('height',v);
   
 };
</script> <input type='button' class='button-add' onclick="mce_body_<?php echo $this->_vars['id']; ?>_Height(true);this.blur();" title='增大高度' /> <input type='button' class='button-cut' onclick="mce_body_<?php echo $this->_vars['id']; ?>_Height(false);this.blur();" title='减小高度' /> </div>