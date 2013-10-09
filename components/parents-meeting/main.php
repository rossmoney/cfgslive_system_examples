<div class="page-header">
	<h1>Parents Evening</h1>
</div>
<?
if (isset($_GET['urlvar2'])) {
	if($_GET['urlvar2'] == 'book') {
		include ($_SERVER['DOCUMENT_ROOT'].'/components/parents-meeting/in-progress.php');
	}
	if($_GET['urlvar2'] == 'teacher') {
		include ($_SERVER['DOCUMENT_ROOT'].'/components/parents-meeting/teacher.php');
	}
} else {
		?>
		<div class="row">
			<div class="span12">
				<div class="hero-unit">
					<h1>Welcome</h1>
					<p><strong>This is the new online booking system for Parents Evening.</strong></p>
					<p>Using your childs CFGSLive account to login you are able to book slots to see each teacher.</p>
						<?php
						if($parentsMeetingMainPeriod)
						{
							$_SESSION['main_access'] = TRUE;
							?>
							<a href="/parents-meeting/book/" class="btn btn-info btn-large pull-right">Start Booking</a>
							<?php
						} else {
							$early_access = query("SELECT * FROM parents_meeting_early_access WHERE student_id = '" . $userInfo['id'] . "'");
							if(mysql_num_rows($early_access) > 0)
							{
								$_SESSION['early_access'] = TRUE;
								?>
								<p>One or more of your teachers have given you early access to the system as they would like to see you.</p><br />
								<a href="/parents-meeting/book/" class="btn btn-info btn-large pull-right">Start Early Booking Now</a>
								<?php
							} else {
								?>
								<p>Booking has not opened yet! Please come back later.</p>
								<?php
							}
						}
						?>
				</div>
			</div>
		</div>
		<?
	}
?>
