<?php
if(!defined('__PRAGYAN_CMS'))
{ 
	header($_SERVER['SERVER_PROTOCOL'].' 403 Forbidden');
	echo "<h1>403 Forbidden<h1><h4>You are not authorized to access the page.</h4>";
	echo '<hr/>'.$_SERVER['SERVER_SIGNATURE'];
	exit(1);
}

$error = 0;
mysql_query("CREATE TABLE IF NOT EXISTS `sites`(
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
mysql_query("CREATE TABLE IF NOT EXISTS `links` (
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
mysql_query("CREATE TABLE IF NOT EXISTS `keywords`	(
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
	mysql_query("CREATE TABLE IF NOT EXISTS `link_keyword$char` (
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

mysql_query("CREATE TABLE IF NOT EXISTS `categories` (
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

mysql_query("CREATE TABLE IF NOT EXISTS `site_category` (
	site_id integer,
	category_id integer
	)");

if (mysql_errno() > 0) {
	print "Error: ";
	print mysql_error();
	print "<br>\n";
	$error += mysql_errno();
}

mysql_query("CREATE TABLE IF NOT EXISTS `temp` (
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

mysql_query("CREATE TABLE IF NOT EXISTS `pending` (
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

mysql_query("CREATE TABLE IF NOT EXISTS `query_log` (
	query varchar(255),
	time timestamp,
	elapsed float(2),
	results int, 
	key query_key(query))");

if (mysql_errno() > 0) {
	print "Error: ";
	print mysql_error();
	print "<br>\n";
	$error += mysql_errno();
}

mysql_query("CREATE TABLE IF NOT EXISTS `domains` (
	domain_id int auto_increment primary key not null,	
	domain varchar(255))");

if (mysql_errno() > 0) {
	print "Error: ";
	print mysql_error();
	print "<br>\n";
	$error += mysql_errno();
}

if($error>0)
	return 'Error in creating sphider search tables';
else
	return '';

?>
