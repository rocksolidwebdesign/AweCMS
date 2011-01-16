#/usr/bin/env bash

################################################################################
# AweCMS
# 
# LICENSE
# 
# This source file is subject to the BSD license that is bundled
# with this package in the file LICENSE.txt
# 
# It is also available through the world-wide-web at this URL:
# http://www.opensource.org/licenses/bsd-license.php
# 
# @category   AweCMS
# @package    AweCMS_Theme_Admin_Default
# @copyright  Copyright (c) 2010 Rock Solid Web Design (http://rocksolidwebdesign.com)
# @license    http://www.opensource.org/licenses/bsd-license.php BSD License
# 
################################################################################

black=$(tput setaf 0)
red=$(tput setaf 1)
green=$(tput setaf 2)
yellow=$(tput setaf 3)
dk_blue=$(tput setaf 4)
pink=$(tput setaf 5)
lt_blue=$(tput setaf 6)

bold=$(tput bold)
reset=$(tput sgr0)

# Refresh Database Schema
MAMP_MYSQL_BIN=/Applications/MAMP/Library/bin/mysql
if [[ -f $MAMP_MYSQL_BIN ]]; then
    mysql_bin=$MAMP_MYSQL_BIN
else
    mysql_bin=`which mysql`
fi
function mysqlBin() { "$mysql_bin" "$@"; }

echo -n "[   ${bold}${green}MYSQL${reset}   ] Please enter your MySQL root password: "
stty -echo
read mysql_password
echo ""
stty echo

echo "[   ${bold}${green}MYSQL${reset}   ] Dropping Old Database"
mysqlBin -u root -p$mysql_password -e "DROP DATABASE IF EXISTS awecms"

echo "[   ${bold}${green}MYSQL${reset}   ] Creating Database"
mysqlBin -u root -p$mysql_password -e "CREATE DATABASE awecms"

echo "[   ${bold}${green}MYSQL${reset}   ] Adding Database User"
mysqlBin -u root -p$mysql_password -e "GRANT ALL ON awecms.* TO 'awecms'@'localhost' IDENTIFIED BY 'awecms'"

# configure folder permissions
cp application/configs/config.php.sample application/configs/config.php
mkdir -p application/doctrine/Proxies
chmod 777 application/doctrine/Proxies
chmod 755 application/doctrine/bin/doctrine
chmod 777 var

# create database with doctrine CLI
echo "[ ${bold}${green}DOCTRINE2${reset} ] Creating Tables"
php application/doctrine/bin/doctrine.php orm:schema-tool:create

echo "[ ${bold}${green}DOCTRINE2${reset} ] Importing Test Data..."
mysqlBin -u awecms -pawecms awecms < application/doctrine/testdata.sql
