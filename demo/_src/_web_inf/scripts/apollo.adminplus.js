$.ns('Apollo.admin.sysEntity',{
    pdt:
    {
tipOption: {closeText: '<img src="/images/admin/close.png" title="关闭" />',closePosition:'title',activation:'click',positionBy:"mouse",ajaxProcess:Apollo.admin.ajaxProcess,cluetipClass:'jtip',sticky: true, arrows: true,width:450,dropShadow:false},
        combineId:function(id)
        {
            return "entity_pdt_"+id;
        },
        combineHtml:function(id,name)
        {
            innerHtml = "<a href=\"index.html?do=pdt_get&pdtid="+id+"\" rel=\"index.html?do=pdt_get&pdtid="+id+"\">"+name+"</a>";
            return innerHtml;
        },
        setup:function(obj)
        {
            var argstr=$(obj).html();
            var argarr=argstr.split(",");
            var id = argarr[0];
            var name  = argarr[1];
            $(obj).attr("id",Apollo.admin.sysEntity.pdt.combineId(id));
            $(obj).attr("title","产品");
            $(obj).html(Apollo.admin.sysEntity.pdt.combineHtml(id,name));
            $(obj).find("a").cluetip(Apollo.admin.sysEntity.pdt.tipOption);
        }
    }
});
$.ns('Apollo.admin.dataCache',{
        opObj:{
            title:{},
            combineId:{},
            combineHtml:{},
            tipOption:{}
            }
        });

