 $.ns('Apollo.admin', {
    ajaxProcess:function(data)
    {
        return data;
    },
    commonSetup:function()
    {
        var options = {minWidth: 120, copyClassAttr: true,offsetLeft:-70};
        $('a.tip').each(
            function()
            {
            $(this).cluetip({activation:'click',ajaxProcess:Apollo.admin.ajaxProcess,cluetipClass: 'jtip',hoverClass: 'highlight',sticky: true, closePosition: 'title', arrows: true,width:520,dropShadow:false,closeText: '<img src="/images/admin/close.png" title="关闭" />'}); 
            }
        );
        Apollo.admin.sysMiniOp.uformSetResponse();
        Apollo.admin.iconSupSetup();
        Apollo.admin.tdHoverSetup();
        $("a[rel*=facebox]").prepend("⊕ ").facebox() ;
        Apollo.admin.miniOpSetup();
        Apollo.admin.entitySetup();
    },
    entitySetup:function()
    {
        var entityObj = {};
        $(".sys-entity").each(
            function()
            {
               donedue = $(this).attr("donedue"); 
               if(!donedue)
               {
               entity  = $(this).attr("entity"); 
               try
               { 
                    if(entityObj[entity])
                    {
                        curEntitySetup = entityObj[miniop];
                    }
                    else
                    {
                        curEntitySetup = entityObj[miniop] = eval("Apollo.admin.sysEntity."+entity+".setup");
                    }
                    if($.isFunction(curEntitySetup))
                    {
                        $(this).attr("donedue","ok");
                        curEntitySetup(this);
                        $(this).show();
                    }
               }
               catch(e)
               {if(console)console.warn(e);}
               }
            }
        );
    },
    miniOpSetup:function()
    {
        var miniopObj = {};
        $(".sys-miniop").each(
            function()
            {
               donedue = $(this).attr("donedue"); 
               if(!donedue)
               {
               miniop = $(this).attr("miniop"); 
               try
               { 
                    if(miniopObj[miniop])
                    {
                        curOpSetup = miniopObj[miniop];
                    }
                    else
                    {
                        curOpSetup = miniopObj[miniop] = eval("Apollo.admin.sysMiniOp."+miniop+".setup");
                    }
                    if($.isFunction(curOpSetup))
                    {
                        miniopObj[miniop] = curOpSetup;
                        $(this).attr("donedue","ok");
                        curOpSetup(this);
                        $(this).show();
                    }
               }
               catch(e)
               {if(console)console.warn(e);}
               }
            }
        );
    },
    tdHoverSetup:function()
    {
       $("table.datalist tr:gt(0)").not(":has(.datalist)").hover(
                function()
                {
                     $(this).data("oldbg",$(this).css("background"));
                     if(typeof $(this).data("oldbg")=="undefined") {$(this).data("oldbg","#fff");}
                     $(this).css("background","#ECF9F2");
                },
                function()
                {
                    $(this).css("background",$(this).data("oldbg"));
                }
         );
    },
    iconSupSetup:function()
    {
        $("#admin_navi a[tag*='new'],#header_park a[tag*='new']").not("a[tag*='new_']").each(
            function()
            {
                var pos = $(this).offset();
                pos.left = pos.left+$(this).width()-5;
                pos.top  = pos.top-$(this).height();
                $("body").append("<sup class='new' style='left:"+pos.left+"px;top:"+pos.top+"px'>new</sup>");
            }
        );
        $("#admin_navi a[tag*='hot'],#header_park a[tag*='hot']").not("a[tag*='hot_']").each(
            function()
            {
                var pos = $(this).offset();
                pos.left = pos.left+$(this).width()-5;
                pos.top  = pos.top-$(this).height();
                $("body").append("<sup class='hot' style='left:"+pos.left+"px;top:"+pos.top+"px'>hot</sup>");
            }
        );
    },
    init: function()
    {
    Apollo.common.formCheck(); 
    var curaction = Apollo.admin.curaction();
    cur  = $("#admin_navi a[href*=?do\="+curaction+"]").removeClass("undis").css("color","#f00");
    cur2 = $("#admin_navi a[rel*="+curaction+"]").removeClass("undis").css("color","#f00");
    if(typeof(Apollo.admin.pageInit) == "function" && $.isFunction(Apollo.admin.pageInit))
    Apollo.admin.pageInit();
    Apollo.admin.commonSetup();
    }
});
$(document).ready(Apollo.admin.init);
var console;

