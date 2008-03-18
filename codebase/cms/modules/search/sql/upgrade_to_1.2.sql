create table query_log (
	query varchar(255),
	time timestamp(14),
	elapsed float(2),
	results int);

alter table links add column size float(2) after indexdate;