$.ns('Apollo.admin.sysMiniOp',{
    formnameArr :["chglfstatus","setvisibleform","upattrform","uptagsform"],
    miniopArr :["lifestatus","visible","upattr","tags"],
    common:
    {
        setup:function(obj,miniop)
        {
            if(!Apollo.admin.dataCache.opObj.title[miniop])
                Apollo.admin.dataCache.opObj.title[miniop] = eval("Apollo.admin.sysMiniOp."+miniop+".title");
            miniopTitle   =  Apollo.admin.dataCache.opObj.title[miniop];
            
            if(!Apollo.admin.dataCache.opObj.combineId[miniop])
                Apollo.admin.dataCache.opObj.combineId[miniop] = eval("Apollo.admin.sysMiniOp."+miniop+".combineId");
            miniopCombineId  =  Apollo.admin.dataCache.opObj.combineId[miniop]
                
            if(!Apollo.admin.dataCache.opObj.combineHtml[miniop])
                Apollo.admin.dataCache.opObj.combineHtml[miniop] = eval("Apollo.admin.sysMiniOp."+miniop+".combineHtml");
            miniopCombineHtml = Apollo.admin.dataCache.opObj.combineHtml[miniop];
           
            if(!Apollo.admin.dataCache.opObj.tipOption[miniop])
                Apollo.admin.dataCache.opObj.tipOption[miniop] = eval("Apollo.admin.sysMiniOp."+miniop+".tipOption");
            miniopTipOption = Apollo.admin.dataCache.opObj.tipOption[miniop] ;
            
            var argstr=$(obj).html();
            var argarr=argstr.split(",");
            var ecls = argarr[0];
            var eid  = argarr[1];
            var value = argarr[2];
            var name  = argarr[3];
            $(obj).attr("id",miniopCombineId(ecls,eid));
            $(obj).attr("title",miniopTitle);
            $(obj).html(miniopCombineHtml(eid,ecls,name,value));
            $(obj).find("a").cluetip(miniopTipOption);
        },
        callback:function(miniop,responseText, statusText)    
        {
            if(!Apollo.admin.dataCache.opObj.combineId[miniop])
                Apollo.admin.dataCache.opObj.combineId[miniop] = eval("Apollo.admin.sysMiniOp."+miniop+".combineId");
            miniopCombineId  =  Apollo.admin.dataCache.opObj.combineId[miniop]
                
            if(!Apollo.admin.dataCache.opObj.combineHtml[miniop])
                Apollo.admin.dataCache.opObj.combineHtml[miniop] = eval("Apollo.admin.sysMiniOp."+miniop+".combineHtml");
            miniopCombineHtml = Apollo.admin.dataCache.opObj.combineHtml[miniop];
           
            if(!Apollo.admin.dataCache.opObj.tipOption[miniop])
                Apollo.admin.dataCache.opObj.tipOption[miniop] = eval("Apollo.admin.sysMiniOp."+miniop+".tipOption");
            miniopTipOption = Apollo.admin.dataCache.opObj.tipOption[miniop] ;
            $("#cluetip-close").click();
            resarr = responseText.split(",");
            if(resarr[0]=="success")
            {
                var ecls = resarr[1];
                var eid  = resarr[2];
                var value = resarr[3];
                var name  = resarr[4];
                var obj = "#"+miniopCombineId(ecls,eid);
                $(obj).fadeOut(600,function()
                        {
                        $(this).html(miniopCombineHtml(eid,ecls,name,value));
                        $(this).find("a").cluetip(miniopTipOption);
                        }
                        );
                $(obj).fadeIn(600);
            }
        }
    },
    visible:
    {
        title:"可见性",
        tipOption: {closePosition:'top',activation:'hover',positionBy:"mouse",ajaxProcess:Apollo.admin.ajaxProcess,cluetipClass:'jtip',sticky: true, arrows: true,width:200,dropShadow:false,cluezIndex:100010},
        combineId:function(ecls,eid)
        {
            return "vis_"+ecls+"_"+eid;
        },
        combineHtml:function(eid,ecls,name,value)
        {
            innerHtml = "<font class='visible-"+value+"'>"+name+"</font>";
            innerHtml+= " <a href='index.html?do=setvisible&ecls="+ecls+"&eid="+eid+"' rel='index.html?do=setvisible&ecls="+ecls+"&eid="+eid+"'>修改</a>";
            return innerHtml;
        },
        setup:function(obj)
        {
            Apollo.admin.sysMiniOp.common.setup(obj,"visible");
        },
        callback:function(responseText, statusText)    
        {
            Apollo.admin.sysMiniOp.common.callback("visible",responseText,statusText);
        }
    },    
    lifestatus:
    {
        title:"状态",
        tipOption: {closePosition:'top',activation:'hover',positionBy:"mouse",ajaxProcess:Apollo.admin.ajaxProcess,cluetipClass:'jtip',sticky: true, arrows: true,width:200,dropShadow:false,cluezIndex:100010},
        combineId:function(ecls,eid)
        {
            return "lfs_"+ecls+"_"+eid;
        },
        combineHtml:function(eid,ecls,name,value)
        {
            innerHtml = "<font class='lifestatus-"+value+"'>"+name+"</font>";
            innerHtml+= " <a href='index.html?do=fstatus&ecls="+ecls+"&eid="+eid+"' rel='index.html?do=fstatus&ecls="+ecls+"&eid="+eid+"'>修改</a>";
            return innerHtml;
        },
        setup:function(obj)
        {
            Apollo.admin.sysMiniOp.common.setup(obj,"lifestatus");
        },
        callback:function(responseText, statusText)    
        {
            Apollo.admin.sysMiniOp.common.callback("lifestatus",responseText,statusText);
        }
    },
    tags:
    {
        title:"Tags",
        tipOption: {closePosition:'top',activation:'click',positionBy:"mouse",ajaxProcess:Apollo.admin.ajaxProcess,cluetipClass:'jtip',sticky: true, arrows: true,width:400,dropShadow:false},
        combineId:function(advid)
        {
            return "tags_"+advid;
        },
        combineHtml:function(advid,tagsStr)
        {
            innerHtml= tagsStr + "<a href='index.html?do=advtags_edit&advid="+advid+"' rel='index.html?do=advtags_edit&advid="+advid+"'>修改</a>";
            return innerHtml;
        },
        setup:function(obj)
        {
            var advid    = $(obj).attr("advid");
            var tagsStr  = $(obj).html();
            $(obj).attr("id",Apollo.admin.sysMiniOp.tags.combineId(advid));
            $(obj).attr("title",Apollo.admin.sysMiniOp.tags.title);
            $(obj).html(Apollo.admin.sysMiniOp.tags.combineHtml(advid,tagsStr));
            $(obj).find("a").cluetip(Apollo.admin.sysMiniOp.tags.tipOption);
        },
        callback:function(responseText, statusText)    
        {
            $("#cluetip-close").click();
            resarr = responseText.split(",");
            if(resarr[0]=="success")
            {
                var tagsStr = resarr[1];
                var advid  = resarr[2];
                var obj = "#"+Apollo.admin.sysMiniOp.tags.combineId(advid);
                $(obj).fadeOut(600,function()
                        {
                        $(this).html(Apollo.admin.sysMiniOp.tags.combineHtml(advid,tagsStr));
                        $(this).find("a").cluetip(Apollo.admin.sysMiniOp.tags.tipOption);
                        }
                        );
                $(obj).fadeIn(600);
            }
        }  
    } ,
    upattr:
    {
        tipOption: {closePosition:'top',activation:'click',positionBy:"mouse",ajaxProcess:Apollo.admin.ajaxProcess,cluetipClass:'jtip',sticky: true, arrows: true,width:200,dropShadow:false,cluezIndex:100010},
        combineId:function(attr,ecls,eid)
        {
            return "up"+attr+"_"+ecls+"_"+eid;
        },
        combineHtml:function(eid,ecls,key,value)
        {
            innerHtml = value
                +" <a rel='index.html?do=upattr&ecls="+ecls+"&eid="+eid+"&attribute="+key+"' href='index.html?do=upattr&ecls="+ecls+"&eid="+eid+"&attribute="+key+"' >修改</a>";
            return innerHtml;
        },
        setup:function(obj)
        {
            var argstr=$(obj).html();
            var argarr=argstr.split(",");
            var ecls  = argarr[0];
            var eid   = argarr[1];
            var value = argarr[2];
            var key   = argarr[3];
            var title  = argarr[4];
            $(obj).attr("id",Apollo.admin.sysMiniOp.upattr.combineId(key,ecls,eid));
            $(obj).attr("title",title);
            $(obj).html(Apollo.admin.sysMiniOp.upattr.combineHtml(eid,ecls,key,value));
//            $(obj).find("a").facebox();
            $(obj).find("a").cluetip(Apollo.admin.sysMiniOp.upattr.tipOption);
        },
        callback:function(responseText, statusText)    
        {
            $("#cluetip-close").click();
//            $.facebox.close();
            resarr = responseText.split(",");
            if(resarr[0]=="success")
            {
                var ecls = resarr[1];
                var eid  = resarr[2];
                var value = resarr[3];
                var key  = resarr[4];
                var obj = "#"+Apollo.admin.sysMiniOp.upattr.combineId(key,ecls,eid);
                $(obj).fadeOut(600,function()
                        {
                        $(this).html(Apollo.admin.sysMiniOp.upattr.combineHtml(eid,ecls,key,value));
//                        $(this).find("a").facebox();
                        $(this).find("a").cluetip(Apollo.admin.sysMiniOp.upattr.tipOption);
                        }
                        );
                $(obj).fadeIn(600);
            }
        }

    },
    interceptSetResponse:function(formname)
    {
        //adjust for classic form ajaxcall
        //not for uform
        index = $.inArray(formname,Apollo.admin.sysMiniOp.formnameArr);
        if(index!=-1)
        {
            miniop        = Apollo.admin.sysMiniOp.miniopArr[index];
            try
            { 
                curOpCallback = eval("Apollo.admin.sysMiniOp."+miniop+".callback");
                if($.isFunction(curOpCallback))
                {
                    return curOpCallback;
                }
                else
                {
                    return false;
                }
            }
            catch(e)
            {if(console)console.warn(e);}
        }
        return false;
    }
    ,
    uformSetResponse:function()
    {
        //just for uform ajaxcall
        formnameArr = Apollo.admin.sysMiniOp.formnameArr;
        for(i=0;i<formnameArr.length;i++)
        {
            formid = formnameArr[i];
            miniop = Apollo.admin.sysMiniOp.miniopArr[i];
            try
            { 
                curOpCallback = eval("Apollo.admin.sysMiniOp."+miniop+".callback");
                if($.isFunction(curOpCallback))
                {
                    eval("callback_"+formid+"=Apollo.admin.sysMiniOp."+miniop+".callback;");
                }
            }
            catch(e)
            {if(console)console.warn(e);}
        }
    }    
 }
);


