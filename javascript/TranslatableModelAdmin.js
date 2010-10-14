(function($) {
$(document).ready(function() {

	$('#Form_EditForm_createtranslation').live('click', function() {
		var url = $('#Form_EditForm').attr('action') 
			+ '?action_createtranslation=1'
			+ '&newlang=' + $('#Form_EditForm_NewTransLang').val()
			+ '&SecurityID=' + $('#Form_EditForm_SecurityID').val()
			
		$('#ModelAdminPanel').fn('loadForm', url);
		
		return false;
	});


	var _TRANSLATING_LANG = null;
	$('#LangSelector').change(function() {
		document.location = SiteTreeHandlers.controller_url + '?locale=' + this.value;
	});
})
})(jQuery);