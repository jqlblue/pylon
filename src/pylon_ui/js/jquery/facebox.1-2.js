/*
 * Facebox (for jQuery)
 * version: 1.2 (03/13/2008)
 * @requires jQuery v1.2 or later
 *
 * Examples at http://famspam.com/facebox/
 * Code at http://github.com/defunkt/facebox
 * List at http://groups.google.com/groups/facebox
 *
 * Licensed under the MIT:
 *   http://www.opensource.org/licenses/mit-license.php
 *
 * Copyright 2007, 2008 Chris Wanstrath [ chris@ozmm.org ]
 *
 * Usage:
 *  
 *  jQuery(document).ready(function() {
 *    jQuery('a[rel*=facebox]').facebox() 
 *  })
 *
 *  <a href="#terms" rel="facebox">Terms</a>
 *    Loads the #terms div in the box
 *
 *  <a href="terms.html" rel="facebox">Terms</a>
 *    Loads the terms.html page in the box
 *
 *  <a href="terms.png" rel="facebox">Terms</a>
 *    Loads the terms.png image in the box
 *
 *
 *  You can also use it programmatically:
 * 
 *    jQuery.facebox('some html')
 *
 *  This will open a facebox with "some html" as the content.
 *    
 *    jQuery.facebox(function() { ajaxes })
 *
 *  This will show a loading screen before the passed function is called,
 *  allowing for a better ajax experience.
 *
 *  Want to close the facebox?  Trigger the 'close.facebox' document event:
 *
 *  jQuery(document).trigger('close.facebox')
 *
 */
