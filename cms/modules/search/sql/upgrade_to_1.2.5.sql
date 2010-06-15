alter table links add column description varchar(255) after title;
update sites set url =concat("http://", url);
update links set url =concat("http://", url);