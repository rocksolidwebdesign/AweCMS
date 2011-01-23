@echo off

REM /usr/bin/env bash

REM ###############################################################################
REM  AweCMS
REM  
REM  LICENSE
REM  
REM  This source file is subject to the BSD license that is bundled
REM  with this package in the file LICENSE.txt
REM  
REM  It is also available through the world-wide-web at this URL:
REM  http://www.opensource.org/licenses/bsd-license.php
REM  
REM  @category   AweCMS
REM  @package    AweCMS_Theme_Admin_Default
REM  @copyright  Copyright (c) 2010 Rock Solid Web Design (http://rocksolidwebdesign.com)
REM  @license    http://www.opensource.org/licenses/bsd-license.php BSD License
REM  
REM ###############################################################################

REM  Refresh Database Schema

echo [   MYSQL   ] Dropping Old Database
mysql -u root -e "DROP DATABASE IF EXISTS awecms"

echo [   MYSQL   ] Creating Database
mysql -u root -e "CREATE DATABASE awecms"

echo [   MYSQL   ] Adding Database User
mysql -u root -e "GRANT ALL ON awecms.* TO 'awecms'@'localhost' IDENTIFIED BY 'awecms'"

REM  configure folders

del application\configs\config.php
copy application\configs\config.php.sample application\configs\config.php
rmdir /S /Q application\doctrine\Proxies
mkdir application/doctrine/Proxies

REM  create database with doctrine CLI

echo [ DOCTRINE2 ] Creating Tables
php bin\doctrine.php orm:schema-tool:create

echo [ DOCTRINE2 ] Importing Test Data...
mysql -u awecms -pawecms awecms < application\doctrine\testdata.sql
