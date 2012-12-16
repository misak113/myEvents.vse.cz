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

/**
 * Searching events
 */

jQuery(document).ready(function ($){
    
    var searchInput = $('.filter-search');
    searchInput.keyup(function(){
        var q = encodeURIComponent(this.value);
        var url = baseUrl + '/default/event/autocomplete/?q=' + q;
        $.get(url, function(data){
            var json = JSON.parse(data);
            searchInput.autocomplete({
                lookup: json,
                onSelect: function(suggestion){
                    searchInput.closest('form').trigger('submit');
                }
            });
        });
    });
});