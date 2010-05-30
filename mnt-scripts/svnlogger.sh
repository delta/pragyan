#!/bin/bash
svn log -r HEAD:1 $1 | grep -v "^$" | grep -v ^- | awk -F "|" ' 
BEGIN{
username=""
date=""
revno=""
}
{
if($1 ~ /^r[0-9]*/)
{
 username=$2
 date=substr($3,1,19)
 revno=substr($1,2)
}
else
{
 print "Revision " revno " : " $1 " by " username " on " date
 print "---------------------------------------------------------------"
} 
}
END{
print "Log generated using svnlogger v0.1 by abhishekdelta"
}' > $2
