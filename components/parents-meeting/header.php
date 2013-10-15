<?php

if(isset($_POST['bookNow']))
{
	$timeSlot = $_POST['timeSlot'];
	$focus = mysql_real_escape_string($_POST['special']);

	if($_POST['sender'] == 'student')
	{
		$teacher_id = $_POST['name'];

		$subject_id = $_POST['subject_id'];

		$teachers_on_timeslot = query("SELECT u_teacher.id as Teacher_ID
			 FROM users u_teacher
			 inner join parents_meeting_booking pmb ON pmb.teacher_id = u_teacher.id
			 WHERE pmb.timeslot = " . $_GET['timeslot'] . " AND pmb.student_id != " . $userInfo['id']);

		$booked_up = FALSE;
		while($teacher_timeslot = mysql_fetch_assoc($teachers_on_timeslot))
		{
			if($teacher_timeslot['Teacher_ID'] == $teacher_id)
			{
				$booked_up = TRUE;
			}
		}

		$userid = $userInfo['id'];

		if($booked_up)
		{
			$_SESSION['errorMessage']  = "Timeslot has been booked, you were too late! Try another available slot.";
			unset($_POST);
 			if (!empty($_SERVER['HTTP_REFERER'])){
    			header("Location: ".$_SERVER['HTTP_REFERER']);}
		}
	}
	if($_POST['sender'] == 'teacher')
	{
		$students_on_timeslot = query("SELECT u.id as StuID
			FROM users u
			inner join parents_meeting_booking pmb ON pmb.student_id = u.id
			WHERE pmb.timeslot = " . @$_POST['timeslot'] . " AND pmb.teacher_id != " . $userInfo['id']);

		$userid = $_POST['name'];
		$teacher_id = $userInfo['id'];

		$subject_id = query("SELECT s.id as subject_id FROM subjects s
			INNER JOIN users_to_subjects us ON us.subject_id = s.id WHERE us.user_id = $teacher_id");

		$subject_id = mysql_fetch_assoc($subject_id);
		$subject_id = $subject_id['subject_id'];

		$booked_up = FALSE;
		while($student_timeslot = mysql_fetch_assoc($students_on_timeslot))
		{
			if($student_timeslot['StuID'] == $userid)
			{
				$booked_up = TRUE;
			}
		}

		if($booked_up)
		{
			$_SESSION['errorMessage'] = "Timeslot has already been booked, you were too late! Try another available slot.";
			unset($_POST);
 			if (!empty($_SERVER['HTTP_REFERER'])){
    			header("Location: ".$_SERVER['HTTP_REFERER']);}
		}

		if($userid == "Break")
		{
			query("INSERT INTO `parents_meeting_booking` (
			`student_id` ,
			`teacher_id` ,
			`timeslot` ,
			`breakslot` ,
			`focus`
			)
			VALUES (
			  '$teacher_id',  '$teacher_id',  '$timeSlot',  '2',  '$focus'
			);
			");
		}
	}

	if($userid > 0 && $teacher_id > 0)
	{
		if($userid != "Break")
		{
			$checkBreakBefore = query("SELECT id FROM parents_meeting_booking
				WHERE breakslot = 1 AND student_id = $userid AND timeslot = " .  ($timeSlot - 1));

			if(@mysql_num_rows($checkBreakBefore) == 0)
			{

			query("INSERT INTO `parents_meeting_booking` (
		`student_id` ,
		`teacher_id` ,
		`timeslot` ,
		`breakslot`,
		`subject_id`
		)
		VALUES (
		  '$userid',  '$teacher_id',  '" . ($timeSlot - 1) . "',  '1', '$subject_id'
		);
		");
			}

			query("INSERT INTO `parents_meeting_booking` (
		`student_id` ,
		`teacher_id` ,
		`timeslot` ,
		`breakslot` ,
		`focus`,
		`subject_id`
		)
		VALUES (
		  '$userid',  '$teacher_id',  '$timeSlot',  '0',  '$focus', '$subject_id'
		);
		");

			$checkBreakAfter = query("SELECT id FROM parents_meeting_booking
				WHERE breakslot = 1 AND student_id = $userid  AND timeslot = " .  ($timeSlot + 1));
			if(@mysql_num_rows($checkBreakAfter) == 0)
			{

			query("INSERT INTO `parents_meeting_booking` (
		`student_id` ,
		`teacher_id` ,
		`timeslot` ,
		`breakslot`,
		`subject_id`
		)
		VALUES (
		  '$userid',  '$teacher_id',  '" . ($timeSlot + 1) . "',  '1', '$subject_id'
		);
		");
			}

		}
	}

	unset($_POST);
 	if (!empty($_SERVER['HTTP_REFERER'])){
    header("Location: ".$_SERVER['HTTP_REFERER']);}
}


?>