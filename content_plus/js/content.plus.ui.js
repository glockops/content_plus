$(document).ready(function(e) {
	
	/* Run on first load */
	configPanes();
	configLink();
	
	
	/**
	Updates link configuration when link type is changed
	*/
	$("#link_type").change(function() {
		configLink();
	});
	/**
	Updates pane configuration when image/quote checkbox changed
	*/
	$(".enable_pane").click(function() {
		var check = $(this).attr("checked");
		$(".enable_pane").attr("checked",false).parents(".options").removeClass('options_active');
		if(check) {
			$(this).attr("checked",true);
		}
		configPanes();
	});
	
	
	
});

/**
	Configures visibility of link options
*/
function configLink() {
	var linkIs = $("#link_type").val();
	$(".link_option").hide();
	switch(linkIs) {
		case "page":
			$("#lkpage").show();
			$("#lkoptions").show();
			break;
		case "url":
			$("#lkurl").show();
			$("#lkoptions").show();
			break;
		default:
			// Do nothing
			break;
	}
}

/**
	Configures visibility of panes
*/
function configPanes() {
	$(".plus_options").hide();
	var image = $("#enable_image");
	var quote = $("#enable_quote");
	$(".enable_pane:checked").parents(".options").addClass('options_active');
	if((image).attr("checked")||(quote).attr("checked")) {
		
		if(image.attr("checked")) {
			$(".image_options, .layout_options").show();
		} else {
			$(".quote_options, .layout_options").show();
		}
		
	} 
}