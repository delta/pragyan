<html>
	<head>		
	<title>
	Sphider installation script.
	</title>
	<LINK REL=STYLESHEET HREF="admin.css" TYPE="text/css">
	</head>
<body>
<h2>Sphider installation script.</h2>
<?php
error_reporting(E_ALL);
$settings_dir = "../settings";
include "$settings_dir/database.php";

$error = 0;
mysql_query("create table `".$mysql_table_prefix."sites`(
	site_id int auto_increment not null primary key,
	url varchar(255),
	title varchar(255),
	short_desc text,
	indexdate date,
	spider_depth int default 2,
	required text,
	disallowed text,
	can_leave_domain bool)");
if (mysql_errno() > 0) {
	print "Error: ";
	print mysql_error();
	print "<br>\n";
	$error += mysql_errno();
}
mysql_query("create table `".$mysql_table_prefix."links` (
	link_id int auto_increment primary key not null,
	site_id int,
	url varchar(255) not null,
	title varchar(200),
	description varchar(255),
	fulltxt mediumtext,
	indexdate date,
	size float(2),
	md5sum varchar(32),
	key url (url),
	key md5key (md5sum),
	visible int default 0, 
	level int)");

if (mysql_errno() > 0) {
	print "Error: ";
	print mysql_error();
	print "<br>\n";
	$error += mysql_errno();
}
mysql_query("create table `".$mysql_table_prefix."keywords`	(
	keyword_id int primary key not null auto_increment,
	keyword varchar(30) not null,
	unique kw (keyword),
	key keyword (keyword(10)))");

if (mysql_errno() > 0) {
	print "Error: ";
	print mysql_error();
	print "<br>\n";
	$error += mysql_errno();
}

for ($i=0;$i<=15; $i++) {
	$char = dechex($i);
	mysql_query("create table `".$mysql_table_prefix."link_keyword$char` (
		link_id int not null,
		keyword_id int not null,
		weight int(3),
		domain int(4),
		key linkid(link_id),
		key keyid(keyword_id))");

	if (mysql_errno() > 0) {
		print "Error: ";
		print mysql_error();
		print "<br>\n";
		$error += mysql_errno();
	}
}

mysql_query("create table `".$mysql_table_prefix."categories` (
	category_id integer not null auto_increment primary key, 
	category text,
	parent_num integer
	)");

if (mysql_errno() > 0) {
	print "Error: ";
	print mysql_error();
	print "<br>\n";
	$error += mysql_errno();
}

mysql_query("create table `".$mysql_table_prefix."site_category` (
	site_id integer,
	category_id integer
	)");

if (mysql_errno() > 0) {
	print "Error: ";
	print mysql_error();
	print "<br>\n";
	$error += mysql_errno();
}

mysql_query("create table `".$mysql_table_prefix."temp` (
	link varchar(255),
	level integer,
	id varchar (32)
	)");

if (mysql_errno() > 0) {
	print "Error: ";
	print mysql_error();
	print "<br>\n";
	$error += mysql_errno();
}

mysql_query("create table `".$mysql_table_prefix."pending` (
	site_id integer,
	temp_id varchar(32),
	level integer,
	count integer,
	num integer
)");

if (mysql_errno() > 0) {
	print "Error: ";
	print mysql_error();
	print "<br>\n";
	$error += mysql_errno();
}

mysql_query("create table `".$mysql_table_prefix."query_log` (
	query varchar(255),
	time timestamp(14),
	elapsed float(2),
	results int, 
	key query_key(query))");

if (mysql_errno() > 0) {
	print "Error: ";
	print mysql_error();
	print "<br>\n";
	$error += mysql_errno();
}

mysql_query("create table `".$mysql_table_prefix."domains` (
	domain_id int auto_increment primary key not null,	
	domain varchar(255))");

if (mysql_errno() > 0) {
	print "Error: ";
	print mysql_error();
	print "<br>\n";
	$error += mysql_errno();
}


if ($error >0) {
	print "<b>Creating tables failed. Consult the above error messages.</b>";
} else {
	print "<b>Creating tables successfully completed. Go to <a href=\"admin.php\">admin.php</a> to start indexing.</b>";
}
?>
</body>
</html>