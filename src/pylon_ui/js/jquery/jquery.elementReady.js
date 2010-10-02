/* Jquery elementReadyPlus plugin
 * version 0.1
 * Copyright (c) 2008 irideas
 */
;(function($)
 {
   var interval  = null;
   var checklist = [];
   $.elementReady = function(id,fn)
   {
        checklist.push({id: id, fn: fn, consume:0});
        if (!interval) 
        {
            interval = setInterval(check, $.elementReady.interval_ms);
        }
        return this;
   } 
   $.elementReady.interval_ms =23; 
   $.elementReady.consume_max =500; //timeout:$.elementReady.timeout_nu*0.023 sec
   function check()
   {
        var docReady = $.isReady; // check doc ready first; thus ensure that check is made at least once _after_ doc is ready
        
        if(checklist.length!=0)
        for (var i = checklist.length - 1; 0 <= i; --i)
        {
            var el = document.getElementById(checklist[i].id);
            if (el)
            {
                var fn = checklist[i].fn; // first remove from checklist, then call function
                checklist[i] = checklist[checklist.length - 1]; //replace to delself
                checklist.pop();
                fn.apply(el, [$]);
            }
            else if(!docReady)
            {
                if(checklist[i].consume < $.elementReady.consume_max)
                {
                    checklist[i].consume = checklist[i].consume+1;
                }
                else
                {
                    checklist[i] = checklist[checklist.length - 1]; //replace to delself
                    checklist.pop();
                }
                
            }
        }
        if (docReady&&checklist.length==0)
        {
                clearInterval(interval);
                interval = null;
        }
   };
 }
 )(jQuery);
