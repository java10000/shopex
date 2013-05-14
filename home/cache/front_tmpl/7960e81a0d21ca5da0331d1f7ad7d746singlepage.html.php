<?php if(!function_exists('tpl_block_capture')){ require(CORE_DIR.'/include_v5/smartyplugins/block.capture.php'); } ?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"> <html xmlns="http://www.w3.org/1999/xhtml"> <head> <meta http-equiv="Content-Type" content="text/html; charset=utf-8"> <meta http-equiv="X-UA-Compatible" content="IE=7" > <title><?php echo $this->_vars['title']; ?></title> <link rel="shortcut icon" href="../favicon.gif" type="image/gif" /> <link rel="stylesheet" href="css/adminsinglepage.css" type="text/css" media="screen, projection"/> <?php $this->_tag_stack[] = array('tpl_block_capture', array('name' => "body")); tpl_block_capture(array('name' => "body"), null, $this); ob_start();  $_tpl_tpl_vars = $this->_vars; echo $this->_fetch_compile_include($this->_vars['_PAGE_'], array()); $this->_vars = $_tpl_tpl_vars; unset($_tpl_tpl_vars);  $_block_content = ob_get_contents(); ob_end_clean(); $_block_content = tpl_block_capture($this->_tag_stack[count($this->_tag_stack) - 1][1], $_block_content, $this); echo $_block_content; array_pop($this->_tag_stack); $_block_content='';  echo $this->_env_vars['capture']['header']; ?> <script hold="true">
    var SHOPADMINDIR='<?php echo $this->_vars['shopadmin_dir']; ?>';
    var SHOPBASE='<?php echo $this->_vars['shop_base']; ?>';
    var DEBUG_JS=false;
    var MODALPANEL={};
    var Setting = {};
    var Menus = "json from=$mlist";
    var sess_id = '<?php echo $this->_vars['session_id']; ?>';
    <?php echo $this->_vars['script'];  if( $this->_vars['statusId'] ){ ?>
    window.statusId = <?php echo $this->_vars['statusId']; ?>;
    <?php } ?>
