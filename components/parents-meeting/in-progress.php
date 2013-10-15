<?php
if((isset($_SESSION['main_access']) || isset($_SESSION['early_access'])) && $userInfo['staff'] == 0)
{
} else {
	die("You do not have access to this page.");
}
?>
<div class="row">
	<div class="span6">
		<table class="table">
			<?php $user_details = query("SELECT u.firstname, u.lastname, sd.reg as Form FROM sims_data sd
			INNER join users u ON u.admission_no = sd.adno WHERE u.id = '" . $userInfo['id'] . "'");
			$user_details = mysql_fetch_assoc($user_details);
			?>
			<caption>Below is the booking timetable for <strong><?php echo $user_details['firstname']; ?> <?php echo $user_details['lastname']; ?> - <?php echo $user_details['Form']; ?></strong></caption>
			<thead>
				<tr>
					<th>Time</th>
					<th>Teacher</th>
					<th>Subject</th>
					<th></th>
				</tr>
			</thead>
			<tbody>
					<?php

					$bookings_student = query("SELECT pmb.id, pmb.student_id, pmb.teacher_id, pmb.timeslot, pmb.breakslot, pmb.focus
					, pmb.timestamp, u_teacher.firstname as Teacher_Firstname,
					u_teacher.lastname as Teacher_Lastname, s.subject as Teacher_Subject
					FROM parents_meeting_booking pmb
					inner join users u_teacher ON u_teacher.id = pmb.teacher_id
					inner join users u_student ON u_student.id = pmb.student_id
					inner join users_to_subjects us ON us.user_id = u_teacher.id
					inner join jobs j ON j.id = us.job_id
					inner join subjects s ON s.id = pmb.subject_id
					WHERE pmb.student_id = '" . $userInfo['id'] . "' AND j.id IN (5, 6, 8) ORDER BY pmb.timeslot DESC"
					);

					$printable_timeslots = array(
"4:15","4:20","4:25","4:30","4:35","4:40","4:45","4:50","4:55","5:00","5:05","5:10","5:15","5:20","5:25","5:30","5:35","5:40","5:45","5:50","5:55","6:00","6:05","6:10","6:15","6:20","6:25","6:30","6:35","6:40","6:45","6:50","6:55"
					);

						for($i = 0; $i < 33; $i++)
						{
							$filled = FALSE;
							@mysql_data_seek($bookings_student, 0);
							while($booking = mysql_fetch_assoc($bookings_student))
							{
								if( $booking['timeslot'] == $i)
								{
									if( $booking['breakslot'] == 0)
									{
										echo '<tr>';
										//echo '<td>' . $i . '</td>';
										echo '<td>' . $printable_timeslots[$i] . 'pm</td>';
										echo '<td>' . $booking['Teacher_Firstname'] . ' ' . $booking['Teacher_Lastname'] . '</td>';
										echo '<td>' . $booking['Teacher_Subject'] . '</td>';
										echo '<td><a class="btnCancelBooking btn btn-danger" data-bookingid="' . $booking['id']. '" >Cancel</a></td>';
										echo '</tr>';
									}
									if( $booking['breakslot'] == 1)
									{
									?>
									<tr class="warning">
										<td><?php echo $printable_timeslots[$i]; ?>pm</td>
										<td>BREAK</td>
										<td></td>
										<td></td>
									</tr>
									<?php
									}
									$filled = TRUE;
								}
							}
							if(!$filled)
							{
								if( is_null($booking['student_id']) )
								{
									echo '<tr class="success">';
									//echo '<td>' . $i . '</td>';
									echo '<td>' . $printable_timeslots[$i] . 'pm</td>';
									echo '<td>FREE</td>';
									echo '<td></td>';
									echo '<td><a class="bookButton btn btn-primary" href="#" data-timeslot="' . $i . '">Book</a></td>';
									echo '</tr>';
								}
							}
						}

					?>
			</tbody>
		</table>
	</div>
	<?php
	if(isset($_SESSION['early_access']) && !$parentsMeetingMainPeriod)
	{
		$teachers_list = query("SELECT u_teacher.id as Teacher_ID, u_teacher.firstname as Teacher_Firstname, u_teacher.lastname as Teacher_Lastname, sub.subject as Subject
			FROM parents_meeting_early_access pm_ea
			INNER JOIN users u_teacher ON u_teacher.id = pm_ea.teacher_id
			inner join users_to_subjects us ON us.user_id = u_teacher.id
			inner join jobs j ON j.id = us.job_id
			inner join subjects sub ON sub.id = us.subject_id
			WHERE pm_ea.student_id = '" . $userInfo['id'] . "' AND j.id IN (5, 6, 8) ORDER BY Teacher_Lastname ASC");
	} else {
		$teachers_list = query("SELECT u_teacher.id as Teacher_ID, u_teacher.firstname as Teacher_Firstname, u_teacher.lastname as Teacher_Lastname, sub.subject as Subject
		 	FROM users u_teacher
		 	inner join users_to_subjects us ON us.user_id = u_teacher.id
			inner join jobs j ON j.id = us.job_id
			inner join subjects sub ON sub.id = us.subject_id
		 	WHERE j.id IN (5, 6, 8) ORDER BY Teacher_Lastname ASC");
	}

	?>
	<a class="yourTeachers btn btn-primary" href="#">View Your Teachers</a>
	<div class="span6">
		View the timetable for
		<select id="name" name="name">
			<option value="" disabled="true" selected="true">Select a Teacher</option>
			<?php
			while($teacher = mysql_fetch_assoc($teachers_list))
			{
				echo "<option value=\"" . $teacher['Teacher_ID'] . "\">" . $teacher['Teacher_Firstname'] . " " . $teacher['Teacher_Lastname'] . " (" . $teacher['Subject']. ")</option>";
			}
			?>
		</select>
		<div id="teacherTimetable">

		</div>
	</div>
	<div class="modal hide fade" id="yourTeacherModal">
		<div class="modal-header">
			<button type="button" class="close" data-dismiss="modal">×</button>
			<h3>Your Teachers</h3>
		</div>
		<div class="modal-body">
		<?php
		$teachers = query("SELECT DISTINCT u_teacher.id as Teacher_ID, u_teacher.firstname as Teacher_Firstname, u_teacher.lastname as Teacher_Lastname,
u_student.id as Student_ID, u_student.firstname as Student_Firstname, u_student.lastname as Student_Lastname, sd.reg as Form, s.subject as Subject
FROM users u_teacher
inner join users u_student
inner join sims_data sd ON sd.adno = u_student.admission_no
inner join sims_students_to_classes sstc ON sstc.adno = u_student.admission_no
inner join sims_teachers_to_classes sttc ON sttc.initials = u_teacher.initials
inner join users_to_subjects us ON us.user_id = u_teacher.id
inner join jobs j ON j.id = us.job_id
inner join subjects s ON s.id = us.subject_id
WHERE u_student.id = " . $userInfo['id']. " AND u_teacher.staff = 1 AND j.id IN (5, 6, 8) AND sstc.class = sttc.class");

		while($teacher = mysql_fetch_assoc($teachers))
		{
			echo '<p>' . $teacher['Teacher_Firstname'] . " " . $teacher['Teacher_Lastname']. '(' . $teacher['Subject'] . ')</p>';
		}

		?>
		</div>
	</div>
	<div id="bookSlot" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
	<form class="form-horizontal" action="/parents-meeting/book/" method="post">
		<div class="modal-header" style="color: #000">
			<h3>Please choose the teacher you would like to see for this slot.</h3>
		</div>
		<div class="modal-body">
			<p>Only available teachers will be displayed</p>
			<div class="control-group" style="color: #000">
				<label class="control-label" for="special">Teacher</label>
				<div class="controls">
					<select id="teacherName" name="name">
					</select>
				</div>
				<input id="teacherSubject" name="subject_id" type="hidden" value="" />
				<input id="timeSlot" name="timeSlot" type="hidden" value="" />
				<input id="sender" name="sender" type="hidden" value="student" />
				<label class="control-label" for="special">Note<br><small>Is there something specific you would like us to focus on?</small></label>
				<div class="controls">
					<textarea rows="5" name="special" style="width: 300px"></textarea>
				</div>
			</div>
		</div>

		<div class="modal-footer">
			<input type="submit" value="Book" name="bookNow" class="btn btn-primary" />
		</div>
	</form>
	</div>
</div>