(function($){

 /**
  * * Handles converting a CSS Style into an Integer.
  * * @private
  * */
    var num = function(elem, prop) {
        return elem[0] && parseInt( jQuery.curCSS(elem[0], prop, true), 10 ) || 0;
    };

  $.fn.extend({
    sidesOffset : function (options,returnObject){
                elem = this[0];
                options = $.extend({ toMargin: true, toBorder: false, toPadding: false},options||{});
                /**offset 
                 * top : pos.top+margintop
                 * left: pos.left+marginleft
                 * */
                if(options.toBorder) {options.toMargin = true;}
                if(options.toPadding){options.toMargin = true;options.toBorder = true;}

                var leftx   = $(elem).offset().left;
                var rightx  = leftx + $(elem).outerWidth(); 
                var topy    = $(elem).offset().top;
                var bottomy = topy + $(elem).outerHeight();
                 
                if ( !options.toMargin){
                    leftx  -= num(elem, 'marginLeft');
                    rightx += num(elem, 'marginRight');
                    topy   -= num(elem, 'marginTop');
                    bottomy+= num(elem, 'marginBottom');
                }
                if (options.toBorder){
                    leftx  += num(elem, 'borderLeftWidth');
                    rightx -= num(elem, 'borderRightWidth'); 
                    topy   += num(elem, 'borderTopWidth');
                    bottomy-= num(elem, 'borderBottomWidth');
                }
                if(options.toPadding){
                    leftx  += num(elem, 'paddingLeft');
                    rightx -= num(elem, 'paddingRight'); 
                    topy   += num(elem, 'paddingTop');
                    bottomy-= num(elem, 'paddingBottom');
                }
                var returnValue = {top:topy,bottom:bottomy,left:leftx,right:rightx};
                if (returnObject) { $.extend(returnObject, returnValue); return this; }
                else              { return returnValue; }
              }
            });
 })(jQuery);