</script> <script type="text/javascript" src="js/fixie6.js"></script> </head> <body class="single-page <?php if( $this->_env_vars['capture']['sidebar'] ){ ?>single-page-col2<?php } ?>"> <?php if( $this->_env_vars['capture']['title'] ){ ?> <div id="single-page-tbar"> <?php echo $this->_env_vars['capture']['title']; ?> </div> <?php }  if( $this->_env_vars['capture']['sidebar'] ){ ?> <div class="spage-sidebar-wrap"> <div id="single-page-sidebar"> <?php echo $this->_env_vars['capture']['sidebar']; ?> </div> </div> <?php } ?> <div id="main" class="mainwrap"> <?php echo $this->_env_vars['capture']['body']; ?> </div> <div id='footBar' class='footBar'> <?php echo $this->_env_vars['capture']['footbar']; ?> </div> <div class="clear"></div> <div id='tabernacle'></div> <div id='loadMask' class='loading' style='z-index:65535;display:none;'> <span class='loading_msg'>正在加载...</span> </div> <div style="display:none;"> <iframe name="download" src="about:blank"></iframe> <div id='elTempBox'></div> <div id="dialogProtoType" > <div class="dialog-box"> <div class="dialog-head"> <div class="dialog-title"></div> <img src="images/transparent.gif" class="imgbundle dialog-btn-close" style="width:17px;height:17px;background-position:0 -197px;" /> </div> <div class="dialog-content-head"></div> <div class="dialog-content-body" container='true'>正在加载...</div> <div class="dialog-content-foot"></div> <img class="dialog-btn-resize" src='images/resize-btn.gif'/> </div> </div> <div id="frameDialogProtoType"> <div class="dialog-box"> <div class="dialog-head"> <div class="dialog-title"></div> <img src="images/transparent.gif" class="imgbundle dialog-btn-close" style="width:17px;height:17px;background-position:0 -197px;" /> </div> <div class="dialog-content-head"></div> <iframe class="dialog-content-body dialog-frame-body" container='true' src='about:blank'></iframe> <div class="dialog-content-foot"></div> <img class="dialog-btn-resize" src='images/resize-btn.gif'/> </div> </div> </div> <script type="text/javascript" hold="true"> 
/*siglepage Js tools plus*/
void function(){
    var b = new Hash();
    try {
        b.empty.periodical(60 * 1000, b)
    } 
    catch (c) {
    }
    var a = new Class({
        Extends: Ajax,
        initialize: function(e, d){
            b.set(e, "init");
            this.addEvent("onCancel", function(){
                b.set(e, "cancel")
            });
            this.parent(e, d)
        },
        send: function(g, f){
            b.set(this.url, "request");
            if (!this.check(arguments.callee, f)) {
                return this
            }
            this.running = true;
            var h = $type(f);
            if (h == "string" || h == "element") {
                f = {
                    data: f
                }
            }
            var d = this.options;
            f = $extend({
                data: d.data,
                url: g,
                method: d.method
            }, f);
            var j = f.data, g = f.url, l = f.method;
           
            switch ($type(j)) {
                case "element":
                    j = $(j).toQueryString();
                    break;
                case "object":
                case "hash":
                    j = Hash.toQueryString(j)
            }
            if (this.options.urlEncoded && "post" == l.toLowerCase()) {
                var i = (this.options.encoding) ? "; charset=" + this.options.encoding : "";
                this.headers.set("Content-type", "application/x-www-form-urlencoded" + i)
            }
            if (j && "get" == l.toLowerCase()) {
                g = g + (g.contains("?") ? "&" : "?") + j;
                j = null
            }
            g += "&_ajax=true&_singlepage=true";        /*2009.10.23 新窗口不返回菜单栏*/
            this.xhr.open(l.toUpperCase(), g, this.options.async);
            this.xhr.onreadystatechange = this.onStateChange.bind(this);
            this.headers.each(function(m, e){
                if (!$try(function(){
                    this.xhr.setRequestHeader(e, m);
                    return true
                }.bind(this))) {
                    this.fireEvent("exception", [e, m])
                }
            }, this);
            this.loadMask("onrequest", $(this.options.update));
            if (window.ie && this.options.update && $(this.options.update)) {
                try {
                    $ES("iframe", this.options.update).each(function(m){
                        m.remove()
                    })
                } 
                catch (k) {
                }
            }
            this.fireEvent("request");
            this.xhr.send(j);
            if (!this.options.async) {
                this.onStateChange()
            }
            return this
        },
        onFailure: function(){
            if ($chk($("loadMask"))) {
                this.loadMask("onfailure")
            }
            if (this.options.elMap) {
                switch (this.transport.status) {
                    case 404:
                        MessageBox.error("页面未找到.");
                        break;
                    case 401:
                        new Dialog("401.html", {
                            ajaksable: false,
                            modal: true,
                            width: 300,
                            height: 150,
                            resizeable: false
                        });
                        break;
                    case 403:
                        MessageBox.error("您无此操作的权限.");
                        break
                }
            }
            b.set(this.url, "failure");
            this.fireEvent("onFailure", this.transport);
        },
        onException: function(e, d){
            if ($chk($("loadMask"))) {
                this.loadMask("onException")
            }
            MessageBox.error("出现错误:" + e + ":" + d)
        },
        success: function(j, g){
            try {
                var f = this.options;
                var d = this.response;
                var h = $(f.update);
                d.html = j.stripScripts(function(e){
                    d.javascript = e
                });
                d.html = this.processHTML(d.html);
                if (!h) {
                    MessageBox.show(d.html);
                    this.loadMask("oncomplete");
                    return this.onSuccess(d.html)
                }
                if (f.elMap) {
                    $each(f.elMap, function(k, e){
                        if (e == ".sideContent" || !k) {
                            return
                        }
                        $(k).empty()
                    });
                    d.html = d.html.replace(/<\!-{5}(.*?)-{5}([\s\S]*?)-{5}(.*?)-{5}>/g, function(){
                        if (f.elMap[arguments[1]]) {
                            if (arguments[1] == ".sideContent" && arguments[2]) {
                                f.elMap[arguments[1]].adopt(new Element("div").setHTML(arguments[2]).getFirst().setStyle("display", "none"))
                            }
                            else {
                                f.elMap[arguments[1]].set("html", arguments[2])
                            }
                        }
                        return ""
                    });
                    updateSize()
                }
                h.empty().setHTML(d.html);
                if (f.evalScripts) {
                    $exec(d.javascript)
                }
                makeAjaksLink(h);
                this.onSuccess(d.html, null, f, null, d.javascript);
                this.loadMask("oncomplete")
            } 
            catch (i) {
                MessageBox.error("目标页面有错误!" + i)
            }
        },
        loadMask: function(d, f){
            f = $(f);
            var e = f || window;
            switch (d) {
                case "onrequest":
                    return $("loadMask").amongTo(window).show();
                default:
                    $("loadMask").hide();
                    if ($(this.options.update)) {
                        b.set(this.url, "success")
                    }
            }
        }
    });
    ShopEx_AdminXHR = new Class({
        options: {
            evalScripts: true,
            autoCancel: true,
            method: "get",
            elMap: false,
            data: null,
            link: "cancel"
        },
        page: function(){
            var h = Array.flatten(arguments).link({
                url: String.type,
                options: Object.type,
                sponsor: Element.type
            });
            if (["request", "init"].indexOf(b.get(e)) > -1) {
                return $("loadMask").amongTo(window).show()
            }
            var f = $merge(this.options, h.options);
            var e = h.url;
            var g = h.sponsor;
            if (f.autoCancel && this.curAjax) {
                this.curAjax.cancel()
            }
            if ($type(f.data) == "element") {
                if (!$(f.data).bindValidator("x-input")) {
                    return $("loadMask").hide()
                }
            }
            /*else {
                if (!!g) {
                    var d = $(g).getContainer();
                    if (d) {
                        if (!d.bindValidator("x-input")) {
                            return $("loadMask").hide()
                        }
                    }
                }
            }*/
            f.update = f.update || $($chk(g) ? $(g).getContainer() : false) || "main";
            if (f.update == "main" || ($type(f.update) == "element" && f.update.id == "main")) {
                if (!f.elMap) {
                    f.elMap = {
                        ".mainHead": $("headBar"),
                        ".mainFoot": $("footBar")
                    }
                }
            }
            $extend(f.elMap, {
                ".sideContent": $("sidecontent")
            });
            this.curAjax = new a(e, f);
            this.curAjax.request()
        }
    });
    void function(){
        var e = function(f){
            if (f.getTag() != "a") {
                f = f.getParent("a")
            }
            if (f&&(!$chk($(f).target) || f.target.test("{", "i")) && !f.href.match(/^javascript.+/i) && !f.onclick) {
                return f
            }
            return false
        };
        var d = function(f){
            if (f.getTag() != "form") {
                return false
            }
            return $chk($(f).getProperty("action")) && (!$chk(f.target) || f.target.test("{", "i")) && !f.onsubmit
        };
        $(document.body).addEvents({
            click: function(h){
                var g = e($(h.target));
                if (!g) {
                    return
                }
                h.stop();
                var i;
                try {
                    i = Json.evaluate(g.target)
                } 
                catch (h) {
                    if ($chk(g.href)) {
                        return W.page(g.href, g)
                    }
                    return false
                }
                if ("fm" in i && $chk(i.fm)) {
                    var j = false;
                    if (i.update == "main") {
                        var j = new Element("div", {
                            "class": "navBar"
                        }).inject($("headBar").empty());
                        $("footBar").empty()
                    }
                    var f = new Element("iframe", {
                        name: i.fm,
                        styles: {
                            width: "100%",
                            height: "98%",
                            border: "none",
                            "overflow-y": "scroll"
                        }
                    }).inject($(i.update).empty());
                    f.setProperty("src", g.href)
                }
                else {
                    W.page(g.href, i, g)
                }
            },
            submit: function(j){
			
                var g = $(j.target);
                console.log(g.getProperty("target"));
                if (d(g)) {
                    j.stop();
                    new Element("input", {
                        type: "hidden",
                        value: 1,
                        name: "__"
                    }).inject(g);
                    if (!g.bindValidator("x-input")) {
                        return $("loadMask").hide()
                    }
                    $ES("textarea[ishtml=true]", g).getValue();
                    var i = g.getProperty("action");
                    var h = h || {};
                    if (g.target.test("{", "i")) {
                        try {
                            h = Json.evaluate(g.target)
                        } 
                        catch (j) {
                        }
                    }
					if(f_target_obj=g.retrieve('target')){                  
                  		$extend(h,f_target_obj);              
                    }
					
                    if (j.requestOptions) {
                        $extend(h, j.requestOptions)
                    }
                    var f = $ES('input[type="file"]', g);
                    if (f.length > 0 && f.some(function(k){
                        return k.value
                    })) {
                        MODALPANEL.show();
                        $("loadMask").amongTo(window).show();
                        if (!$("upload_iframe")) {
                            new Element("iframe", {
                                id: "upload_iframe",
                                name: "upload_iframe",
                                styles: {
                                    display: "none"
                                }
                            }).inject(document.body)
                        }
						//if(g.target)g.store('reset_target',g.target);
                        g.setProperty("target", "upload_iframe");
                        g.set({
                            enctype: "multipart/form-data",
                            encoding: "multipart/form-data"
                        });
                        g.setProperty("action", g.getProperty("action") + "&_upload=true");
                        window.upload_rs_el = h && h.ure ? h.ure : $(j.target).getContainer();
                        
                        g.submit();
						//g.retrieve('reset_target')?g.target=g.retrieve('reset_target'):g.target='';
						return g;//.removeProperties('enctype', 'encoding');
					}
                    if (h) {
                        g.removeProperties('enctype', 'encoding');
                        if ("fm" in h) {
                            g.setProperty("target", ftarget.fm);
                            return g.submit()
                        }
                        return W.page(i, $extend(h, {
                            method: g.getProperty("method"),
                            data: g.toQueryString()
                        }), g)
                    }
                    W.page(i, {
                        method: g.getProperty("method"),
                        data: g.toQueryString()
                    }, g)
                }
            }
        })
    }();
    makeAjaksLink = function(d){
        $(d|| document).getElements("form").addEvent("submit", function(g){
            var f = g ? (g.stop ? g.stop : $empty) : $empty;
			
            document.body.fireEvent("submit", $extend((g ||{}), {
                target: this,
                stop: f
            }));
          
			
        })
		return arguments.callee;
    }();
}();
var W;
var getW = function(){
    return (W && $type(W) == "object") ? W : new ShopEx_AdminXHR()
};
var updateSize = $empty;
var mainResize = function(){
     
    var sptH = $('single-page-tbar')?$('single-page-tbar').getSize().y:0;
    var spsW = $E('.spage-sidebar-wrap')?150:0;
    
    var ftbH = $('footBar')?$('footBar').getSize().y:0;
    
    $("main").setStyle("height", (window.getSize().y - (sptH+ftbH)));
    $("main").setStyle("width", (window.getSize().x - spsW));
};

