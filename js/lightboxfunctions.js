$jq1(document).ready(function() {
		$jq1("a[rel=light_box]").fancybox({
			'transitionIn'		: 'none',
			'transitionOut'		: 'none',
			'titlePosition' 	: 'inside',
			'titleFormat'		: function(title, currentArray, currentIndex, currentOpts) {
				return '<span id="fancybox-title-over">Image ' + (currentIndex + 1) + ' / ' + currentArray.length + (title.length ? ' &nbsp; ' + title : '') + ' <a href="javascript:setMainImage(\''+currentArray[currentIndex]+'\');" style="color:#fff;">Set as main image</a></span>';
			}
		});
	
		$jq1("a[rel=light_box_2]").fancybox({
			'transitionIn'		: 'none',
			'transitionOut'		: 'none',
			'titlePosition' 	: 'inside',
			'titleFormat'		: function(title, currentArray, currentIndex, currentOpts) {
				//return '<span id="fancybox-title-over">Image ' + (currentIndex + 1) + ' / ' + currentArray.length + (title.length ? ' &nbsp; ' + title : '') + ' </span>';
				//$("#fancybox-img").attr("title",title);
				return '<span id="fancybox-title-over">' + title + ' </span>';
			}
		});
});