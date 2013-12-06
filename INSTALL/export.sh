#!/bin/bash
#The script to extract the latest build from tags folder in repository
rm -rf pragyanshadow
a=`svn list https://delta.nitt.edu/repos/pragyan/tags | cut -d . -f 3 | sort -n | tail -1`
svn export https://10.0.0.126/repos/pragyan/tags/$a ./pragyanshadow/
b=${a:0:`expr \`echo $a | wc -c\` - 2`}
mv pragyanshadow pragyan-$b
tar -czf pragyan-$b.tar.gz pragyan-$b
tar -cjf pragyan-$b.tar.bz2 pragyan-$b