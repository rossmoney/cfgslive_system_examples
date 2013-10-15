<?
include $_SERVER['DOCUMENT_ROOT']. "/includes/ajax_include.php";

if(isset($_GET['action']))
{
	if($_GET['action'] = "deletedevice")
	{
		if($_GET['device_id'] == "")
		{
			$error = array ("success" => "0", "errormessage" => "No device selected!");
			header('Content-Type: application/json');
			echo json_encode($error);
		} else {
			query("DELETE FROM devices WHERE id = " . $_GET['device_id']);
			$success = array ("success" => "1");
			header('Content-Type: application/json');
			echo json_encode($success);
		}
	}
}
else
{
	$error = array ("success" => "0", "errormessage" => "No action was passed");
	header('Content-Type: application/json');
	echo json_encode($error);
}
?>