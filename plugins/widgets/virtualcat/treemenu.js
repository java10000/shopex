var TreeMenu=$pick(TreeMenu,new Class({
     options:{
        showStep:3,
        container:'',
        checkboxName:false,
        saveStatus:false,
        nodeClass:{
           clazz:'node',
           handle:'node-handle',
           first:'first-node',
           last:'last-node',
           close:'node-close',
           open:'node-open',
           hasc:'node-hasc',
           nl:'node-line',
           icon:'node-icon',
           name:'node-name',
           loading:'node-loading',
           cbox:'node-child-box'
        },
        remoteURL:'',
        remoteParamKey:'p[0]',
        dataMap:{
          PID:'parent_id',
          NID:'cat_id',
          CNAME:'cat_name',
          HASC:'isleaf'
        }
     },
     initialize:function(options){
       $extend(this.options,options);
       this.container=$(this.options.container);
       if(!this.container)return;
       if(this.options.saveStatus){
         this.nodecookie=this.options.ustatus;
       }
       this.initTree();
     },
     createNode:function(data){
                  var options=this.options;
                  var nc=options.nodeClass;
                  var node_handle=new Element('span',{'class':nc.handle})
                                  .set({'pid':data['PID'],
                                        'nid':data['NID'],
                                        'hasc':data['HASC']
                                        })
                                   .setHTML('&nbsp;');
                  var node_name=new Element('a',{'class':nc.name,
                                                'href':(data['URL']||'#'),
                                                'target':'_blank'
                                                 }).setText(data['CNAME']);
                  var node=new Element('span',{'class':nc.clazz}).adopt([node_handle,node_name]);
                  
                 if(!!data['HASC'].toInt()){
                         var _this=this;
                         node_handle.addClass(nc.close);
                         node_handle.addEvent('click',function(e){
                             var node=this.getParent('.'+nc.clazz);
                             if(this.hasClass(nc.close)){
                               if(!node.getNext()||node.getNext().getTag()!=='div'){
                                 var ncontainer=new Element('div',{'class':nc.cbox}).injectAfter(node);
                                 _this.loadNodes(this.get('nid'),ncontainer);
                                 this.addClass(nc.loading);
                               }else if(node.getNext()&&node.getNext().getTag()=='div'){
                                    node.getNext().show();   
                               }
                               this.removeClass(nc.close);
                               _this.nodeStatus(this.get('nid'),1);                               
                             }else if(node.getNext()&&node.getNext().getTag()=='div'){
                                        node.getNext().hide(); 
                                        this.addClass(nc.close);
                                        _this.nodeStatus(this.get('nid'),0);                                        
                                }
                         });
                     }
                     return node;
     }, 
     initTree:function(){
         this.loadNodes(0);
     },
     loadNodes:function(pid,c){
       var nodes;
       var options=this.options;
       var d=options.dataMap;
       new Request.JSON({
       
       onRequest:function(){
       },
       onSuccess:function(data){
        
         var options=this.options;
         var dmap=$H(options.dataMap);
         if($E('span[nid='+pid+']',this.container))
         $E('span[nid='+pid+']',this.container).removeClass(options.nodeClass.loading);
         
         data.each(function(item,index){
               
               var node_pro={};
               dmap.each(function(v,k){
                 node_pro[k]=item[v];
               });
            var node=this.createNode(node_pro);
                 if(index==0){
                   node.addClass(options.nodeClass.first);
                 }
                 if(data.length==index+1){
                   node.addClass(options.nodeClass.last);
                 }
                 if(node_pro.HASC.toInt()){
                    node.addClass(options.nodeClass.hasc);
                 }
                 this.addNode(node,c);
         
         }.bind(this));

       }.bind(this)}).get(this.options.remoteURL.substitute({param:this.options.remoteParamKey,value:pid})+"&v="+$time());
     },
     addNode:function(node,container){
       if(!container)
       $(node).inject(this.container);
       else
       $(node).inject(container);
       var handle=$E('.'+this.options.nodeClass.handle,node);
       switch(this.options.showStep){
          case 1:
           this.getStatusByHandle(handle);
           break;
          case 2:
              if(!container){
                handle.fireEvent('click')
              }else{
                this.getStatusByHandle(handle);
              }
           break;
          case 3:
              if(!container||(container&&!container.getParent().hasClass(this.options.nodeClass.cbox))){
                handle.fireEvent('click')
              }else{
                this.getStatusByHandle(handle);
              }
           break;
          case 4:
          $E('.'+this.options.nodeClass.handle,node).fireEvent('click');
          break;
       }
     },
     nodeStatus:function(nid,status){
          if(!this.nodecookie)return;
          this.nodecookie.get(this.options.saveStatus,function(nodeck){
              nodeck=JSON.decode(nodeck);
              var ns=nodeck;
              if(!ns){ns={}};
              ns[nid+'s']=status;
              ns=$H(ns).filter(function(v,k){return v}).getClean();
              this.nodecookie.set(this.options.saveStatus,ns);
          }.bind(this));
          
     },
     getStatusByHandle:function(h){
         if(!this.nodecookie)return;
         this.nodecookie.get(this.options.saveStatus,function(value){
             var ns=$H(JSON.decode(value));
             var nid=h.get('nid');
             if(ns.getKeys().contains(nid+'s')){
                 h.fireEvent('click');
             }
         });
     },
     removeNode:function(){
        
     }
}));