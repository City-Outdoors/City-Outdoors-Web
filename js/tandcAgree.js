
$(document).ready(function() {
	var select = $('#tandcAgree');
	var form = select.parent('form');
	$('form').submit(function() {
		var select = $('#tandcAgree');
		if (select.is(':checked')) {
			return true;
		} else {
			var msg = $('#tandcMustAgree');
			msg.show();			
			return false;
		}
	});
});


