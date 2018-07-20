<?php
/**
 * Created by PhpStorm.
 * User: chenhc
 * Date: 2018/3/29
 * Time: 下午3:29
 */

namespace Addons\SSO;

class Sso{

    protected $_curl_url = 'http://sso.xiaomawang.com/sso_auth.php';    //sso验证的地址
    protected $_curl_login = 'http://sso.xiaomawang.com/sso_login.php';  //sso登录的地址
    protected $_curl_login_out = 'http://sso.xiaomawang.com/sso_loginout.php';   //sso退出的地址

    /*
     * 通知验证中心
     */
    public function auth(){
        $token = $_COOKIE["token_key"];
        if($token){
            $this->_curl_url = $this->_curl_url.'?token='.$token;
        }
        $result = $this->get($this->_curl_url,'json');
        $result = object_to_array($result);
        setcookie('token_key',$result['token_key'],null,'/');
        return $result;
    }

    /*
     * 登录
     */
    public function login(){
        $token = $_COOKIE["token_key"];
        $phone = $_REQUEST['phone'];
        $password = $_REQUEST['password'];
        
        $result = $this->post($this->_curl_login.'?token='.$token,array('phone'=>$phone,'password'=>$password));
        return $result;
    }

    public function loginout(){
        $token = $_COOKIE["token_key"];
        $result = $this->get($this->_curl_login_out.'?token='.$token);
        return $result;
    }

    /*
     * 不同步的根据token获取对应的信息
     */
    public function getTokenInfo($token){
        $this->_curl_url = $this->_curl_url.'?token='.$token;
        $result = $this->get($this->_curl_url,'json');
        $result = object_to_array($result);
        return $result;
    }

    /**
     * 模拟GET请求
     *
     * @param string $url
     * @param string $data_type
     *
     * @return mixed Examples:
     *         ```
     *         HttpCurl::get('http://api.example.com/?a=123&b=456', 'json');
     *         ```
     */
    static public function get($url, $data_type = 'text') {
        $cl = curl_init ();
        if (stripos ( $url, 'https://' ) !== FALSE) {
            curl_setopt ( $cl, CURLOPT_SSL_VERIFYPEER, FALSE );
            curl_setopt ( $cl, CURLOPT_SSL_VERIFYHOST, FALSE );
            curl_setopt ( $cl, CURLOPT_SSLVERSION, 1 );
        }
        curl_setopt ( $cl, CURLOPT_URL, $url );
        curl_setopt ( $cl, CURLOPT_RETURNTRANSFER, 1 );
        $content = curl_exec ( $cl );
        $status = curl_getinfo ( $cl );
        curl_close ( $cl );
        if (isset ( $status ['http_code'] ) && $status ['http_code'] == 200) {
            if ($data_type == 'json') {
                $content = json_decode ( $content );
            }
            return $content;
        } else {
            return FALSE;
        }
    }

    /**
     * 模拟POST请求
     *
     * @param string $url
     * @param array $fields
     * @param string $data_type
     *
     * @return mixed Examples:
     *         ```
     *         HttpCurl::post('http://api.example.com/?a=123', array('abc'=>'123', 'efg'=>'567'), 'json');
     *         HttpCurl::post('http://api.example.com/', '这是post原始内容', 'json');
     *         文件post上传
     *         HttpCurl::post('http://api.example.com/', array('abc'=>'123', 'file1'=>'@/data/1.jpg'), 'json');
     *         ```
     */
    static public function post($url, $fields, $data_type = 'text') {
        $cl = curl_init ();
        if (stripos ( $url, 'https://' ) !== FALSE) {
            curl_setopt ( $cl, CURLOPT_SSL_VERIFYPEER, FALSE );
            curl_setopt ( $cl, CURLOPT_SSL_VERIFYHOST, FALSE );
            curl_setopt ( $cl, CURLOPT_SSLVERSION, 1 );
        }
        curl_setopt ( $cl, CURLOPT_URL, $url );
        curl_setopt ( $cl, CURLOPT_RETURNTRANSFER, 1 );
        curl_setopt ( $cl, CURLOPT_POST, true );
        curl_setopt ( $cl, CURLOPT_POSTFIELDS, $fields );
        $content = curl_exec ( $cl );
        $status = curl_getinfo ( $cl );
        curl_close ( $cl );
        if (isset ( $status ['http_code'] ) && $status ['http_code'] == 200) {
            if ($data_type == 'json') {
                $content = json_decode ( $content );
            }
            return $content;
        } else {
            return FALSE;
        }
    }
}