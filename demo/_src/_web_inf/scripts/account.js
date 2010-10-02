///////////// 切换帐户弹出层[End] /////////////
<!--{{{-->
    var popup_switchAccount = {
        data:[],
        isOpen:0,
        isLoad:0,
        step:10,
        length:0,
        init:function()
        {
            var layer_sa = '';
            layer_sa += '<div id="layer_switchAccount">';
            layer_sa += '    <div class="lsa_top"></div>';
            layer_sa += '    <div class="lsa_body">';
            layer_sa += '        <ul id="lsa_body_list"></ul>';
            layer_sa += '        <div class="lsa_pages">';
            layer_sa += '            <div class="lsa_pages_left" tip="lpi_left"></div>';
            layer_sa += '            <div class="lsa_pages_item" id="lsa_item0" index="0"></div>';
            layer_sa += '            <div class="lsa_pages_item" id="lsa_item1" index="1" tip="lpi_current"></div>';
            layer_sa += '            <div class="lsa_pages_item" id="lsa_item2" index="2"></div>';
            layer_sa += '            <div class="lsa_pages_item" id="lsa_item3" index="3"></div>';
            layer_sa += '            <div class="lsa_pages_right" tip="lpi_right"></div>';
            layer_sa += '        </div>';
            layer_sa += '    </div>';
            layer_sa += '    <div class="lsa_bottom"></div>';
            layer_sa += '</div>';
            $(layer_sa).appendTo("body");
            alert('Debug(Scott): Hi');
        },
dataDisplay:function()
            {
                //alert("Debug: "+this.data);
                eval("var data = "+this.data+";");
                this.data = data;
                if(data) 
                {
                    //alert('Debug(Scott): execute dataDisplay()');
                    this.open();
                } else {
                    alert("Debug(Scott): Enter method dataDisplay()");
                }
            },
open:function() {
         if(this.isLoad == 0) { // 判断数据是否从服务端加载过
             this.load();
         }
         var obj = $("#layer_switchAccount");obj.show();
         //alert("Debug(Scott): open()");
         if(this.isOpen == 0) { // 判断弹出层是否被定位过
             obj.css('right','10px');
             obj.css('top','2px');
             obj.show();//fadeIn("slow");
             this.isOpen = 1;
         } else {
             obj.hide();//("slow");
             this.isOpen = 0;
         }
         alert("Debug(Scott): Hi2");
     },
display:function(start) {                                                             
         //var obj = $("#layer_switchAccount");obj.show();
            var startPage = start * this.step;                                                
            var endPage = startPage + this.step;                                              
            //alert('Debug(Scott): this.length='+this.length);
            if(this.length < endPage) {
                endPage = this.length;
            }
            //alert('Debug(Scott): endPage='+endPage);
            $("#lsa_body_list").html("");                                                     
            for(var loop=startPage; loop < endPage; loop++) {                                 
                var content = '';                                                             
                content+= '<li class="lsa_item" index="'+loop+'" title="点击切换游戏帐户">';  
                content+= '<div class="lsa_i1"><img align="absmiddle" src="" width="19px" height="19px" /></div>';
                content+= '<div class="lsa_i2">'+this.data[loop].gamename+'</div>';          
                content+= '<div class="lsa_i3">'+this.data[loop].usercount+'</div>';       
                content+= '<div class="lsa_i4" id="lsa_item_'+loop+'" title="删除帐户"></div>';
                content+= '</li>';
                $("#lsa_body_list").append(content);
            }                                                                                 
            $(".lsa_item").hover(                                                             
                    function() {                                                                  
                    $("#lsa_item_"+$(this).attr("index")).show();                             
                    },                                                                            
                    function() {                                                                  
                    $("#lsa_item_"+$(this).attr("index")).hide();                             
                    }                                                                             
                    );
            //alert('Debug(Scott): #103');
        },
load:function() {
         this.isLoad = 1;
         var length = this.data.length;
         this.length = this.data.length;
         this.total = Math.ceil(this.length/this.step);//计算总分页数
         //alert("Debug(Scott): length="+length);
         for(var loop=0;loop<this.length;loop++) {
             //alert("Debug(Scott): iconpath:"+this.data[loop].iconpath+",gamename:"+this.data[loop].gamename+",usercount:"+this.data[loop].usercount);
             var content = '';
             content+= '<li class="lsa_item" index="'+loop+'" title="点击切换游戏帐户">';
             content+= '<div class="lsa_i1"><img align="absmiddle" src="'+this.data[loop].iconpath+'" \/></div>';
             content+= '<div class="lsa_i2">'+this.data[loop].gamename+'</div>';
             content+= '<div class="lsa_i3">'+this.data[loop].usercount+'</div>';
             content+= '<div class="lsa_i4" id="lsa_item_'+loop+'" title="删除帐户"></div>';
             content+= '</li>';
             //js_apply('user_played','{gamelogo:'+data.minilogo+',gamename:'+data.name+',servername:'+name+',gameserver:'+url+'}','false');
             $("#lsa_body_list").append(content);
             //alert("Debug(Scott): lsa_body_list:"+$("#lsa_body_list"));
         }
         $(".lsa_item").hover(
                 function() {
                 $("#lsa_item_"+$(this).attr("index")).show();
                 },
                 function() {
                 $("#lsa_item_"+$(this).attr("index")).hide();
                 }
                 );
         this.current = 0;
         if(this.total > this.step) {
             this.lightNext(true);
         }                                                           
         this.display(0);
         //初始化右按钮
         $(".lsa_pages_left").click(function() {                     
                 popup_switchAccount.pagePre();                          
                 });
         $(".lsa_pages_right").click(function() {
                 popup_switchAccount.pageNext();                         
                 });
         this.assignPage(0);
         /*
         $(".lsa_pages_item").click(function() {                     
                 if( popup_switchAccount.current < 2 ) {                 
                 popup_switchAccount.pageTo($(this).attr('index'));  
                 } else {                                                
                 //alert('click:'+$(this).attr('index')*1);            
                 //alert('current:'+popup_switchAccount.current);      
                 switch($(this).attr('index')*1) {                   
                 case 0:                                         
                 popup_switchAccount.current -= 2;           
                 popup_switchAccount.pageTo(this.current);   
                 break;                                      
                 case 1:
                 popup_switchAccount.current -= 1;           
                 popup_switchAccount.pageTo(this.current);   
                 break;
                 case 2:                                         
                 popup_switchAccount.current -= 2;           
                 popup_switchAccount.pageTo(this.current);   
                 break;                                      
                 case 3:
                 popup_switchAccount.current += 1;           
                 popup_switchAccount.pageTo(this.current);   
                 break;
                 }                                                   
                 }
         });
         //*/
     },
     //分页[Start]                                          
step:10,//每页显示数                                    
     length:0,//数据帐户数量                                
     current:0,//正在查看的页码                             
     total:0,//总页数                                       
     assId:0,//分配分页点的值                               
     assignPage:function(next) {//给分页点赋值              
         this.refreshBtn();                                 
         if( this.current <= 2 ) {                          
             $("#lsa_item"+next).addClass("lpi_current");   
         } else {                                           
             $("#lsa_item2").addClass("lpi_current");       
         }                                                  
         if(this.current > 0 ) {                            
             this.lightPre(true);                           
         } else {                                           
             this.lightPre(false);                          
         }                                                  
         if((this.current+1) == this.total) {               
             this.lightNext(false);                         
         } else {                                           
             this.lightNext(true);                          
         }                                                  
         $("#debug").html("current: "+this.current);        
     },                                                     
refreshBtn:function() {                                
               for(var loop=0;loop<4;loop++) {                    
                   $("#lsa_item"+loop).removeClass("lpi_current");
               }                                                  
           },                                                     
pagePre:function() {                                   
            var pre = this.current-1;                          
            if( pre < 0 ) {                                    
                this.display(0);                               
                this.current = 0;                              
                pre = 0;                                       
            }                                                  
            this.current = pre;                                
            this.assignPage(pre);                              
            this.display(pre);                                 
        },                                                     
lightPre:function(bool) {                              
             if(bool) {                                         
                 $(".lsa_pages_left").addClass('lpi_left');     
             } else {                                           
                 $(".lsa_pages_left").removeClass('lpi_left');  
             }                                                  
         },                                                     
pageNext:function() {                                  
             var next = this.current+1;                         
             if( next >= this.total ) {                         
                 next = this.current;                           
             }                                                  
             this.current = next;                               
             this.assignPage(next);                             
             this.display(next);                                
         },                                                     
lightNext:function(bool) {                             
              if(bool) {                                         
                  $(".lsa_pages_right").addClass('lpi_right');   
              } else {                                           
                  $(".lsa_pages_right").removeClass('lpi_right');
              }                                                  
          },                                                     
pageTo:function(num) {//跳转到指定页                   
           if(num != undefined) {                             
               this.current = num;                            
               this.display(num);                             
               this.assignPage(num);                          
           }                                                  
           //分页[End]                                          
       }
} 
    <!--}}}-->
///////////// 切换帐户弹出层[End] /////////////
window.setTimeout(function() {
    popup_switchAccount.init();
    //alert('Hi (From scott)');
},500);
function switchAccount(data) {
    popup_switchAccount.data = data;
    popup_switchAccount.dataDisplay();
}
