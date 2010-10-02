/**
 * jQuery Apollo Extention
 * @arthur: boin
 */
(function($){


$.locationhash = function(val)
{
    if(val){
        window.location.hash = val;
        return false;
    }else{
        return location.hash;
    }
}

$.isObject = function(obj)
{
    return obj && obj.constructor && obj.constructor == {}.constructor;
}

$.ns = function(val, obj)
{
    if(val){
        var nsarr = val.split('.');
        if(nsarr.length > 0){
            var parentSpace = window;
            for(var depth=0; depth< nsarr.length; depth++){
                var name = nsarr[depth];
                if(!parentSpace[name])
                    parentSpace[name] = {};
                parentSpace = parentSpace[name];
            }
        }
        if(obj && $.isObject(obj)){
            $.extend(parentSpace, obj);
        }
    }else{
    }
}

})(jQuery);
