#!/bin/bash
# reads file names from the file provided in the parameter and
# excludes them from the svn commit.
# Preferred use from the root directory of Pragyan CMS 
# >sh scripts/xcommit.sh "This is my commit description"
# Default location for exclude file : scripts/exclude.txt

if [ $# -lt 1 ]
then
echo "Usage: $0 <commit-description> [<path-of-exclude-list>]"
exit 1;
fi

XFILE=$2
: ${XFILE:="scripts/exclude.txt"}
FILES=`cat $XFILE`
CHANGED_FILES=`svn status | grep ^[MAD] | cut -c 8-`
#for i in $CHANGED_FILES
#do
#echo Changed file: $i
#done

#for j in $FILES
#do
#echo Ignore file: $j
#done

for i in $CHANGED_FILES
do
    COMMIT=1
    for j in $FILES
    do
	if [ $i = $j ];
	then
		echo "Excluded : $i "
		COMMIT=0
	fi
    done

if [ $COMMIT = "1" ];
then
echo "Included : $i"
COMMIT_FILES=$COMMIT_FILES" $i"
fi
done

echo "Files to commit: $COMMIT_FILES"
#echo $1
svn commit $COMMIT_FILES -m $1


