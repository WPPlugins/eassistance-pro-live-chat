jQuery(document).ready( function() {
	jQuery("input[name=eassistancepro_code_location]").click( function() {
		var _id = jQuery(this).attr("id");
		
		if(_id == "eassistancepro_location_auto") {
			jQuery("#codesnipt").hide();
		}
		
		if(_id == "eassistancepro_location_manually") {
			jQuery("#codesnipt").show();
		}
	});
});