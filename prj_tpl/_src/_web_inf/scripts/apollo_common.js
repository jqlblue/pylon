$.ns('Apollo.common', {
        errbox   : "errorinfo",
        formCheck: function()
        {
            var forceCheckBound = false;
            inputChecker = function()
            {
                function showFail(errmsg)
                {
                    errorElement = $("#"+errorId);
                    promptElement= $("#"+promptId);
                    errorElement.show();
                    promptElement.hide();
                    errorHtml = " <font style=\"color:red;\" id=\""+errorId+"\" errorid=\""+errorId+"\">" + errmsg + "<br/></font>";
                    if(document.getElementById(errorId))
                    {
                        errorElement.html(errorHtml);  
                    }
                    else if(document.getElementById(Apollo.common.errbox))
                    {
                        errbox = $("#"+Apollo.common.errbox);
                        if(errbox.is(":has(font[errorid='"+errorId+"'])"))
                        {
                            errbox.children("font[errorid='"+errorId+"']").html(errmsg);
                        }
                        else
                        {
                            errbox.append(errorHtml);
                        }
                    }
                    else
                    {
                        if(document.getElementById(promptId)&&!document.getElementById(errorId))
                        {
                            $("#"+promptId).after(errorHtml);   
                        }
                        else if(inputElement.parent().is(":has(font[errorid='"+errorId+"'])"))
                        {
                            inputElement.parent().children("font[errorid='"+errorId+"']").html(errmsg);
                        }
                        else
                        {
                            inputElement.after(errorHtml);   
                        }
                    }
                }

                function showSuc()
                {
                    $("font[errorid='"+errorId+"']").remove();
                    $("#"+promptId).hide();
                    if(!inputValue)
                        $("#"+promptId).show();
                    $("#"+errorId).hide();
                }
                var inputElement = $(this);
                var inputName = $(this).attr("name");
                var inputValue = (typeof $(this).val() == "undefined") ? "": $.trim($(this).val());
                $(this).val(inputValue);
                var inputRule = (typeof $(this).attr("rule") == "undefined") ? "": $(this).attr("rule");
                var inputORule = (typeof $(this).attr("orule") == "undefined") ? "": $(this).attr("orule");
                var inputExtFunc = eval($(this).attr("extfunc"));
                var promptId = inputName+"_prompt";
                var errorId  = inputName+"_error";
                
                if (inputValue || forceCheckBound)
                {
                    var eForm = $(this).parents('form').attr("name");
                    var actionName = $(this).parents('form').attr("action");
                    re = /do=([a-zA-Z0-9_]+)/;
                    re.exec(actionName);
                    var eAction = RegExp.$1;
                    var eParams = (inputORule) ? {
                        action: eAction,
                        form: eForm,
                        name: inputName,
                        value: inputValue,
                        orule: inputORule
                    }: {
                        action: eAction,
                        form: eForm,
                        name: inputName,
                        value:inputValue,
                        rule: inputRule
                    };                                                                                                                                                                                                                                                                                                                                                                                                    value: inputValue,
                    $.getJSON("/validate.php", 
                        eParams,
                        function(data)
                        {
                            if(data['RET'] == 'ERR')
                            {
                                showFail(data['ERRMSG']);
                            }
                            else
                            {
                                if($.isFunction(inputExtFunc))
                                {
                                    if(inputExtFunc.call(this))
                                    {
                                        showSuc();
                                    }
                                }
                                else
                                    showSuc();
                            }
                        }
                    );
                }
                else
                {
                    showSuc();
                }


            };
            $("form[formcheck!='false']").each(function()
            {
                $(this).find("input:text,input:password,input:hidden,textarea,select").blur(inputChecker);
            });
            $("form").submit(function ()
                {
                    var formElement = this;
                    totalCheck = function()
                    {
                        var allowSubmit = true;
                        $(formElement).find("input:text,input:password,input:hidden,textarea,select").each
                        (
                            function()
                            {
                                var errorId  = $(this).attr("name")+"_error";
                                if($(formElement).is(":has(font[errorid='"+errorId+"'])"))
                                    allowSubmit = false;
                            }
                        );
                        if(allowSubmit) 
                        {
                            ajaxmode = ($(formElement).attr("ajaxmode")=="true")?true:false;
                            if (ajaxmode) Apollo.common.formAjaxSubmit.call(formElement);
                            else formElement.submit();
                        }
                        else
                        {
                            $(formElement).find(":submit").attr("disabled",false);
                        }
                    };
                    forceCheckBound = true;
                    try
                    {
                        callbefore = eval($(this).attr("callbefore"));
                    }
                    catch(e){if(console)console.warn(e)}
                    if ($.isFunction(callbefore))
                    {
                        res = callbefore.call(this);
                        if (!res) return false;
                    }
                    

                    $(this).find("input:text,input:password,input:hidden,textarea,select").trigger("blur");
                    $(this).find(":submit").attr("disabled",true);
                    window.setTimeout("totalCheck.apply();", 1000);
                    forceCheckBound = false;
                    return false; 
                }
            );
            var html= $("#_message").html();
            if(html!= null && html.length  > 1)
            { 
                $.facebox(html);
            }
        },
        formAjaxSubmit:function()
        {
            function showResponse(responseText, statusText)
            {
                if(responseText.search("result_success")!= -1)
                {
                    $.facebox("操作成功!");
                    window.setTimeout("window.location.reload();",1000);
                }
                else
                {
                    alert(responseText);
                    $(":submit").attr("disabled",false);
                }
            };
            try{
                callback = eval($(this).attr("name")+"_response");
            }
            catch(e){if(console)console.warn(e)}
            try
            {
                callback = eval($(this).attr("callback"));
            }
            catch(e){if(console)console.warn(e)}
            try{
            miniOpResponse = Apollo.admin.sysMiniOp.interceptSetResponse($(this).attr("name"));
            if(miniOpResponse) callback = miniOpResponse;
            }
            catch(e){if(console)console.warn(e)}

            if ($.isFunction(callback))
            {
                showResponse = callback;
            }
            var options = 
            { 
                success:showResponse  
            }
            $(this).ajaxSubmit(options); 
        } 
    })
