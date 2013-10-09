	$("#teldir_name").keyup(function() {
		var query = $("#teldir_name").val();
		$.getJSON("/components/telephone/telephone_ajax.php?action=getTelNumber&query="+ query, function(json){
			if(json.success == "1") {
				$("#teldir_result").html(json.matches);
			} else {
				$("#teldir_result").html('');
				$("#teldir_name").val('');
			}
		});
	});

	$("#teldir_name").css('width', '280px');

	$('body').click(function() {
		$("#teldir_result").html('');
		$("#teldir_name").val('');
	});