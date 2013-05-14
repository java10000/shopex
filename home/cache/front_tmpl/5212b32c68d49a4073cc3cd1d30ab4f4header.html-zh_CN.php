<?php if(!function_exists('tpl_modifier_replace')){ require(CORE_DIR.'/include_v5/smartyplugins/modifier.replace.php'); } ?><title><?php echo htmlentities($this->_vars['TITLE'],ENT_QUOTES,'UTF-8'); ?></title> <meta http-equiv="Content-Type" content="text/html; charset=UTF-8"> <meta name="keywords" content="<?php echo htmlentities($this->_vars['KEYWORDS'],ENT_QUOTES,'UTF-8'); ?>"> <meta name="description" content="<?php echo htmlentities($this->_vars['DESCRIPTION'],ENT_QUOTES,'UTF-8'); ?>"> <meta name="generator" content="ShopEx <?php echo htmlentities($this->_vars['VERSION'],ENT_QUOTES,'UTF-8'); ?>"> <?php echo tpl_modifier_replace($this->_vars['headers'],"\n",' '); ?> <link rel="icon" href="<?php echo $this->_vars['base_url']; ?>favicon.ico" type="image/x-icon" /> <link rel="bookmark" href="<?php echo $this->_vars['base_url']; ?>favicon.ico" type="image/x-icon" /> <link rel="stylesheet" href="<?php echo $this->_vars['base_url']; ?>statics/style.css" type="text/css" /> <script type="text/javascript">
var Shop = <?php echo $this->_vars['shopDefine']; ?>;
</script> <script type="text/javascript" src="<?php echo $this->_vars['base_url']; ?>statics/script/tools.js"></script> <script type="text/javascript" src="<?php echo $this->_vars['base_url']; ?>statics/script/goodscupcake.js"></script> <script type="text/javascript">

window.addEvent('domready',function(){
			var ReferObj =new Object();
			$extend(ReferObj,{
				serverTime:<?php echo time(); ?>,
				init:function(){				
        			var FIRST_REFER=Cookie.get('S[FIRST_REFER]');
					var NOW_REFER=Cookie.get('S[NOW_REFER]');				
					var nowDate=this.time=this.serverTime*1000;						
					if(!window.location.href.test('#r-')&&!document.referrer||document.referrer.test(document.domain))return;				
					if(window.location.href.test('#r-'))Cookie.dispose('S[N]');	
					if(!FIRST_REFER){
						if(NOW_REFER){
							this.writeCookie('S[FIRST_REFER]',NOW_REFER,this.getTimeOut(JSON.decode(NOW_REFER).DATE));
						}else{						
							this.setRefer('S[FIRST_REFER]',Shop.set.refer_timeout);
						}
					}
					this.setRefer('S[NOW_REFER]',Shop.set.refer_timeout);
					this.createGUID();
				},
				getUid:function(){
					var lf=window.location.href,pos=lf.indexOf('#r-');
					return pos!=-1?lf.substr(pos+4):'';	
				},
				getRefer:function(){
					return document.referrer?document.referrer:'';
				},
				setRefer:function(referName,timeout){
					var uid=this.getUid(),referrer=this.getRefer();
					var data={'ID':uid,'REFER':referrer,'DATE':this.time};
					if('S[NOW_REFER]'==referName){		
						var refer=JSON.decode(Cookie.get('S[FIRST_REFER]'));	
						if(uid!=''&&refer&&refer.ID==''){						
							var fdata={'ID':uid,'REFER':refer.REFER,'DATE':refer.DATE};
							this.writeCookie('S[FIRST_REFER]',JSON.encode(fdata),this.getTimeOut(refer.DATE));							
						}else if(uid==''){					
							$extend(data,{'ID':refer.ID});
						}	
					}	
					Cookie.set(referName,JSON.encode(data),{duration:(Shop.set.refer_timeout||15)});
				},				
				getTimeOut:function(nowDate){			
				    var timeout=nowDate+Shop.set.refer_timeout*24*3600*1000;
					var date=new Date(timeout);
					return date;
		 		},
				writeCookie:function(key,value,timeout){
					document.cookie=key+ '=' + value+'; expires=' + timeout.toGMTString();	
				},
				createGUID:function(){
					var GUID = (function(){
						var S4=function(){
							return (((1+Math.random())*0x10000)|0).toString(16).substring(1);
						};
						return (S4()+S4()+"-"+S4()+"-"+S4()+"-"+S4()+"-"+S4()+S4()+S4()).toUpperCase();
					})();
					Cookie.set('S[N]',GUID,{duration:3650});
				}
			});
			ReferObj.init();
});
    

<?php if( $this->_vars['theme_color_href'] ){ ?>
   window.addEvent('domready',function(){       
       new Element('link',{href:'<?php echo $this->_vars['theme_color_href']; ?>',type:'text/css',rel:'stylesheet'}).injectBottom(document.head);
   });
<?php } ?>
  
</script> <?php echo $this->_vars['scriptplus']; ?> 