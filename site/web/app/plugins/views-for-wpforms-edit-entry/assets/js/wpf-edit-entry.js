(function($){
	$(function(){
		$(document).on('click', '.wpf-delete-file', function(e){
			e.preventDefault();
			var conf = confirm('Are you sure you want to remove this file?')
			if( conf ){
				$(this).prev().val('true');
				$(this).parent('.file-entry').hide();
			}
		})


	})
})(jQuery)