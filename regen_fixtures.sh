#!/bin/bash
mv application/doctrine/testdata.sql application/doctrine/testdata.bak.sql
mysqldump -t -c -u awecms -pawecms awecms > application/doctrine/testdata.sql
