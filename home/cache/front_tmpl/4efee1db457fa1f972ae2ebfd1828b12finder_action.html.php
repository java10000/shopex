<a href="index.php?ctl=order/order&act=showAdd" wrapimg="true" type="link" target="_blank" class="btn btn-blank"><span><span><i class="finder-icon"><img src="images/transparent.gif" class="imgbundle icon" style="width:24px;height:24px;background-position:0 -316px;" /></i>添加新订单</span></span></a> <button wrapimg="true" onclick="W.page('index.php?ctl=order/order&act=showOrderFlow')" type="button" class="btn"><span><span><i class="finder-icon"><img src="images/transparent.gif" class="imgbundle icon" style="width:21px;height:24px;background-position:0 -685px;" /></i>启用单据</span></span></button> <button wrapimg="true" onclick="W.page('index.php?ctl=order/order&act=showPrintStyle')" type="button" class="btn"><span><span><i class="finder-icon"><img src="images/transparent.gif" class="imgbundle icon" style="width:24px;height:24px;background-position:0 -620px;" /></i>打印样式</span></span></button> <button submit="index.php?ctl=order/order&act=toPrint" wrapimg="true" target="_blank" type="button" class="btn"><span><span><i class="finder-icon"><img src="images/transparent.gif" class="imgbundle icon" style="width:22px;height:24px;background-position:0 -596px;" /></i>打印选定订单</span></span></button> <script>
function orderPrint(el,type){
    $(el).className='p_prted';
    var id = $(el).getParent('.row').getAttribute('item-id');
    return open('index.php?ctl=order/order&act=printing&p[0]='+type+'&p[1]='+id);
    new Dialog('index.php?ctl=order/order&act=printing&p[0]='+type+'&p[1]='+id,{frameable:true,
      width:window.getSize().x*0.9,
      height:window.getSize().y*0.9
    });
}
</script>