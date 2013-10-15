<?
include $_SERVER['DOCUMENT_ROOT']. "/includes/ajax_include.php";

if(isset($_GET['action']))
{
	if($_GET['action'] == 'getTeacherTimetable')
	{
		?>
		<table class="table">
			<?php $user_details = query("SELECT u.firstname, u.lastname FROM users u WHERE u.id = '" . $_GET['teacher_id'] . "'");
			$user_details = mysql_fetch_assoc($user_details);
			?>
			<caption>Below is the booking timetable for <strong><?php echo $user_details['firstname']; ?> <?php echo $user_details['lastname']; ?></strong></caption>
			<thead>
				<tr>
					<th>Time</th>
					<th>Available</th>
				</tr>
			</thead>
			<tbody>
					<?php

					$bookings_teacher = query("SELECT DISTINCT pmb.id, pmb.student_id, pmb.teacher_id, pmb.timeslot, pmb.breakslot, pmb.focus
					, pmb.timestamp, u_student.firstname as Student_Firstname, u_student.lastname as Student_Lastname, u_teacher.firstname as Teacher_Firstname,
					u_teacher.lastname as Teacher_Lastname
					FROM parents_meeting_booking pmb
					inner join users u_teacher ON u_teacher.id = pmb.teacher_id
					inner join users u_student ON u_student.id = pmb.student_id
					inner join users_to_subjects us ON us.user_id = u_teacher.id
					inner join jobs j ON j.id = us.job_id
					WHERE pmb.teacher_id = '" . $_GET['teacher_id'] . "' AND j.id IN (5, 6, 8) ORDER BY pmb.timeslot DESC"
					);

					$printable_timeslots = array(
"4:15","4:20","4:25","4:30","4:35","4:40","4:45","4:50","4:55","5:00","5:05","5:10","5:15","5:20","5:25","5:30","5:35","5:40","5:45","5:50","5:55","6:00","6:05","6:10","6:15","6:20","6:25","6:30","6:35","6:40","6:45","6:50","6:55"
					);

						for($i = 0; $i < 33; $i++)
						{
							$filled = FALSE;
							@mysql_data_seek($bookings_teacher, 0);
							while($booking = mysql_fetch_assoc($bookings_teacher))
							{
								if( $booking['timeslot'] == $i)
								{
									if( $booking['student_id'] > 0 && $booking['breakslot'] == 0 || $booking['breakslot'] == 2 )
									{
										echo '<tr>';
										echo '<td>' . $printable_timeslots[$i] . 'pm</td>';
										echo '<td style="color: red;">NO</td>';
										echo '</tr>';
										$filled = TRUE;
									}
								}
							}
							if(!$filled)
							{
									echo '<tr class="success">';
									echo '<td>' . $printable_timeslots[$i] . 'pm</td>';
									echo '<td style="color: green; text-align: left;">YES</td>';
									echo '</tr>';
							}
						}

					?>
			</tbody>
		</table>
		<?php
	}
	if($_GET['action'] == 'cancelBooking')
	{
		ob_start();
		$booking_timeslot = query("SELECT student_id, teacher_id, timeslot FROM parents_meeting_booking WHERE id = " . $_GET['booking_id']);
		$booking_timeslot = mysql_fetch_assoc($booking_timeslot);
		$booking_above = query("SELECT id, breakslot FROM parents_meeting_booking WHERE timeslot = '" . ($booking_timeslot['timeslot'] - 2) .
			"' AND student_id = '" .$booking_timeslot['student_id']. "'") ;
		$breakslot = @mysql_fetch_assoc($booking_above);
		if(@mysql_num_rows($booking_above) == 0 || $breakslot['breakslot'] == 1)
		{
			$slot_above = query("SELECT id FROM parents_meeting_booking WHERE timeslot = '" . ($booking_timeslot['timeslot'] - 1) .
			"' AND student_id = '" .$booking_timeslot['student_id']. "'") ;
			if(mysql_num_rows($slot_above) > 0)
			{
				$slot_above = mysql_fetch_assoc($slot_above);
				query("DELETE FROM parents_meeting_booking WHERE id = '" . $slot_above['id'] . "'");
			}
		}
		$booking_below = query("SELECT id, breakslot FROM parents_meeting_booking WHERE timeslot = '" . ($booking_timeslot['timeslot'] + 2) .
			"' AND student_id = '" .$booking_timeslot['student_id']. "'") ;
		$breakslot = @mysql_fetch_assoc($booking_below);
		if(@mysql_num_rows($booking_below) == 0 || $breakslot['breakslot'] == 1)
		{
			$slot_below = query("SELECT id FROM parents_meeting_booking WHERE timeslot = '" . ($booking_timeslot['timeslot'] + 1) .
			"' AND student_id = '" .$booking_timeslot['student_id']. "'") ;
			if(mysql_num_rows($slot_below) > 0)
			{
				$slot_below = mysql_fetch_assoc($slot_below);
				query("DELETE FROM parents_meeting_booking WHERE id = '" . $slot_below['id'] . "'");
			}
		}
		query("DELETE FROM parents_meeting_booking WHERE id = '" . $_GET['booking_id'] . "'");
		$error = array ("success" => "1", "errormessage" => ob_get_clean());
		header('Content-Type: application/json');
		echo json_encode($error);
	}
	if($_GET['action'] == 'cancelBreak')
	{
		ob_start();
		query("DELETE FROM parents_meeting_booking WHERE id = '" . $_GET['booking_id'] . "'");
		$errormessage = ob_get_clean();
		if($errormessage)
		{
			$error = array ("success" => "0", "errormessage" => $errormessage);
		} else {
			$error = array ("success" => "1");
		}
		header('Content-Type: application/json');
		echo json_encode($error);
	}
	if($_GET['action'] == 'allowEA')
	{
		ob_start();
		query("INSERT INTO `parents_meeting_early_access` (
		`student_id` ,
		`teacher_id`
		)
		VALUES (
			 '" . $_GET['student_id'] . "',  '" . $_GET['teacher_id'] . "'
		);
		");
		$error = array ("success" => "1", "errormessage" => ob_get_clean());
		header('Content-Type: application/json');
		echo json_encode($error);
	}
	if($_GET['action'] == 'deleteEA')
	{
		ob_start();
		query("DELETE FROM `parents_meeting_early_access` WHERE id = " . $_GET['ea_id']);
		$error = array ("success" => "1", "errormessage" => ob_get_clean());
		header('Content-Type: application/json');
		echo json_encode($error);
	}
	if($_GET['action'] == 'teachersAvailableForSlot')
	{
		$teachers_on_timeslot = query("SELECT pmb.teacher_id as Teacher_ID
			 FROM parents_meeting_booking pmb
			 WHERE pmb.timeslot = " . $_GET['timeslot'] . " AND pmb.breakslot IN (0, 2)");

		if($parentsMeetingMainPeriod)
		{
			$teachers_available = query("SELECT DISTINCT u_teacher.id as Teacher_ID, u_teacher.firstname as Teacher_Firstname, u_teacher.lastname as Teacher_Lastname, s.subject as Subject, us.subject_id as SubID
			 FROM users u_teacher
			 inner join users_to_subjects us ON us.user_id = u_teacher.id
			 inner join jobs j ON j.id = us.job_id
			 inner join subjects s ON s.id = us.subject_id
			 WHERE j.id IN (5, 6, 8) ORDER BY Teacher_Lastname ASC");
		} else {
			$teachers_available = query("SELECT DISTINCT u_teacher.id as Teacher_ID, u_teacher.firstname as Teacher_Firstname, u_teacher.lastname as Teacher_Lastname, s.subject as Subject, us.subject_id as SubID
			FROM parents_meeting_early_access pm_ea
			INNER JOIN users u_teacher ON u_teacher.id = pm_ea.teacher_id
			inner join users_to_subjects us ON us.user_id = u_teacher.id
			inner join jobs j ON j.id = us.job_id
			inner join subjects s ON s.id = us.subject_id
			WHERE pm_ea.student_id = '" . $userInfo['id'] . "' AND j.id IN (5, 6, 8) ORDER BY Teacher_Lastname ASC");
		}

		@mysql_data_seek($teachers_available, 0);
		$teacher_list = array();
		while($teacher = mysql_fetch_assoc($teachers_available))
		{
			@mysql_data_seek($teachers_on_timeslot, 0);
			$remove_teacher = FALSE;
			if(@mysql_num_rows($teachers_on_timeslot) > 0)
			{
				while($teacher_timeslot = @mysql_fetch_assoc($teachers_on_timeslot))
				{
					if($teacher['Teacher_ID'] ==  $teacher_timeslot['Teacher_ID'])
					{
						$remove_teacher = TRUE;
					}
				}
			}
			if(!$remove_teacher)
			{
				$teacher_list[] = $teacher;
			}
		}

		$output = "<option value=\"\" disabled=\"true\" selected=\"true\">Select a Teacher</option>";
		foreach($teacher_list as $teacher)
		{
			$output .= "<option data-subject=\"" . $teacher['SubID'] . "\" value=\"" . $teacher['Teacher_ID'] . "\">" . $teacher['Teacher_Firstname'] . " " . $teacher['Teacher_Lastname'] . " (" . $teacher['Subject'] . ")</option>";
		}
		echo $output;
	}
	if($_GET['action'] == 'studentsAvailableForSlot')
	{
		echo '<option value="Break">Allocate Break</option>';

		$query = "SELECT pmb.student_id as StuID
			FROM parents_meeting_booking pmb
			WHERE pmb.timeslot = " . $_GET['timeslot'];
		//echo $query;
		$students_on_timeslot = query($query);
		//echo mysql_num_rows($students_on_timeslot);

		$students_available = query("SELECT u.id as StuID, u.firstname, u.lastname, sd.reg as Form
			FROM sims_data sd
			INNER join users u ON u.admission_no = sd.adno
			WHERE u.year = " . $parentsMeetingCurrentYear. " ORDER BY u.lastname ASC");

		@mysql_data_seek($students_available, 0);
		$student_list = array();
		$prev = "";
		while($student = mysql_fetch_assoc($students_available))
		{
			@mysql_data_seek($students_on_timeslot, 0);
			$remove_student = FALSE;
			while($student_timeslot = mysql_fetch_assoc($students_on_timeslot))
			{
				if($student['StuID'] ==  $student_timeslot['StuID'])
				{
					$remove_student = TRUE;
				}
			}
			if(!$remove_student)
			{
				if(@$prev['StuID'] != @$student['StuID'])
				{
					$student_list[] = $student;
				}
			}
			$prev = $student;
		}

		$output = "";
		foreach($student_list as $student)
		{
			$output .=  "<option value=\"" . $student['StuID'] . "\">" . $student['firstname'] . " " . $student['lastname'] . " (" . $student['Form'] . ") </option>";
		}
		echo $output;
	}
}

?>