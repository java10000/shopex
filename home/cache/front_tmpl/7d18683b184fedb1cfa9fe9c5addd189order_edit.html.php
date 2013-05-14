<?php if(!function_exists('tpl_modifier_cdate')){ require(CORE_DIR.'/include_v5/smartyplugins/modifier.cdate.php'); } if(!function_exists('tpl_function_html_options')){ require(CORE_DIR.'/include_v5/smartyplugins/function.html_options.php'); } if(!function_exists('tpl_input_default')){ require(CORE_DIR.'/include_v5/smartyplugins/input.default.php'); } if(!function_exists('tpl_block_help')){ require(CORE_DIR.'/admin/smartyplugin/block.help.php'); } if(!function_exists('tpl_modifier_region')){ require(CORE_DIR.'/admin/smartyplugin/modifier.region.php'); } if(!function_exists('tpl_input_region')){ require(CORE_DIR.'/include_v5/smartyplugins/input.region.php'); } ?><script>
function delgoods(obj){
 for(obj=obj.parentNode; obj.tagName!='TR'; obj=obj.parentNode);
 obj.parentNode.removeChild(obj);
}

function calculate(){
 var iList = document.getElementsByName('aPrice[]');
}
</script> <form method='post' action='index.php?ctl=order/order&act=toEdit' class="tableform" id="orderEdit" extra="subOrder" target="{update:'messagebox'}"> <h4>商品信息</h4> <div class="division" id="orderItemList"> <?php $_tpl_tpl_vars = $this->_vars; echo $this->_fetch_compile_include("order/edit_items.html", array()); $this->_vars = $_tpl_tpl_vars; unset($_tpl_tpl_vars); ?> </div> <input id="add_order_id" TYPE="hidden" value="<?php echo $this->_vars['order']['order_id']; ?>"> <input id="add_cost_item" TYPE="hidden" value="<?php echo $this->_vars['order']['cost_item']; ?>" > <input id="add_payment" TYPE="hidden" value="<?php echo $this->_vars['order']['payment']; ?>" > <h4>订单信息</h4> <div class="division"> <table width="100%" border="0" cellspacing="0" cellpadding="0"> <tr> <th>订单号：</th> <td><?php echo $this->_vars['order']['order_id']; ?></td> <th>下单日期：</th> <td><?php echo tpl_modifier_cdate($this->_vars['order']['createtime'],'SDATE_STIME'); ?></td> </tr> <tr> <th>商品总金额：</th> <td><input id="iditem_amount" name="cost_item" value="<?php echo $this->_vars['order']['cost_item']; ?>" size=10 disabled="disabled"></td> <th>配送方式：</th> <td> <select name=shipping_id id='ship_id'> <?php echo tpl_function_html_options(array('options' => $this->_vars['order']['selectDelivery'],'selected' => $this->_vars['order']['shipping_id']), $this);?> </select> </td> </tr> <tr> <th>配送费用：</th> <td><?php echo tpl_input_default(array('id' => "idcost_freight",'class' => 'item itemrow','name' => "cost_freight",'value' => $this->_vars['order']['cost_freight'],'type' => "unsigned",'size' => 10), $this);?></td> <th>支付方式：</th> <td><select name="payment"><?php echo tpl_function_html_options(array('options' => $this->_vars['order']['selectPayment'],'selected' => $this->_vars['order']['payment']), $this);?></select>&nbsp;&nbsp; <?php foreach ((array)$this->_vars['order']['extendCon'] as $this->_vars['key'] => $this->_vars['item']){  echo $this->_vars['item']; ?>&nbsp;&nbsp; <?php } ?> </td> </tr> <tr> <th>保价：</th> <td> <?php echo tpl_input_default(array('id' => "idcost_protect",'class' => "item itemrow",'type' => "unsigned",'name' => "cost_protect",'size' => 10,'value' => $this->_vars['order']['cost_protect']), $this);?> 是否要保价 <input id="idis_protect" name="is_protect" type='checkbox' value='true' <?php if( $this->_vars['order']['is_protect'] == 'true' ){ ?>checked="checked"<?php } ?>> </td> <th>商品重量：</th> <td> <input name='weight' class='inputstyle' size='10' value="<?php echo $this->_vars['order']['weight']; ?>" id='goodweight'> </td> </tr> <tr> <th>支付手续费：</th> <td><?php echo tpl_input_default(array('id' => "idcost_payment",'class' => 'item itemrow','name' => cost_payment,'type' => "unsigned",'size' => 10,'value' => $this->_vars['order']['cost_payment']), $this);?></td> <th>发票抬头：</th> <td><?php echo tpl_input_default(array('id' => "idtax_company",'name' => "tax_company",'value' => $this->_vars['order']['tax_company']), $this);?></td> </tr> <tr> <th>税金：</th> <td> <?php echo tpl_input_default(array('id' => "idcost_tax",'class' => 'item itemrow','name' => "cost_tax",'type' => "unsigned",'size' => 10,'value' => $this->_vars['order']['cost_tax']), $this);?> 是否开发票 <input id="idis_tax" name="is_tax" type='checkbox' value='true' <?php if( $this->_vars['order']['is_tax'] == 'true' ){ ?>checked="checked"<?php } ?>> </td> <th>支付币别：</th> <td> <?php if( $this->_vars['order']['order_id'] == '' ){  echo tpl_function_html_options(array('options' => $this->_vars['order']['curList'],'selected' => $this->_vars['order']['currency']), $this); }else{  echo $this->_vars['order']['cur_name'];  if( $this->_vars['order']['cur_rate'] != 1 ){ ?>(<?php echo $this->_vars['order']['cur_rate']; ?>)<?php }  } ?> </td> </tr> <tr> <th>订单人工调价：</th> <td><input id="idpmt_amount" class='item itemrow' name="pmt_amount" value="<?php echo $this->_vars['order']['pmt_amount']; ?>" size=10><?php $this->_tag_stack[] = array('tpl_block_help', array()); tpl_block_help(array(), null, $this); ob_start(); ?>所输入数字大于零降价，小于零涨价<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_content = tpl_block_help($this->_tag_stack[count($this->_tag_stack) - 1][1], $_block_content, $this); echo $_block_content; array_pop($this->_tag_stack); $_block_content=''; ?></td> <th>订单总金额：</th> <td> <input id="idtotal_amount" name=total_amount value="<?php echo $this->_vars['order']['total_amount']; ?>" disabled="disabled"> </td> </tr> </table> </div> <h4>购买人信息</h4> <div class="division"> <?php if( $this->_vars['order']['order_id'] == '' ){ ?> <input TYPE="text" NAME="uname" value='' class=inputstyle size=15> <input TYPE="button" class=inputstyle value="导入会员" onClick="seluser(adminForm.uname.value)"> <input TYPE="button" class=inputstyle value="非会员" onClick="seluser('anonymous')"> <input TYPE="hidden" name="userid" value="{userid}"> <?php } ?> <table width="100%" border="0" cellspacing="0" cellpadding="0"> <tr> <th>姓名：</th> <td><?php echo $this->_vars['order']['member']['name']; ?></td> <th>会员用户名：</th> <td><?php echo $this->_vars['order']['member']['uname']; ?></td> </tr> <tr> <th>联系电话：</th> <td><?php echo $this->_vars['order']['member']['tel']; ?></td> <th>Email地址：</th> <td><?php echo $this->_vars['order']['member']['email']; ?></td> </tr> <tr> <th>地区：</th> <td><?php echo tpl_modifier_region($this->_vars['order']['member']['area']); ?></td> <th>邮政编码：</th> <td><?php echo $this->_vars['order']['member']['zip']; ?></td> </tr> <tr> <th>地址：</th> <td><?php echo $this->_vars['order']['member']['addr']; ?></td> <th></th> <td></td> </tr> </table> </div> <?php if( $this->_vars['order']['is_delivery'] == 'Y' ){ ?> <h4>收货人信息</h4> <div class="division" id="order_edit_receiver"> <table width="100%" border="0" cellspacing="0" cellpadding="0"> <tr> <th>收货人姓名：</th> <td><?php echo tpl_input_default(array('type' => "text",'NAME' => "ship_name",'required' => "true",'value' => $this->_vars['order']['ship_name']), $this);?>*</td> <th>联系手机：</th> <td> <input vtype="order_tel" NAME="ship_mobile" value="<?php echo $this->_vars['order']['ship_mobile']; ?>" class=inputstyle> </td> </tr> <tr> <th>联系电话：</th> <td><?php echo tpl_input_default(array('type' => "order_tel",'NAME' => "ship_tel",'class' => inputstyle,'value' => $this->_vars['order']['ship_tel']), $this);?></td> <th>Email地址：</th> <td><?php if( $this->_vars['order']['member'] ){  echo $this->_vars['order']['ship_email'];  }else{  echo tpl_input_default(array('type' => "email",'NAME' => "ship_email",'value' => $this->_vars['order']['ship_email']), $this); } ?></td> </tr> <tr> <th>送货时间：</th> <td><input type="text" NAME="ship_time" value="<?php echo $this->_vars['order']['ship_time']; ?>" class=inputstyle></td> <th>邮政编码：</th> <td><?php echo tpl_input_default(array('NAME' => "ship_zip",'class' => inputstyle,'value' => $this->_vars['order']['ship_zip']), $this);?></td> </tr> <tr> <th>收货地区：</th> <td><?php echo tpl_input_region(array('name' => "ship_area",'required' => "true",'value' => $this->_vars['order']['ship_area']), $this);?>*</td> <th>收货地址：</th> <td><?php echo tpl_input_default(array('type' => "text",'NAME' => "ship_addr",'required' => "true",'class' => inputstyle,'value' => $this->_vars['order']['ship_addr']), $this);?>*</td> </tr> </table> </div> <?php } ?> </form> <script>
$E('div.mainwrap').addEvent('change',function(eve){
  var ele = eve.target;
  if(-1 == $ES('input.itemrow[name^="aNum"]').indexOf(ele)) return;

  (function(){
    var item_num = ele.getValue().toFloat(),
      item_id = /aNum\[(\d+)\]/.exec(ele.getProperty('name'));
    if ( !item_id ) return;

    item_id = item_id[1];
    var item_price = $E('.itemrow[name="aPrice\['+item_id+'\]"]').getValue().toFloat();

    // 小计
    $E('.itemSub_'+item_id).setText( Number(item_price*item_num).toFixed(3) );

    // 计算运费
    (function(){
      var shipping_area=$ES('input[name=ship_area]').getValue(),shipping_id=$('ship_id').getValue(),
      order_id=$('order_id').get('text'),weight=$('goodweight').getValue(),
      goodprice=$('iditem_amount').getValue();
      var url="index.php?ctl=order/order&act=toedit_dlyprice&shipping_id="+shipping_id+"&ship_area="+shipping_area+"&orderid="+order_id+"&Orderweight="+weight+"&cost_item="+goodprice;
      new Request({
        method:'post', url:url, async:false, noCache:true,
        onComplete:function(resp){
          $('idcost_freight').value = resp;
        }
      }).send();
    })();

    countF();
  })();
});

