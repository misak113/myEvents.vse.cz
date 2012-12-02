
(function($){


	var metaData = function () {
		var metaData = {
			screenHeight: $(window).height(),
			screenWidth: $(window).width()
		};

		var value = JSON.stringify(metaData);
		$('#meta_data').val(value);
	};

	$(document).ready(metaData);
})(jQuery)