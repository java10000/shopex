window.addEvent('domready', function() {									 
    $('siderIMchatStyle').inject($E('link'), 'before');
	

   $('siderIMchat_hiddenbar').addEvent('mouseover',function(){
				         this.setStyle('display','none');
						 $('siderIMchat_main').setStyle('display','block')
				         });
	
   $('closeSiderIMchat').addEvent('click',function(){
				         $('siderIMchat_main').setStyle('display','none');
						 $('siderIMchat_hiddenbar').setStyle('display','block')
				         })	;			
	

	
siderIMchatsetGoTop();	
});

function siderIMchatsetGoTop(){
	$('siderIMchat').tween('top',$E('body').getScroll().y+100)
  }	
	  
window.addEvent('scroll',function(){	
	  siderIMchatsetGoTop();
	})