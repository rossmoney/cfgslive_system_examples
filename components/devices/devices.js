$('.deletedevice').on( 'click', function() {
	var device_id = $(this).attr('data-deviceid');
	bootbox.confirm("Are you sure you want to delete this device?", function(agree) {
		if (agree) {
			$.getJSON("/components/devices/device_ajax.php?action=deletedevice&device_id="+device_id, function(json){
				if(json.success == "1") {
					console.log(device_id);
					$('#device_' + device_id).remove();
					bootbox.alert("Device Deleted!");
				} else {
					bootbox.alert(json.errormessage);
				}
			});
		} else {
			return false;
		}
	});
});