$.ns('Apollo.admin', {

        errbox: "errorinfo",
        checkBlank: false,
        showResponse: function(responseText, statusText)  { 
            if(responseText.search("result_success")!= -1)
            {
                window.location.reload();
//                  window.location = "index.html?do=backto";
            }
        }, 

        inputChecker: function ()
        {
            var inputElement = $(this); 
            var inputName  = $(this).attr("name");
            var inputValue = $(this).attr("value");
            inputValue = (typeof inputValue=="undefined")?"":inputValue;
            var inputExtvalid = eval($(this).attr("extvalid"));

            var promptName = inputName+"_prompt";
            var noticeName = inputName+"_notice";
            if(inputValue || Apollo.admin.checkBlank)
            {
                var eform = $(this).parents('form').attr("name");
                var actionName = $(this).parents('form').attr("action");
                re=/do=([a-zA-Z0-9_]+)/ ;
                re.exec(actionName);
                var eaction =RegExp.$1;
                if ( eaction.length < 1 )
                    eaction = Apollo.admin.current_action;
                $.getJSON("/validate.php", 
                    {action: eaction, form:eform, name: inputName, value: inputValue }, 
                    function(data){
                        if(data['RET'] == 'ERR')
                        {
                            if($("#"+promptName).is("font")) 
                            {
                                if($("#"+promptName).html() == data['ERRMSG'] + "<br/>")
                                    return ;
                                else
                                    $("#"+promptName).html(data['ERRMSG'] + "<br/>");
                            }
                            else
                            {
                                if(document.getElementById(Apollo.admin.errbox))
                                {
                                    $("#"+Apollo.admin.errbox).html($("#"+Apollo.admin.errbox).html()+"<font style=\"color:red;\" id=\""+promptName+"\">" + data['ERRMSG'] + "<br/></font>");
                                }
                                else
                                {
                                    promptHtml = " <font style=\"color:red;\" id=\""+promptName+"\">" + data['ERRMSG'] + "<br/></font>";
                                    if(document.getElementById(noticeName))
                                    {
                                        $("#"+noticeName).after(promptHtml);   
                                    }
                                    else
                                        inputElement.after(promptHtml);   
                                }
                            }
                        }
                        else
                        {
                            if($.isFunction(inputExtvalid))
                            {
                                if(inputExtvalid())
                                {
                                    $("#"+promptName).remove();
                                }
                            }
                            else
                                $("#"+promptName).remove();

                        }
                        $("#"+noticeName).hide();
                    });
            }
            else
            {
                $("#"+noticeName).show();
                $("#"+promptName).remove();
            }
        },

        noticeEnable: function()
        {
            $("input:text,input:password,input:hidden,textarea,select").blur(Apollo.admin.inputChecker);
            $("form").submit(
                function ()
                {
                    var submitFunc;
                    try{
                        submitFunc = eval("submit_"+$(this).attr("name"));
                    }catch(e){if(console)console.warn(e)};
                    if(typeof(submitFunc)=="function" && $.isFunction(submitFunc))
                    {
                        return submitFunc();
                    }
                    else
                        return true;
                }
            );
            var html= $("#_message").html();
            if(html!= null && html.length  > 1)
            { 
                jQuery.facebox(html);
            }
        },
    
        curaction : function()
        {
            var name = "do";
            var str=window.location.search; 
            if (str.indexOf(name)!=-1){ 
                var pos_start=str.indexOf(name)+name.length+1; 
                var pos_end=str.indexOf("&",pos_start); 
                if (pos_end==-1){ 
                    return str.substring(pos_start); 
                }else{ 
                    return str.substring(pos_start,pos_end) 
                } 
            }else{ 
                return "login";
            } 
        }


    })

function check_submit(formid,ajaxmod)
{
    var allowSubmit = true;
    $("#"+formid+" :submit").attr("disabled",true);
    $('#'+formid+" input").each(function()
        {
            var promptName = ($(this).attr("name"))+"_prompt";
            if(document.getElementById(promptName))
            {
                allowSubmit = false;
            }
        }
    );
    if(allowSubmit)
    {
        if(ajaxmod)
            form_submit(formid);
        else    
            document.getElementById(formid).submit();
    }
    else
    {
        $("#"+formid+" :submit").attr("disabled",false);
    }
}

function curform_submit()
{ 
    var responseFunc;
    try{
        responseFunc = eval($(this).attr("name")+"_response");
    }catch(e){if(console)console.warn(e)}
    if(typeof(responseFunc)=="function" && $.isFunction(responseFunc))
    {
        showResponse = responseFunc;
    }
    var options = 
        { 
            beforeSubmit:  showRequest,  
            success:       showResponse  
        }
    $(this).ajaxSubmit(options); 
    return false;
}
function form_submit(eid)
{ 
    var responseFunc;
    try{
        responseFunc = eval(eid+"_response");
    }catch(e){if(console)console.warn(e)}
    if(typeof(responseFunc)=="function" && $.isFunction(responseFunc))
    {
        showResponse = responseFunc;
    }
    miniOpResponse = Apollo.admin.sysMiniOp.interceptSetResponse(eid);
    if(miniOpResponse) showResponse = miniOpResponse;
    var options = 
        { 
            success:       showResponse  
        }
    $("#"+eid).ajaxSubmit(options); 
    return false;
}

function showResponse(responseText, statusText)
{
    if(responseText.search("result_success")!= -1)
    {
        $.facebox("操作成功!");
//        window.location = "index.html?do=backto";
        window.location = window.location;
    }
    else
    {
        alert(responseText);
        $(":submit").attr("disabled",false)
    }
}
