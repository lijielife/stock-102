<?php
header("Content-type: text/html; charset=utf-8");

date_default_timezone_set('PRC');

define("CHANNEL", 'EMALL_CHANNEL');

//include __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../vendor/phpmailer/phpmailer/PHPMailerAutoload.php';

$config = include __DIR__ . '/config.php';
extract($config);

$redis = new Redis();
$redis->connect($db['host'], $db['port']);
$redis->auth($db['auth']);
//发布 
if(!empty($argv[1]))
{
    $ret=$redis->publish(CHANNEL, $argv[1]);
}
else //订阅
{    
    ini_set('default_socket_timeout', -1);  //不超时
    $result=$redis->subscribe(array(CHANNEL), 'sendMessage');
}


/**
 * 发送消息
 * @param $instance
 * @param $channelName
 * @param $message
 */
function sendMessage($instance, $channelName, $message)
{
    global $mail;
    sendEMail($mail['from_email'], $mail['from_name'], $message, $message, null, $mail);
}

/**
 * 发送EMAIL
 * @param $to
 * @param $name
 * @param string $subject
 * @param string $body
 * @param null $attachment
 * @param string $config
 * @return bool
 */
function sendEMail($to, $name, $subject = '', $body = '', $attachment = null, $config = '')
{
    if(!isset($config['from_name'])) $config['from_name'] = $config['smtp_user'];
    if(!isset($config['from_email'])) $config['from_email'] = $config['smtp_user'];
    if(!isset($config['reply_email'])) $config['reply_email'] = $config['from_email'];
    if(!isset($config['reply_name'])) $config['reply_name'] = $config['from_name'];
    //dump($config);die;

    $mail = new PHPMailer();
    $mail->CharSet = 'UTF-8';
    $mail->IsSMTP();
    //$mail->IsHTML(true);
    //$mail->SMTPDebug = 1;
    $mail->SMTPAuth = true;
    if ($config['smtp_port'] == 465)
        $mail->SMTPSecure = 'ssl';
    $mail->Host = $config['smtp_host'];
    $mail->Port = $config['smtp_port'];
    $mail->Username = $config['smtp_user'];
    $mail->Password = $config['smtp_pass'];
    $mail->SetFrom($config['from_email'], $config['from_name']);
    $replyEmail = $config['reply_email'];
    $replyName = $config['reply_name'];
    $mail->AddReplyTo($replyEmail, $replyName);
    $mail->Subject = $subject;
    $mail->MsgHTML($body);
    $mail->AddAddress($to, $name);
    if (is_array($attachment)) {
        foreach ($attachment as $file) {
            if (is_array($file)) {
                is_file($file['path']) && $mail->AddAttachment($file['path'], $file['name']);
            } else {
                is_file($file) && $mail->AddAttachment($file);
            }
        }
    } else {
        is_file($attachment) && $mail->AddAttachment($attachment);
    }
    return $mail->Send() ? true : $mail->ErrorInfo;
}