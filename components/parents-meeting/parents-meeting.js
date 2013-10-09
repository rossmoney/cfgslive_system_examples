$(document).ready(function(){
	$(".bookButton").click(function(){
		$('#bookSlot').modal('show');
		$('#timeSlot').val($(this).attr('data-timeslot'));
		if($('#sender').val() == 'student')
		{
			$("#teacherName").load("/components/parents-meeting/pm_ajax.php?action=teachersAvailableForSlot&timeslot=" + $(this).attr('data-timeslot'));
		} else {
			$("#studentName").load("/components/parents-meeting/pm_ajax.php?action=studentsAvailableForSlot&timeslot=" + $(this).attr('data-timeslot'));
		}
	});
	$("#name").change(function() {
		var tid = $("#name").val();
		$('#teacherTimetable').load("/components/parents-meeting/pm_ajax.php?action=getTeacherTimetable&teacher_id="+ tid);
	});
	$('.btnCancelBooking').click(function(){
		var $$ = $(this);
		$.getJSON("/components/parents-meeting/pm_ajax.php?action=cancelBooking&booking_id="+ $(this).attr('data-bookingid'), function(json){
			if(json.success == "1") {
				location.reload(true);
			} else {
				bootbox.alert(json.errormessage);
				location.reload(true);
			}
		});
	});
	$('.btnCancelBreak').click(function(){
		var $$ = $(this);
		$.getJSON("/components/parents-meeting/pm_ajax.php?action=cancelBreak&booking_id="+ $(this).attr('data-bookingid'), function(json){
			if(json.success == "1") {
				location.reload(true);
			} else {
				bootbox.alert(json.errormessage);
				location.reload(true);
			}
		});
	});
	$("#earlyaccess-uid").change(function() {
		var stuID = $("#earlyaccess-uid").val();
		var teacherID = $(this).attr('data-tid');
		$.getJSON("/components/parents-meeting/pm_ajax.php?action=allowEA&student_id="+ stuID + '&teacher_id=' + teacherID, function(json){
			if(json.success == "1") {
				location.reload(true);
			} else {
				bootbox.alert(json.errormessage);
				location.reload(true);
			}
		});
	});
	$(".eaDelete").click(function() {
		var eaID = $(this).attr('data-eaID');
		$.getJSON("/components/parents-meeting/pm_ajax.php?action=deleteEA&ea_id="+ eaID, function(json){
			if(json.success == "1") {
				location.reload(true);
			} else {
				bootbox.alert(json.errormessage);
				location.reload(true);
			}
		});
	});
});