(function($) {
 $.facebox = function(data, klass) {
 $.facebox.init()
 $.facebox.loading()
 $.isFunction(data) ? data.call() : $.facebox.reveal(data, klass)
 }

 $.facebox.settings = {
loading_image : '/images/pui/facebox/loading.gif',
close_image   : '/images/pui/facebox/closelabel.gif',
window_hash   : '#facebox',
image_types   : [ 'png', 'jpg', 'jpeg', 'gif' ],
facebox_html  : '\
<div id="facemask"></div> \
<div id="facebox" style="display:none;"> \
<div class="popup"> \
<table> \
<tbody> \
<tr> \
<td class="body"> \
<div id="alertBox"> \
<div class="title"> \
<h4></h4> \
<span><a href="#" class="close">&nbsp;</a></span> \
</div> \
<div class="main"> \
<div class="content"> \
</div> \
</div> \
</div> \
</td> \
</tr> \
</tbody> \
</table> \
</div> \
</div>'
 }

 // Opening the facebox adds #facebox to the url.  Clicking 'back' closes the facebox
 // but keeps you on the page you were on.
 function back_button_observer() {
     if (window.location.hash != $.facebox.settings.window_hash) $(document).trigger('close.facebox')
 }

 function observe_back_button() {
     $.facebox.settings.old_hash = window.location.hash || '#'
         window.location.hash = $.facebox.settings.window_hash
         $.facebox.settings.back_button_observer = setInterval(back_button_observer, 200)
 }

 function stop_observing_back_button() {
     if (window.location.hash != $.facebox.settings.old_hash) window.location.hash = $.facebox.settings.old_hash
         $.facebox.settings.old_hash = null
             clearInterval($.facebox.settings.back_button_observer)
 }

 $.facebox.loading = function() {
     if ($('#facebox .loading').length == 1) return true

//         observe_back_button()
    $('#facemask').css('opacity', '0.2').css('width', '100%').css('height', $(document).height());
             $('#facebox .content').empty()
             $('#facebox .body').children().hide().end().
             append('<div class="loading"><img src="'+$.facebox.settings.loading_image+'"/></div>')

             var pageScroll = $.facebox.getPageScroll()
             $('#facebox').css({
top:pageScroll[1] + ($.facebox.getPageHeight() / 10),
left:pageScroll[0]
}).show()

    $(document).bind('keydown.facebox', function(e) {
            if (e.keyCode == 27) $.facebox.close()
            })
$('#facebox .close').click($.facebox.close)
}
$(document).scroll(function(){
        if($("#facemask").css("width")!="0px");
        {
        $('#facemask').css('height', $(document).height()+"px");
        }
        });

$.facebox.reveal = function(data, klass) {
    if (klass) $('#facebox .content').addClass(klass)
        $('#facebox .content').append(data)
            $('#facebox .loading').remove()
            $('#facebox .body').children().fadeIn('normal')
            if($("#facebox .body").width()<200){
                $("#facebox .body").width(300);
                if($("#facebox .body .content").height<50)
                    $("#facebox .body .content").height(50);
            }

}

$.facebox.close = function() {
    $(document).trigger('close.facebox')
        return false
}

    $(document).bind('close.facebox', function() {
//            stop_observing_back_button()
            $(document).unbind('keydown.facebox')
            $('#facebox').fadeOut(function() {
                $('#facebox .content').empty().removeClass().addClass('content')
                })
            $('#facemask').css('width', '0px');
            })

    $.fn.facebox = function(settings) {
        $.facebox.init(settings)

            var image_types = $.facebox.settings.image_types.join('|')
            image_types = new RegExp('\.' + image_types + '$', 'i')

            function click_handler() {
                $.facebox.loading(true)

                    // support for rel="facebox[.inline_popup]" syntax, to add a class
                    var klass = this.rel.match(/facebox\[\.(\w+)\]/)
                    if (klass) klass = klass[1]

                        // div
                        if (this.href.match(/#/)) {
                            var url    = window.location.href.split('#')[0]
                                var target = this.href.replace(url,'')
                                $.facebox.reveal($(target).clone().show(), klass)

                                // image
                        } else if (this.href.match(image_types)) {
                            var image = new Image()
                                image.onload = function() {
                                    $.facebox.reveal('<div class="image"><img src="' + image.src + '" /></div>', klass)
                                }
                            image.src = this.href

                                // ajax
                        } else {
                            $.get(this.href, function(data) { $.facebox.reveal(data, klass) })
                        }

                return false
            }

        return this.click(click_handler)
    }

$.facebox.init = function(settings) {
    if ($.facebox.settings.inited) return true
    else $.facebox.settings.inited = true

        if (settings) $.extend($.facebox.settings, settings)
            $('body').append($.facebox.settings.facebox_html)

                var preload = [ new Image(), new Image() ]
                preload[0].src = $.facebox.settings.close_image
                preload[1].src = $.facebox.settings.loading_image

                $('#facebox').find('.b:first, .bl, .br, .tl, .tr').each(function() {
                        preload.push(new Image())
                        preload.slice(-1).src = $(this).css('background-image').replace(/url\((.+)\)/, '$1')
                })


    $('#facebox .close').click($.facebox.close)
    $('#facebox .close_image').attr('src', $.facebox.settings.close_image)
}

// getPageScroll() by quirksmode.com
$.facebox.getPageScroll = function() {
    var xScroll, yScroll;
    if (self.pageYOffset) {
        yScroll = self.pageYOffset;
        xScroll = self.pageXOffset;
    } else if (document.documentElement && document.documentElement.scrollTop) { // Explorer 6 Strict
        yScroll = document.documentElement.scrollTop;
        xScroll = document.documentElement.scrollLeft;
    } else if (document.body) {// all other Explorers
        yScroll = document.body.scrollTop;
        xScroll = document.body.scrollLeft;
    }
    return new Array(xScroll,yScroll) 
}

// adapter from getPageSize() by quirksmode.com
$.facebox.getPageHeight = function() {
    var windowHeight
        if (self.innerHeight) {// all except Explorer
            windowHeight = self.innerHeight;
        } else if (document.documentElement && document.documentElement.clientHeight) { // Explorer 6 Strict Mode
            windowHeight = document.documentElement.clientHeight;
        } else if (document.body) { // other Explorers
            windowHeight = document.body.clientHeight;
        }
    return windowHeight
}
})(jQuery);