window.addEvent("resize", mainResize);
window.addEvent("domready", function(){
    W = getW();
    mainResize();
    setInterval("if($('elTempBox')&&$('elTempBox').getElements('*').length)$('elTempBox').empty()", 2 * 60000);
    MODALPANEL = (function(){
        var b = $pick($("MODALPANEL"), new Element("div", {
            id: "MODALPANEL"
        }).inject(document.body)).setStyle("display", "none");
        var a = {
            position: "absolute",
            background: "#333333",
            width: window.getSize().size.x,
            height: window.getSize().size.y,
            top: 0,
            left: 0,
            zIndex: 65500,
            opacity: 0.4
        };
        b.setStyles(a);
        b.addEvent("onshow", function(c){
            c.setStyles({
                width: window.getSize().size.x,
                height: window.getSize().size.y
            })
        });
        return b
    })();
	window.Xtip=new Tips($('shop_title_block'),{className:'x-tip'});
    $exec($("__eval_scripts__").get("html"));
    
    
    
    
    window.addEvent('unload',function(){
        if(!window.opener||!window.opener.finderGroup)return;
        for(var f in window.opener.finderGroup){
            if(!!window.opener.finderGroup[f].isVisibile()){
                window.opener.finderGroup[f].refresh();
            }else{
                delete (window.opener.finderGroup[f]); 
            } 
        }
    });
    
    
    
});







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
		    location.reload();
		 }).delay(timeout.toInt());
	}
});
/*siglepage Js */
//$$('.mainFoot').setStyle('display', 'none');


</script> 