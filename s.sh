#!/bin/sh

filename='/data/shell/stock/'
filename=$filename$(date +%Y%m%d)

for((i=1;i<=15;i++));do
    /usr/local/php7/bin/php /data/shell/stock/run.php>>"$filename"
     sleep 4
done
