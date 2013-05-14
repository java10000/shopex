<?php if(!function_exists('tpl_input_date')){ require(CORE_DIR.'/include_v5/smartyplugins/input.date.php'); } ?> <!--<div class="actionItems"> <table cellspacing="0" cellpadding="0" border="0" width="100%"> <tbody> <tr> <td class="functop"><h3>搜索</h3></td> </tr> <tr> <td class="func"><table> <tr> <td valign="top"><label for="start_date">起始日期</label> <?php echo tpl_input_date(array('name' => "start_date",'id' => "start_date",'class' => "cal _x_ipt",'style' => "width:100px",'value' => "$this->_vars['sfinddate']"), $this);?> </td> <td valign="top"><label for="end_date">结束日期</label> <?php echo tpl_input_date(array('name' => "end_date",'id' => "end_date",'class' => "cal _x_ipt",'style' => "width:100px",'readonly' => true,'value' => "$this->_vars['efinddate']"), $this);?> </td> <td><button style="font-size:12px; height:26px;padding:0 2px;marign:0" class="sysiconBtnNoIcon" onclick="$('finder-filterInput-<?php echo $this->_vars['_finder']['_name']; ?>').name='sdtime';$('finder-filterInput-<?php echo $this->_vars['_finder']['_name']; ?>').value=$('start_date').value+'/'+$('end_date').value;<?php echo $this->_vars['_finder']['var']; ?>.refresh.call(<?php echo $this->_vars['_finder']['var']; ?>);return false" >搜索</button></td> </tr> </table></td> </tr> </tbody> </table> </div>--> <button type="button" wrapimg="true" dropmenu_opts="<?php echo "relative:{$this->_vars['_finder']['var']}.action";?>" dropmenu="finder-date" id=x_btn_finder-date class="btn"><span><span><i class="finder-icon"><img src="images/transparent.gif" class="imgbundle icon" style="width:22px;height:24px;background-position:0 -388px;" /></i>日期<img src="images/transparent.gif" class="drop-handle" /></span></span></button><script>new DropMenu("x_btn_finder-date",{<?php echo "relative:{$this->_vars['_finder']['var']}.action";?>});</script> <div id="finder-date" class="x-drop-menu"> <div class="group"> <label for="start_date">起始日期</label><br /> <?php echo tpl_input_date(array('name' => "start_date",'id' => "start_date",'class' => "cal _x_ipt",'style' => "width:100px",'value' => $this->_vars['sfinddate']), $this);?><br /> <label for="end_date">结束日期</label><br /> <?php echo tpl_input_date(array('name' => "end_date",'id' => "end_date",'class' => "cal _x_ipt",'style' => "width:100px",'readonly' => true,'value' => $this->_vars['efinddate']), $this);?><br /> <button style="margin-top:5px;" onclick="submit_<?php echo $this->_vars['_finder']['_name']; ?>()" >搜索</button> </div> </div> <script>
/*
$('finder-filter-<?php echo $this->_vars['_finder']['_name']; ?>').style.display='none';
$('finder-mode-<?php echo $this->_vars['_finder']['_name']; ?>').style.display='none';
<?php echo $this->_vars['_finder']['var']; ?>._initList = <?php echo $this->_vars['_finder']['var']; ?>.initList;
<?php echo $this->_vars['_finder']['var']; ?>.initList = (function(){
 this._initList();
    $('finder-filter-<?php echo $this->_vars['_finder']['_name']; ?>').style.display='none';
 $('finder-mode-<?php echo $this->_vars['_finder']['_name']; ?>').style.display='none'; 
 this.selectMode.className='selectModeBar x-all';
 $ES('strong',this.selectMode).setStyle('display','none');
}).bind(<?php echo $this->_vars['_finder']['var']; ?>);
$ES('strong',<?php echo $this->_vars['_finder']['var']; ?>.selectMode).setStyle('display','none');
<?php echo $this->_vars['_finder']['var']; ?>.selectAll();
*/

var submit_<?php echo $this->_vars['_finder']['_name']; ?>=function(){

    <?php echo $this->_vars['_finder']['var']; ?>.filter.reset()
     .push({label:'日期范围:'+$('start_date').value+' --- '+$('end_date').value,name:'sdtime',value:$('start_date').value+'/'+$('end_date').value});

    //<?php echo $this->_vars['_finder']['var']; ?>.refresh();
};
$('finder-date').addEvent('click', function(e){if(!$(e.target).match('button')) e.stopPropagation()});

</script> 