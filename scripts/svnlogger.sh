#!/bin/bash
#
# Prints the usage details
#
print_help()
{
	echo "svnlogger 0.1 by Abhishek Shrivastava [i.abhi27[at]gmail.com]";
	echo "Usage : $0 [options] [svn root] [changelog]"
	echo "options include:";
	echo "	-h : Use HTML output";
	echo "	-x : Use TEXT output (default)";
	echo " 	-t path : use path as template folder instead of default (Only with -h)";
	echo "svnroot : The root directory of SVN Repo. Default = ./";
	echo "changelog : The path of changelog file. Default = ./ChangeLog";
	return
}

#
# Prints an error
#

print_error()
{
	case "$1" in
	 1) echo "Error : -x and -h cannot be both set";;
	 2) echo "Error : -h must be set before using -t";;
	esac
	exit 1;
}

#
# Generates text log
#

generate_text_log()
{
 SVNROOT=$1
 CHANGELOG=$2
 : ${SVNROOT:="./"}
 : ${CHANGELOG:="./ChangeLog"}
 svn log -r HEAD:1 $SVNROOT | grep -v "^$" | grep -v ^- | awk -F "|" ' 
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
  print "Revision " revno " by " username " on " date 
  print "Description : " $1
  print "--------------------------------------------------------------------------------"
 } 
 }
 END{
 print "Log generated using svnlogger v0.1 by abhishekdelta"
 }' > $CHANGELOG
}
 
#
# Generate HTML log
#

generate_html_log()
{
 echo "hey"
}
#
# Main procedure begins here
#

#
# Parse arguments
#
while getopts hxt: opt
do 
 case "$opt" in
	h) if [ -z "$TEXT" ] ;
	   then
		HTML="true"
	   else print_error 1;
	   fi ;;
	t) if [ ! -z "$HTML" ] ;
	   then 
		TEMPLATE="$OPTARG"
	   else print_error 2;
	   fi ;;
	x) if [ -z "$HTML" ] ;
	   then
		TEXT="true"
	   else print_error 1;
	   fi ;;
	[?]) print_help; exit 1;;
 esac
done

#
# Generate Log
#
if [ -z "$HTML" ] || [ $TEXT -eq "true" ] ;
then
 echo "Generating text log ..... "
 generate_text_log;
 echo " done!"
else 
 echo "Generating html log ..... "
 generate_html_log;
 echo " done!"
fi

