jQuery.fn.ajaxSubmit = function(foo, conf) {
    var config = {
        method: false,  // request method (get/post) defaults to form's
        action: false,  // action (url) defaults to form's
        loading: 'Loading...'
    };
    config = jQuery.extend(config, conf);

    var callback = (typeof(foo) === 'string') ? function(data) {jQuery(foo).html(data);} : (typeof(foo) === 'function') ? foo : false;

    return this.each(function() {
            var form = jQuery(this);

            if(form.is('form')) {
            var method = config.method || form.attr('method');
            var action = config.action || form.attr('action');
            var submit = jQuery('input[type="submit"]', form);
            var data = {};

            form.submit(function() {
                jQuery('*[name]', form).each(function() {
                    var t = jQuery(this);
                    var val = (t.attr('type') == 'checkbox') ? (t.attr('checked') == true) ? 1 : 0 : t.val();
                    data[t.attr('name')] = val;
                    });
                submit.val(config.loading);
                jQuery[method](action, data, function(data) {
                    callback(data);
                    });

                return false;
                });
            }
    });
};


