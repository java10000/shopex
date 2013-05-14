var validatorMap=new Hash({
  'required':['本项必填',function(element,v){
   return v!=null && v!=''&& v.trim()!='';
  }],
  'number':['请录入数值',function(element,v){
   return v==null || v=='' || !isNaN(v) && !/^\s+$/.test(v);
  }],
  'params1':['参数名中不能含有单引号或双引号',function(element,v){
   return !/(?:\"|\')/.test(v);
  }],
  'params2':['参数组名称中不能含有单引号或双引号',function(element,v){
   return !/(?:\"|\')/.test(v);
  }],
   'msn':['请输入MSN',function(element,v){
	return v==null || v=='' || /\S+@\S+/.test(v);
  }],
   'skype':['请输入Skype',function(element,v){
   return v==null || v=='' || !/\W/.test(v) || /^[a-zA-Z0-9]+$/.test(v);
  }],
  'digits':['请录入整数',function(element,v){
   return v==null || v=='' || !/[^\d]/.test(v);
  }],
  'unsignedint':['请录入正整数',function(element,v){
   return v==null || v=='' || (!/[^\d]/.test(v) && v > 0);
  }],
  'unsigned':['请输入大于等于0的数值',function(element,v){
   return v==null || v=='' || (!isNaN(v) && !/^\s+$/.test(v) && v >= 0);
  }],
  'positive':['请输入大于0的数值',function(element,v){
   return v==null || v=='' || (!isNaN(v) && !/^\s+$/.test(v) && v > 0);
  }],
  'alpha':['请录入英文字母',function(element,v){
   return v==null || v=='' || /^[a-zA-Z]+$/.test(v);
  }],
  'alphaint':['请录入英文字母或者数字',function(element,v){
   return v==null || v=='' || !/\W/.test(v) || /^[a-zA-Z0-9]+$/.test(v);
  }],
   'space':['请确认录入不包含空格',function(element,v){
   return /^\S+$/.test(v);
  }],
  'alphanum':['请录入英文字母、中文及数字',function(element,v){
   return v==null || v=='' || !/\W/.test(v) || /^[\u4e00-\u9fa5a-zA-Z0-9]+$/.test(v);
  }],
  'date':['请录入日期格式yyyy-mm-dd',function(element,v){
   return v==null || v=='' || /^(19|20)[0-9]{2}-([1-9]|0[1-9]|1[012])-([1-9]|0[1-9]|[12][0-9]|3[01])+$/.test(v);
  }],
  'email':['请录入正确的Email地址',function(element,v){
   return v==null || v=='' || /^([a-zA-Z0-9]+[_|\-\_|\.]?)*[a-zA-Z0-9]+@([a-zA-Z0-9]+[_|\_\-|\.]?)*[a-zA-Z0-9]+\.[a-zA-Z]{2,3}$/.test(v);
  }],
  'mobile':["请录入正确的手机号码",function(b,a){
   return a==null||a==""||/^1[3|4|5|8][0-9]\d{8}$/.test(a)
   }],
  'tel':["请录入正确的固话号码",function(b,a){
   return a==null||a==""||/^\d{3,4}-\d{7,8}(?:$|-\d{4,6}$)/.test(a)
   }],
  'text':['',function(element,v){
   return true;
  }],
  'select':['',function(element,v){
   return true;
  }],
  'radio':['',function(element,v){
   return true;
  }],
  'checkbox':['',function(element,v){
   return true;
  }],
  'url':['请录入正确的网址',function(element,v){
   return v==null || v=='' || /^(http|https|ftp):\/\/([A-Z0-9][A-Z0-9_-]*)(:(\d+))?\/?/i.test(v);
  }],
  'area':['请选择完整的地区',function(element,v){
    return  element.getElements('select').every(function(sel){
                  var selValue=sel.getValue();
                  return selValue!=''&&selValue!='_NULL_';
      });
  }],
  'requiredcheckbox':['必须选择一项',function(element){
                 
       var chkbox=element.getParent().getElements('input[type=checkbox]');
       
       chkbox.removeEvents('change').addEvent('change',function(){
       
           if(validator.test(element.form,element)){
                validator.removeCaution(element);           
           }
       });  

       return chkbox.some(function(ck){return ck.checked});       
  
  }]
 });

var validator=new Abstract({
 test:function(form,element){
 
  form=form||$(document.body);
  
  element=$(element);
  
  if(element.get('type')=='hidden'){
     if(element.getParent() && !element.getParent().isDisplay()){
        return true;
     }
  }else{
     
     if(!element.isDisplay()&&element.get('vtype')!='checkForm'){
      return true;
     }      
  
  }
  
  
  this.bindBlurWithValidator(form,element);
  
  var validityList=[],
  required=element.get('required'),
  vtype=element.get('vtype'),
  extendCaution=element.get('caution');
  
  var extra =form?form.get('extra'):false;
  
  if(vtype&&vtype.contains('&&')){
     vtype=vtype.split('&&');
  }
  
  
  if(required){validityList.include('required');}
  
  if(!!vtype){validityList.include(vtype);}
  
  validityList=validityList.flatten();
  
  var vresult=validityList.every(function(t){
  
     var validateResult=true;
     
     var validator=validatorMap.get(t)||window[t];
     
     if(!validator)return validateResult;
     
     var caution=extendCaution||validator[0];
     
     var validateFuc=validator[1];
     
     if($type(validateFuc)=='function'){
         validateResult=validateFuc(element,element.getValue());
     }
     
     if(!validateResult){
        this.showCaution(element,caution);
     }
     return validateResult;
  },this);
  
  
  if(!vresult) return false;
  
  //todo
  if(extra&&extra_validator){
        var vobj = extra_validator[extra];
        if(!vobj)return true;
        var obj = vobj[vtype];  //Get the extra validator of the element
        if(!obj)return true;
             var caution = obj[0];
             var fuc = obj[1];
             
             if(!fuc(form,(element.getVlaue?element.getVlaue():element))){
                   this.showCaution(element,caution);
                   return false;
              }
                          /* msgbox=e.getNext();
                          if(!msgbox || msgbox.getAttribute('name')!='validationMsgBox'){
                           this.msgbox(msg).injectAfter(e);
                          }*/
  }
  
  return true;
 },
 showCaution:function(element,caution){
  var el=$(element).getNext();
  if(el&&el.get('name')&&el.get('name').contains('validationMsgBox'))return;
  if(!caution||caution.trim()=='')return;
  new Element('div', {
       'class': 'x-vali-error',
       'name': 'validationMsgBox',
       'html':caution
      }).injectAfter(element);
 },
 removeCaution:function(element){
   var el=$(element).getNext();
   if(el&&el.get('name')&&el.get('name').contains('validationMsgBox'))return el.remove();
 },
 bindBlurWithValidator:function(form,element){
  var xinput=$$(element,element.getFormElements());
  xinput.removeEvents('blur').addEvent('blur',function(){
    this.removeCaution(element);
    this.test(form,element);
  }.bind(this));
 }
});
