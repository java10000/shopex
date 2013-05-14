window.addEvent('domready',function(){
  /*多规格商品加入购物车按钮*/
  $$('.GoodsSearchWrap .buy-select').each(function(e){
    new QMenu(e,$E('.buy-select-list',e),{lazyload:true});
  });

  /*加入收藏夹*/
  var FAVCOOKIE= new Cookie('S[GFAV]',{duration:365}),
    _toogle = {'star-on':'off','star-off':'on','off':'Del','on':'Add','off_':'erase','on_':'include'};

  var setStar = function(item,state,gid){
    if( 'on' == state ) {
        item.className = 'star-on';
        var itemText =item.children[0];

        while (itemText.nodeType !=3 ) itemText = itemText.firstChild;

        itemText.nodeValue = '已加入收藏';
    } else {
      item.className = 'star-off';
      var itemText =item.children[0];
      while (itemText.nodeType !=3 ){itemText = itemText.firstChild;}
      itemText.nodeValue = '收藏此商品';
    }

    if(!gid) return;

    FAVCOOKIE.write(
      $splat((FAVCOOKIE.read('S[GFAV]')||'').split(','))[_toogle[state+'_']](gid).clean().join(',')
    );

    new Request({
      method:'post',
      evalScripts:true,
      url:'index.php?member-'+gid+'-ajax'+_toogle[state]+'Fav.html'
    }).send();
  };

  var splatFC = $splat((FAVCOOKIE.read('S[GFAV]')||'').split(','));
  $$('li[star]').each(function(item){
    var GID = item.get('star');

    if( splatFC.contains(GID) ) setStar(item,'on');

    item.addEvent('click',function(e){
      e.stop();
      setStar(item,_toogle[item.className],GID);
    });
  });

// 商品对比
var gc = $('goods-compare')||new Element('div').set('html',["<div class='FormWrap goods-compare' id='goods-compare' style='display:none'>",
"<div class='title'><h3>商品对比<span class='close-gc' onclick='gcompare.hide()'>[关闭]</span></h3></div>",
"<form action='"+Shop.url.diff+"' method='post' target='_compare_goods'>",
"<input type='hidden' name='t' value="+$time()+">",
"<ul class='compare-box'>",
 "<li class='division clearfix tpl'>",
    "<div class='span-3'>",
       "<a href='#' gid='{gid}' title='{gname}'>{gname}</a>",
    "</div>",
    "<span class='floatRight lnk' onclick='gcompare.erase(\"{gid}\",this);'>删除</span>",
 "</li>",
"</ul>",
"<div class='compare-bar'>",
  "<input type='button' name='comareing' onclick='gcompare.submit()' class='btn-compare' value='对比'>",
  "<input type='button' class='btn-compare' onclick='gcompare.empty()' value='清空'>",
"</div>",
"</form>",
"</div>"].join('\n')).getFirst().inject(document.body);

var gcBox = gc.getElement('.compare-box');
var tpl = gc.getElement('.compare-box .tpl').get('html');
var itemClass ='division clearfix';
var fixLayout =function(){
     if(gc.style.display=='none')return;
     gc.setStyle('top',window.getScrollTop());
};
window.addEvents({
    'resize':fixLayout,
    'scroll':fixLayout
});
var GCOMPARE_COOKIE = new Cookie('S[GCOMPARE]');

gcompare = {
   init:function(){
     var tmpC = $splat((GCOMPARE_COOKIE.read('S[GCOMPARE]')||'').split('|')).erase("").clean();

     if(tmpC.length){
        tmpC.each(function(i){
            this.add(JSON.decode(i),true);
        }.bind(this));
     }
   },
   hide:function(){
      gc.hide();
   },
   show:function(){
      gc.show();
      fixLayout();
   },
   add:function(dataItem,isInit){
      this.show();
      if(!isInit){
          var tmpC = $splat((GCOMPARE_COOKIE.read('S[GCOMPARE]')||'').split('|')).erase("").clean();
          if(tmpC.length&&tmpC.some(function(i){return (JSON.decode(i)['gtype']+'_')!=(dataItem.gtype+'_')}))return MessageBox.error('只能对比同类的商品.');
          if(tmpC.length>4){return MessageBox.error('最多只能对比5个商品.');}
          GCOMPARE_COOKIE.write(tmpC.include(JSON.encode(dataItem)).join('|'));
      }
      if(gcBox.getElement('a[gid="'+dataItem['gid']+'"]'))return;
      var newItem =new Element('li',{'class':itemClass}).set('html',tpl.substitute(dataItem));
      var glink = newItem.getElement('a');glink.set('href','?product-'+dataItem.gid+'.html')
      gcBox.adopt(newItem);
   },
   erase:function(gid,el){
     var tmpC = $splat((GCOMPARE_COOKIE.read('S[GCOMPARE]')||'').split('|')).erase("").clean();
     var c;
     tmpC.each(function(i,l){
        if(gid==JSON.decode(i)['gid'])c=l;
     });
     tmpC.splice(c,1);
     GCOMPARE_COOKIE.write(tmpC.join('|'));
     $(el).getParent('li').remove();
   },
   empty:function(){
      GCOMPARE_COOKIE.dispose();
      gcBox.getElements('li').each(function(itm){
          if(!itm.hasClass('tpl'))return itm.remove();
      });

   },
   submit:function(){
       if(Browser.Engine.webkit)gcBox.getParent('form').action=Shop.url.diff;
       gcBox.getParent('form').submit();
   }

};
gcompare.init();

});
