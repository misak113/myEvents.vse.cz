$('.nav-header ul').hide();

$(document).ready(function(){
	$('.filter .nav-header').hover(function(){
			$(this).find('ul').stop().slideDown();
		}, function(){
			$(this).find('ul').stop().slideUp();
	});
});