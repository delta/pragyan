alter table sites add column spider_depth int default 2, add column required text, add column disallowed text, add column can_leave_domain bool;
alter table links add column visible int default 0, add column level int;
