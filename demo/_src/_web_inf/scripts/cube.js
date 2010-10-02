try {document.execCommand("BackgroundImageCache", false, true);}catch(e){};

var js_apply = function ()
{
	var args = Array.prototype.slice.call(arguments);
	var request = args.shift();
	try{
		reqFunc = funcProxy[request].clfunc;
		if($.isFunction(reqFunc))
		{
			reqFunc.apply(null,args);
		}
	}catch(e){
		alert(e.message);
	}
};
var js_callback = function ()
{
	var args = Array.prototype.slice.call(arguments);
	var request = args.shift();
	try{
		reqFunc = funcProxy[request].jsfunc;
		if($.isFunction(reqFunc))
		{
			reqFunc.apply(null,args);
		}
	}catch(e){
		
	}
};

var handler_switch = true;
function toggleObj(target) {
    if( handler_switch ) {
        $(target).show();
        handler_switch = false;
    }else{
        $(target).hide();
        handler_switch = true;
    }
}
