alter table sites add column spider_depth int default 2, must_include text, mustnot_include text;
alter table links add column visible int default 1;