(function($){
    $.fn.yellowFade = function(obj, options){
    obj = obj ? $(obj) : this;
    if(obj.length<1)return obj;
    var oldBg = obj.css('background-color');
    var targetBg = oldBg == 'transparent' ? '#000' : oldBg;
    obj.css('background-color', '#ff0');
    obj.animate({'background-color': targetBg}, 2000, function(){obj.css('background-color', oldBg)});
    return obj;
 }})(jQuery);

function tagcloud(ecls,labelreq,label)
{

    $.getJSON("tags.php?ecls=" + ecls, function(data) {
            //create list for tag links
            $("<ul>").attr("id", "tagList").appendTo(".tagCloud");

            //create tags
            $.each(data.tags, function(i, val) 
            {

                //create item
                var li = $("<li>");
                //create link
                $("<a>").text(val.tag).attr({title:"See all pages tagged with " + val.tag, href: labelreq + "&label=" + val.tag }).appendTo(li);

                if ( label  == val.tag)
                {
                //set tag size
                li.children().css("color", "red");
                }
                li.children().css("fontSize", (val.cnt / 10 < 1) ? val.cnt / 10 + 1 + "em": (val.cnt / 10 > 2) ? "2em" : val.cnt / 10 + "em");

                //add to list
                li.appendTo("#tagList");

            });


            var li = $("<li>");

            //create link
            $("<a>").text("[清除]").attr({title:"清除Tag" , href: labelreq + "&label=null" }).appendTo(li);

            li.children().css("color", "blue");

            //add to list
            li.appendTo("#tagList");

    });
}
