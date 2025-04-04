#Create configuration files from conf template files
echo "Creating dbconfiguration file: /config/dbconnection.php"
cp ../config/dbconnection_template.php ../config/dbconnection.php
echo "Creating Symbiota configuration file: /config/symbini.php"
cp ../config/symbini_template.php ../config/symbini.php
echo "Creating homepage: /index.php"
cp ../index_template.php ../index.php
echo "Creating header include: /includes/header.php"
cp ../includes/header_template.php ../includes/header.php
cp ../includes/minimalheader_template.php ../includes/minimalheader.php
echo "Creating Left Menu include: /includes/leftmenu.php"
cp ../includes/leftmenu_template.php ../includes/leftmenu.php
echo "Creating footer include: /includes/footer.php"
cp ../includes/footer_template.php ../includes/footer.php
echo "Creating head include: /includes/head.php"
cp ../includes/head_template.php ../includes/head.php
echo "Creating central CSS: /css/main.css"
cp ../css/main_template.css ../css/main.css
echo "Creating CSS for Taxon Profile page: /css/speciesprofile.css"
cp ../css/speciesprofile_template.css ../css/speciesprofile.css
echo "Creating default JQuery CSS: /css/jquery-ui.css"
cp ../css/jquery-ui_template.css ../css/jquery-ui.css
echo "Creating usage policy include: /includes/usagepolicy.php"
cp ../includes/usagepolicy_template.php ../includes/usagepolicy.php

#Adjust file permission to give write access to certain folders and files
echo "Adjusting file permissions"
chmod -R 777 ../temp
chmod -R 777 ../content/collicon
chmod -R 777 ../content/css
chmod -R 777 ../content/dwca
chmod -R 777 ../content/geolocate
chmod -R 777 ../content/imglib
chmod -R 777 ../content/lang
chmod -R 777 ../content/logs 
chmod -R 777 ../api/storage/framework 
chmod -R 777 ../api/storage/logs 
