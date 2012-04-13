// long live the crockford
String.prototype.supplant = function (o) {return this.replace(/{([^{}]*)}/g, function (a, b) { var r = o[b]; return typeof r === 'string' || typeof r === 'number' ? r : a; });};

(function($) {
    /**
    * Variables
    **********/
    // get variables from DOM that wordpress put in via localize script
    var ajaxurl = BPF_params['ajaxurl']
    ,ajaxParams = {
        'action'		: BPF_params['action']
		,'_ajax_nonce'	: BPF_params['_ajax_nonce']
	}

    // the DOM node to put the goods into
    ,$hostNode			= $('#box-public-folder-file-list')

	// HTML Templates
	,DOC_ROW  			=   '<li><a class="box-public-folder-document-link" href="{link}">{title}</a><span class="pubDate">{pubDate}</span></li>'
	,LINK               =   '<li class="chrome"><a class="feed-link" href="{uri}">Go To Community Folder &raquo;</a></li>';

    /**
    *
    **************************************************/
	$.get(ajaxurl, ajaxParams, processFeed);
	
	function processFeed(data) {
		var htmlLink    = extractURI(data['link'])
        ,items          = $.makeArray(data['item'])
		,documents      = ''
        ,html;
		
		$(items).each(function() {
		    documents += makeRow(this);
		});

        html = documents + LINK.supplant({
            'uri': htmlLink
        });

		// if > 0 docs Append widget to document
		if(data['item'].length > 0) {
		    updateView(html);
		}else {
            updateView('No updates to report');
		}
	}

    /**
    * make html for a document
    **************************************************/
    function makeRow(doc) {
        doc['pubDate'] = doc['pubDate']
            .split(' ')
            .slice(1,4)
            .join(' ');

        return DOC_ROW.supplant({
            "link": doc['link'],
            "title": doc['title'],
            "pubDate": doc['pubDate']
        });
    }
    
    /**
    * strip '/rss.xml' from URL, for regular box.com UI link
    **************************************************/
	function extractURI(str) {
        return str.replace(/\/rss\.xml$/, '');;
	}

    /**
    * add html string to the dom
    **************************************************/
	function updateView(html) {
        $hostNode.find('#box-loading > img').fadeOut(100, function() {
            $hostNode.remove('#box-loading');
            $hostNode.append(html);
        });
	}
})(jQuery);