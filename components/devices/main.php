<?php

log_include(__FILE__);
// Check that this file is included
if(!$config['included']) die("This file may only be accessed as an include.");
?>
<div class="page-header">
	<h1>My Devices</h1>
</div>
<?php
if(isset($loggedin) && $loggedin == true)
{
	?>
	<p>Here you can add your devices to the school's WiFi network.</p>
	<?php
	$devices = query("SELECT * FROM devices WHERE user_id = " . $userInfo['id']);
	if(mysql_num_rows($devices) > 0)
	{
		?>
		<table class="table">
			<thead>
				<th>Type</th>
				<th>Description</th>
				<th>Mac Address</th>
				<th></th>
			</thead>
			<tbody>
			<?php
			while($device = mysql_fetch_assoc($devices))
			{
				echo "<tr id=\"device_". $device['id'] . "\">" . '<td>' . $device['type'] . '</td><td>' . $device['description'] . '</td><td>' . $device['mac_address'] . '</td><td><td>';
				?>
				<button class="btn btn-danger deletedevice" data-deviceid="<?php echo $device['id']; ?>">Delete</button>
				<?php
				echo '</td></tr>';
			}
			?>
			</tbody>
		</table>
	<?php
	}
	?>
	<?php $id = time(); ?>
	<a id="message" data-toggle="modal" href="#messageBox<?php echo $id; ?>">Add New Device</a>

	<div class="modal hide fade" id="messageBox<?php echo $id;  ?>">
		<form id="sendMessage" action="/devices/" method="post">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal">×</button>
				<h3>Add a new device</h3>
			</div>
			<div class="modal-body">
				<fieldset>
					<div class="control-group">
					  <label class="control-label" for="selectbasic">Type</label>
					  <div class="controls">
					    <select id="selectbasic" name="type">
					      <option>Phone</option>
					      <option>Laptop</option>
					      <option>Tablet</option>
					      <option>Other</option>
					    </select>
					  </div>
					</div>
					<?= $TB->form_control('textarea',
						'description',
						'Device description',
						'',
						'span5'
						); ?>
						<label class="control-label">MAC Address</label>
						<div class="input-prepend input-append">
                             <input class="span1" id="appendedPrependedInput" name="mac_address_1" maxlength="2" type="text">
                             <span class="add-on">:</span>
                             <input class="span1" id="appendedPrependedInput" name="mac_address_2" maxlength="2" type="text">
                             <span class="add-on">:</span>
                             <input class="span1" id="appendedPrependedInput" name="mac_address_3" maxlength="2" type="text">
                             <span class="add-on">:</span>
                             <input class="span1" id="appendedPrependedInput" name="mac_address_4" maxlength="2" type="text">
                             <span class="add-on">:</span>
                             <input class="span1" id="appendedPrependedInput" name="mac_address_5" maxlength="2" type="text">
                             <span class="add-on">:</span>
                             <input class="span1" id="appendedPrependedInput" name="mac_address_6" maxlength="2" type="text">
                             <span class="add-on">:</span>
                        </div>
						<input type="hidden" name="user_id" value="<?php echo $userInfo['id']; ?>">
					</fieldset>
			</div>
			<div class="modal-footer">
				<a href="#" class="btn" data-dismiss="modal">Close</a>
				<input class="btn btn-primary" type="submit" name="saveDevice" value="Save Device">
			</div>
		</form>
	</div>
	<?php
}
?>