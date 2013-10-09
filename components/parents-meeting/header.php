<?php

if(isset($_POST['bookNow']))
{
	$timeSlot = $_POST['timeSlot'];
	$focus = mysql_real_escape_string($_POST['special']);

	if($_POST['sender'] == 'student')
	{
		$teacher_id = $_POST['name'];
		$userid = $userInfo['id'];
	}
	if($_POST['sender'] == 'teacher')
	{
		$userid = $_POST['name'];
		$teacher_id = $userInfo['id'];
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
	`breakslot`
	)
	VALUES (
	  '$userid',  '$teacher_id',  '" . ($timeSlot - 1) . "',  '1'
	);
	");
		}

		query("INSERT INTO `parents_meeting_booking` (
	`student_id` ,
	`teacher_id` ,
	`timeslot` ,
	`breakslot` ,
	`focus`
	)
	VALUES (
	  '$userid',  '$teacher_id',  '$timeSlot',  '0',  '$focus'
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
	`breakslot`
	)
	VALUES (
	  '$userid',  '$teacher_id',  '" . ($timeSlot + 1) . "',  '1'
	);
	");
		}

	}
	unset($_POST);
 	if (!empty($_SERVER['HTTP_REFERER'])){
    header("Location: ".$_SERVER['HTTP_REFERER']);}
}


?>