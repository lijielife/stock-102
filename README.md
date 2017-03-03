###log
![image](https://github.com/yeosz/stock/blob/master/stock.png)

###crontab配置

* 9-15 * * 1-5 /data/shell/stock/s.sh
0 1 * * * /data/shell/stock/mv.sh

###supervisord配置demo

[program:email]
command=/usr/local/php5.6/bin/php /data/shell/stock/email/run.php
user=root
autostart=true
autorestart=true
stderr_logfile=/var/log/supervisord/email-err.log
stdout_logfile=/var/log/supervisord/email-out.log