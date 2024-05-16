jQuery(document).ready(function($){
	$( '.post-answer > div' ).on( 'click', function() {
		$(this).siblings().removeClass('active')
		$(this).addClass('active');
	});
	$( '#post-saves' ).on('click', function() {
		var savedArray = [];
		$(".post-approve.active").each(function() {
			savedArray.push( $(this).parent().parent('.post-box').attr('id') )
		});
		
		var declinedArray = [];
		$(".post-decline.active").each(function() {
			declinedArray.push( $(this).parent().parent('.post-box').attr('id') )
		});

		window.location.href = "dashboard?saved=" + savedArray + "&declined=" + declinedArray;
	});
	//get query string to select active/inactive
} );