$.ns("PUI", {
    debug: function(str) {
        alert("PUI.Uform Debug Info:\n" + str);
    },
    calcuSpaceWidth: function(context) {
        thiswidth = $(context).width();
        thiswidth += parseInt($(context).css("margin-left"));
        thiswidth += parseInt($(context).css("margin-right"));
        thiswidth += parseInt($(context).css("border-left-width"));
        thiswidth += parseInt($(context).css("border-right-width"));
        thiswidth += parseInt($(context).css("padding-right"));
        thiswidth += parseInt($(context).css("padding-left"));
        return thiswidth;
    },
    arsynLoad: function(data){
        function replace_url(out, url_prefix) {
            if(url_prefix)
            {
                var url_prefix_array = url_prefix.toString().split(",");
                var hrefSelect_array = new Array();
                for(i in url_prefix_array)
                {
                    hrefSelect_array[i]="a[href*='"+url_prefix_array[i]+"']";
                }
                find = out +" "+hrefSelect_array.join(",");
                $(find).click(
                function() {
                data = {};
                data.url = $(this).attr("href");
                data.out = out;
                data.prefix = url_prefix;
                PUI.arsynLoad(data);
                return false;
                });
             }
        }
        $.get(data.url,
            function(outdata) {
            $(data.out).html(outdata);
            replace_url(data.out, data.prefix);
            });
    }
});

$.ns("PUI.Uform", {
    totalCheckBound: false,
    eventData: {},
    inputChecker: function() {
        //prompt   class="prompt"
        //error    class="error"
        var inputName = $(this).attr("name");
        var inputValue = (typeof $(this).val() == "undefined") ? "": $.trim($(this).val());
        $(this).val(inputValue);
        var inputRule = (typeof $(this).attr("rule") == "undefined") ? "": $(this).attr("rule");
        var inputORule = (typeof $(this).attr("orule") == "undefined") ? "": $(this).attr("orule");

        //            PUI.debug("input[name="+inputName+"] executing blur event");     
        var inputElement = $(this);
        var inputExtFunc = eval($(this).attr("extfunc"));

        var promptElement = $(this).siblings(".prompt:first");
        var errorElement = $(this).siblings(".error:first");
        if (inputValue || PUI.Uform.forceCheckBound) {
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
                value: inputValue,
                rule: inputRule
            };
            $.getJSON("/validate.php", eParams,
            function(data) {
                if (data['RET'] == 'ERR') {
                    errorElement.show();
                    promptElement.hide();
                    if (errorElement.attr("className") == "error") {
                        if (errorElement.html() == data['ERRMSG']) return;
                        else errorElement.html(data['ERRMSG']);
                    }
                    else {
                        errorHtml = " <font style=\"color:red;\" class=\"error\">" + data['ERRMSG'] + "</font>";
                        inputElement.after(errorHtml);
                    }
                }
                else {
                    errorElement.html("").hide();
                    promptElement.hide();
                }
            });
        }
        else {
            promptElement.show();
            errorElement.hide();
        }
    },
    onSubmit: function(event) {
        formid = $(this).attr("id");
        PUI.Uform.eventData = data = event.data;
        PUI.Uform.forceCheckBound = true;
        if (typeof(data.callbefore) == "function" && $.isFunction(data.callbefore)) {
            res = data.callbefore(formid);
            if (!res) return false;
        }
        $("#" + formid + " input:text,textarea,select").trigger("blur");
        $("#" + formid + " input:submit").attr("disabled", true);
        window.setTimeout("PUI.Uform.totalCheck('" + formid + "',PUI.Uform.eventData);", 1000);
        PUI.Uform.forceCheckBound = false;
        return false;
    },
    totalCheck: function(formid, data) {
        var allowSubmit = true;
        $("#" + formid + " input:submit").attr("disabled", true);

        $('#' + formid + " input:text,textarea,select").each(function() {
            errorElement = $(this).siblings(".error:first");
            if (errorElement.attr("className") == "error" && errorElement.html() != "") {
                allowSubmit = false;
            }
        });
        if (allowSubmit) {
            if (data.ajaxmode) PUI.Uform.formSubmit(formid, data);
            else document.getElementById(formid).submit();
        }
        else {
            $("#" + formid + " input:submit").attr("disabled", false);
        }
    },
    formSubmit: function(formid, data) {
        if (typeof(data.callback) == "function" && $.isFunction(data.callback)) {
            callback = data.callback;
        }
        else {
            callback = PUI.Uform.defaultResponse;
        }
        var options = {
            success: callback
        }
        $("#" + formid).ajaxSubmit(options);
        return false;
    },
    defaultResponse: function(responseText, statusText) {
        if (responseText.search("result_success") != -1) {
            $.facebox("操作成功!");
            window.location.reload();
        }
        else {
            alert(responseText);
            $(":submit").attr("disabled", false)
        }
    }
});
$.ns("PUI.Ufilter", {
    pickStartDate: "2008-01-01",
    pickEndDate: "",
    excutedBound:false,
    adjustPosition: function(obj) {
        ulobj = $(obj).find("ul:first");
        var _maxwidth = ulobj.width() ;
        var _layout = new Array();
        var _layindex = 0;
        var _rowwidth = 0;
        _layout[_layindex] = new Array();
        ulobj.find("li.filter").each(function() {
            thiswidth = PUI.calcuSpaceWidth(this);
            if (_rowwidth+thiswidth >= _maxwidth) {
                _layindex++;
                _rowwidth = 0;
                _layout[_layindex] = new Array();
            }
            _rowwidth += thiswidth ;
            _layout[_layindex].push($(this).attr("relindex"));
        });
        newfilter = ulobj.clone();
        newfilter.empty();
        for (i in _layout) {
            for (n in _layout[i]) {
                ulobj.find("li.filter[relindex=" + _layout[i][n] + "]").appendTo(newfilter);
            }
            for (n in _layout[i]) {
                ulobj.find("li.filterBox[relindex=" + _layout[i][n] + "]").appendTo(newfilter);
            }
        }
        ulobj.replaceWith(newfilter);
    },
    setup: function() {
        if(!PUI.Ufilter.excutedBound)
            PUI.Ufilter.excutedBound = true;
        else
            return false;
        $("div.filter-space").each(
        function()
        {
            PUI.Ufilter.adjustPosition(this);
            $(this).find("ul:first li a.select").click
            (
             function() {
                var ulobj = $(this).parent().parent();
                if ($(this).parent().hasClass("curr")) {
                    relindex = $(this).parent().attr("relindex");
                    ulobj.find(".filterBox[relindex='" + relindex + "']").hide();
                    $(this).parent().removeClass("curr");
                }
                else {
                    ulobj.find(".filterBox").hide();    
                    ulobj.find(".filter").removeClass("curr");
                    relindex = $(this).parent().attr("relindex");
                    ulobj.find(".filterBox[relindex='" + relindex + "']").show();
                    $(this).parent().addClass("curr");
                }
             }
            );
        }
        );       
        $('.datePick').datePicker({
            startDate: PUI.Ufilter.pickStartDate,
            endDate: PUI.Ufilter.pickEndDate,
            createButton: false,
            clickInput: true
        });
    }
});
