<?php
// Sets a new MySQLi instance
	$db_hostname = 'localhost';
	$db_username = 'root';
	$db_password = '';
	$db_database = 'pscsorg_attendance';
	$db_server = new mysqli($db_hostname, $db_username, $db_password, $db_database);
	if ($db_server->connect_error) { die('Connect Error (' . $db_server->connect_errno . ') '  . $db_server->connect_error); }
        // THIS LINE TELLS THE SERVER TO UNDERSTAND US AS IN THE PACIFIC TIME ZONE
        $db_server->query("SET time_zone='US/Pacific';");
?>