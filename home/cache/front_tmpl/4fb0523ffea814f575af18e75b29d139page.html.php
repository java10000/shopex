<?php if(!function_exists('tpl_block_capture')){ require(CORE_DIR.'/include_v5/smartyplugins/block.capture.php'); }  $this->_tag_stack[] = array('tpl_block_capture', array('name' => "header")); tpl_block_capture(array('name' => "header"), null, $this); ob_start();  $_tpl_tpl_vars = $this->_vars; echo $this->_fetch_compile_include("common/header.meta.html", array()); $this->_vars = $_tpl_tpl_vars; unset($_tpl_tpl_vars); ?> <!--JAVASCRIPTS SRC END--> <?php if( $this->_vars['order']['is_has_remote_pdts']=='true' ){ ?> <link rel="stylesheet" href="css/purchase.css" type="text/css" media="screen, projection"/> <?php } ?> <style> #payment td img,#shipping td img{display:none!important;}/*fix IMG SRC bug*/ </style> <?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_content = tpl_block_capture($this->_tag_stack[count($this->_tag_stack) - 1][1], $_block_content, $this); echo $_block_content; array_pop($this->_tag_stack); $_block_content='';  $this->_tag_stack[] = array('tpl_block_capture', array('name' => "title")); tpl_block_capture(array('name' => "title"), null, $this); ob_start(); ?> <h1><img src="images/transparent.gif" class="imgbundle" style="width:16px;height:16px;background-position:0 -2235px;" /><strong>正在编辑:</strong><?php if( $this->_vars['order'] ){ ?>订单号 - <span id='order_id'><?php echo $this->_vars['order']['order_id']; ?></span><?php }else{ ?>新建订单<?php } ?></h1> <ul class="btn-bar"> <li><button onclick="if(confirm('确定退出?'))window.close()" type="button" class="btn btn-quit"><span><span>退出编辑</span></span></button></li> <li><span type="button" onclick='subOrderForm()' id=x_btn_drop-save class="btn btn-save btn-drop-menu drop-active"><span><span>保　存<img dropfor="x_btn_drop-save" id="x_btn_drop-save-handel" dropmenu="drop-save" src="images/transparent.gif" class="drop-handle drop-handle-stand" /></span></span></span><script>new DropMenu("x_btn_drop-save-handel",{});</script></li> </ul> <ul id="drop-save" class="x-drop-menu"> <li onclick='subOrderForm(1)'><span class="info" title="设为默认"><img src="images/transparent.gif" alt="设为默认" class="imgbundle" style="width:16px;height:16px;background-position:0 -1993px;" /></span><span><img src="images/transparent.gif" class="imgbundle" style="width:16px;height:16px;background-position:0 -2009px;" />并返回列表</span></li> </ul> <?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_content = tpl_block_capture($this->_tag_stack[count($this->_tag_stack) - 1][1], $_block_content, $this); echo $_block_content; array_pop($this->_tag_stack); $_block_content=''; ?> <div class="spage-main-box"> <?php if( $this->_vars['order'] ){  if( $this->_vars['order']['is_has_remote_pdts']!=='true' ){  $_tpl_tpl_vars = $this->_vars; echo $this->_fetch_compile_include("order/order_edit.html", array()); $this->_vars = $_tpl_tpl_vars; unset($_tpl_tpl_vars);  }elseif( $this->_vars['order']['is_has_remote_pdts']=='true' ){  $_tpl_tpl_vars = $this->_vars; echo $this->_fetch_compile_include("order/edit_po.html", array()); $this->_vars = $_tpl_tpl_vars; unset($_tpl_tpl_vars);  }  }else{  $_tpl_tpl_vars = $this->_vars; echo $this->_fetch_compile_include("order/order_new.html", array()); $this->_vars = $_tpl_tpl_vars; unset($_tpl_tpl_vars);  } ?> </div> <script>
(function(){
subOrderForm=function(sign){
    var tmp_target,_form;
	if(document.getElements('form')){
        _form=document.getElements('form').getLast();
    }

    if(_form&&_form.target.length==0){
        tmp_target=_form;
        tmp_target.target="{update:'messageBox'}";
    }
    
	if(sign){
		window.MessageBoxOnShow=function(box,success){
			if(MODALPANEL)MODALPANEL.hide();
			if(!success){
				if(tmp_target)tmp_target.target=''
				return
			}
			window.close();
		}
	}else{
		if(tmp_target)tmp_target.target='';
	}			
	_form.fireEvent('submit');		
};
})();
</script> <?php $this->_tag_stack[] = array('tpl_block_capture', array('name' => 'footbar')); tpl_block_capture(array('name' => 'footbar'), null, $this); ob_start(); ?> <table cellspacing="5" cellpadding="0" style="margin:0 auto; height:50px; width:auto;" class="tableAction"> <tbody> <tr valign="middle"> <td> <b class="submitBtn"> <button onclick="subOrderForm(1);"> <span class="iconbutton savetolist">保存并关闭窗口</span> </button> </b> <b class="submitBtn blue"> <button onclick="subOrderForm()"> <span class="iconbutton savebutton">保存当前</span> </button> </b> <b isclosedialogbtn="true" class="submitBtn blue"> <button onclick="if(confirm('确定退出?'))window.close()"><span>关闭</span></button> </b> </td> </tr> </tbody> </table> <?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_content = tpl_block_capture($this->_tag_stack[count($this->_tag_stack) - 1][1], $_block_content, $this); echo $_block_content; array_pop($this->_tag_stack); $_block_content=''; ?> 