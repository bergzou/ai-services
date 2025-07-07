#!/bin/bash 
step=3 #间隔的秒数，不能大于60 
for (( i = 0; i < 60; i=(i+step) )); do
/usr/bin/php '/www/wwwroot/artisan' command:mysqlMaster all
sleep $step 
done
exit 0