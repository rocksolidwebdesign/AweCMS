#!/usr/bin/env bash
app_dir=application
config_dir=$app_dir/configs
source $config_dir/environment.ini

if [[ -e $config_dir/database/$APPLICATION_ENV.ini ]]; then
    source $config_dir/database/$APPLICATION_ENV.ini
elif [[ -e $config_dir/database.ini ]]; then
    source $config_dir/database.ini
else
    echo "Could not find database config file"; exit;
fi

d2_dir=$app_dir/doctrine
ts=$(date +%Y-%m-%d_%H.%m.%S)
echo "Making backup at $d2_dir/fixtures.sql.bak_$ts"
mv $d2_dir/fixtures.sql $d2_dir/fixtures.sql.bak_$ts

echo "Dumping latest data to $d2_dir/fixtures.sql"
mysqldump -t -c -h $db_host -u $db_user -p$db_pass $db_name > $d2_dir/fixtures.sql
