jQuery(document).ready( function($) {

	jQuery.fn.setIndicatorHeights = function() {
		var display_height = parseInt( $(window).height() );
		var page_height = parseInt( $(document).height() );
		var comments_top = parseInt( $('#comments').offset().top );

		var post_length_ratio = ( comments_top / page_height ) * 100;
		var post_length = Math.round( display_height * ( post_length_ratio / 100 ) );
		var comments_length = display_height - post_length;

		$('#post_length_indicator').height(display_height);
		$('#post_length_indicator .post_length').height(post_length);
		$('#post_length_indicator .comments_length').height(comments_length);
	}

	jQuery.fn.setIndicatorHeights();

	$(window).resize(function () {
	   jQuery.fn.setIndicatorHeights();
	});
});