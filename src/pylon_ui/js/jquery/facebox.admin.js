(function($) {
 $.facebox = function(data, klass)
 {
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
<td class="body blueFrame"> \
<div class="content"> \
</div> \
<div class="footer"> \
<a href="#" class="close"> \
<img title="close" class="close_image" /> \
</a> \
</div> \
</td> \
</tr> \
</tbody> \
</table> \
</div> \
</div>'
 };

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

    $('#facemask').css('opacity', '0.2').css('width', $(document).width()+"px").css('height', $(document).height()+"px");
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

$.facebox.reveal = function(data, klass) {
    if (klass) $('#facebox .content').addClass(klass)
        $('#facebox .content').html(data);
            $('#facebox .loading').remove()
            $('#facebox .body').children().fadeIn('normal')
            if($("#facebox .body").width()<150){
                $("#facebox .body").width(300);
                if($("#facebox .body .content").height<50)
                    $("#facebox .body .content").height(50);
            }

}


$.facebox.click_handler = function () {
        $.facebox.loading(true)

            var image_types = $.facebox.settings.image_types.join('|')
            image_types = new RegExp('\.' + image_types + '$', 'i')
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

    $(document).scroll(function(){
            if($("#facemask").css("width")!="0px");
            {
            $('#facemask').css('height', $(document).height()+"px");
            }
            });


    $.fn.facebox = function(settings) {
        $.facebox.init(settings)

        return this.click($.facebox.click_handler);
    }

$.facebox.init = function(settings) {
    if ($.facebox.settings.inited) return true
    else $.facebox.settings.inited = true

        if (settings) $.extend($.facebox.settings, settings)
            $('body').append($.facebox.settings.facebox_html);

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
        if (self.innerHeight) {innerHeight// all except Explorer
            windowHeight = self.innerHeight;
        } else if (document.documentElement && document.documentElement.clientHeight) { // Explorer 6 Strict Mode
            windowHeight = document.documentElement.clientHeight;
        } else if (document.body) { // other Explorers
            windowHeight = document.body.clientHeight;
        }
    return windowHeight
}
})(jQuery);

