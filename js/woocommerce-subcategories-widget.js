jQuery(document).ready(function($) {

	$('select.woosubcats').on('change', function(){
		var option = $('option:selected', this).attr('data-url');
		// console.log(option);
		window.location = option;
	});
});