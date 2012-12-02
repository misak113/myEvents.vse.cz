/**
 * Hiding filter on mouse over
 */
jQuery(document).ready(function($){
	/* @todo nejak me to sere :D
	$('.nav-header ul').hide();
	$('.filter .nav-header').hover(function(){
			$(this).find('ul').stop().slideDown();
		}, function(){
			$(this).find('ul').stop().slideUp();
	});*/
});



/**
 * Filtering events
 */
jQuery(document).ready(function ($) {

	var filterItems = $('.filter-item .filter-input');
	var filterForm = filterItems.closest('form');

	filterItems.bind('change', function (ev) {
		filterForm.trigger('submit');
	});


});