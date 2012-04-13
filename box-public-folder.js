// long live the crockford
String.prototype.supplant = function (o) {return this.replace(/{([^{}]*)}/g, function (a, b) { var r = o[b]; return typeof r === 'string' || typeof r === 'number' ? r : a; });};

(function($) {
    // Globals
    var ajaxurl = BPF_params['ajaxurl']
    ,ajaxParams = {
        'action'		: BPF_params['action']
		,'_ajax_nonce'	: BPF_params['_ajax_nonce']
	}
    ,$host 				= $('#box-public-folder')

	// HTML Templates
    ,SECTION_HEADLINE	=   '<h2 class="entry-title">Community Folder Updates</h2>'
	,FEED_LINK			=   '<li class="chrome"><a class="feed-link" href="{uri}">{title}</a></li>'
	,FEED_DOC  			=   '<li><a class="box-public-folder-document-link" href="{link}">{title}</a><span class="pubDate">{pubDate}</span></li>';

	// Process the Feed
	$.get(ajaxurl, ajaxParams, function(data) {
		var htmlLink = data['link'].replace(/\/rss\.xml$/, '');
		var items = $.makeArray(data['item']);
		    

		var html = '<ul id="box-public-folder-file-list">';
        html += FEED_LINK.supplant({ "uri": htmlLink, "title": 'Go To Community Folder &raquo;'});
		console.log(data);
		
		$(items).each(function() {
			var pubDate = this['pubDate'].
				replace(/^[^\W]+\,*/,'').
				replace(/\-*[^\W]+$/, '');
				
			var pubDateArray = this['pubDate'].split(" ");
			pubDateArray = pubDateArray.slice(1,4);
			pubDate = pubDateArray.join(' ');
			html += FEED_DOC.supplant({
				"link": this['link'],
				"title": this['title'],
				"pubDate": pubDate
			});
		});

        html += FEED_LINK.supplant({ "uri": htmlLink, "title": 'Go To Community Folder &raquo;'});
		html += '</ul>';

		// Append HTML to document if > 0 documents
		if(data['item'].length > 0) {
		    
		    $host.find('#box-loading-gif').fadeOut(100, function() {
    			$host.append(html);
		    });
		}
	});
})(jQuery);