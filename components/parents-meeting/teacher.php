<?php
log_include(__FILE__);
// Check that this file is included
if(!$config['included']) die("This file may only be accessed as an include.");
if(isset($loggedin) && $loggedin == true)
{
	if($userInfo['staff'])
	{
		?>
		<div class="page-header">
			<h2>Teacher View</h2>
		</div>
		<?php
		$result = query("SELECT u.id, u.firstname, u.lastname, sd.reg
			FROM sims_data sd
			INNER join users u ON u.admission_no = sd.adno
			LEFT JOIN parents_meeting_early_access pm_ea ON pm_ea.student_id = u.id
			WHERE u.id != 0 AND u.student = 1 AND pm_ea.id IS NULL ORDER BY u.lastname ASC");

		while($row = mysql_fetch_object($result)) {
			$students[$row->id] = array($row->firstname." ".$row->lastname, $row->reg);
		}
?>
		<div class="row">
			<div class="span6">
				<label for="earlyaccess-username">Allow early access to: </label>
				<select tabindex=1 id="earlyaccess-uid" data-tid="<?php echo $userInfo['id']; ?>">
					<option value="-1">Choose a user</option>
					<optgroup label='Students'>
						<? foreach ($students as $key => $value) { ?>
						<option value="<?= $key ?>"><?= $value[0] ?> (<?= $value[1] ?>)</option>
						<? } ?>
					</optgroup>
					<?php if($userInfo['slam'] > 1) { ?>
					<optgroup label='Staff'>
						<? foreach ($staff as $key => $value) { ?>
						<option value="<?= $key ?>"><?= $value ?></option>
						<? } ?>
					</optgroup>
					<?php } ?>
				</select>
				<table class="table">
					<?php $student_details = query("SELECT u.id as StuID, u.firstname, u.lastname, sd.reg as Form, pm_ea.id as EarlyAccessID
					FROM sims_data sd
					INNER join users u ON u.admission_no = sd.adno
					INNER JOIN parents_meeting_early_access pm_ea ON pm_ea.student_id = u.id
					WHERE pm_ea.teacher_id = " . $userInfo['id'] . " ORDER BY u.lastname ASC");
					?>
					<caption><h4>Students with Early Access</h4></caption>
					<thead>
						<tr>
							<th></th>
							<th></th>
							<th></th>
						</tr>
					</thead>
					<tbody>
						<?php while($student = mysql_fetch_assoc($student_details)) { ?>
						<tr>
							<td><?php displayUserPhoto($student['StuID']); ?></td>
							<td><?php echo $student['firstname'] . " " . $student['lastname']; ?> - <?php echo $student['Form']; ?></td>
							<td><span class='btn btn-danger eaDelete' data-eaID='<?= $student['EarlyAccessID']; ?>'>Delete</span></td>
						</tr>
						<?php } ?>
					</tbody>
				</table>
			</div>
			<div class="span6">
				<table class="table">
					<h2>Your Timetable</h2>
					<thead>
						<tr>
							<th>Time</th>
							<th>Student</th>
							<th>Subject</th>
							<th>Actions</th>
						</tr>
					</thead>
					<tbody>
							<?php

							$bookings_teacher = query("SELECT pmb.id, pmb.student_id, pmb.teacher_id, pmb.timeslot, pmb.breakslot, pmb.focus
							, pmb.timestamp, u_student.firstname as Student_Firstname, u_student.lastname as Student_Lastname, s.subject as Teacher_Subject
							FROM parents_meeting_booking pmb
							inner join users u_teacher ON u_teacher.id = pmb.teacher_id
							inner join users u_student ON u_student.id = pmb.student_id
							inner join users_to_subjects us ON us.user_id = u_teacher.id
							inner join jobs j ON j.id = us.job_id
							inner join subjects s ON s.id = us.subject_id
							WHERE pmb.teacher_id = '" . $userInfo['id'] . "' AND j.name = 'Teacher' ORDER BY pmb.timeslot DESC"
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
											if( $booking['breakslot'] == 0 )
											{
												echo '<tr>';
												//echo '<td>' . $i . '</td>';
												echo '<td>' . $printable_timeslots[$i] . 'pm</td>';
												echo '<td>' . $booking['Student_Firstname'] . ' ' . $booking['Student_Lastname'] . '</td>';
												echo '<td>' . $booking['Teacher_Subject'] . '</td>';
												echo '<td><a class="btnCancelBooking btn btn-danger" data-bookingid="' . $booking['id']. '" >Cancel</a></td>';
												echo '</tr>';
											}
											if( $booking['breakslot'] == 2 )
											{
											?>
											<tr class="warning">
												<td><?php echo $printable_timeslots[$i]; ?>pm</td>
												<td>BREAK</td>
												<td></td>
												<td><a class="btnCancelBreak btn btn-danger" href="#" data-bookingid="<?php echo $booking['id']; ?>">Cancel</a></td>
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
		</div>
		<div id="bookSlot" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
		<form class="form-horizontal" action="/parents-meeting/teacher/" method="post">
		<div class="modal-header" style="color: #000">
			<h3>Please choose the student you would like to see for this slot.</h3>
		</div>
		<div class="modal-body">
			<p>Only available students will be displayed</p>
			<div class="control-group" style="color: #000">
				<label class="control-label" for="special">Student</label>
				<div class="controls">
					<select id="studentName" name="name">
					</select>
				</div>
				<input id="timeSlot" name="timeSlot" type="hidden" value="" />
				<input id="sender" name="sender" type="hidden" value="teacher" />
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
<?php
	} else {
		echo 'You must be staff to view this page!';
	}
}
?>