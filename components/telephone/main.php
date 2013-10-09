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
			<h2>Telephone Directory</h2>
		</div>
		<p>Enter the name of a member of staff to find their telephone number:</p>
		<label class="control-label" for="teldir_name">Name</label>
		<input type="text" name="teldir_name" id="teldir_name" style="width: 80px;">
		<br />
		<div id="teldir_result"></div>
<?php
	}
}
?>
