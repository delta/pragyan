CMSPATH=$1
: ${CMSPATH:="."}
echo "" > $CMSPATH/cms/config.inc.php
rm $CMSPATH/.htaccess
cp $CMSPATH/htaccess-dist $CMSPATH/.htaccess
chmod 777 $CMSPATH/.htaccess
echo "Pragyan CMS Uninstalled!"
