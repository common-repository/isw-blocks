jQuery(document).ready(function($){

	$('*[data-isw-tooltip]').click(function(){

		$('.wp-pointer').fadeOut(100);

		var content = '<h3>'+ $(this).attr('title') +'</h3>';
		var tooltips = $(this).data('isw-tooltip');
		$.each( tooltips, function(index, tooltip) {
			content += '<p>'+ tooltip +'</p>';
		});
		
		$(this).pointer({
			content: content,
			position: {
				edge: 'right',
				align: 'left',
				offset: '-5 0'
			}
		}).pointer('open');

	});

});