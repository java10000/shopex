var popProductEidtPanel =function(url,e){
    e=new Event(e);
    e.stop();
    e=$(e.target);
    var row=e.getParent('.row');
    var goodsId=row.get('item-id');
    
    var goodsName=row.getElement('div[key=name]');
    goodsName=goodsName?goodsName.getText():'';
    var goodsBn=row.getElement('div[key=bn]');
    goodsBn=goodsBn?goodsBn.getText():'';
    
        url+='&p[0]='+goodsId+'&pop=true';
    new Dialog(url,{singlon:false,onLoad:function(){
                   $E('.nav-bar',this.dialog).remove();
                  },width:window.getSize().x*0.9,
                  height:window.getSize().y*0.8,
                  title:'正在编辑商品:“'+goodsName+'”'
    });
};
