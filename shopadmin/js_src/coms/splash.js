var Splash = new Abstract({
	go:function(to,timeout,options){
	     $$($('loadMaks'),MODALPANEL).hide();
		 (function(){
		    var splashPanels=$$($('successSplash'),$('noticeSplash'),$('failedSplash'));
			//console.info(splashPanels);
			var sp;
			var hassp=splashPanels.some(function(_sp){
			     if($chk(_sp)){
				    sp=_sp;
					return true;
				 }
			});
			if(sp)
		    W.page(to,JSON.decode(options),sp);
		 }).delay(timeout.toInt());
	}
});