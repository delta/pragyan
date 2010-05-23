<?php
	global $sph_database, $sph_mysql_user, $sph_mysql_password, $sph_mysql_host, $sph_mysql_table_prefix;

	$success = mysql_pconnect ($sph_mysql_host, $sph_mysql_user, $sph_mysql_password);
	if (!$success)
		displayerror('Could not connect to database. Could not perform the search.');
    $success = mysql_select_db ($sph_database);
	if (!$success) {
		displayerror('Could not connect to database. Could not perform the search.');
		die();
	}
?>