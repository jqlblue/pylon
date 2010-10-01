$.ns('Apollo.stage', {
        init: function()
        {
            Apollo.common.formCheck(); 
            $("a[rel*=facebox]").facebox();
            $("table.datatable.alt tbody tr:even").addClass("alt");
        }
    }
);
$(document).ready(Apollo.stage.init);
var console;
