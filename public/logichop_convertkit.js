jQuery(document).ready(function($) {
	
	logichop_remove_ckdata();
    
	function logichop_remove_ckdata () { 
		var query = window.location.search.substring(1);
        
        if (query) {		
			var qs = clean = false;
	        var vars = query.split('&');
	        var url = window.location.href;
        	var clean_url = url.substring(0, url.indexOf('?'));
        	var title = document.getElementsByTagName('title')[0].innerHTML;
        	
        	for (var i = 0; i < vars.length; i++) {
        	    var pair = vars[i].split('=');
            
            	if (pair[0] == 'ck_subscriber_id'){
                	clean = true
        		} else {
        			if (!qs) {
        				qs = true;
        				clean_url += '?' + vars[i];
        			} else {
        				clean_url += '&' + vars[i];
        			}
        		}
            }
            
            if (clean) {
            	window.history.pushState( null, title, clean_url );
            }
        }
    }
});
