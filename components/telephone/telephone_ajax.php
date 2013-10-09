<?
include $_SERVER['DOCUMENT_ROOT']. "/includes/ajax_include.php";

if(isset($_GET['action']))
{
	if($_GET['action'] = "getTelNumber")
	{
		if($_GET['query'] == "")
		{
			$error = array ("success" => "0", "errormessage" => "No query!");
			header('Content-Type: application/json');
			echo json_encode($error);
		} else {
			$q = $_GET['query'];
			$results = query("SELECT firstname, lastname, telephone, email FROM users WHERE staff = 1 ");
			if (strlen($q) > 0)
			{
				$matches_table = '<table class="table"><thead><th style="width: 140px;"></th><th style="width: 30px;"><div style="margin-left: 10px;" class="icon-phone"></div></th></head><tbody>';
				for($i = 0; $i < 3; $i++)
				{
			         mysql_data_seek($results, 0);
			         while($result = mysql_fetch_object($results))
			         {
				           $valarray = array($result->firstname, $result->lastname, $result->telephone);
				           if (strtolower($q)==strtolower(substr($valarray[$i],0,strlen($q))))
				           {
		                        $matches_table .= '<tr>';
								$matches_table .= '<td>' . $result->firstname . " ". $result->lastname . '</td>';
								//$matches_table .= '<td>' . $match['telephone'] . '<br />' . $email_split[0] . '@<br />' . $email_split[1] . '</td>' ;
								$matches_table .= '<td style="text-align: center;">' . $result->telephone . '</td>' ;
								$matches_table .= '</tr>';
				           }
			         }

				}
			  	$matches_table .= '</tbody></table>';
				$success = array ("success" => "1", "matches" => $matches_table );
				header('Content-Type: application/json');
				echo json_encode($success);
			} else {
				$error = array ("success" => "0", "errormessage" => "No result!");
				header('Content-Type: application/json');
				echo json_encode($error);
			}
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