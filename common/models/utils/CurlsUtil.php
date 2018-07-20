<?php
namespace common\models\utils;
class CurlsUtil {
	public static function get($url, $asyncPost = 0, $header = array()) {
// 		$header [] = "Content-Type: text/xml; charset=utf-8";
// 		$header [] = "Accept: text/html";
		if ($asyncPost == 1)
			$header [] = "Connection: close";
		$ch = curl_init ();
		curl_setopt ( $ch, CURLOPT_URL, $url );
		curl_setopt ( $ch, CURLOPT_HTTPHEADER, $header );
		curl_setopt ( $ch, CURLOPT_HEADER, 0 );
		curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, true );
		curl_setopt ( $ch, CURLOPT_POST, false );
		if (substr ( $url, 0, 5 ) == 'https') {
			curl_setopt ( $ch, CURLOPT_SSL_VERIFYPEER, FALSE );
			curl_setopt ( $ch, CURLOPT_SSL_VERIFYHOST, FALSE );
		}
		$data = curl_exec ( $ch );
		curl_close ( $ch );
		return $data;
	}
	public static function asyncGet($url, $header = array()) {
		$header [] = "Connection: close";
		$ch = curl_init ();
		curl_setopt ( $ch, CURLOPT_URL, $url );
		curl_setopt ( $ch, CURLOPT_HTTPHEADER, $header );
		curl_setopt ( $ch, CURLOPT_HEADER, 0 );
		curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, true );
		curl_setopt ( $ch, CURLOPT_POST, true );
		if (substr ( $url, 0, 5 ) == 'https') {
			curl_setopt ( $ch, CURLOPT_SSL_VERIFYPEER, FALSE );
			curl_setopt ( $ch, CURLOPT_SSL_VERIFYHOST, FALSE );
		}
		curl_exec ( $ch );
		curl_close ( $ch );
		return true;
	}
	public static function asyncPost($url, $postData, $header = array()) {
		if ($header == null)
			$header = array ();
		if(is_string($postData))
			$header [] = "Content-Length: " . strlen ( $postData );
		$header [] = "Connection: close";
		$ch = curl_init ();
		curl_setopt ( $ch, CURLOPT_URL, $url );
		curl_setopt ( $ch, CURLOPT_HTTPHEADER, $header );
		curl_setopt ( $ch, CURLOPT_HEADER, 0 );
		curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, true );
		curl_setopt ( $ch, CURLOPT_POST, true );
		curl_setopt ( $ch, CURLOPT_POSTFIELDS, $postData );
		if (substr ( $url, 0, 5 ) == 'https') {
			curl_setopt ( $ch, CURLOPT_SSL_VERIFYPEER, FALSE );
			curl_setopt ( $ch, CURLOPT_SSL_VERIFYHOST, FALSE );
		}
		curl_exec ( $ch );
		curl_close ( $ch );
		return true;
	}

    public static function asyncTestPost($url, $postData, $header = array()) {
        if ($header == null)
            $header = array ();
        if(is_string($postData))
            $header [] = "Content-Length: " . strlen ( $postData );
        $header [] = "Connection: close";
        var_dump($header);
        $ch = curl_init ();
        curl_setopt ( $ch, CURLOPT_URL, $url );
        curl_setopt ( $ch, CURLOPT_HTTPHEADER, $header );
        curl_setopt ($ch, CURLINFO_HEADER_OUT, true);
        curl_setopt ( $ch, CURLOPT_HEADER, 0 );
        curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, true );
        curl_setopt ( $ch, CURLOPT_POST, true );
        curl_setopt ( $ch, CURLOPT_POSTFIELDS, $postData );
        if (substr ( $url, 0, 5 ) == 'https') {
            echo 1;
            curl_setopt ( $ch, CURLOPT_SSL_VERIFYPEER, FALSE );
            curl_setopt ( $ch, CURLOPT_SSL_VERIFYHOST, FALSE );
        }
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);

        $data = curl_exec ( $ch );
        $httpCode = curl_getinfo($ch);
        curl_close ( $ch );
        return $httpCode;
    }

	public static function post($url, $postData, $header = array()) {
		// $header [] = "Content-Type: text/xml; charset=utf-8";
		// $header [] = "Accept: text/html";
		if ($header == null)
			$header = array ();

		if(is_string($postData))
			$header [] = "Content-Length: " . strlen ( $postData );
		$ch = curl_init ();
		curl_setopt ( $ch, CURLOPT_URL, $url );
		curl_setopt ( $ch, CURLOPT_HTTPHEADER, $header );
		curl_setopt ( $ch, CURLOPT_HEADER, 0 );
		curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, true );
		curl_setopt ( $ch, CURLOPT_POST, true );
		curl_setopt ( $ch, CURLOPT_POSTFIELDS, $postData );
		if (substr ( $url, 0, 5 ) == 'https') {
			curl_setopt ( $ch, CURLOPT_SSL_VERIFYPEER, FALSE );
			curl_setopt ( $ch, CURLOPT_SSL_VERIFYHOST, FALSE );
		}
		$data = curl_exec ( $ch );
		$curl_errno = curl_errno ( $ch );
		$curl_error = curl_error ( $ch );
		curl_close ( $ch );
		if ($curl_errno > 0) {
			echo ('Error=> ' . $curl_error);
		}
		return $data;
	}
	public static function curlMulti($urlArr = array(), $dataType = 'text', $dataNum = 0, $killspace = TRUE, $forhtml = TRUE, $timeout = 2, $header = 0, $follow = 1) {
		if (empty ( $urlArr ))
			return array ();
		$res = array (); // 用于保存结果
		                 // $connomains=array_flip(array_flip($connomains));//去除url中的重复项
		$mh = curl_multi_init (); // 创建多curl对象，为了几乎同时执行
		foreach ( $urlArr as $key => $url ) {
			$conn [$url] = curl_init ( $url ); // 若url中含有gb2312汉字，例如FTP时，要在传入url的时候处理一下，这里不用
			curl_setopt ( $conn [$url], CURLOPT_TIMEOUT, $timeout ); // 此时间须根据页面的HTML源码出来的时间，一般是在1s内的，慢的话应该也不会6秒，极慢则是在16秒内
			curl_setopt ( $conn [$url], CURLOPT_HEADER, $header ); // 不返回请求头，只要源码
			curl_setopt ( $conn [$url], CURLOPT_RETURNTRANSFER, 1 ); // 必须为1
			curl_setopt ( $conn [$url], CURLOPT_FOLLOWLOCATION, $follow ); // 如果页面含有自动跳转的代码如301或者302HTTP时，自动拿转向的页面
			curl_multi_add_handle ( $mh, $conn [$url] ); // 关键，一定要放在上面几句之下，将单curl对象赋给多对象
		}
		// 下面一大步的目的是为了减少cpu的无谓负担，暂时不明，来自php.net的建议，几乎是固定用法
		do {
			$mrc = curl_multi_exec ( $mh, $active ); // 当无数据时或请求暂停时，active=true
		} while ( $mrc == CURLM_CALL_MULTI_PERFORM ); // 当正在接受数据时
		while ( $active and $mrc == CURLM_OK ) {
			// 当无数据时或请求暂停时，active=true,为了减少cpu的无谓负担,这一步很难明啊
			if (curl_multi_select ( $mh ) != - 1) {
				do {
					$mrc = curl_multi_exec ( $mh, $active );
				} while ( $mrc == CURLM_CALL_MULTI_PERFORM );
			}
		}
		// ///////////////////////////////////////////////////////////////////////////////////////
		// 下面返回结果
		foreach ( $urlArr as $key => $url ) {
			$cinfo = curl_getinfo ( $conn [$url] ); // 可用于取得一些有用的参数，可以认为是header
			                                        // $url=$cinfo[url];//真实url,有些url
			if ($killspace) {
				// 有点水消耗
				$str = (curl_multi_getcontent ( $conn [$url] ));
				// $str = preg_replace('/\s(?=\s)/', '', $str);//去掉跟随别的挤在一块的空白
				// $str = preg_replace('/[\n\r\t]/', ' ', $str); //最后，去掉非space
				// 的空白，用一个空格代替
				$res [$key] = ($str); // 取得对象源码，并取消换行，节约内存的同时，可以方便作正则处理
			} else {
				$res [$key] = curl_multi_getcontent ( $conn [$url] );
			}
			if (! $forhtml) {
				// 节约内存
				$res [$key] = NULL;
			}
			/*
			 *
			 * 下面这一段放一些高消耗的程序代码，用来处理HTML，我保留的一句=NULL是要提醒，及时清空对象释放内存，此程序在并发过程中如果源码太大，内在消耗严重
			 * //事实上，这里应该做一个callback函数或者你应该将你的逻辑直接放到这里来，我为了程序可重复，没这么做
			 * preg_match_all($preg,$res[$i],$matchlinks); $res[$i]=NULL;
			 */
			curl_close ( $conn [$url] ); // 关闭所有对象
			curl_multi_remove_handle ( $mh, $conn [$url] ); // 用完马上释放资源
		}
		curl_multi_close ( $mh );
		$mh = NULL;
		$conn = NULL;
		$connomains = NULL;
		if ($dataType == 'json') {
			foreach ( $res as $key => $value ) {
				$res [$key] = json_decode ( $value );
			}
		}
		if ($dataNum > 0) {
			foreach ( $res as $key => $value ) {
				if (is_array ( $value )) {
					foreach ( $value as $i => $val ) {
						if ($i >= $dataNum)
							unset ( $value [$i] );
					}
				}
				$res [$key] = $value;
				unset ( $value );
			}
		}
		return $res;
	} // cmi
	public static function curlMulti0($urlArr = array())

	{
		if (empty ( $urlArr ))
			return array ();
		// $result 返回结果数组 $ch cURL资源数组
		$result = $ch = array ();
		// $mh cURL批处理句柄
		$mh = false;
		// cURL码
		$mrc = null;
		// 默认cURL超时设置
		$timeout = 1;
		// 判断操作是否仍在执行
		$active = null;
		// 创建批处理cURL句柄
		$mh = curl_multi_init ();
		foreach ( $urlArr as $key => $value ) {
			// 创建cURL资源
			// $ch [$key] = curl_minit ();
			$time = isset ( $value ['timeout'] ) ? $value ['timeout'] : $timeout;
			if (isset ( $value ['method'] ) && $value ['method'] == 'post') {
				$options = array (
						CURLOPT_URL => $value ['url'],
						CURLOPT_HEADER => false,
						CURLOPT_RETURNTRANSFER => true,
						CURLOPT_TIMEOUT => $time,
						CURLOPT_POST => 1,
						CURLOPT_POSTFIELDS => $value ['data']
				);
			} else {
				$options = array (
						CURLOPT_URL => $value ['url'],
						CURLOPT_HEADER => false,
						CURLOPT_RETURNTRANSFER => true,
						CURLOPT_TIMEOUT => $time
				);
			}
			curl_setopt_array ( $ch [$key], $options );
			curl_multi_add_handle ( $mh, $ch [$key] );
		}
		// 执行批处理句柄
		do {
			$mrc = curl_multi_exec ( $mh, $active );
		} while ( $mrc == CURLM_CALL_MULTI_PERFORM );
		while ( $active && $mrc == CURLM_OK ) {
			if (curl_multi_select ( $mh, 0.5 ) != - 1) {
				do {
					$mrc = curl_multi_exec ( $mh, $active );
				} while ( $mrc == CURLM_CALL_MULTI_PERFORM );
			}
		}
		if ($mrc != CURLM_OK) {
			exit ( "CURL Data Error" );
		}
		foreach ( $urlArr as $key => $value ) {
			if (curl_error ( $ch [$key] ) == '') {
				$result [$key] = curl_multi_getcontent ( $ch [$key] );
			} else {
				exit ( "curl error" );
			}
			curl_multi_remove_handle ( $mh, $ch [$key] );
			curl_close ( $ch [$key] );
		}
		curl_multi_close ( $mh );
		return $result;
	}

    /**
     * 向Rest服务器发送请求
     * @param string $http_type http类型,比如https
     * @param string $method 请求方式，比如POST
     * @param string $url 请求的url
     * @return string $data 请求的数据
     */
    public static function http_req($http_type, $method, $url, $data)
    {
        $ch = curl_init();
        if (strstr($http_type, 'https'))
        {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        }

        if ($method == 'post')
        {
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        } else
        {
            $url = $url . '?' . $data;
        }
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT,15);//超时时间

        try
        {
            $ret=curl_exec($ch);
        }catch(Exception $e)
        {
            curl_close($ch);
            return json_encode(array('ret'=>0,'msg'=>'failure'));
        }
        curl_close($ch);
        return $ret;
    }
}