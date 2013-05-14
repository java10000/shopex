<?php if(!function_exists('tpl_input_html')){ require(CORE_DIR.'/include_v5/smartyplugins/input.html.php'); } ?><h3>详细介绍</h3> <div class="division"> <button class="lnk" style="background:none;border:none; width:180px;line-height:20px" type="button" >将商品相册图片插入编辑区</button> <?php echo tpl_input_html(array('name' => "goods[intro]",'class' => "",'value' => $this->_vars['goods']['intro'],'includeBase' => true), $this);?> </div> <script >
$E('#gEditor-Body .lnk[type=button]').addEvent('click',function(e){
	if(!$ES('#all-pics .gpic-box').length){MessageBox.error("商品相册没有图片!!!");return;}
	var mce=<?php echo $this->_vars['var']; ?>;
	if(!mce.inc){MessageBox.error("请先将光标移到编辑框内,激活编辑框!!!");return;}
	if(mce.inc.editType=='textarea'){MessageBox.error("请在可视化编辑模式下操作此功能!!!");return;}
	var path='<?php echo basename(constant('HOME_DIR')); ?>/upload/';	
	var html='';		
	$ES('#all-pics .gpic-box input[type=hidden]').each(function(el){		
		var url=el.get('bigPic')?el.get('bigPic').split('|')[0]:path+el.get('soucre');
		var img=new Element('img',{src:url}).setStyle('marginTop','30px');		
		img.removeProperties('width','height');
		var imgBox=new Element('div').adopt(img).get('html');
		html+='<center>'+imgBox+'</center>';
	});
	mce.exec('insertHTML',html);	
});
</script>