// 重新计算订单价格
function countF(){
  // 商品总额
  $('iditem_amount').value=(function(){
    var count=0;
    $ES(".itemCount").each(function(item){
      count += item.getText().toFloat();
    });
    return count.round(3);
  })();

  // 订单总额
  $('idtotal_amount').value=(function(){
    // 保价 和 税费
    var cost_protect = $('idis_protect').checked ? $('idcost_protect').value.toFloat() : 0;
    var cost_tax = $('idis_tax').checked ? Number($('idcost_tax').value) : 0;
    // 订单人工调价
    var pmt_amount = Number($('idpmt_amount').value);

    var count = Number($('iditem_amount').value) + cost_protect + 
      $('idcost_freight').value.toFloat() + Number($('idcost_payment').value) +
      cost_tax - pmt_amount;

    return count.round(3);
  })();
}

  
//手工调价,支付手续费
$ES('#idcost_payment,#idpmt_amount,#idcost_protect,#idcost_tax,#idcost_freight').addEvent('change',function(){
  countF();
});

// 是否保价
$('idis_protect').addEvent('click',function(e){
  $('idcost_protect').disabled = !this.checked;
  countF();
});

// 是否发票
$('idis_tax').addEvent('click',function(e){
  $('idcost_tax').disabled = !this.checked;
  countF();
});

$('idcost_tax').disabled = !$('idis_tax').checked;
$('idcost_protect').disabled = !$('idis_protect').checked;

var extra_validator={};
if(!extra_validator['subOrder']){
  extra_validator['subOrder'] ={
    'order_tel':['请至少输入联系电话和联系手机中的一项',function(f,i){
        var tel = $E('#order_edit_receiver input[name=ship_tel]').getProperty('value');
        var mob = $E('#order_edit_receiver input[name=ship_mobile]').getProperty('value');
        console.log(mob);
        return (tel.trim() != '') || (mob.trim() != '');
      }]
  };
}
</script> 