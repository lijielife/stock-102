###log
-----------------------------------
![image](https://github.com/yeosz/stock/blob/master/stock.png)

###crontab配置
-----------------------------------
  * 9-15 * * 1-5 /data/shell/stock/s.sh<br />
  0 1 * * * /data/shell/stock/mv.sh<br />

###supervisord配置demo
-----------------------------------
[program:email]
  command=/usr/local/php5.6/bin/php /data/shell/stock/email/run.php<br />
  user=root<br />
  autostart=true<br />
  autorestart=true<br />
  stderr_logfile=/var/log/supervisord/email-err.log<br />
  stdout_logfile=/var/log/supervisord/email-out.log<br />