<?php
class stock
{
	const API_STOCK = 'http://hq.sinajs.cn/list=';
    
	/**
	 * 判断是否歇市
	 * 接口 http://www.easybots.cn/api/holiday.php?d=20170205
	 * @return boolean
	 */
	public static function isClose()
	{
		$week = date('w');
		if (in_array($week, array(0, 6)))
		{
			return true;
		}
		$time = date('Hi');
		if ($time < 930 || ($time > 1130 && $time < 1300) || $time > 1500)
		{
			return true;
		}
		return false;
	}

	/**
	 * 获取城市代号
	 *
     * @param string $code
	 * @return string
	 */
    public static function getCity($code)
    {
        if(substr($code, 0, 2) == '60')
        {
            return 'sh';
        }
        elseif(substr($code, 0, 3) == '204')
        {
            return 'sh';
        }
        elseif(substr($code, 0, 4) == '0000')
        {
            return 'sh';
        }
        elseif(substr($code, 0, 2) == '00')
        {
            return 'sz';
        }
        elseif(substr($code, 0, 2) == '30')
        {
            return 'sz';
        }
        elseif(substr($code, 0, 3) == '399')
        {
            return 'sz';
        }        
        elseif(substr($code, 0, 4) == '1318')
        {
            return 'sz';
        }
        return '';
    }
    
	/**
	 * 要查询的股票代码
	 *
	 * @param array $data
	 * @return array
	 */
	public function query($data)
	{
		$code = array();
		if (! in_array('000001', $data))
		{
			array_unshift($data, '000001');
		}
		foreach ($data as $v )
		{
			$code[] = $this->getCity($v) . $v;
		}
		$str = implode(',', $code);
		$url = self::API_STOCK . $str;
		$requestResult = self::httpGet($url);
		if (empty($requestResult))
			return array();
		$requestResult = iconv('GBK', 'UTF-8', $requestResult);
		$requestResult = substr($requestResult, 0, - 2);
		$requestResult = explode(';', $requestResult);
		$list = array();
		foreach ($requestResult as $v )
		{
			$temp = explode(',', $v);
			$code = substr(trim($temp[0]), 13, 6);
			$name = substr(trim($temp[0]), 21);
			$rate = round((($temp[3] - $temp[2])/$temp[2]) * 100, 2) . '%';
			$list[$code] = array(
					'名称' => $name, // 名称
					'当前' => $temp[3], // 当前
					'昨收' => $temp[2], // 昨收
					'今开' => $temp[1], // 今开
					'涨跌幅' => $rate, // 当前涨跌幅
			);
		}
		return $list;
	}
	
	/**
	 * 重新组织数据
	 * 
	 * @param string $code
	 * @param array $data
	 * @return string
	 */
	public function reorgData($own, $data)
	{
		if (!isset($own['000001']))
		{
			$own['000001'] = array();
		}
		ksort($own);
		foreach ($own as $id=>$v)
		{
			$temp  = $data[$id];
			$temp['成本'] = empty($v) ? '-' : $v[0]; //成本价
			$temp['数量'] = empty($v) ? '-' : $v[1]; //数量
			$temp['市值'] = empty($v) ? '-' : $v[1] * $temp['当前']; // 当前市值
			$temp['总亏盈'] = empty($v) ? '-' : round($v[1] * $temp['当前'] - $v[0]*$v[1], 2); //总亏盈
			$temp['亏盈比'] = empty($v) ? '-' : round((($temp['当前'] - $v[0])/$temp['当前'])*100,2) . '%'; //亏盈比            
			$own[$id] = $temp;
		}
		return $own;
	}

	/**
	 * 预警
	 * 
	 * @param array $notice
     * @param array $list
	 * @param object $redis
	 */
	public function notice($notice, $list, $redis)
	{
		foreach ($notice as $k=>$v)
		{
			foreach ($v as $vv)
			{
				if (empty($list[$k]['当前'])) continue;
				$result = abs($list[$k]['当前'] - $vv);
				if ($result/$list[$k]['当前'] <= $this->getBaseRate($vv))
				{
					$redisKey = $k.'_'.$vv;
					$isNotice = $redis->get($redisKey);
					if (!$isNotice)
					{
						$content = '预警：'. $list[$k]['名称'] . '当前' . $list[$k]['当前'] . ',涨跌幅' . $list[$k]['涨跌幅'] . ',接近目标价位:' . $vv;
						$this->sendMsg($redis, $content);
						$redis->set($redisKey, time());
						$redis->expire($redisKey, 1800); // 半小时内不重复提醒
					}
				}
			}
		}
	}
	
	/**
	 * http get 请求
	 * 
	 * @param unknown $url
	 * @param string $param
	 * @param number $time
	 * @return boolean|mixed
	 */
	public static function httpGet($url, $param = null, $time = 5000)
	{
		$url = empty($param) ? $url : $url . '?' . http_build_query($param);
		$ch = curl_init();
		// set URL and other appropriate options
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // 不验证证书
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false); // 不验证host
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT_MS, 500); // 设置时间
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_NOSIGNAL, 1); // 注意，毫秒超时一定要设置这个
		curl_setopt($ch, CURLOPT_TIMEOUT_MS, $time);
		// curl_setopt($ch, CURLOPT_TIMEOUT, $time);//设置时间
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); // 不直接输出
		                                             // grab URL and pass it to the browser
		$result = curl_exec($ch);
		if (curl_errno($ch))
		{
			// echo 'Errno2'.curl_error($ch);//捕抓异常
			return false;
		}
		// close cURL resource, and free up system resources
		curl_close($ch);
		return $result;
	}
	
	/**
	 * 发送消息
	 * 
     * @param object $redis
	 * @param string $content
	 */
	public static function sendMsg($redis, $content)
	{
        return $redis->publish('EMALL_CHANNEL', $content); 
	}
 
	/**
	 * 报警比
	 *
	 * @param int $num
	 * @return number
	 */
	private function getBaseRate($num)
	{
		if ($num > 3000)
		{
			return 0.001;
		}
        elseif ($num > 2000)
		{
			return 0.0015;
		}
		elseif ($num > 10)
		{
			return 0.002;
		}
		elseif ($num > 5)
		{
			return 0.003;
		}
		else
		{
			return 0.006;
		}
	}
}
