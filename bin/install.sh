#!/usr/bin/env bash

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

bin_dir=bin
app_dir=application
conf_dir=$app_dir/configs
config_file=$conf_dir/config.php
db_conf_dir=$conf_dir/database
db_file=$conf_dir/database.ini
fixture_file=$app_dir/doctrine/fixtures.sql

# pretty colors {{{
black=$(tput setaf 0)
red=$(tput setaf 1)
green=$(tput setaf 2)
yellow=$(tput setaf 3)
dk_blue=$(tput setaf 4)
pink=$(tput setaf 5)
lt_blue=$(tput setaf 6)

bold=$(tput bold)
reset=$(tput sgr0)
# }}}

# params {{{
# accept environment as first param
if [[ $# -eq 1 && $1 != 'refresh' ]]; then
    APPLICATION_ENV=$1
fi

# full or re install as last param
  if [[ $# -eq 1 && $1 == 'refresh' ]]; then
    refresh=1
elif [[ $# -eq 2 && $2 == 'refresh' ]]; then
    refresh=1
else
    refresh=0
fi

if (( $refresh )); then
    echo "[    ${bold}${green}RUN${reset}    ] Reinstall"
else
    echo "[    ${bold}${green}RUN${reset}    ] Fresh Install"
fi
# }}}

# copy config file samples {{{
# copy sample config files into place if real ones don't exist yet
if (( ! $refresh )); then 
    # doctrine fixtures {{{
    if [[ ! -e $fixture_file && -e $fixture_file.sample ]]; then
        cp $fixture_file.sample $fixture_file
        echo "[  ${bold}${green}CONFIG${reset}   ] Copying application config file $fixture_file.sample to $fixture_file"
    fi
    # }}}

    # main config file {{{
    if [[ ! -e $config_file && -e $config_file.sample ]]; then
        cp $config_file.sample $config_file
        echo "[  ${bold}${green}CONFIG${reset}   ] Copying application config file $config_file.sample to $config_file"
    fi
    # }}}

    # global database config file {{{
    if [[ ! -e $db_file && -e $db_file.sample ]]; then
        cp $db_file.sample $db_file
        echo "[  ${bold}${green}CONFIG${reset}   ] Copying sample db config file $db_file.sample to $db_file"
    fi
    # }}}

    # environment setting file {{{
    if [[ ! -e $conf_dir/environment.ini && -e $conf_dir/environment.ini.sample ]]; then
        cp $conf_dir/environment.ini.sample $conf_dir/environment.ini
        echo "[  ${bold}${green}CONFIG${reset}   ] Copying sample db config file $conf_dir/environment.ini.sample to $conf_dir/environment.ini"
    fi
    # }}}

    # per environment database config files {{{
    envnames[0]=development
    envnames[1]=testing
    envnames[2]=staging
    envnames[3]=production

    for (( x=0; $x<4; x=$x+1 )); do
        env_name="${envnames[$x]}"
        env_file=$db_conf_dir/$env_name.ini

        if [[ ! -e $env_file ]]; then

            # first try copying the current global settings
            # then try the global sample
            # then try the per environment sample
              if [[ -e $db_file ]]; then
                cp_file=$db_file
                cp $cp_file $env_file
                echo "[  ${bold}${green}CONFIG${reset}   ] Copying sample db config file $cp_file to $env_name"

            elif [[ -e $db_file.sample ]]; then
                cp_file=$db_file.sample
                cp $cp_file $env_file
                echo "[  ${bold}${green}CONFIG${reset}   ] Copying sample db config file $cp_file to $env_name"

            elif [[ -e $env_file.sample ]]; then
                cp_file=$env_file.sample
                cp $cp_file $env_file
                echo "[  ${bold}${green}CONFIG${reset}   ] Copying sample db config file $cp_file to $env_name"

            fi
        fi
    done
    # }}}
fi
# }}}

# folder permissions {{{
# make sure we have the dirs we need
mkdir -p $app_dir/modules/other
mkdir -p $app_dir/doctrine/Proxies
mkdir -p $app_dir/doctrine/log

# make sure we can write to the dirs we need
chmod 777 $app_dir/doctrine/Proxies
chmod 777 $app_dir/doctrine/log
chmod 755 $bin_dir/doctrine
# }}}

# database {{{
# check for MAMP {{{
real_mysql_bin=$(which mysql)
mamp_mysql_bin=/Applications/MAMP/Library/bin/mysql

if [[ -n $real_mysql_bin ]]; then
    mysql_bin=$real_mysql_bin
elif [[ -f $mamp_mysql_bin ]]; then
    mysql_bin=$mamp_mysql_bin
else
    echo "MySQL not found at $mamp_mysql_bin or in $PATH"; exit;
fi
function mysqlBin() { "$mysql_bin" "$@"; }
# }}}

# determine environment {{{
# if param was blank then try config file
if [[ -z $APPLICATION_ENV ]]; then
    source $conf_dir/environment.ini
fi


# if environment is still unknown
if [[ -z $APPLICATION_ENV ]]; then

    APPLICATION_ENV=development
    echo "[    ${bold}${green}RUN${reset}    ] No environment detected (using global db config)"

    # try the global config file
    if [[ -e $db_conf_file ]]; then
        source $db_conf_file
    else
        # otherwise ohe noes!
        echo "Could not find database config file"; exit;
    fi
else

    echo "[    ${bold}${green}RUN${reset}    ] Using $APPLICATION_ENV environment"

    # look for an environment related db
    if [[ -e $db_conf_dir/$APPLICATION_ENV.ini ]]; then
        source $db_conf_dir/$APPLICATION_ENV.ini
    else
        # otherwise ohe noes!
        echo "Could not find database config file"; exit;
    fi
fi
# }}}

# add user on initial install {{{
if (( ! $refresh )); then
    echo -n "[   ${bold}${green}MYSQL${reset}   ] Please enter your MySQL root password: "
    stty -echo
    read mysql_root_password
    echo ""
    stty echo

    echo "[   ${bold}${green}MYSQL${reset}   ] Adding Database User"
    mysqlBin -h $db_host -u root -p$mysql_root_password -e "GRANT ALL ON $db_name.* TO '$db_user'@'$db_host' IDENTIFIED BY '$db_pass'"
fi
# }}}

# refresh database {{{
echo "[   ${bold}${green}MYSQL${reset}   ] Dropping Old Database"
mysqlBin -h $db_host -u $db_user -p$db_pass -e "DROP DATABASE IF EXISTS $db_name"

echo "[   ${bold}${green}MYSQL${reset}   ] Creating Database"
mysqlBin -h $db_host -u $db_user -p$db_pass -e "CREATE DATABASE $db_name"

echo "[ ${bold}${green}DOCTRINE2${reset} ] Creating Tables..."
php $bin_dir/doctrine.php -q orm:schema-tool:create

if [[ -e $app_dir/doctrine/fixtures.sql ]]; then
    echo "[ ${bold}${green}DOCTRINE2${reset} ] Importing Test Data"
    mysqlBin -h $db_host -u $db_user -p$db_pass $db_name < $fixture_file
fi
# }}}
# }}}

echo ""
echo "[ ${bold}${green}DONE${reset} ]"
