(function($){
	
 $.fn.editable = function(options) { 	
	
	var defaults = {  
    	typex: "text",		
		url: "action_ajax.php",
		actionx: "nothing",
		id: 0,
		style_class: "editable",		
		width: "200px"
   };  
   
   var options = $.extend(defaults, options);  

    return this.each(function() {
		
		var obj = $(this);
				
		obj.addClass(options.style_class);		
		
		var text_saved = obj.html();
		var namex = this.id + "editMode";
		var items = "";		      		       
											
		obj.click(function() {
			switch (options.typex) {
			 	case "text": {
					var inputx = "<input id='" + namex + "' type='text' style='width: " + options.width + "' value='" + text_saved + "' />";
					var btnSend = "<input type='submit' id='btnSave" + this.id + "' value='OK' />";
					var btnCancel = "<input type='button' id='btnCancel" + this.id + "' value='Cance' />";
					items = inputx + btnSend + btnCancel; 
					break;
				}
			}  
			
		   	obj.html(items);			
			$("#" + namex).focus().select();			
			$("#btnSave" + this.id, obj).click(function () {
				$.ajax({
				    type: "POST", 		       
				   	data:	    		 
				   		{ 		   		
					   		text_string: $("#" + namex).val(),
							actionx: options.actionx,
							idx: options.id													
						},
		    		url: options.url,    		    		
		    		success: function(data) {
		    			if (data > '') {
							obj.html(data);							
						} else {
							obj.html('Pinchar para introducir un texto');	
						}
						text_saved = data;		
				    },
					error: function(objHttpRequest, error_str) {
						obj.html(error_str);
					}
		  		});				
			})				
			
			$("#btnCancel" + this.id, obj).click(function () {
				obj.html(text_saved);					
			})
				
			return false;
		});		  
    });			
 };
})(jQuery);
