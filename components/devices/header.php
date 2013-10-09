<?php
log_include(__FILE__);

// Check that this file is included
if(!$config['included']) die("This file may only be accessed as an include.");

// Check if a mentor request has been made
if(isset($_POST['saveDevice']))
{
		// Collect the variables
	$type = sanitise_before_db(trim($_POST['type']));
	$description = sanitise_before_db(trim($_POST['description']));
	$mac_address = implode(":", array($_POST['mac_address_1'], $_POST['mac_address_2'],
		$_POST['mac_address_3'], $_POST['mac_address_4'], $_POST['mac_address_5'], $_POST['mac_address_6']));
	$user_id = (int)(trim($_POST['user_id']));

	// Check for any errors and validate
	$_SESSION['formErrors'] = array();

	if(empty($description))
	{
		$_SESSION['formErrors'][] = "No description was entered!";
	}

	if(empty($type))
	{
		$_SESSION['formErrors'][] = "No type was entered!";
	}

	if(empty($user_id))
	{
		$_SESSION['formErrors'][] = "No user id was specified!";
	}

	// If there are no input errors, process the message
	if(count($_SESSION['formErrors']) == 0)
	{
		$user = query("SELECT firstname, lastname FROM users WHERE id = ". $user_id);
		$user = mysql_fetch_assoc($user);
		$query = "INSERT INTO devices (user_id, type, description, mac_address)
			VALUES('$user_id', '$type', '$description', '$mac_address');";
		query($query);
		mail_user(19 , "Devices add request.", $user['firstname'] . " "  . $user['lastname'] .
			" would like their " . $type. " with MAC address " . $mac_address . " added to the school WiFi network.") ;
		$_SESSION['successMessage'] = "Device Added!" ;
	}

	header("Location: /devices/");
	exit;
}
?>